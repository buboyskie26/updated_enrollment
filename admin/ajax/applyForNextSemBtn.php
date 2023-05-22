<?php 

    
    require_once("../../includes/config.php");

    require_once("../../enrollment/classes/StudentEnroll.php");


    if(isset($_POST['student_username'])){
        $student_username = $_POST['student_username'];

        $enrol = new StudentEnroll($con);

        $student_id = $enrol->GetStudentId($student_username);

        echo $student_username;
    }
    


?>