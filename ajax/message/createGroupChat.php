<?php

    require_once("../../includes/config.php");


    if(isset($_POST['teacher_course_id']) &&
       isset($_POST['teacher_id'])){
        
        $teacher_course_id = $_POST['teacher_course_id'];
        $teacher_id = $_POST['teacher_id'];

        $check_gc = $con->prepare("SELECT * FROM group_chat
                WHERE teacher_course_id=:teacher_course_id
                AND teacher_id=:teacher_id");

        $check_gc->bindValue(":teacher_course_id", $teacher_course_id);
        $check_gc->bindValue(":teacher_id", $teacher_id);
        $check_gc->execute();

        if($check_gc->rowCount() == 0){

            $sql = $con->prepare("INSERT INTO group_chat
                (teacher_course_id, description, teacher_id)
                VALUES(:teacher_course_id, :description, :teacher_id)");
            
            $sql->bindValue(":teacher_course_id", $teacher_course_id);
            $sql->bindValue(":teacher_id", $teacher_id);
            $sql->bindValue(":description", "Group Chat");

            if($sql->execute()){

                $group_chat_id = $con->lastInsertId();


                // List of all students in this teacher_course.
                $course_student = $con->prepare("SELECT * FROM teacher_course_student
                    WHERE teacher_course_id=:teacher_course_id
                    AND teacher_id=:teacher_id");
                
                $course_student->bindValue(":teacher_course_id", $teacher_course_id);
                $course_student->bindValue(":teacher_id", $teacher_id);
                $course_student->execute();

                if($course_student->rowCount() > 0){

                    while($row = $course_student->fetch(PDO::FETCH_ASSOC)){
                        $student_id = $row['student_id'];


                        $student = $con->prepare("SELECT username FROM Student
                            WHERE student_id=:student_id
                            LIMIT 1");

                        $student->bindValue(":student_id", $student_id);
                        $student->execute();
                        $student_username = $student->fetchColumn();

                        $sql2 = $con->prepare("INSERT INTO group_chat_member
                            (group_chat_id, user_username)
                            VALUES(:group_chat_id, :user_username)");
                
                        $sql2->bindValue(":group_chat_id", $group_chat_id);
                        $sql2->bindValue(":user_username", $student_username);

                        $sql2->execute();
                    }
                }
                else{
                    echo "no";
                }
            }
        }

    }
?>