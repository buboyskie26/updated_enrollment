<?php

    require_once("../../includes/config.php");


    if(isset($_POST['teacher'])){
        // Retrieve courses for selected teacher
        $teacher_id = $_POST['teacher'];

        // echo $teacher_id;

        $sql = $con->prepare("SELECT * FROM teacher_course
            WHERE teacher_id=:teacher_id");

        $sql->bindValue("teacher_id", $teacher_id);
        $sql->execute();

        $courses = array();
        if($sql->rowCount() > 0){

            while($row = $sql->fetch(PDO::FETCH_ASSOC)) {

                $courses[] = array(
                    'teacher_course_id' => $row['teacher_course_id']
                );
            }
            echo json_encode($courses);

            // echo "have";
        }else{
            // echo "none";
        }
        // echo $teacher_id;
    }else{
        echo "nothing";
    }


    // $sql = "SELECT course_id, course_name FROM courses WHERE 
    //     teacher='$teacher'";
    
    // $result = $conn->query($sql);

    // $courses = array();

    // if ($result->num_rows > 0) {

    //     while($row = $result->fetch_assoc()) {

    //         $courses[] = array(
    //             'course_id' => $row['course_id'], 
    //             'course_name' => $row['course_name']
    //         );
    // }


?>