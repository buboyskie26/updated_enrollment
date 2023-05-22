<?php

    require_once("../../includes/config.php");
    require_once("../../includes/classes/Teacher.php");
    require_once("../../includes/classes/Message.php");
    require_once("../../includes/classes/Student.php");

    if(isset($_POST['action']) &&
        isset($_POST['to_user_id']) &&
        isset($_POST['from_user_id']) &&
        $_POST["action"] == 'fetch_chat'){


        // Student Id
        $from_user_id = $_POST['from_user_id'];

        // Teacher Id
        $to_user_id = $_POST['to_user_id'];


        $student_username = $con->prepare("SELECT username FROM Student
            WHERE student_id=:student_id
            LIMIT 1");

        $student_username->bindValue(":student_id", $from_user_id);
        $student_username->execute();
        $student_username = $student_username->fetchColumn();

        $studentLoggedInObj = new Student($con, $student_username);
        // 

        $message = new Message($con, null, $studentLoggedInObj);

        $queryTeacher = $con->prepare("SELECT username FROM Teacher
            WHERE teacher_id=:teacher_id
            LIMIT 1");

        $queryTeacher->bindValue(":teacher_id", $to_user_id);
        $queryTeacher->execute();
        $teacher_username = $queryTeacher->fetchColumn();

        $fetchMessage = $message->GetOneToOneMessage($teacher_username,
            $student_username, "student");

        echo json_encode($fetchMessage);
 
    }
?>