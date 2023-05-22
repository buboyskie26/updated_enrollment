<?php

    require_once("../../includes/config.php");


    if(isset($_POST['edit_subjectPeriodQuiz'])){

        $quiz_title = $_POST['quiz_title_edit'];
        $quiz_description = $_POST['quiz_description_edit'];
        $due_date = $_POST['online_exam_datetime_edit'];

        $subject_period_id = $_POST['subject_period_id'];
        $teacher_id = $_POST['teacher_id'];

        $subject_period_quiz_id = $_POST['subject_period_quiz_id'];

        // echo $quiz_title;
        // echo $quiz_description;
        // echo $due_date;

        if($quiz_title == NULL || $quiz_description == null || $due_date == null)
        {
            $res = [
                'status' => 422,
                'message' => 'All fields are mandatory'
            ];
            echo json_encode($res);
            return;
        }

        $query = $con->prepare("UPDATE subject_period_quiz
            SET quiz_title=:quiz_title, quiz_description=:quiz_description, due_date=:due_date
            WHERE subject_period_quiz_id=:subject_period_quiz_id
            AND teacher_id=:teacher_id
            AND subject_period_id=:subject_period_id");

        $query->bindValue(":quiz_title", $quiz_title);
        $query->bindValue(":quiz_description", $quiz_description);
        $query->bindValue(":due_date", $due_date);

        $query->bindValue(":subject_period_id", $subject_period_id);
        $query->bindValue(":subject_period_quiz_id", $subject_period_quiz_id);
        $query->bindValue(":teacher_id", $teacher_id);

        if($query->execute()){

            $res = [
                'status' => 200,
                'message' => 'Quiz Updated Successfully',
            ];

            echo json_encode($res);
            return;
        }
        else{
            $res = [
                'status' => 500,
                'message' => 'Quiz Not Updated',
            ];

            echo json_encode($res);
            return;
        }
    }
?>