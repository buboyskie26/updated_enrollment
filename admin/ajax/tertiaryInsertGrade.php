<?php 

    require_once("../../includes/config.php");

    require_once("../../enrollment/classes/StudentEnroll.php");
    require_once("../../enrollment/classes/OldEnrollees.php");

    if(isset($_POST['student_id']) 
        && isset($_POST['subjectId'])
        && isset($_POST['remark_input'])
        && isset($_POST['student_subject_tertiary_id'])
        ){

        // echo "yehey";

        $student_id = $_POST['student_id'];
        $subject_tertiary_id = $_POST['subjectId'];

        $student_subject_tertiary_id = $_POST['student_subject_tertiary_id'];
        $text_input = $_POST['remark_input'];

        $enroll = new StudentEnroll($con);
        $old_enroll = new OldEnrollees($con, $enroll);

        $sql = $con->prepare("INSERT INTO student_subject_grade_tertiary 
        
            (student_id, subject_tertiary_id, remarks, student_subject_tertiary_id)
            VALUES(:student_id, :subject_tertiary_id, :remarks, :student_subject_tertiary_id)");

        // Validation for incorrect, Failed or Passed input.
        if($text_input === "Passed" && $text_input !== "Failed"){
      

            $sql->bindValue(":student_id", $student_id);
            $sql->bindValue(":subject_tertiary_id", $subject_tertiary_id);
            $sql->bindValue(":remarks", $text_input);
            $sql->bindValue(":student_subject_tertiary_id", $student_subject_tertiary_id);

            // $sql->bindValue(":course_id", $course_id);

            if($sql->execute()){
                echo "success";
            }
        }
        else if($text_input === "Failed"){

            //
            $student_irregular = $con->prepare("UPDATE student
                SET student_status=:student_status
                WHERE student_id=:student_id
                AND student_status=:current_status
                AND is_tertiary=:is_tertiary
                ");
            
            $student_irregular->bindValue(":student_id", $student_id);
            $student_irregular->bindValue(":student_status", "Irregular");
            $student_irregular->bindValue(":current_status", "Regular");
            $student_irregular->bindValue(":is_tertiary", 1);

            // if($sql_update->execute()){
            //     echo "Irregular";
            // }
 
            $sql->bindValue(":student_id", $student_id);
            $sql->bindValue(":subject_tertiary_id", $subject_tertiary_id);
            $sql->bindValue(":remarks", $text_input);
            $sql->bindValue(":student_subject_tertiary_id", $student_subject_tertiary_id);

            // $sql->bindValue(":course_id", $course_id);
            if($sql->execute() && $student_irregular->execute()){
                echo "Student have failed $subject_tertiary_id subject_id and he will become irregular";
            }
       
        }
        else{
            echo "Invalid Input";
        }
      

        // echo "eclih";
    }else{
        echo "first not working";
    }
?>