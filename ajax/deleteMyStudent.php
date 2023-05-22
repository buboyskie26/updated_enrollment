<?php 


    require_once('../includes/config.php');

    if(isset($_GET['student_id'])){
        $student_id = $_GET['student_id'];


        // $query = $con->prepare("DELETE FROM teacher_course_student
        //     WHERE student_id=:student_id");

        $query = $con->prepare("UPDATE teacher_course_student
            SET deleted=:deleted
            WHERE student_id=:student_id");
        
        $query->bindValue(":student_id", $student_id);
        $query->bindValue(":deleted", "yes");
        $query->execute();

    }

?>