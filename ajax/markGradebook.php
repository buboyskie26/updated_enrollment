<?php



    require_once("../includes/config.php");

    if(isset($_POST['value']) 
        && isset($_POST['student_period_assignment_id']) 
        && isset($_POST['subject_period_assignment_id']) 
        && isset($_POST['student_id'])){

        $student_id = $_POST['student_id'];
        $mark_value = $_POST['value'];
        $student_period_assignment_id = $_POST['student_period_assignment_id'];
        $subject_period_assignment_id = $_POST['subject_period_assignment_id'];

        $queryOverScore = $con->prepare("SELECT max_score FROM subject_period_assignment
            WHERE subject_period_assignment_id=:subject_period_assignment_id
            LIMIT 1");
        
        $queryOverScore->bindValue(":subject_period_assignment_id", $subject_period_assignment_id);
        $queryOverScore->execute();
        $maxScore = $queryOverScore->fetchColumn();

        if($mark_value > $maxScore){
            // Alert the maximum score if it was heated.\
            echo $maxScore;
            return;
        }
        
        $query = $con->prepare("UPDATE student_period_assignment
            SET grade=:grade
            WHERE student_period_assignment_id=:student_period_assignment_id
            AND subject_period_assignment_id=:subject_period_assignment_id
            AND student_id=:student_id");
        
        $query->bindValue(":grade", $mark_value);
        $query->bindValue(":student_period_assignment_id", $student_period_assignment_id);
        $query->bindValue(":subject_period_assignment_id", $subject_period_assignment_id);
        $query->bindValue(":student_id", $student_id);

        $query->execute();
        
        if($query->execute() == true){
            echo "success";
        }
    }
?>