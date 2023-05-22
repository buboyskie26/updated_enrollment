<?php 

    require_once("../../../includes/config.php");

    require_once("../../../enrollment/classes/StudentEnroll.php");

    // echo "yehey";

    if(isset($_POST['course_id']) ){

        $enroll = new StudentEnroll($con);

        $school_year_obj = $enroll->GetActiveSchoolYearAndSemester();
        $current_period = $school_year_obj['period'];
        $current_term = $school_year_obj['term'];

        $course_id = $_POST['course_id'];

        // echo $course_id;

        $query = $con->prepare("SELECT 
        
            t2.subject_code, t2.subject_title, t2.unit, t2.semester, t2.subject_id
            FROM course as t1

            INNER JOIN subject as t2 ON t2.course_id = t1.course_id
            WHERE t1.course_id=:course_id
            -- AND t1.school_year_term=:school_year_term
            -- AND t2.semester=:semester
        ");

        $query->bindValue(":course_id", $course_id);
        // $query->bindValue(":school_year_term", $current_term);
        // $query->bindValue(":semester", $current_period);
        $query->execute();

        if($query->rowCount() > 0){
            while($row = $query->fetch(PDO::FETCH_ASSOC)) {

                $subject_code = $row['subject_code'];
                $subject_title = $row['subject_title'];
                $unit = $row['unit'];
                $semester = $row['semester'];
                $subject_id = $row['subject_id'];

                $data[] = array(
                    'subject_id' => $subject_id,
                    'subject_code' => $subject_code,
                    'subject_title' => $subject_title,
                    'unit' => $unit,
                    'semester' => $semester,
                );
            }
            echo json_encode($data);
        }
        // else{
        //     echo "not";
        // }
    }
?>