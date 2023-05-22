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

        $update_irreg_enrollment_course_id = $con->prepare("UPDATE enrollment_tertiary
            SET course_tertiary_id=:course_tertiary_id
            WHERE student_id=:student_id
            WHERE is_irregular=:is_irregular
            ");

        $update_irreg_enrollment_course_id->bindValue(":course_tertiary_id", $courseTertiaryId);
        $update_irreg_enrollment_course_id->bindValue(":student_id", $student_id);
        $update_irreg_enrollment_course_id->bindValue(":is_irregular", 1);

        $update_irreg_section = $con->prepare("UPDATE student
            SET course_tertiary_id=:course_tertiary_id
            WHERE student_id=:student_id
            AND active=:active
            AND is_tertiary=:is_tertiary
            ");
        
        $update_irreg_section->bindValue(":student_id", $student_id);
        $update_irreg_section->bindValue(":active", 1);
        $update_irreg_section->bindValue(":is_tertiary", 1);
        $update_irreg_section->bindValue(":course_tertiary_id", $courseTertiaryId);

        // $update_irreg_section->execute();
        if($update_irreg_section->execute() && $update_irreg_section->rowCount() > 0 &&
            $update_irreg_enrollment_course_id->execute() && $update_irreg_enrollment_course_id->rowCount() > 0){

            echo "success";
        }


    }
?>