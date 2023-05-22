<?php

    require_once("../includes/config.php");


    if(isset($_POST['subject_period_assignment_handout_id']) &&
        isset($_POST['student_id'])){

        $subject_period_assignment_handout_id = $_POST['subject_period_assignment_handout_id'];
        $student_id = $_POST['student_id'];


        $query = $con->prepare("SELECT * FROM handout_viewed
            WHERE subject_period_assignment_handout_id=:subject_period_assignment_handout_id
            AND student_id=:student_id
            LIMIT 1");
        
                
        $query->bindValue(":subject_period_assignment_handout_id", $subject_period_assignment_handout_id);
        $query->bindValue(":student_id", $student_id);
        $query->execute();

        if($query->rowCount() == 0){
            $queryInsert = $con->prepare("INSERT INTO handout_viewed
                (subject_period_assignment_handout_id, student_id, count)
                VALUES(:subject_period_assignment_handout_id, :student_id, :count)");
        
            $queryInsert->bindValue(":subject_period_assignment_handout_id", $subject_period_assignment_handout_id);
            $queryInsert->bindValue(":student_id", $student_id);
            $queryInsert->bindValue(":count", 1);

            if($queryInsert->execute()){
                echo "Handout is now viewed";
            }else{
                echo "Sometinh went wrong";
            }
        }else{
            $query = $query->fetch(PDO::FETCH_ASSOC);

            $count = $query['count'];
            $queryUpdate = $con->prepare("UPDATE handout_viewed
                SET count=:count
                WHERE subject_period_assignment_handout_id=:subject_period_assignment_handout_id
                AND student_id=:student_id 
                ");
            $totalCount = $count + 1;

            $queryUpdate->bindValue(":count", $totalCount);
            $queryUpdate->bindValue(":subject_period_assignment_handout_id", $subject_period_assignment_handout_id);
            $queryUpdate->bindValue(":student_id", $student_id);
            if($queryUpdate->execute()){
                echo "from $count to $totalCount times of handout viewed.";

            }
        }

    }

?>