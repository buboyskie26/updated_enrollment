<?php 

    require_once("../../../includes/config.php");

    require_once("../../../enrollment/classes/StudentEnroll.php");


    if(isset($_POST['course_id'])){

        $course_id = $_POST['course_id'];

        // echo $course_id;
     

        $sql = $con->prepare("SELECT 
        
            t2.subject_title,

            t1.schedule_day,
            t1.schedule_time,
            t1.time_from,
            t1.time_to,
            t1.room

        FROM subject_schedule as t1

        INNER JOIN subject as t2 ON t2.subject_id = t1.subject_id
        WHERE t2.course_id=:course_id");
        
        $sql->bindValue(":course_id", $course_id);
        $sql->execute();

        if($sql->rowCount() > 0){

            while($row = $sql->fetch(PDO::FETCH_ASSOC)){

                $data[] = array(
                    'subject_title' => $row['subject_title'],
                    'schedule_day' => $row['schedule_day'],
                    'schedule_time' => $row['schedule_time'],
                    'time_from' => $row['time_from'],
                    'time_to' => $row['time_to'],
                    'room' => $row['room']
                );
            }
        }

        if(empty($data)){
            echo json_encode([]);
        }else{
            echo json_encode($data);
        }
        
    }
?>
