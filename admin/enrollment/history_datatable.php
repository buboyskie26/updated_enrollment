<?php

include('../../includes/config.php');

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
    $searchQuery = " AND (student_id LIKE '%".$searchValue."%' OR 
        firstname LIKE '%".$searchValue."%' OR 
        course_level LIKE '%".$searchValue."%' OR 
        lastname LIKE '%".$searchValue."%' ) ";
}

## Total number of records without filtering
$stmt = $con->prepare("SELECT COUNT(*) AS allcount FROM student");
$stmt->execute();
$records = $stmt->fetch(PDO::FETCH_ASSOC);
$totalRecords = $records['allcount'];

## Total number of records with filtering
$stmt = $con->prepare("SELECT COUNT(*) AS allcount FROM student WHERE 1 ".$searchQuery);
$stmt->execute();

$records = $stmt->fetch(PDO::FETCH_ASSOC);
$totalRecordwithFilter = $records['allcount'];


$enrolled  = "enrolled";

$query = $con->prepare("SELECT 
    t1.course_id,

    t2.*,

    t3.program_section,

    t4.term, t4.period
    FROM enrollment as t1

    LEFT JOIN student as t2 ON t2.student_id = t1.student_id
    LEFT JOIN course as t3 ON t3.course_id = t1.course_id
    LEFT JOIN school_year as t4 ON t4.school_year_id = t1.school_year_id

    WHERE t1.enrollment_status=:enrolled");

$query->bindValue(":enrolled", $enrolled);
$query->execute();



## Fetch records
$empQuery = "SELECT 

    t1.course_id, t2.*, t3.program_section, t4.term, t4.period, t3.course_level
 
    FROM enrollment as t1

    LEFT JOIN student as t2 ON t2.student_id = t1.student_id
    LEFT JOIN course as t3 ON t3.course_id = t1.course_id
    LEFT JOIN school_year as t4 ON t4.school_year_id = t1.school_year_id

    -- WHERE t1.active=1) as subquery
    WHERE 1  ".$searchQuery."
    AND t1.enrollment_status='enrolled'

    ORDER BY ".$columnName." ".$columnSortOrder." 
    LIMIT ".$row.",".$rowperpage;


// $empQuery = "SELECT t1.*, t2.program_section
 
//     FROM student  as t1

//     LEFT JOIN course as t2 ON t2.course_id = t1.course_id

//     -- WHERE t1.active=1) as subquery
//     WHERE 1  ".$searchQuery."
//     ORDER BY ".$columnName." ".$columnSortOrder." 
//     LIMIT ".$row.",".$rowperpage;


$stmt = $con->prepare($empQuery);
$stmt->execute();
$data = array();

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    
    // $program_section = $row['program_section'];
    $student_username = $row['username'];
    $program_section = $row['program_section'];
    $is_tertiary = $row['is_tertiary'];

    $department = $is_tertiary == 1 ? "Tertiary" : "SHS";

    $data[] = array(

        "student_id"=> $row['student_id'],
        "student_name"=> $row['firstname'] ." ". $row['lastname'],
        "course_level"=> $row['course_level'],
        "section"=> $row['program_section'],
        "period"=> $row['period'],
        "term"=> $row['term'],
        "department"=> $department
    );
     // "actions"=> "
        //     <form method='POST'>
        //         <input name='student_username' type='hidden' value='$student_username'>
        //         <button type='submit' name='reset_student_password' class='btn btn-sm btn-primary'>Reset Password</button>
        //     </form>
        // "
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
