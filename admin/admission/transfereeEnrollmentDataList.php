<?php

// include('../../includes/config.php');
include('../../includes/config.php');
include('../../enrollment/classes/StudentEnroll.php');

    $enroll = new StudentEnroll($con);

    $student_id = $_GET['id'];

    $enrollment_id = null;

    if(isset($_SESSION['enrollment_id'])){
        $enrollment_id = $_SESSION['enrollment_id'];
    }

    // echo $enrollment_id;

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
        WHERE 1  ".$searchQuery."
        AND t2.active='yes'

        ORDER BY ".$columnName." ".$columnSortOrder." 
        LIMIT ".$row.",".$rowperpage;
    $stmt = $con->prepare($empQuery);
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

        $add_click = "add_non_transferee($student_id, $subject_id,
                $course_level, $current_school_year_id, \"$subject_code\", $enrollment_id)";

        $add_credit_click = "add_credit_transferee($student_id, $subject_id,
            $course_level, $current_school_year_id,
            $course_id, \"$subject_title\", \"$subject_code\", $enrollment_id)";

        $data[] = array(
            "subject_id"=> $row['subject_id'],
            "subject_code"=> $row['subject_code'],
            "subject_title"=> $row['subject_title'],
            "unit"=> $row['unit'],
            "subject_type"=> $row['subject_type'],
            "semester"=> $row['semester'],
            "actions2" => "
                <button type='button' onclick='$add_click' class='btn btn-outline-primary'>Add</button>
            ", 
            "actions3" => "
                <button type='button' onclick='$add_credit_click' class='btn btn-outline-success'>Credit</button>
            ",
        );
    }

    ## Response
    $response = array(
        "draw" => intval($draw),
        "iTotalRecords" => $totalRecords,
        "iTotalDisplayRecords" => $totalRecordwithFilter,
        "aaData" => $data
    );


    echo json_encode($response);


?>
