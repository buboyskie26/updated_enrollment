<?php 

    require_once("../../../includes/config.php");

    require_once("../../../enrollment/classes/StudentEnroll.php");
    require_once("../../../enrollment/classes/Enrollment.php");
    require_once("../../../includes/classes/Student.php");

   if (isset($_POST['student_id']) 
        && isset($_POST['student_course_id']) 
        && isset($_POST['current_school_year_id']) 
        && isset($_POST['enrollment_form_id'])){


        $student_id = $_POST['student_id'];
        $student_course_id = $_POST['student_course_id'];
        $current_school_year_id = $_POST['current_school_year_id'];
        $enrollment_form_id = $_POST['enrollment_form_id'];

        $enrollment = new Enrollment($con, null);

        $wasSuccess = $enrollment->EnrolledOSRegular($student_id, $student_course_id,
            $current_school_year_id, $enrollment_form_id);

        if($wasSuccess){
            unset($_SESSION['enrollment_form_id_manual']);
            echo "manual_enrollment_success";
        }else{
            echo "not_success";
        }
    }
?>