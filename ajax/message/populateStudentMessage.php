<?php

    require_once("../../includes/config.php");
    require_once("../../includes/classes/Teacher.php");
    require_once("../../includes/classes/Message.php");

    if(isset($_POST['action']) &&
        isset($_POST['to_user_id']) &&
        isset($_POST['from_user_id']) &&
        $_POST["action"] == 'fetch_chat'){


        // Teacher Id
        $from_user_id = $_POST['from_user_id'];

        // Student_id
        $to_user_id = $_POST['to_user_id'];
        
        $teacher_username = $con->prepare("SELECT username FROM Teacher
            WHERE teacher_id=:teacher_id
            LIMIT 1");

        $teacher_username->bindValue(":teacher_id", $from_user_id);
        $teacher_username->execute();
        $teacher_username = $teacher_username->fetchColumn();

        $teacherLoggedInObj = new Teacher($con, $teacher_username);
        // 

        $message = new Message($con, null, $teacherLoggedInObj);


        $queryTeacher = $con->prepare("SELECT username FROM Teacher
            WHERE teacher_id=:teacher_id
            LIMIT 1");

        $queryTeacher->bindValue(":teacher_id", $from_user_id);
        $queryTeacher->execute();
        $teacher_username = $queryTeacher->fetchColumn();


        $queryStudent = $con->prepare("SELECT username FROM Student
            WHERE student_id=:student_id
            LIMIT 1");

        $queryStudent->bindValue(":student_id", $to_user_id);
        $queryStudent->execute();

        $student_username = $queryStudent->fetchColumn();

        $fetchMessage = $message->GetOneToOneMessage($teacher_username, $student_username,
            "teacher");

        // echo $fetchMessage;

        echo json_encode($fetchMessage);

        // $num = "not";
        // if(is_numeric($num)){
        //     echo "numeric";
        // }else{
        //     echo "String";
        // }
        // echo $from_user_id;
    }
?>