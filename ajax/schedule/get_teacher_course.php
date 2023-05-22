<?php

    require_once("../../includes/config.php");

    if(isset($_POST['teacher'])){

        // Retrieve courses for selected teacher
        $teacher_id = $_POST['teacher'];
 
        $sql = $con->prepare("SELECT * FROM teacher_course
            WHERE teacher_id=:teacher_id
            
            ");

        $sql->bindValue("teacher_id", $teacher_id);
        $sql->execute();

        $courses = array();

        $arr1 = [];
        $arr2 = [];
        if($sql->rowCount() > 0){

            $isValid = false;

            while($row = $sql->fetch(PDO::FETCH_ASSOC)) {

                // 6 7 16
                $teacher_course_id = $row['teacher_course_id'];
                    // echo $teacher_course_id . " ";

                array_push($arr1, $teacher_course_id);

                $sql2 = $con->prepare("SELECT teacher_course_id FROM teacher_schedule
                    WHERE teacher_course_id=:teacher_course_id
                    AND teacher_id=:teacher_id
                    LIMIT 1
                    ");

                $sql2->bindValue("teacher_course_id", $teacher_course_id);
                $sql2->bindValue("teacher_id", $teacher_id);
                $sql2->execute();

                if($sql2->rowCount() > 0){
                    $teacher_course_id_v2 = $sql2->fetchColumn();
                    array_push($arr2, $teacher_course_id_v2);

                    // echo $teacher_course_id_v2 . "<br>";
                    
                    // if($teacher_course_id != $teacher_course_id_v2){
                    //     $remaining_teacher_course = array(
                    //         'teacher_course_id' => $teacher_course_id
                    //     );

                    //     array_push($courses, $remaining_teacher_course);
                    //     // echo "innnnn";
                    // }else{
                    //     // echo "nothinng.";
                    // }
                    
                }
                
                $courses[] = array(
                    'teacher_course_id' => $row['teacher_course_id']
                );
                
            }

            $arr_new = array_diff($arr1, $arr2);
            $arr_new = array_values($arr_new);

            // print_r($courses);
            echo json_encode($arr_new);

            // echo "have";
        }else{
            // echo "none";
        }
        // echo $teacher_id;
    }else{
        echo "nothing";
    }
 


?>