<?php 

   require_once("../../../includes/config.php");

    require_once("../../../enrollment/classes/StudentEnroll.php");


    if(isset($_POST['courseTertiaryId'])
        && isset($_POST['student_id'])){

        $courseTertiaryId = $_POST['courseTertiaryId'];
        $student_id = $_POST['student_id'];

        // $student_id = 0;
        // echo $courseTertiaryId;
        // echo $student_id;

         


    }
?>