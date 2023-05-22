<?php

    require_once("../includes/config.php");

    
    if(isset($_POST['subject_period_assignment_quiz_question_id'])
        && isset($_POST['val'])
        && isset($_POST['student_id'])
        && isset($_POST['subject_period_assignment_id'])
        && isset($_POST['student_period_assignment_quiz_id'])){

        $student_period_assignment_quiz_id = $_POST['student_period_assignment_quiz_id'];
        $subject_period_assignment_quiz_question_id = $_POST['subject_period_assignment_quiz_question_id'];
        $subject_period_assignment_id = $_POST['subject_period_assignment_id'];

        // echo $subject_period_quiz_question_id;
        $student_id = $_POST['student_id'];
        $my_answer = $_POST['val'];
       


        $queryIfHaveBeenAnswered = $con->prepare("SELECT * FROM student_period_assignment_quiz_question_answer
                    WHERE subject_period_assignment_quiz_question_id=:subject_period_assignment_quiz_question_id
                    AND student_id=:student_id
                    AND student_period_assignment_quiz_id=:student_period_assignment_quiz_id
                    -- AND subject_period_assignment_id=:subject_period_assignment_id
                    ");
                
        $queryIfHaveBeenAnswered->bindValue(":subject_period_assignment_quiz_question_id", $subject_period_assignment_quiz_question_id);
        $queryIfHaveBeenAnswered->bindValue(":student_id", $student_id);
        $queryIfHaveBeenAnswered->bindValue(":student_period_assignment_quiz_id", $student_period_assignment_quiz_id);
        // $queryIfHaveBeenAnswered->bindValue(":subject_period_assignment_id", $subject_period_assignment_id);
        $queryIfHaveBeenAnswered->execute();

        // Been answred already, UPDATE
        if($queryIfHaveBeenAnswered->rowCount() > 0){

            $queryPreviousAnswer = $con->prepare("SELECT my_answer FROM student_period_assignment_quiz_question_answer
                    WHERE subject_period_assignment_quiz_question_id=:subject_period_assignment_quiz_question_id
                    AND student_id=:student_id
                    AND student_period_assignment_quiz_id=:student_period_assignment_quiz_id
                    LIMIT 1");

            $queryPreviousAnswer->bindValue(":subject_period_assignment_quiz_question_id", $subject_period_assignment_quiz_question_id);
            // $queryPreviousAnswer->bindValue(":my_answer", $my_answer);
            $queryPreviousAnswer->bindValue(":student_id", $student_id);
            $queryPreviousAnswer->bindValue(":student_period_assignment_quiz_id", $student_period_assignment_quiz_id);
            $queryPreviousAnswer->execute();

            $prev_answer = $queryPreviousAnswer->fetchColumn();

            if($prev_answer != $my_answer){
                $editPreviousAnswer = $con->prepare("UPDATE student_period_assignment_quiz_question_answer
                SET my_answer=:my_answer
                WHERE subject_period_assignment_quiz_question_id=:subject_period_assignment_quiz_question_id
                AND my_answer != :prev_answer
                AND student_period_assignment_quiz_id=:student_period_assignment_quiz_id
                ");

                $editPreviousAnswer->bindValue(":my_answer", $my_answer);
                $editPreviousAnswer->bindValue(":prev_answer", $my_answer);
                $editPreviousAnswer->bindValue(":subject_period_assignment_quiz_question_id", $subject_period_assignment_quiz_question_id);
                $editPreviousAnswer->bindValue(":student_period_assignment_quiz_id", $student_period_assignment_quiz_id);
                if($editPreviousAnswer->execute()){
                    echo "Successfully Updated your previous answer to $my_answer.";
                }
                
            }else{
                echo "Old answer you have chosen was your answer right now.";
            }
        }
        // Not yet answered.
        else if($queryIfHaveBeenAnswered->rowCount() <= 0){
            $queryAnswer = $con->prepare("SELECT question_answer FROM subject_period_assignment_quiz_question
                    WHERE subject_period_assignment_quiz_question_id=:subject_period_assignment_quiz_question_id
                    ");
                
            $queryAnswer->bindValue(":subject_period_assignment_quiz_question_id", $subject_period_assignment_quiz_question_id);
            // $queryAnswer->bindValue(":student_id", $student_id);
            // $queryAnswer->bindValue(":student_period_assignment_quiz_id", $student_period_assignment_quiz_id);
            $queryAnswer->execute();
            $question_answer = "undefined";

            if($queryAnswer->rowCount() > 0){
                $question_answer = $queryAnswer->fetchColumn();
            }
            
            if($question_answer != "undefined"){

                $queryInsert = $con->prepare("INSERT INTO student_period_assignment_quiz_question_answer
                (subject_period_assignment_quiz_question_id, my_answer, question_answer,
                student_id, subject_period_assignment_id, student_period_assignment_quiz_id)
                VALUES(:subject_period_assignment_quiz_question_id, :my_answer, :question_answer, :student_id,
                :subject_period_assignment_id, :student_period_assignment_quiz_id)");
                
                $queryInsert->bindValue(":subject_period_assignment_quiz_question_id", $subject_period_assignment_quiz_question_id);
                $queryInsert->bindValue(":my_answer", $my_answer);
                $queryInsert->bindValue(":question_answer", $question_answer);
                $queryInsert->bindValue(":student_id", $student_id);
                $queryInsert->bindValue(":subject_period_assignment_id", $subject_period_assignment_id);
                $queryInsert->bindValue(":student_period_assignment_quiz_id", $student_period_assignment_quiz_id);

                if($queryInsert->execute()){
                    echo "Answered Successfully.";
                    return;
                }else{
                    echo "error insert";
                    return;
                }
            }else{
                echo "error 2";
                return;
            }
            
        }

       


    }
?>

