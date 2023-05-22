<?php 

    require_once("../../../includes/config.php");

    require_once("../../../enrollment/classes/StudentEnroll.php");

     
    if(isset($_POST['course_id']) && isset($_POST['student_id'])){

        $course_id = $_POST['course_id'];
        $student_id = $_POST['student_id'];

        $SET_TO_YES = "yes";
        $not_evaluated = "no";

        $registrar_confirm = $con->prepare("UPDATE enrollment
            SET registrar_evaluated=:set_registrar_evaluated
            WHERE student_id=:student_id
            AND course_id=:course_id
            AND registrar_evaluated=:registrar_evaluated
        ");

        $registrar_confirm->bindValue(":set_registrar_evaluated", $SET_TO_YES);
        $registrar_confirm->bindValue(":student_id", $student_id);
        $registrar_confirm->bindValue(":course_id", $course_id);
        $registrar_confirm->bindValue(":registrar_evaluated", $not_evaluated);

        if($registrar_confirm->execute()){
            echo "success registrar evaluated this new enrollee";
        }
        // echo $course_id;
        // echo "<br>";
        // echo $student_id;
    }
     
?>