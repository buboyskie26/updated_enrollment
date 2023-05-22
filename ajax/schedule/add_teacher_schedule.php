<?php

    require_once("../../includes/config.php");
    require_once("../../includes/classes/SubjectPeriod.php");
    require_once("../../includes/classes/SubjectPeriodQuiz.php");
    require_once("../../includes/classes/Teacher.php");

    if(isset($_POST['save_ScheduleForm'])){

        // $start_time = $_POST['start_date'];
        // $end_time = $_POST['end_date'];
        // $hours = $_POST['hours'];

        $teacher_id = $_POST['teacher_id'];
        $teacher_course_id = $_POST['teacher_course'];
        $room_number = $_POST['room_number'];

        $start_hour = $_POST['start_hour'];
        $end_hour = $_POST['end_hour'];
        $day = $_POST['day'];
        
        $teacher_id = $_POST['teacher_id'];

        // if($start_time == null || $end_time == null || $teacher_id == 0)
        // {
        //     $res = [
        //         'status' => 422,
        //         'message' => 'All fields are mandatory'
        //     ];

        //     echo json_encode($res);
        //     return;
        // }


        // If theres a row with the same teacher id 
        // check the previous start_time (7:00am) and end_time (12:00pm)
        // ensure that the incoming schedule, 

        // it have to be greater than end_time (12:01am) with the SAME day (Monday)
        // If the day is (Tuesday) and previous is (Monday) it would be fine.

        // Check the existing data based on the teacher_id
        $checkPrev = $con->prepare("SELECT start_hour, end_hour FROM teacher_schedule
            WHERE teacher_id=:teacher_id
            AND day=:day");
        
        $checkPrev->bindValue(":teacher_id", $teacher_id);
        $checkPrev->bindValue(":day", $day);
        $checkPrev->execute();

        // No record of schedule yet
        if($checkPrev->rowCount() == 0){
            //
            
            $query = $con->prepare("INSERT INTO teacher_schedule
                (teacher_course_id, teacher_id, start_hour,
                    end_hour, room_number, day)
                VALUES(:teacher_course_id, :teacher_id, :start_hour,
                    :end_hour, :room_number, :day)");
            
            $query->bindValue(":teacher_course_id", $teacher_course_id);
            $query->bindValue(":teacher_id", $teacher_id);
            $query->bindValue(":start_hour", $start_hour);
            $query->bindValue(":end_hour", $end_hour);
            $query->bindValue(":room_number", $room_number);
            $query->bindValue(":day", $day);

            // $wasSuccess = $query->execute();
            $wasSuccess = true;
            if($wasSuccess){
                $res = [
                    'status' => 200,
                    'message' => 'Schedule Successfully added'
                ];
                echo json_encode($res);
                return;
            }
            else{
                $res = [
                    'status' => 500,
                    'message' => 'Schedule Not Updated',
                ];

                echo json_encode($res);
                return;
            }
            //
        }
        // else 

        // if($checkPrev->rowCount() > 0){

        //     $overlapping = false;
            
        //     while($row = $checkPrev->fetch(PDO::FETCH_ASSOC)){

        //         $start_hour_db = $row['start_hour'];
        //         $end_hour_db = $row['end_hour'];
 

        //         if(($start_hour > $start_hour_db && $start_hour < $end_hour_db) 
        //             || ($end_hour > $start_hour_db && $end_hour < $end_hour_db )){
        //             $overlapping = true;
        //             break;
        //         }
        //     }
                
        //     if ($overlapping == true) {
        //         // overlapping records found, handle error
        //         // ...
        //         echo " Theres a confliction";
        //     } else {

        //         // no overlapping records found, insert new data
        //         $insert = $con->prepare("INSERT INTO teacher_schedule
        //             (teacher_course_id, teacher_id, start_hour, end_hour, room_number, day)
        //             VALUES(:teacher_course_id, :teacher_id, :start_hour, :end_hour, :room_number, :day)");
            
        //         $insert->bindValue(":teacher_course_id", $teacher_course_id);
        //         $insert->bindValue(":teacher_id", $teacher_id);
        //         $insert->bindValue(":start_hour", $start_hour);
        //         $insert->bindValue(":end_hour", $end_hour);
        //         $insert->bindValue(":room_number", $room_number);
        //         $insert->bindValue(":day", $day);

        //         // $wasSuccess = $insert->execute();
        //         $wasSuccess = true;
        //         if($wasSuccess){
        //             $res = [
        //                 'status' => 200,
        //                 'message' => 'Schedule Successfully added'
        //             ];
        //             echo json_encode($res);
        //             return;
        //         }
        //         else{
        //             $res = [
        //                 'status' => 500,
        //                 'message' => 'Schedule Not Updated',
        //             ];

        //             echo json_encode($res);
        //             return;
        //         }
        //         //
        //         echo " no confliction";
        //     }
        // }


        $checkPrev2 = $con->prepare("SELECT start_hour, end_hour FROM teacher_schedule
            WHERE teacher_id=:teacher_id
            AND day=:day

            AND (:new_start BETWEEN start_hour AND end_hour) 
            OR (:new_end BETWEEN start_hour AND end_hour)
            OR (:new_start <= start_hour AND :new_end >= end_hour)
            
            ");
        
        $checkPrev2->bindValue(":teacher_id", $teacher_id);
        $checkPrev2->bindValue(":day", $day);
        $checkPrev2->bindValue(':new_start', $start_hour);
        $checkPrev2->bindValue(':new_end', $end_hour);

        $checkPrev2->execute();

        if($checkPrev2->rowCount() > 0){
             echo " Theres a conflictionx";
                return;
        }
        else {
            // no overlapping records found, insert new data
            // ...

            echo " no conflictionc";
            return;
        }
        
        // if($checkPrev2->rowCount() > 0){

        //     while($row = $checkPrev2->fetch(PDO::FETCH_ASSOC)){
        //         $start_hour_db = $row['start_hour'];
        //         $end_hour_db = $row['end_hour'];

        //         // $stmt = $pdo->prepare("SELECT * FROM your_table WHERE 
        //         //           (:new_start BETWEEN start_hour AND end_hour) OR 
        //         //           (:new_end BETWEEN start_hour AND end_hour) OR 
        //         //           (start_hour BETWEEN :new_start AND :new_end) OR 
        //         //           (end_hour BETWEEN :new_start AND :new_end)");

        //         $stmt = $con->prepare("SELECT * FROM teacher_schedule

        //             WHERE (:new_start BETWEEN start_hour AND end_hour) 
        //             OR (:new_end BETWEEN start_hour AND end_hour)
        //             OR (:new_start <= start_hour AND :new_end >= end_hour)
                    
        //             LIMIT 1");
        //         // 
        //         // bind values to parameters
        //         $stmt->bindValue(':new_start', $start_hour);
        //         $stmt->bindValue(':new_end', $end_hour);
        //         // execute statement
        //         $stmt->execute();

        //         if ($stmt->rowCount() > 0) {
        //             // overlapping records found, handle error
        //             // ...
        //             echo " Theres a conflictionx";
        //             return;
        //         } 
        //         else {
        //             // no overlapping records found, insert new data
        //             // ...

        //             echo " no conflictionc";
        //             return;
        //         }
 
        //     }
        // } 

        // $timestamp = strtotime($start_time);
        // $weekDayName = date('l', $timestamp);

       
        
    }
    
?>