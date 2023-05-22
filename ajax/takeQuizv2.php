<?php

    require_once("../includes/config.php");
    require_once("../includes/classes/Student.php");
    require_once("../includes/classes/SubjectPeriodAssignmentQuizClass.php");
    require_once("../includes/classes/SubjectPeriodAssignment.php");


    if(isset($_POST['subject_period_assignment_quiz_class_id']) &&
        isset($_POST['student_id']) &&
        isset($_POST['subject_period_assignment_id']) &&
        isset($_POST['teacher_course_id'])
        ){
       
        // This will prevent the student to go back in the browser
        // and would by pass the retake quiz button.
        
        if(!isset($_SESSION['token_quiz'])){
            $_SESSION['token_quiz'] = "token_quiz_value";
        }
        // echo $token_quiz;
            
        $teacher_course_id = $_POST['teacher_course_id'];
        $subject_period_assignment_id = $_POST['subject_period_assignment_id'];
        $subject_period_assignment_quiz_class_id = $_POST['subject_period_assignment_quiz_class_id'];
        $student_id = $_POST['student_id'];

        // if(isset($_SESSION['token_quiz'])){
        //     $token_quiz = $_SESSION['token_quiz'];
        //     echo $token_quiz;
        //     // header("Location: student_assignment_view.php?subject_period_assignment_id=$subject_period_assignment_id&tc_id=$teacher_course_id");
        //     header("Location: student_assignment_view.php?subject_period_assignment_id=$subject_period_assignment_id&tc_id=6");

        //     // exit();
        // }else{
        //     //
        //     header("Location: student_assignment_view.php?subject_period_assignment_id=$subject_period_assignment_id&tc_id=$teacher_course_id");
        //     exit();
        // }

        // echo $subject_period_assignment_quiz_class_id;
        $studentLoggedInObj = new Student($con, $student_id);

        $subject_period_assignment_quiz_class = new SubjectPeriodAssignmentQuizClass($con, 
            $subject_period_assignment_quiz_class_id, $studentLoggedInObj);

        $subject_period_assignment_id = $subject_period_assignment_quiz_class->GetSubjectPeriodAssignmentId();

        $student_quiz_time = $subject_period_assignment_quiz_class->GetQuizTime();

        $subjectPeriodAssignment = new SubjectPeriodAssignment($con, $subject_period_assignment_id, $studentLoggedInObj);
        
        $doesQuizHadTaken = $subjectPeriodAssignment->DoesQuizHadTaken($subject_period_assignment_quiz_class_id, $student_id);
        $takeQuizCount = $subjectPeriodAssignment->GetTakeQuizCount($subject_period_assignment_quiz_class_id, $student_id);
        $max_submission = $subjectPeriodAssignment->GetMaxSubmission();

        $teacher_course_id =  $_SESSION['teacher_course_id'];

        // if($doesQuizHadTaken == true && $takeQuizCount >= $max_submission){
        //     header("Location: student_assignment_view.php?subject_period_assignment_id=$subject_period_assignment_id&tc_id=$teacher_course_id");
        //     echo "qweqwe";
        //     // exit();
        // }

        $queryCheckIfUnAnswered = $con->prepare("SELECT * FROM student_period_assignment_quiz
            WHERE subject_period_assignment_quiz_class_id=:subject_period_assignment_quiz_class_id
            AND student_id=:student_id
            LIMIT 1");

        $queryCheckIfUnAnswered->bindValue(":subject_period_assignment_quiz_class_id", $subject_period_assignment_quiz_class_id);
        $queryCheckIfUnAnswered->bindValue(":student_id", $student_id);
        $queryCheckIfUnAnswered->execute();


        $queryCheckIfAnswered = $con->prepare("SELECT * FROM student_period_assignment_quiz
            WHERE subject_period_assignment_quiz_class_id=:subject_period_assignment_quiz_class_id
            AND student_id=:student_id
            AND time_finish IS NOT NULL
            LIMIT 1");

        $queryCheckIfAnswered->bindValue(":subject_period_assignment_quiz_class_id", $subject_period_assignment_quiz_class_id);
        $queryCheckIfAnswered->bindValue(":student_id", $student_id);
        $queryCheckIfAnswered->execute();

        if($queryCheckIfUnAnswered->rowCount() == 0){

            $queryInsert = $con->prepare("INSERT INTO student_period_assignment_quiz
                        (subject_period_assignment_quiz_class_id, student_id, take_quiz_count, student_quiz_time)
                        VALUES(:subject_period_assignment_quiz_class_id, :student_id, :take_quiz_count, :student_quiz_time)");
        
            $queryInsert->bindValue(":subject_period_assignment_quiz_class_id", $subject_period_assignment_quiz_class_id);
            $queryInsert->bindValue(":student_id", $student_id);
            $queryInsert->bindValue(":take_quiz_count", 1);
            $queryInsert->bindValue(":student_quiz_time", $student_quiz_time);

            if($queryInsert->execute()){
                echo "Success";
            }

        }else if($queryCheckIfAnswered->rowCount() > 0){
            // This for quizzes that allows teahcers to took more than once.


            $queryCheckIfAnswered = $queryCheckIfAnswered->fetch(PDO::FETCH_ASSOC);
                $take_quiz_count = $queryCheckIfAnswered['take_quiz_count'];

            $resetUpdate = $con->prepare("UPDATE student_period_assignment_quiz
                    SET take_quiz_count=:take_quiz_count, time_finish=NULL, total_score=0, student_quiz_time=:student_quiz_time 
                    WHERE subject_period_assignment_quiz_class_id=:subject_period_assignment_quiz_class_id
                    AND student_id=:student_id
                    AND time_finish IS NOT NULL");
            
            $resetUpdate->bindValue(":take_quiz_count", $take_quiz_count + 1);
            $resetUpdate->bindValue(":student_quiz_time", $student_quiz_time);
            $resetUpdate->bindValue(":subject_period_assignment_quiz_class_id", $subject_period_assignment_quiz_class_id);
            $resetUpdate->bindValue(":student_id", $student_id);

            if($resetUpdate->execute()){
                
                $studentPeriodAssQuizQuery = $con->prepare("SELECT student_period_assignment_quiz_id FROM student_period_assignment_quiz
                    WHERE subject_period_assignment_quiz_class_id=:subject_period_assignment_quiz_class_id
                    AND student_id=:student_id
                    AND time_finish IS NULL
                    LIMIT 1");
                
                $studentPeriodAssQuizQuery->bindValue(":subject_period_assignment_quiz_class_id", $subject_period_assignment_quiz_class_id);
                $studentPeriodAssQuizQuery->bindValue(":student_id", $student_id);
                $studentPeriodAssQuizQuery->execute();
                

                if($studentPeriodAssQuizQuery->rowCount() > 0){
                    echo "You take your remaining submission on this quiz.The previous quiz is now voided.";

                    $student_period_assignment_quiz_id = $studentPeriodAssQuizQuery->fetchColumn();

                    $checkMultipleAnswerExist = $con->prepare("SELECT * FROM student_period_assignment_multi_question_answer
                        WHERE subject_period_assignment_id=:subject_period_assignment_id
                        AND student_id=:student_id
                        AND student_period_assignment_quiz_id=:student_period_assignment_quiz_id
                    -- AND total_score = 0
                    ");

                    // echo $subject_period_assignment_id;

                    $checkMultipleAnswerExist->bindValue(":subject_period_assignment_id", $subject_period_assignment_id);
                    $checkMultipleAnswerExist->bindValue(":student_id", $student_id);
                    $checkMultipleAnswerExist->bindValue(":student_period_assignment_quiz_id", $student_period_assignment_quiz_id);
                    $checkMultipleAnswerExist->execute();
                    
                    if($checkMultipleAnswerExist->rowCount() > 0){

                        $queryMultiAnsRemove = $con->prepare("DELETE FROM student_period_assignment_multi_question_answer
                            WHERE subject_period_assignment_id=:subject_period_assignment_id
                            AND student_id=:student_id
                            AND student_period_assignment_quiz_id=:student_period_assignment_quiz_id
                            
                            ");

                        $queryMultiAnsRemove->bindValue(":subject_period_assignment_id", $subject_period_assignment_id);
                        $queryMultiAnsRemove->bindValue(":student_id", $student_id);
                        $queryMultiAnsRemove->bindValue(":student_period_assignment_quiz_id", $student_period_assignment_quiz_id);
                        $queryMultiAnsRemove->execute();
                    }

                    $checkTrueOrFalseAnswerExist = $con->prepare("SELECT * FROM student_period_assignment_quiz_question_answer
                            WHERE subject_period_assignment_id=:subject_period_assignment_id
                            AND student_id=:student_id
                            AND student_period_assignment_quiz_id=:student_period_assignment_quiz_id
                            
                            ");
            
                    $checkTrueOrFalseAnswerExist->bindValue(":subject_period_assignment_id", $subject_period_assignment_id);
                    $checkTrueOrFalseAnswerExist->bindValue(":student_id", $student_id);
                    $checkTrueOrFalseAnswerExist->bindValue(":student_period_assignment_quiz_id", $student_period_assignment_quiz_id);
                    $checkTrueOrFalseAnswerExist->execute();

                    if($checkTrueOrFalseAnswerExist->rowCount() > 0){

                        $queryTrueFalseAnsRemove = $con->prepare("DELETE FROM student_period_assignment_quiz_question_answer
                            WHERE subject_period_assignment_id=:subject_period_assignment_id
                            AND student_id=:student_id
                            AND student_period_assignment_quiz_id=:student_period_assignment_quiz_id
                            ");

                        $queryTrueFalseAnsRemove->bindValue(":subject_period_assignment_id", $subject_period_assignment_id);
                        $queryTrueFalseAnsRemove->bindValue(":student_id", $student_id);
                        $queryTrueFalseAnsRemove->bindValue(":student_period_assignment_quiz_id", $student_period_assignment_quiz_id);
                        $queryTrueFalseAnsRemove->execute();
                    }

                }
            }
        }else{
            echo "Welcome back again to the quiz.";
        }
    }

?>