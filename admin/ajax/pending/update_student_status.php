<?php 

    require_once("../../../includes/config.php");
    require_once("../../../enrollment/classes/Enrollment.php");
    require_once("../../../enrollment/classes/OldEnrollees.php");
    require_once("../../../includes/classes/Student.php");
 


    if(isset($_POST['transferee_student_status'])
        && isset($_POST['studentId'])){

        $transferee_student_status = $_POST['transferee_student_status'];
        $studentId = $_POST['studentId'];

        $student = new Student($con, null);


        $updateSuccess = $student->UpdateStudentStatusv2($studentId,
            $transferee_student_status);

        if($updateSuccess){
            echo "success";
        }

    }
?>