<?php

    require_once("../../includes/config.php");
    
    if(isset($_GET['subject_period__quiz_id'])){
        $subject_period__quiz_id = $_GET['subject_period__quiz_id'];

        

        $query = $con->prepare("SELECT * FROM subject_period_quiz 
            WHERE subject_period_quiz_id=:subject_period_quiz_id
            LIMIT 1");

        $query->bindValue(":subject_period_quiz_id", $subject_period__quiz_id);
        $query->execute();

        if($query->rowCount() > 0){


            $quizObj = $query->fetch(PDO::FETCH_ASSOC);

            $res = [
                'status' => 200,
                'message' => 'Quiz Fetch Successfully by id',
                'data' => $quizObj
            ];

            echo json_encode($res);
            return;
        }
        else{
            $res = [
                'status' => 404,
                'message' => 'SubjectPeriodQuiz Id Not Found'
            ];

            echo json_encode($res);
            return;
        }

    }
?>