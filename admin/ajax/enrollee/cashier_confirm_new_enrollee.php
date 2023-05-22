<?php 

    require_once("../../../includes/config.php");

    require_once("../../../enrollment/classes/StudentEnroll.php");

     
    if(isset($_POST['course_id']) && isset($_POST['student_id'])){

        $course_id = $_POST['course_id'];
        $student_id = $_POST['student_id'];

        $SET_TO_YES = "yes";
        $not_evaluated = "no";
        $is_new_enrollee = 1;

        $registrar_confirm = $con->prepare("UPDATE enrollment
            SET cashier_evaluated=:set_cashier_evaluated
            WHERE student_id=:student_id
            AND course_id=:course_id
            AND cashier_evaluated=:cashier_evaluated
            AND is_new_enrollee=:is_new_enrollee
        ");

        $registrar_confirm->bindValue(":set_cashier_evaluated", $SET_TO_YES);
        $registrar_confirm->bindValue(":student_id", $student_id);
        $registrar_confirm->bindValue(":course_id", $course_id);
        $registrar_confirm->bindValue(":cashier_evaluated", $not_evaluated);
        $registrar_confirm->bindValue(":is_new_enrollee", $is_new_enrollee);

        if($registrar_confirm->execute()){
            echo "success cashier evaluated this new student";

        }else{
            echo "qwee";
        }
 
    }
     
?>