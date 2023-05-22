<?php

    require_once("../../includes/config.php");
    require_once("../../includes/classes/SubjectPeriod.php");
    require_once("../../includes/classes/SubjectPeriodQuiz.php");
    require_once("../../includes/classes/Teacher.php");

    if(isset($_POST['save_subjectPeriodQuiz'])){

        $due_date = $_POST['due_date'];
        $quiz_title = $_POST['quiz_title'];
        $quiz_description = $_POST['quiz_description'];

        $subject_period_id = $_POST['subject_period_id'];
        $teacher_id = $_POST['teacher_id'];

        // echo $due_date;
        // echo "<br>";
        // echo $teacher_id;
        // echo "<br>";
        // echo $quiz_title;
        // echo "<br>";

        $teacherUserLoggedInObj = new Teacher($con, $teacher_id);

        $subjectPeriod = new SubjectPeriod($con, $subject_period_id,
            $teacherUserLoggedInObj, "teacher");

        $subjectPeriodQuizId = $subjectPeriod->GetSubjectPeriodQuizId($teacher_id);
        

        $periodQuiz = new SubjectPeriodQuiz($con, $subjectPeriodQuizId,  $teacherUserLoggedInObj);


        if($quiz_title == null && $due_date == null)
        {
            $res = [
                'status' => 422,
                'message' => 'All fields are mandatory'
            ];

            echo json_encode($res);
            return;
        }

        $wasSuccess = $periodQuiz->insertPeriodQuiz($quiz_title, $quiz_description,
            $due_date, $subject_period_id, $teacher_id);

        if($wasSuccess){
            $res = [
                'status' => 200,
                'message' => 'Quiz Successfully added'
            ];
            echo json_encode($res);
            return;
        }
        else{
            $res = [
                'status' => 500,
                'message' => 'Course Not Updated',
            ];

            echo json_encode($res);
            return;
        }
    }

?>