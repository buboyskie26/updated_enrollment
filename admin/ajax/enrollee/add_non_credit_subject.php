<?php 

    require_once("../../../includes/config.php");

    require_once("../../../enrollment/classes/StudentEnroll.php");

     
    if(isset($_POST['student_id']) 
        && isset($_POST['subject_id'])
        && isset($_POST['course_level'])
        && isset($_POST['school_year_id'])
        && isset($_POST['enrollment_id'])
        
        ){

        $student_id = $_POST['student_id'];
        $subject_id = $_POST['subject_id'];
        $course_level = $_POST['course_level'];
        $school_year_id = $_POST['school_year_id'];
        $enrollment_id = $_POST['enrollment_id'];

        $insert = $con->prepare("INSERT INTO student_subject
            (student_id, subject_id, course_level, school_year_id, is_final, enrollment_id)
            VALUES (:student_id, :subject_id, :course_level, :school_year_id, :is_final, :enrollment_id)");
        
        $insert->bindValue(":student_id", $student_id);
        $insert->bindValue(":subject_id", $subject_id);
        $insert->bindValue(":course_level", $course_level);
        $insert->bindValue(":school_year_id", $school_year_id);
        $insert->bindValue(":is_final", 0);
        $insert->bindValue(":enrollment_id", $enrollment_id);
        
        if($insert->execute()){
            echo "Successfullu added Subject Id $subject_id";
        }


    }

?>