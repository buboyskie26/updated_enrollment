<?php 

    require_once("../../includes/config.php");

    require_once("../../enrollment/classes/StudentEnroll.php");
    require_once("../../enrollment/classes/OldEnrollees.php");


    if(isset($_POST['student_id'])
        && isset($_POST['subject_id'])
        && isset($_POST['remarks'])
        && isset($_POST['student_subject_id'])
        && isset($_POST['subject_title'])
        && isset($_POST['course_id'])
    ) {


        $student_id = $_POST['student_id'];
        $subject_id = $_POST['subject_id'];
        $remarks = $_POST['remarks'];

        $student_subject_id = $_POST['student_subject_id'];
        $subject_title = $_POST['subject_title'];
        $course_id = $_POST['course_id'];

        $enroll = new StudentEnroll($con);
        $old_enroll = new OldEnrollees($con, $enroll);

        if($remarks == "Passed"){

            $sql = $con->prepare("INSERT INTO student_subject_grade 
            
                (student_id, subject_id, remarks, student_subject_id, subject_title, course_id)
                VALUES(:student_id, :subject_id, :remarks, :student_subject_id, :subject_title, :course_id)");
            
            $sql->bindValue(":student_id", $student_id);
            $sql->bindValue(":subject_id", $subject_id);
            $sql->bindValue(":remarks", "Passed");
            $sql->bindValue(":student_subject_id", $student_subject_id);
            $sql->bindValue(":subject_title", $subject_title);
            $sql->bindValue(":course_id", $course_id);

            if($sql->execute()){
                echo "success";
            }

        }else if($remarks == "Failed"){

            // $sql_update = $con->prepare("UPDATE student
            //     SET student_status=:student_status
            //     WHERE student_id=:student_id
            //     AND student_status=:current_status");
            
            // $sql_update->bindValue(":student_id", $student_id);
            // $sql_update->bindValue(":student_status", "Irregular");
            // $sql_update->bindValue(":current_status", "Regular");
            // if($sql_update->execute()){
            //     echo "Irregular";
            // }
 
            $sql = $con->prepare("INSERT INTO student_subject_grade 
            
                (student_id, subject_id, remarks, student_subject_id, subject_title, course_id)
                VALUES(:student_id, :subject_id, :remarks, :student_subject_id, :subject_title, :course_id)");
            
            $sql->bindValue(":student_id", $student_id);
            $sql->bindValue(":subject_id", $subject_id);
            $sql->bindValue(":remarks", "Failed");
            $sql->bindValue(":student_subject_id", $student_subject_id);
            $sql->bindValue(":subject_title", $subject_title);
            $sql->bindValue(":course_id", $course_id);

            if($sql->execute()){
                echo "failed";
            }
        }
        
    }
?>
