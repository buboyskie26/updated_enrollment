<?php

    require_once("../includes/config.php");

    
    if(isset($_POST['val']) 
        && isset($_POST['subject_period_quiz_question_id'])
        && isset($_POST['student_id'])
        && isset($_POST['subject_period_quiz_id'])
        && isset($_POST['subject_period_quiz_question_answer_id'])){

        $subject_period_quiz_question_id = $_POST['subject_period_quiz_question_id'];
        echo $subject_period_quiz_question_id;

        $subject_period_quiz_question_answer_id = $_POST['subject_period_quiz_question_answer_id'];
        $student_id = $_POST['student_id'];
        $subject_period_quiz_id = $_POST['subject_period_quiz_id'];
        $my_answer = $_POST['val'];


        // If you have ONE subject_period_quiz_question_id
        // Then the next click on the same question on multiple would be updating.
        $query = $con->prepare("SELECT * FROM student_period_quiz_multi_question_answer
                    WHERE subject_period_quiz_question_id=:subject_period_quiz_question_id
                    AND student_id=:student_id");
                
        $query->bindValue(":subject_period_quiz_question_id", $subject_period_quiz_question_id);
        $query->bindValue(":student_id", $student_id);
        $query->execute();

        if($query->rowCount() == 1){

            $query_update = $con->prepare("UPDATE student_period_quiz_multi_question_answer
                SET my_answer=:my_answer, subject_period_quiz_question_answer_id=:subject_period_quiz_question_answer_id
                WHERE subject_period_quiz_question_id=:subject_period_quiz_question_id
                AND student_id=:student_id ");
                 
            $query_update->bindValue(":my_answer", $my_answer);
            $query_update->bindValue(":subject_period_quiz_question_answer_id", $subject_period_quiz_question_answer_id);
            $query_update->bindValue(":subject_period_quiz_question_id", $subject_period_quiz_question_id);
            $query_update->bindValue(":student_id", $student_id);

            if( $query_update->execute()){
                echo "updated successfully";
                return;
            }

        }else if($query->rowCount() == 0){
            // Else creation

            $queryAnswer = $con->prepare("SELECT question_answer FROM subject_period_quiz_question
                        WHERE subject_period_quiz_question_id=:subject_period_quiz_question_id");
                    
            $queryAnswer->bindValue(":subject_period_quiz_question_id", $subject_period_quiz_question_id);
            $queryAnswer->execute();
            $question_answer = $queryAnswer->fetchColumn();

            
            $queryInsert = $con->prepare("INSERT INTO student_period_quiz_multi_question_answer
                (subject_period_quiz_question_answer_id, my_answer, question_answer, student_id,
                    subject_period_quiz_id, subject_period_quiz_question_id)
                
                VALUES(:subject_period_quiz_question_answer_id, :my_answer,
                    :question_answer, :student_id, :subject_period_quiz_id, :subject_period_quiz_question_id)");
            
            $queryInsert->bindValue(":subject_period_quiz_question_answer_id", $subject_period_quiz_question_answer_id);
            $queryInsert->bindValue(":my_answer", $my_answer);
            $queryInsert->bindValue(":question_answer", $question_answer);
            $queryInsert->bindValue(":student_id", $student_id);
            $queryInsert->bindValue(":subject_period_quiz_id", $subject_period_quiz_id);
            $queryInsert->bindValue(":subject_period_quiz_question_id", $subject_period_quiz_question_id);
            
            if($queryInsert->execute()){
                echo "success";
                return;
            }
        }
    }


?>