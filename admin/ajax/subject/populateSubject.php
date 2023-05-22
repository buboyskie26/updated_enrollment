<?php 

    require_once("../../../includes/config.php");

    require_once("../../../enrollment/classes/StudentEnroll.php");

    // echo "yehey";

    if(isset($_POST['program_id'])){

        $program_id = $_POST['program_id'];
 
        // echo $program_id;
        // echo "qwe";

        $query = $con->prepare("SELECT subject_template_id, subject_title, subject_type FROM subject_template
            WHERE program_id=:program_id
            OR program_id=0
            ORDER BY subject_type DESC
            -- AND NOT EXISTS (
            -- SELECT 1
            -- FROM subject_schedule
            -- WHERE subject_schedule.subject_id = subject.subject_id
            -- )
            ");
        $query->bindValue(":program_id", $program_id);
        $query->execute();

        if($query->rowCount() > 0){
            while($row = $query->fetch(PDO::FETCH_ASSOC)) {
                $data[] = array(
                    'subject_template_id' => $row['subject_template_id'],
                    'subject_title' => $row['subject_title'],
                    'subject_type' => $row['subject_type'],
                    // 'subject_title' => $row['subject_title']
                );
            }
        }
        echo json_encode($data);
        // echo $course_id;
    }

    

?>