<?php 

    require_once("../../../includes/config.php");

    require_once("../../../enrollment/classes/StudentEnroll.php");
    require_once("../../../enrollment/classes/OldEnrollees.php");

     
    if(isset($_POST['student_id'])){
        $student_id = $_POST['student_id'];

        // echo $student_id;



        $enroll = new StudentEnroll($con);
        $old_enroll = new OldEnrollees($con, $enroll);

        $checkAlignedSectionGr12 = $old_enroll->CheckGrade12AlignedSections($student_id);

        if($checkAlignedSectionGr12 == false){
            echo "checkAlignedSectionGr12";
            // echo "<script>alert('Grade 12 sections are not aligned. Please contact the registrar.');</script>";
            return;
        }
        $school_year_obj = $enroll->GetActiveSchoolYearAndSemester();

        $current_school_year_term = $school_year_obj['term'];
        $current_school_year_semester = $school_year_obj['period'];

        $sql = $con->prepare("SELECT t1.*, 
            t2.program_section,
            t2.course_id as course_course_id,

            t3.subject_code, t3.subject_title, t3.unit,
            t3.semester, t3.subject_id

            FROM student as t1

            INNER JOIN course as t2 ON t2.course_id = t1.course_id
            LEFT JOIN subject as t3 ON t3.course_id = t1.course_id

            WHERE t1.student_id=:student_id

            AND t2.school_year_term=:school_year_term
            AND t3.semester=:semester
            
        ");

        $sql->bindValue("student_id", $student_id);
        $sql->bindValue("school_year_term", $current_school_year_term);
        $sql->bindValue("semester", $current_school_year_semester);
        $sql->execute();

        $data = [];
        if($sql->rowCount() > 0){

            while($row = $sql->fetch(PDO::FETCH_ASSOC)){
                $course_level = $row['course_level'];

                $subject_code = $row['subject_code'];
                $subject_title = $row['subject_title'];
                $unit = $row['unit'];
                $semester = $row['semester'];
                $subject_id = $row['subject_id'];

                $data[] = array(
                    'course_level' => $row['course_level'],
                    'student_id' => $row['student_id'],
                    'student_status' => $row['student_status'],
                    'program_section' => $row['program_section'],
                    'course_course_id' => $row['course_course_id'],
                    'current_semester' => $current_school_year_semester,
                    'subject_id' => $subject_id,
                    'subject_code' => $subject_code,
                    'subject_title' => $subject_title,
                    'unit' => $unit,
                    'semester' => $semester,
                );
            }
        }


        echo json_encode($data);

    }

?>