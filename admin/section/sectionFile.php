<?php


include('../../includes/config.php');
include('../../enrollment/classes/Section.php');

$section = new Section($con, null);


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
    $searchQuery = " AND (program_section LIKE '%".$searchValue."%' OR 
        program_id LIKE '%".$searchValue."%' OR 
        firstname LIKE '%" . $searchValue . "%' OR 
        school_year_term LIKE '%".$searchValue."%' ) ";
}

## Total number of records without filtering
$stmt = $con->prepare("SELECT COUNT(*) AS allcount FROM course");
$stmt->execute();
$records = $stmt->fetch(PDO::FETCH_ASSOC);
$totalRecords = $records['allcount'];


## Total number of records with filtering
$stmt = $con->prepare("SELECT COUNT(*) AS allcount
    FROM course as t1

    LEFT JOIN teacher as t2 ON t2.teacher_id = t1.adviser_teacher_id

    WHERE 1 ".$searchQuery);

$stmt->execute();
$records = $stmt->fetch(PDO::FETCH_ASSOC);
$totalRecordwithFilter = $records['allcount'];

## Fetch records

$empQuery = "SELECT * FROM 

        (SELECT t1.*,
        t2.firstname, t2.lastname

        FROM course as t1
        LEFT JOIN teacher as t2 ON t2.teacher_id = t1.adviser_teacher_id
        WHERE t1.active='yes') as subquery
        WHERE 1";

if (!empty($searchQuery)) {
    $empQuery .= " " . $searchQuery;
}

$empQuery .= " ORDER BY " . $columnName . " " . $columnSortOrder . 
             " LIMIT " . $row . "," . $rowperpage;

$stmt = $con->prepare($empQuery);
$stmt->execute();
$data = array();

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

    $course_id = $row['course_id'];
    $capacity = $row['capacity'];

    $room = $row['room'] == "" ? "*N/A" : $row['room'];

    $teacher_name = $row['firstname'];
    // echo $teacher_name;
    $teacher_name = $row['firstname'] . " " . $row['lastname'];
    $teacher_name = $row['firstname'] == "" ? "N/A" : $teacher_name;

    $editUrl = directoryPath . "edit.php?section_id=$course_id";
    $totalStudent = $section->GetTotalNumberOfStudentInSection($course_id);

    $data[] = array(
        "program_section"=>$row['program_section'],
        "total_student"=> $totalStudent,
        "capacity"=> $capacity,
        "room"=> $room,
        "school_year_term"=>$row['school_year_term'],
        "advisery_name"=> $teacher_name,
        "actions"=> "
                    <a href='$editUrl'>
                        <button class='btn btn-primary btn-sm'>Edit</button>
                    </a>
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
