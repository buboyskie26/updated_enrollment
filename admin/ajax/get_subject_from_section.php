<?php 

    require_once("../../includes/config.php");

    require_once("../../enrollment/classes/StudentEnroll.php");

    // echo "yehey";

    if(isset($_POST['course_id'])){

        $course_id = $_POST['course_id'];

        $query = $con->prepare("SELECT * FROM subject
            WHERE course_id=:course_id
            -- AND NOT EXISTS (
            -- SELECT 1
            -- FROM subject_schedule
            -- WHERE subject_schedule.subject_id = subject.subject_id
            -- )
            ");
        $query->bindValue(":course_id", $course_id);
        $query->execute();

        if($query->rowCount() > 0){
            while($row = $query->fetch(PDO::FETCH_ASSOC)) {
                $data[] = array(
                    'subject_id' => $row['subject_id'],
                    'subject_title' => $row['subject_title']
                );
            }
        }
        echo json_encode($data);
        // echo $course_id;
    }

    

?>