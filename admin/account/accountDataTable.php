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

## Fetch records
$empQuery = "SELECT t1.*

    --  t2.program_section 
 
    FROM student  as t1

    -- LEFT JOIN course as t2 ON t2.course_id = t1.course_id
    -- WHERE t1.active=1) as subquery
    WHERE 1  ".$searchQuery."
    ORDER BY ".$columnName." ".$columnSortOrder." 
    LIMIT ".$row.",".$rowperpage;
$stmt = $con->prepare($empQuery);
$stmt->execute();
$data = array();

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    
    // $program_section = $row['program_section'];
    $student_username = $row['username'];

    $data[] = array(
        "student_id"=> $row['student_id'],
        "student_name"=> $row['firstname'] ." ". $row['lastname'],
        "course_level"=> $row['course_level'],
        // "section"=> $row['program_section']
        "actions"=> "
            <form method='POST'>
                <input name='student_username' type='hidden' value='$student_username'>
                <button type='submit' name='reset_student_password' class='btn btn-sm btn-primary'>Reset Password</button>
            </form>
        "
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
