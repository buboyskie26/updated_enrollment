<?php 

    require_once("../../../includes/config.php");

    require_once("../../../enrollment/classes/StudentEnroll.php");

    // echo "yehey";

    
    if (
        // isset($_POST['subject_code']) && 
        isset($_POST['subject_template_id']) 
        && isset($_POST['course_level']) 
        && isset($_POST['program_id'])
        && isset($_POST['semester'])
        ) {

        $program_id = $_POST['program_id'];
        $subject_template_id = $_POST['subject_template_id'];
        // $subject_code = $_POST['subject_code'];
        $course_level = $_POST['course_level'];
        $semester = $_POST['semester'];

        // echo $course_level;

        $get_subject_template = $con->prepare("SELECT * FROM subject_template
                WHERE subject_template_id=:subject_template_id
                LIMIT 1");

        $get_subject_template->bindValue(":subject_template_id", $subject_template_id);
        $get_subject_template->execute();

        if($get_subject_template->rowCount() > 0){

            $row = $get_subject_template->fetch(PDO::FETCH_ASSOC);

            $subject_title = $row['subject_title'];
            $subject_code = $row['subject_code'];

            $description = $row['description'];
            $unit = $row['unit'];
            $subject_type = $row['subject_type'];
            $pre_requisite_title = $row['pre_requisite_title'];


            $create = $con->prepare("INSERT INTO subject_program
                    (program_id, subject_code, pre_req_subject_title, 
                        subject_title, unit, description, 
                        course_level, semester, subject_type, subject_template_id)

                    VALUES(:program_id, :subject_code, :pre_req_subject_title,
                        :subject_title, :unit, :description,
                        :course_level, :semester, :subject_type, :subject_template_id)");
                                        
            $create->bindParam(':program_id', $program_id);
            $create->bindParam(':subject_code', $subject_code);
            $create->bindParam(':pre_req_subject_title', $pre_requisite_title);
            $create->bindParam(':subject_title', $subject_title);
            $create->bindParam(':unit', $unit);
            $create->bindParam(':description', $description);
            $create->bindParam(':course_level', $course_level);
            $create->bindParam(':semester', $semester);
            $create->bindParam(':subject_type', $subject_type);
            $create->bindParam(':subject_template_id', $subject_template_id);

            if($create->execute()){
                echo "success";
            }
        }

    }
    else{
        echo "not";
    }
?>