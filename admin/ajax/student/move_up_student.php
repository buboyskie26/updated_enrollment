<?php 

    require_once("../../../includes/config.php");

    require_once("../../../enrollment/classes/StudentEnroll.php");
    require_once("../../../enrollment/classes/OldEnrollees.php");

    // echo "yehey";

    if(isset($_POST['student_username'])){

        $student_username = $_POST['student_username'];

        $old_enrol = new OldEnrollees($con, null);

        $wasSuccess = $old_enrol->StudentMoveUpToGrade12($student_username);

        if($wasSuccess){
            echo "$student_username has been Move Up to Grade 12";

        }
    }
?>