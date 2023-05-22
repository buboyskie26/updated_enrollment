<?php

    require_once("../includes/config.php");
    require_once("../includes/classes/Teacher.php");
    require_once("../includes/classes/SubjectPeriodQuizQuestion.php");
    require_once("../includes/classes/SubjectPeriodQuiz.php");


    if(isset($_POST['request']) && isset($_POST['subject_period_quiz_id']) && isset($_POST['teacher_id'])){


        $questionTypeId = $_POST['request'];
        $subject_period_quiz_id = $_POST['subject_period_quiz_id'];
        $teacher_id = $_POST['teacher_id'];

        $teacherUserLoggedInObj = new Teacher($con, $teacher_id);

        $subjectPeriosQuiz = new SubjectPeriodQuiz($con, $subject_period_quiz_id, $teacherUserLoggedInObj);

        // $subQuizQuestion = new SubjectPeriodQuizQuestion($con, $subjectPeriosQuiz,  $teacherUserLoggedInObj);

        $output = "";

        if($questionTypeId == 1 && $questionTypeId != 2){

            $output .= "
                A: <input type='text' size='40' class='mt-2 mb-2' name='answer1'> 
                <input type='radio' name='question_answer' value='A'>
                <br>
                B: <input type='text' size='40' class='mt-2 mb-2' name='answer2'> 
                <input type='radio' name='question_answer' value='B'>
                <br>
                C: <input type='text' size='40' class='mt-2 mb-2' name='answer3'> 
                <input type='radio' name='question_answer' value='C'>
                <br>
                D: <input type='text' size='40' class='mt-2 mb-2' name='answer4'> 
                <input type='radio' name='question_answer' value='D'>
            ";
        }
        if($questionTypeId == 2 && $questionTypeId != 1){

            $output .= "
                <input type='radio' name='question_answer' value='True'>&nbsp;True&nbsp;
	            &nbsp;
                <br>
                <input type='radio' name='question_answer' value='False'>&nbsp;False
            ";
        }
        echo $output;

        
    }
?>