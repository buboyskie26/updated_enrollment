<?php

// include('../../includes/config.php');
    // include('../admin_enrollment_header.php');
    // include('../../includes/config.php');

## Read value
// $draw = $_POST['draw'];
// $row = $_POST['start'];
// $rowperpage = $_POST['length']; // Rows display per page
// $columnIndex = $_POST['order'][0]['column']; // Column index
// $columnName = $_POST['columns'][$columnIndex]['data']; // Column name
// $columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
// $searchValue = mysqli_real_escape_string($con, $_POST['search']['value']); // Search value

// ## Search 
// $searchQuery = " ";
// if($searchValue != ''){
// 	$searchQuery = " and (emp_name like '%".$searchValue."%' or 
//         email like '%".$searchValue."%' or 
//         city like'%".$searchValue."%' ) ";
// }

// ## Total number of records without filtering
// $sel = mysqli_query($con,"select count(*) as allcount from employee");
// $records = mysqli_fetch_assoc($sel);
// $totalRecords = $records['allcount'];

// ## Total number of records with filtering
// $sel = mysqli_query($con,"select count(*) as allcount from employee WHERE 1 ".$searchQuery);
// $records = mysqli_fetch_assoc($sel);
// $totalRecordwithFilter = $records['allcount'];

// ## Fetch records
// $empQuery = "select * from employee WHERE 1 ".$searchQuery." order by ".$columnName." ".$columnSortOrder." limit ".$row.",".$rowperpage;
// $empRecords = mysqli_query($con, $empQuery);
// $data = array();

// while ($row = mysqli_fetch_assoc($empRecords)) {
//     $data[] = array(
//     		"emp_name"=>$row['emp_name'],
//     		"email"=>$row['email'],
//     		"gender"=>$row['gender'],
//     		"salary"=>$row['salary'],
//     		"city"=>$row['city']
//     	);
// }

// ## Response
// $response = array(
//     "draw" => intval($draw),
//     "iTotalRecords" => $totalRecords,
//     "iTotalDisplayRecords" => $totalRecordwithFilter,
//     "aaData" => $data
// );


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
    $searchQuery = " AND (program_name LIKE '%".$searchValue."%' OR 
        department_id LIKE '%".$searchValue."%' OR 
        dean LIKE '%".$searchValue."%' ) ";
}

## Total number of records without filtering
$stmt = $con->prepare("SELECT COUNT(*) AS allcount FROM program");
$stmt->execute();
$records = $stmt->fetch(PDO::FETCH_ASSOC);
$totalRecords = $records['allcount'];

## Total number of records with filtering
$stmt = $con->prepare("SELECT COUNT(*) AS allcount FROM program WHERE 1 ".$searchQuery);
$stmt->execute();
$records = $stmt->fetch(PDO::FETCH_ASSOC);
$totalRecordwithFilter = $records['allcount'];

## Fetch records
$empQuery = "SELECT * FROM program 
    WHERE 1  ".$searchQuery."
    AND department_id=4
    ORDER BY ".$columnName." ".$columnSortOrder." 
    LIMIT ".$row.",".$rowperpage;
$stmt = $con->prepare($empQuery);
$stmt->execute();
$data = array();

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $data[] = array(
        "program_name"=>$row['program_name'],
        "department_id"=>$row['department_id'],
        "dean"=>$row['dean'],
        "actions"=> "<a href='registrar_course_subject.php?id=".$row['program_id']."'>
                        <button class='btn btn-sm btn-primary'>Subjects</button>
                    </a>"
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
