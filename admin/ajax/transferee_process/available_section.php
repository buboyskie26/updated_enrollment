<?php 

    require_once("../../../includes/config.php");

    require_once("../../../enrollment/classes/StudentEnroll.php");
    require_once("../../../enrollment/classes/Section.php");

    if(isset($_POST['standing_level'])
        && isset($_POST['program_id'])
        && isset($_POST['student_course_id'])
    ){

         $studentEnroll = new StudentEnroll($con);

        $standing_level = $_POST['standing_level'];
        $program_id = $_POST['program_id'];
        $student_course_id = $_POST['student_course_id'];

        $school_year_obj = $studentEnroll->GetActiveSchoolYearAndSemester();

        $current_school_year_id = $school_year_obj['school_year_id'];
        $current_school_year_term = $school_year_obj['term'];
        $current_school_year_period = $school_year_obj['period'];

        // echo $standing_level;

        $selected_section = "";
        $course_level = 11;
        $active = "yes";

        # Only Available now.

        $sql = $con->prepare("SELECT * FROM course
        WHERE program_id=:program_id
        AND active=:active
        AND course_level=:course_level
        ");
        $sql->bindValue(":program_id", $program_id);
        $sql->bindValue(":active", $active);
        $sql->bindValue(":course_level", $standing_level);

        $sql->execute();
    
        if($sql->rowCount() > 0){

            while($get_course = $sql->fetch(PDO::FETCH_ASSOC)){

                $course_id = $get_course['course_id'];
                $program_section = $get_course['program_section'];
                $school_year_term = $get_course['school_year_term'];
                $capacity = $get_course['capacity'];

                $section = new Section($con, $course_id);

                $selected_section= $program_section;

                $totalStudent = $section->GetTotalNumberOfStudentInSection(
                    $course_id, $current_school_year_id);

                $removeSection = "removeSection($course_id, \"$program_section\")";
                // $student_course_id = 5;
                
                $isClosed = $totalStudent >= $capacity;

                $isCloseddisabled = "<input name='selected_course_id' 
                    class='radio' value='$course_id' 
                    type='radio' " . ($course_id == $student_course_id ? "checked" : "") . " " . ($isClosed ? "disabled" : "") . ">";   
                                               

                $data[] = array(
                    'course_id' => $course_id,
                    'program_section' => $program_section,
                    'school_year_term' => $school_year_term,
                    'capacity' => $capacity,
                    'course_id' => $course_id,
                    'totalStudent' => $totalStudent,
                    'semester' => $current_school_year_period
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