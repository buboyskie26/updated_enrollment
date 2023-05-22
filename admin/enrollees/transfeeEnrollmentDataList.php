<?php

// include('../../includes/config.php');
include('../../includes/config.php');
include('../../enrollment/classes/StudentEnroll.php');

    $enroll = new StudentEnroll($con);


    $student_id = $_GET['id'];

    $school_year_obj = $enroll->GetActiveSchoolYearAndSemester();

    $current_school_year_id = $school_year_obj['school_year_id'];

    // $student_program_section = "";
    // $student_course_id = 0;
    // $student_id = null;
    // $student_course_level = null;

    // $enrollment_id = null;

    // $student = $con->prepare("SELECT username, student_id, course_level 
        
    //     FROM student
    //     WHERE firstname=:firstname
    //     AND lastname=:lastname
    //     AND middle_name=:middle_name
        
    //     ");
    // $student->bindValue(":firstname", $firstname);
    // $student->bindValue(":lastname", $lastname);
    // $student->bindValue(":middle_name", $middle_name);
    // $student->execute();

    // $enrollment = new Enrollment($con, $enroll);

    // if($student->rowCount() > 0){

    //     $row_student = $student->fetch(PDO::FETCH_ASSOC);

    //     $student_username = $row_student['username'];
    //     $student_id = $row_student['student_id'];
    //     $student_course_level = $row_student['course_level'];

    //     $student_course_id = $enroll->GetStudentCourseId($student_username);
    //     $student_program_section = $enroll->GetStudentProgramSection($student_course_id);

    //     $enrollment_id = $enrollment->GetEnrollmentId($student_id, $student_course_id, $current_school_year_id);

    // } 


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
    $course_id = $row['course_id'];
    $subject_title = $row['subject_title'];
    $subject_code = $row['subject_code'];
    $subject_type = $row['subject_type'];
    $semester = $row['semester'];
    $course_level = $row['course_level'];

    $add_click = "add_transferee($student_id, $subject_id,
            $course_level, $current_school_year_id)";

    $add_credit_click = "add_credit_transferee($student_id, $subject_id,
        $course_level, $current_school_year_id,
        $course_id, \"$subject_title\")";

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

        // "actions1"=> "<a href='registrar_course_subject.php?id=".$row['subject_id']."'>
        //                 <button class='btn btn-sm btn-primary'>Subjects</button>
        //             </a>"
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
