<?php 

    require_once("../../includes/config.php");

    require_once("../../enrollment/classes/StudentEnroll.php");

    // echo "yehey";

    if(isset($_POST['subject_id']) 
     && isset($_POST['course_id']) 
    ){

        $subject_id = $_POST['subject_id'];
        $course_id = $_POST['course_id'];

        $arr = [];

        $query = $con->prepare("SELECT t2.subject_title, t1.student_id, t1.subject_id,

            t3.firstname,
            t4.remarks,
            t5.course_level,
            t6.period

            FROM student_subject as t1

            INNER JOIN subject as t2 ON t1.subject_id = t2.subject_id

            LEFT JOIN student as t3 ON t1.student_id = t3.student_id
            LEFT JOIN student_subject_grade as t4 ON t1.student_subject_id = t4.student_subject_id
            LEFT JOIN course as t5 ON t2.course_id = t5.course_id
            LEFT JOIN school_year as t6 ON t1.school_year_id = t6.school_year_id


            WHERE t1.subject_id=:subject_id
            AND t2.course_id=:course_id
           
            ");
        $query->bindValue(":subject_id", $subject_id);
        $query->bindValue(":course_id", $course_id);
        $query->execute();

        if($query->rowCount() > 0){

            while($row = $query->fetch(PDO::FETCH_ASSOC)) {

                $remarks = "";

                if($row['remarks'] != ''){
                    $remarks = $row['remarks'];
                }else{
                    $remarks = "N/A";
                }   

                // array_push($arr, $row['subject_title']);
                // array_push($arr, $row['student_id']);
                // array_push($arr, $row['firstname']);
                // array_push($arr, $remarks);

                $data[] = array(
                    'subject_id' => $row['subject_id'],
                    'subject_title' => $row['subject_title'],
                    'student_id' => $row['student_id'],
                    'firstname' => $row['firstname'],
                    'period' => $row['period'],
                    'course_level' => $row['course_level'],
                    'remarks' => $remarks,
                );
            }
        }
        
        if(empty($data)){
            echo json_encode([]);
        }else{
            echo json_encode($data);
        }
    }

    

?>