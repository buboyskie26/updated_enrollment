<?php

    require_once("../includes/config.php");
    require_once("../includes/classes/Student.php");
    require_once("../includes/classes/SubjectPeriodQuizClass.php");
    require_once("../includes/classes/SubjectPeriodQuiz.php");


     if(isset($_POST['subject_period_quiz_class_id']) &&
        isset($_POST['student_id'])){

        $subject_period_quiz_class_id = $_POST['subject_period_quiz_class_id'];
        $student_id = $_POST['student_id'];

        $studentLoggedInObj = new Student($con, $student_id);

        $subjectPeriodQuizClass = new SubjectPeriodQuizClass($con,
            $subject_period_quiz_class_id, $studentLoggedInObj);

        $subjectPeriodQuizId = $subjectPeriodQuizClass->GetSubjectPeriodQuizId();
        $maxSubmission = $subjectPeriodQuizClass->GetMaxAttempt();

        $subjectPeriodQuiz = new SubjectPeriodQuiz($con,
            $subject_period_quiz_class_id, $studentLoggedInObj);

        $answerQuizCount = $subjectPeriodQuiz->StudentAnsweredTheQuizCount($subject_period_quiz_class_id);
        $answerQuizCountv2 = $subjectPeriodQuiz->StudentAnsweredTheQuizCountv2($subject_period_quiz_class_id);
        
        

        $answeredQuiz = $subjectPeriodQuiz->CheckStudentAnsweredTheQuiz($subject_period_quiz_class_id);

        // In an unexplanaible scenario, student already answered the quiz
        // but there`s a button, this will helps to prevent them from taking again.
        // AND, check if there`s only once chance to take the quiz.
        if($answeredQuiz == true && $maxSubmission == 1 && $answerQuizCount == 1){
            echo "You already taken the quiz. Only once to take quiz.";
            // header("Location: student_quiz_view.php?subject_period_quiz_class_id=$subject_period_quiz_class_id&tc_id=6");
            return;
        }

        $queryCheckIfUnAnswered = $con->prepare("SELECT * FROM student_period_quiz
                    WHERE subject_period_quiz_class_id=:subject_period_quiz_class_id
                    AND student_id=:student_id
                    LIMIT 1
                    -- AND total_score = 0
                    ");
    
        $queryCheckIfUnAnswered->bindValue(":subject_period_quiz_class_id", $subject_period_quiz_class_id);
        $queryCheckIfUnAnswered->bindValue(":student_id", $student_id);
        $queryCheckIfUnAnswered->execute();

        $queryCheckIfAnswered = $con->prepare("SELECT * FROM student_period_quiz
                    WHERE subject_period_quiz_class_id=:subject_period_quiz_class_id
                    AND student_id=:student_id
                    AND time_finish IS NOT NULL
                    LIMIT 1
                    -- AND total_score = 0
                    ");
    
        $queryCheckIfAnswered->bindValue(":subject_period_quiz_class_id", $subject_period_quiz_class_id);
        $queryCheckIfAnswered->bindValue(":student_id", $student_id);
        $queryCheckIfAnswered->execute();


        // if($answerQuizCount < $maxSubmission){
        if($answerQuizCountv2 < $maxSubmission){

            // $remaining = $maxSubmission -  $answerQuizCount;
            if($queryCheckIfUnAnswered->rowCount() == 0){
                //

                $student_quiz_time = $subjectPeriodQuizClass->GetQuizTime();
                
                $queryInsert = $con->prepare("INSERT INTO student_period_quiz
                    (subject_period_quiz_class_id, student_id, take_quiz_count, student_quiz_time)
                    VALUES(:subject_period_quiz_class_id, :student_id, :take_quiz_count, :student_quiz_time)");
            
                $queryInsert->bindValue(":subject_period_quiz_class_id", $subject_period_quiz_class_id);
                $queryInsert->bindValue(":student_id", $student_id);
                $queryInsert->bindValue(":take_quiz_count", 1);
                $queryInsert->bindValue(":student_quiz_time", $student_quiz_time);
                $queryInsert->execute();

            }
            else if($queryCheckIfAnswered->rowCount() > 0){
                //

                $queryCheckIfAnswered = $queryCheckIfAnswered->fetch(PDO::FETCH_ASSOC);
                $take_quiz_count = $queryCheckIfAnswered['take_quiz_count'];

                $resetUpdate = $con->prepare("UPDATE student_period_quiz
                    SET take_quiz_count=:take_quiz_count, time_finish=NULL, total_score=0
                    WHERE subject_period_quiz_class_id=:subject_period_quiz_class_id
                    AND student_id=:student_id
                    AND time_finish IS NOT NULL ");
            
                $resetUpdate->bindValue(":take_quiz_count", $take_quiz_count + 1);
                $resetUpdate->bindValue(":subject_period_quiz_class_id", $subject_period_quiz_class_id);
                $resetUpdate->bindValue(":student_id", $student_id);
                $resetUpdate->execute();

                $checkMultipleAnswerExist = $con->prepare("SELECT * FROM student_period_quiz_multi_question_answer
                    WHERE subject_period_quiz_id=:subject_period_quiz_id
                    AND student_id=:student_id
                    -- AND total_score = 0
                    ");
    
                $checkMultipleAnswerExist->bindValue(":subject_period_quiz_id", $subjectPeriodQuizId);
                $checkMultipleAnswerExist->bindValue(":student_id", $student_id);
                $checkMultipleAnswerExist->execute();

                if($checkMultipleAnswerExist->rowCount() > 0){

                    $queryMultiAnsRemove = $con->prepare("DELETE FROM student_period_quiz_multi_question_answer
                        WHERE subject_period_quiz_id=:subject_period_quiz_id
                        AND student_id=:student_id");

                    $queryMultiAnsRemove->bindValue(":subject_period_quiz_id", $subjectPeriodQuizId);
                    $queryMultiAnsRemove->bindValue(":student_id", $student_id);
                    $queryMultiAnsRemove->execute();
                }

                $checkTrueOrFalseAnswerExist = $con->prepare("SELECT * FROM student_period_quiz_question_answer
                    WHERE subject_period_quiz_id=:subject_period_quiz_id
                    AND student_id=:student_id");
    
                $checkTrueOrFalseAnswerExist->bindValue(":subject_period_quiz_id", $subjectPeriodQuizId);
                $checkTrueOrFalseAnswerExist->bindValue(":student_id", $student_id);
                $checkTrueOrFalseAnswerExist->execute();

                if($checkTrueOrFalseAnswerExist->rowCount() > 0){

                    $queryTrueFalseAnsRemove = $con->prepare("DELETE FROM student_period_quiz_question_answer
                        WHERE subject_period_quiz_id=:subject_period_quiz_id
                        AND student_id=:student_id");

                    $queryTrueFalseAnsRemove->bindValue(":subject_period_quiz_id", $subjectPeriodQuizId);
                    $queryTrueFalseAnsRemove->bindValue(":student_id", $student_id);
                    $queryTrueFalseAnsRemove->execute();
                }
            
            }
            
            else{
                echo "You are now returned from this quiz.";
                return;
            }

            // echo "You are able to take quiz $remaining more.";
            // header("Location: student_quiz_view.php?subject_period_quiz_class_id=$subject_period_quiz_class_id&tc_id=6");
            // return;
        }else if($answerQuizCountv2 >= $maxSubmission){
            echo "You have reached the max submission.";
            return; 
        }

        

    }
?>