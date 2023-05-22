<?php

    require_once("../includes/config.php");

    
    if(isset($_POST['subject_period_quiz_question_id'])
        && isset($_POST['val'])
        && isset($_POST['student_id'])
        && isset($_POST['subject_period_quiz_id'])){


        $subject_period_quiz_question_id = $_POST['subject_period_quiz_question_id'];

        // echo $subject_period_quiz_question_id;
        $student_id = $_POST['student_id'];
        $subject_period_quiz_id = $_POST['subject_period_quiz_id'];
        $my_answer = $_POST['val'];

        // echo $my_answer;
        // Check if student will change the selected answer
        $queryPreviousAnswer = $con->prepare("SELECT * FROM student_period_quiz_question_answer
                    WHERE subject_period_quiz_question_id=:subject_period_quiz_question_id
                    AND student_id=:student_id
                    AND my_answer !=:my_answer
                    LIMIT 1");
    
        $queryPreviousAnswer->bindValue(":subject_period_quiz_question_id", $subject_period_quiz_question_id);
        $queryPreviousAnswer->bindValue(":my_answer", $my_answer);
        $queryPreviousAnswer->bindValue(":student_id", $student_id);
        $queryPreviousAnswer->execute();

        if($queryPreviousAnswer->rowCount() > 0){
            
            $fetchqueryPreviousAnswer = $queryPreviousAnswer->fetch(PDO::FETCH_ASSOC);

            $student_period_quiz_question_answer_id = $fetchqueryPreviousAnswer['student_period_quiz_question_answer_id'];
            $prev = $fetchqueryPreviousAnswer['my_answer'];
            
            // echo $student_period_quiz_question_answer_id;
            // echo $student_period_quiz_question_answer_id;
            $editPreviousAnswer = $con->prepare("UPDATE student_period_quiz_question_answer
                SET my_answer=:my_answer
                WHERE student_period_quiz_question_answer_id=:student_period_quiz_question_answer_id
                ");

            $editPreviousAnswer->bindValue(":my_answer", $my_answer);
            $editPreviousAnswer->bindValue(":student_period_quiz_question_answer_id", $student_period_quiz_question_answer_id);
            // // $editPreviousAnswer->bindValue(":student_id", $student_id);
            
            // $wasSuccess = $editPreviousAnswer->execute();
 
            // if($wasSuccess == true){
            //     echo "The changed is successful";
            //     return;
            // }else{
            //     echo "Something went wrong on changing your answer.";
            //     return;
            // }

            // echo $student_period_quiz_question_answer_id;
            // return;
        }else{
            // echo "You selected the prev answer.";
            // return;
        }
        
        $queryAnswer = $con->prepare("SELECT question_answer FROM subject_period_quiz_question
                    WHERE subject_period_quiz_question_id=:subject_period_quiz_question_id");
                
        $queryAnswer->bindValue(":subject_period_quiz_question_id", $subject_period_quiz_question_id);
        $queryAnswer->execute();

        $question_answer = $queryAnswer->fetchColumn();

        // echo $question_answer;
        //

        // if($queryInsert->execute()){
        //     echo "success";
        //     return;
        // }else{
        //     echo "failed";
        //     return;
        // }

        // Check if the subject_period_quiz_question_id is in the data
        // If it was present, you have already anwer the question
        // means want to change your answer with the subject_period_quiz_question_id
        $queryCheckIfAnswered = $con->prepare("SELECT * FROM student_period_quiz_question_answer
                    WHERE subject_period_quiz_question_id=:subject_period_quiz_question_id
                    AND student_id=:student_id");
    
        $queryCheckIfAnswered->bindValue(":subject_period_quiz_question_id", $subject_period_quiz_question_id);
        $queryCheckIfAnswered->bindValue(":student_id", $student_id);
        $queryCheckIfAnswered->execute();

        if($queryCheckIfAnswered->rowCount() == 0){

            // If it was not there, it means you dont yet answered the question\
            // so you need to answer it.

            $queryInsert = $con->prepare("INSERT INTO student_period_quiz_question_answer
            (subject_period_quiz_question_id, my_answer, question_answer, student_id, subject_period_quiz_id)
            VALUES(:subject_period_quiz_question_id, :my_answer,
                :question_answer, :student_id, :subject_period_quiz_id)");
        
            $queryInsert->bindValue(":subject_period_quiz_question_id", $subject_period_quiz_question_id);
            $queryInsert->bindValue(":my_answer", $my_answer);
            $queryInsert->bindValue(":question_answer", $question_answer);
            $queryInsert->bindValue(":student_id", $student_id);
            $queryInsert->bindValue(":subject_period_quiz_id", $subject_period_quiz_id);

            if($queryInsert->execute()){
                echo "success";
                return;
            }else{
                echo "failed";
                return;
            }

        }else{
            // Check if student will change the selected answer
            $queryPreviousAnswer = $con->prepare("SELECT * FROM student_period_quiz_question_answer
                        WHERE subject_period_quiz_question_id=:subject_period_quiz_question_id
                        AND student_id=:student_id
                        AND my_answer !=:my_answer
                        LIMIT 1");
        
            $queryPreviousAnswer->bindValue(":subject_period_quiz_question_id", $subject_period_quiz_question_id);
            $queryPreviousAnswer->bindValue(":my_answer", $my_answer);
            $queryPreviousAnswer->bindValue(":student_id", $student_id);
            $queryPreviousAnswer->execute();

            $fetchqueryPreviousAnswer = $queryPreviousAnswer->fetch(PDO::FETCH_ASSOC);

            if($queryPreviousAnswer->rowCount() > 0){
                $student_period_quiz_question_answer_id = $fetchqueryPreviousAnswer['student_period_quiz_question_answer_id'];
                $prev = $fetchqueryPreviousAnswer['my_answer'];
                
                // echo $student_period_quiz_question_answer_id;
                // echo $student_period_quiz_question_answer_id;
                $editPreviousAnswer = $con->prepare("UPDATE student_period_quiz_question_answer
                    SET my_answer=:my_answer
                    WHERE student_period_quiz_question_answer_id=:student_period_quiz_question_answer_id
                    ");

                $editPreviousAnswer->bindValue(":my_answer", $my_answer);
                $editPreviousAnswer->bindValue(":student_period_quiz_question_answer_id", $student_period_quiz_question_answer_id);
                // // $editPreviousAnswer->bindValue(":student_id", $student_id);
                
                $wasSuccess = $editPreviousAnswer->execute();
    
                if($wasSuccess == true){
                    echo "The changed is successful";
                    return;
                }else{
                    echo "Something went wrong on changing your answer.";
                    return;
                }
                
                // echo $student_period_quiz_question_answer_id;
                // return;
            }else{
                echo "You selected the prev answer.";
                return;
            }
        } 


    }
?>

