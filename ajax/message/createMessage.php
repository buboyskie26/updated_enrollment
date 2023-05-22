<?php

    require_once("../../includes/config.php");


    if(isset($_POST['user_id']) &&
        isset($_POST['receiver_userid']) && 
        isset($_POST['message'])){


        // Student id
        $user_id = $_POST['user_id'];

        // Teacher id
        $receiver_userid = $_POST['receiver_userid'];
        
        $message = $_POST['message'];

        $queryTeacher = $con->prepare("SELECT username FROM Teacher
            WHERE teacher_id=:teacher_id
            LIMIT 1");

        $queryTeacher->bindValue(":teacher_id", $receiver_userid);
        $queryTeacher->execute();
        $teacher_username = $queryTeacher->fetchColumn();

        $queryStudent = $con->prepare("SELECT username FROM Student
            WHERE student_id=:student_id
            LIMIT 1");

        $queryStudent->bindValue(":student_id", $user_id);
        $queryStudent->execute();

        $student_username = $queryStudent->fetchColumn();

        $query = $con->prepare("INSERT INTO message_teacher
            (to_username, from_username, body)
            VALUES(:to_username, :from_username, :body)");

        $query->bindValue(":to_username", $teacher_username);
        $query->bindValue(":from_username", $student_username);
        $query->bindValue(":body", $message);

        if($query->execute()){
            
            $message_teacher_id = $con->lastInsertId();

            $statement = $con->prepare("SELECT body, from_username, to_username,
                 message_teacher_id, message_creation FROM message_teacher
                WHERE message_teacher_id=:message_teacher_id
                LIMIT 1");

            $statement->bindValue(":message_teacher_id", $message_teacher_id);
            $statement->execute();

            $insertData = $statement->fetch(PDO::FETCH_ASSOC);
            echo json_encode($insertData);
        }
        
    }


?>