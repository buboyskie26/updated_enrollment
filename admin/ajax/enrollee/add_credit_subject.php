<?php 

    require_once("../../../includes/config.php");

    require_once("../../../enrollment/classes/StudentEnroll.php");

     
    if(isset($_POST['student_id']) 
        && isset($_POST['subject_id'])
        && isset($_POST['course_level'])
        && isset($_POST['school_year_id'])
        && isset($_POST['course_id'])
        && isset($_POST['subject_title'])
        && isset($_POST['enrollment_id'])
        
        ){

        $student_id = $_POST['student_id'];
        $subject_id = $_POST['subject_id'];
        $course_level = $_POST['course_level'];
        $school_year_id = $_POST['school_year_id'];
        $course_id = $_POST['course_id'];
        $subject_title = $_POST['subject_title'];
        $enrollment_id = $_POST['enrollment_id'];


        $insert = $con->prepare("INSERT INTO student_subject
            (student_id, subject_id, course_level,
                school_year_id, is_final, is_transferee, enrollment_id)
            VALUES (:student_id, :subject_id, :course_level,
                :school_year_id, :is_final, :is_transferee, :enrollment_id)");
        
        $insert->bindValue(":student_id", $student_id);
        $insert->bindValue(":subject_id", $subject_id);
        $insert->bindValue(":course_level", $course_level);
        $insert->bindValue(":school_year_id", $school_year_id);
        $insert->bindValue(":is_final", 0);
        $insert->bindValue(":is_transferee", "yes");
        $insert->bindValue(":enrollment_id", $enrollment_id);
        
        if($insert->execute()){
            // echo "success";

            $student_subject_id = $con->lastInsertId();

            $mark_as_passed = $con->prepare("INSERT INTO student_subject_grade
                (student_id, subject_id, student_subject_id,
                course_id, subject_title, remarks, is_transferee)
                VALUES (:student_id, :subject_id, :student_subject_id,
                    :course_id, :subject_title, :remarks, :is_transferee)");
            
            $mark_as_passed->bindValue(":student_id", $student_id);
            $mark_as_passed->bindValue(":subject_id", $subject_id);
            $mark_as_passed->bindValue(":student_subject_id", $student_subject_id);
            $mark_as_passed->bindValue(":course_id", $course_id);
            $mark_as_passed->bindValue(":subject_title", $subject_title);
            $mark_as_passed->bindValue(":remarks", "Passed");
            $mark_as_passed->bindValue(":is_transferee", "yes");

            if($mark_as_passed->execute()){

                echo "Successfully credited Subject Id $subject_id";
            }
        }


    }

?>