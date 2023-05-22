<?php 

    require_once("../../../includes/config.php");

    require_once("../../../enrollment/classes/StudentEnroll.php");

    // echo "yehey";

    if(isset($_GET['id'])){

        $subject_program_id = $_GET['id'];

        // echo $subject_program_id;

        $query = $con->prepare("SELECT * FROM subject_program 
            WHERE subject_program_id=:subject_program_id
            LIMIT 1");

        $query->bindValue(":subject_program_id", $subject_program_id);
        $query->execute();

        if($query->rowCount() > 0){

            $subject_program = $query->fetch(PDO::FETCH_ASSOC);

            // $res = [
            //     'data' => $subject_program
            // ];

            echo json_encode($subject_program);
        }
    }


    if(isset($_POST['subject_code'])
        && isset($_POST['course_level'])
        && isset($_POST['semester'])
        && isset($_POST['subject_program_id'])
        && isset($_POST['edit_subject_template_id'])
        
    ){

        $subject_code = $_POST['subject_code'];
        $course_level = $_POST['course_level'];
        $semester = $_POST['semester'];
        $subject_program_id = $_POST['subject_program_id'];
        $subject_template_id = $_POST['edit_subject_template_id'];


        // echo $subject_template_id;

        // $subject_program_id = null;

        $query = $con->prepare("UPDATE subject_program
            SET subject_code=:subject_code,
                course_level=:course_level,
                semester=:semester,
                subject_template_id=:subject_template_id
            WHERE subject_program_id=:subject_program_id");

        $query->bindValue(":subject_code", $subject_code);
        $query->bindValue(":course_level", $course_level);
        $query->bindValue(":semester", $semester);
        $query->bindValue(":subject_template_id", $subject_template_id);
        $query->bindValue(":subject_program_id", $subject_program_id);

        if($query->execute()){
            echo "success";
        }
    }
?>