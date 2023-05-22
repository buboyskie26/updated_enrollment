<?php 

    require_once("../../../includes/config.php");

    require_once("../../../enrollment/classes/StudentEnroll.php");

     
    if(isset($_POST['course_id']) && isset($_POST['student_id'])){

        $enrol = new StudentEnroll($con);

        $school_year_obj = $enrol->GetActiveSchoolYearAndSemester();
        $current_school_year_id = $school_year_obj['school_year_id'];

        $course_id = 0;
        $student_id = $_POST['student_id'];

        $SET_TO_YES = "yes";
        $not_evaluated = "no";
        $is_new_enrollee = 1;
        $is_transferee = 1;

        $registrar_confirm = $con->prepare("UPDATE enrollment
            SET registrar_evaluated=:set_registrar_evaluated
            WHERE student_id=:student_id
            AND is_new_enrollee=:is_new_enrollee
            AND is_transferee=:is_transferee
            AND course_id=:course_id
            AND registrar_evaluated=:registrar_evaluated
            AND school_year_id=:school_year_id
        ");

        $registrar_confirm->bindValue(":set_registrar_evaluated", $SET_TO_YES);
        $registrar_confirm->bindValue(":student_id", $student_id);
        $registrar_confirm->bindValue(":is_new_enrollee", $is_new_enrollee);
        $registrar_confirm->bindValue(":is_transferee", $is_transferee);
        $registrar_confirm->bindValue(":course_id", $course_id);
        $registrar_confirm->bindValue(":registrar_evaluated", $not_evaluated);
        $registrar_confirm->bindValue(":school_year_id", $current_school_year_id);

        if($registrar_confirm->execute()){
            echo "success registrar evaluated this transferee enrollee";
        }
        // echo $course_id;
        // echo "<br>";
        // echo $student_id;
    }
     
?>