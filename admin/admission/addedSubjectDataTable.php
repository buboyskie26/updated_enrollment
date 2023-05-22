<?php

// include('../../includes/config.php');
include('../../includes/config.php');
include('../../enrollment/classes/StudentEnroll.php');
include('../../enrollment/classes/StudentSubject.php');

    $enroll = new StudentEnroll($con);

    if(isset($_GET['id'])
    && isset($_GET['level'])
    && isset($_GET['semester'])
    && isset($_GET['st_id'])
    && isset($_GET['e_id'])
    ){

    $course_id = $_GET['id'];
    $course_level = $_GET['level'];
    $semester = $_GET['semester'];
    $student_id = $_GET['st_id'];
    $enrollment_id = $_GET['e_id'];
 

    $studentSubject = new StudentSubject($con);

    $school_year_obj = $enroll->GetActiveSchoolYearAndSemester();

    $current_school_year_id = $school_year_obj['school_year_id'];


    $draw = $_POST['draw'];
    $row = $_POST['start'];
    $rowperpage = $_POST['length'];
    $columnIndex = $_POST['order'][0]['column'];
    $columnName = $_POST['columns'][$columnIndex]['data'];
    $columnSortOrder = $_POST['order'][0]['dir'];
    $searchValue = $_POST['search']['value'];

    ## Search
    $searchQuery = " ";
    if($searchValue != ''){
        $searchQuery = " AND (subject_id LIKE '%".$searchValue."%' OR 
            subject_code LIKE '%".$searchValue."%' OR 
            subject_title LIKE '%".$searchValue."%' OR 
            semester LIKE '%".$searchValue."%' OR 
            unit LIKE '%".$searchValue."%' ) ";
    }

    ## Total number of records without filtering
    $stmt = $con->prepare("SELECT COUNT(*) AS allcount FROM subject");
    $stmt->execute();
    $records = $stmt->fetch(PDO::FETCH_ASSOC);
    $totalRecords = $records['allcount'];

    ## Total number of records with filtering
    $stmt = $con->prepare("SELECT COUNT(*) AS allcount FROM subject
        
        WHERE 1 ".$searchQuery
    );

    $stmt->execute();
    $records = $stmt->fetch(PDO::FETCH_ASSOC);
    $totalRecordwithFilter = $records['allcount'];

    ## Fetch records
    $empQuery = "SELECT * FROM subject as t1
        INNER JOIN course as t2 ON t2.course_id = t1.course_id

        WHERE t1.course_id !=:course_id
        AND t1.course_level=:course_level
        AND t1.semester=:semester

        AND 1  ".$searchQuery."
        AND t2.active='yes'

        ORDER BY ".$columnName." ".$columnSortOrder." 
        LIMIT ".$row.",".$rowperpage;

    $stmt = $con->prepare($empQuery);

    $stmt->bindParam(':course_id', $course_id);
    $stmt->bindParam(':course_level', $course_level);
    $stmt->bindParam(':semester', $semester);

    $stmt->execute();
    $data = array();

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {


        $unit = $row['unit'];
        $subject_id = $row['subject_id'];
        $subject_code = $row['subject_code'];
        $course_id = $row['course_id'];
        $subject_title = $row['subject_title'];
        $subject_code = $row['subject_code'];
        $subject_type = $row['subject_type'];
        $semester = $row['semester'];
        $course_level = $row['course_level'];

        $student_student_subject_id = 0;

        $get_student_subject = $studentSubject->
            GetNonFinalizedStudentSubject($student_id, $subject_id,
            $enrollment_id, $current_school_year_id);

        
        if(count($get_student_subject) > 0){
            $student_student_subject_id = $get_student_subject['subject_id'];
            // echo $student_student_subject_id;
        }

        $data[] = array(
            "subject_id"=> $row['subject_id'],
            "program_section"=> $row['program_section'],
            "subject_code"=> $row['subject_code'],
            "subject_title"=> $row['subject_title'],
            "unit"=> $row['unit'],
            "pre_requisite"=> $row['subject_title'],
            "subject_type"=> $row['subject_type'],
            "actions1" => "
                <input name='normal_selected_subject[]' 
                    value='" . $subject_id . "'
                    type='checkbox'" . ($student_student_subject_id == $subject_id ? " checked" : "") . ">
            "
            // "actions1" => "
            //     // <button type='button' onclick='' class='btn btn-outline-primary'>Add</button>
            // "
        );
    }
    $response = array(
        "draw" => intval($draw),
        "iTotalRecords" => $totalRecords,
        "iTotalDisplayRecords" => $totalRecordwithFilter,
        "aaData" => $data
    );

    echo json_encode($response);
    }
    
?>
