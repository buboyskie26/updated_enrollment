<?php 

    require_once("../../../includes/config.php");

    require_once("../../../enrollment/classes/StudentEnroll.php");

     
    if(isset($_POST['course_id']) && isset($_POST['student_id'])){

        $course_id = $_POST['course_id'];
        $student_id = $_POST['student_id'];

        $enroll = new StudentEnroll($con);

        $school_year_obj = $enroll->GetActiveSchoolYearAndSemester();

        $current_school_year_id = $school_year_obj['school_year_id'];

        $SET_TO_YES = "yes";
        $not_evaluated = "no";

        $is_new_enrollee = 0;
        $is_transferee = 0;
        $registrar_evaluted = "yes";

        $registrar_confirm = $con->prepare("UPDATE enrollment
            SET cashier_evaluated=:set_cashier_evaluated
            WHERE student_id=:student_id
            AND course_id=:course_id
            AND cashier_evaluated=:cashier_evaluated
            AND is_new_enrollee=:is_new_enrollee
            AND is_transferee=:is_transferee
            AND registrar_evaluated=:registrar_evaluated
            AND school_year_id=:school_year_id
        ");

        $registrar_confirm->bindValue(":set_cashier_evaluated", $SET_TO_YES);
        $registrar_confirm->bindValue(":student_id", $student_id);
        $registrar_confirm->bindValue(":course_id", $course_id);
        $registrar_confirm->bindValue(":cashier_evaluated", $not_evaluated);
        $registrar_confirm->bindValue(":is_new_enrollee", $is_new_enrollee);
        $registrar_confirm->bindValue(":is_transferee", $is_transferee);
        $registrar_confirm->bindValue(":registrar_evaluated", $registrar_evaluted);
        $registrar_confirm->bindValue(":school_year_id", $current_school_year_id);

        if($registrar_confirm->execute()){
            echo "success cashier evaluated this ongoing student";

        }else{
            echo "qwee";
        }
 
    }
     
?>