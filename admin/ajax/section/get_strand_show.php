<?php 

    require_once("../../../includes/config.php");

    require_once("../../../enrollment/classes/StudentEnroll.php");
    require_once("../../../enrollment/classes/StudentEnroll.php");
    require_once("../../../enrollment/classes/Section.php");
    require_once("../../../admin/classes/Subject.php");

    // echo "yehey";

    if(isset($_POST['course_id']) && isset($_POST['school_year_id'])){

        $course_id = $_POST['course_id'];
        $school_year_id = $_POST['school_year_id'];

        $section = new Section($con, $course_id);
        $enroll = new StudentEnroll($con);
        $subject = new Subject($con, null, null);

        $school_year_obj = $enroll->GetActiveSchoolYearAndSemester();
        $current_school_year_id = $school_year_obj['school_year_id'];
        $current_school_year_term = $school_year_obj['term'];
        $current_school_year_period = $school_year_obj['period'];

        // Grade 12 STEM12-A 2024-2025 1st Semester Subjects Schedule
        // Grade 12 STEM12-A 2024-2025 2nd Semester
    
        $section_name = $section->GetSectionName();
        $section_id = $section->GetSectionId();
        $section_s_y = $section->GetSectionSY();
        $section_level = $section->GetSectionGradeLevel();
        $section_advisery = $section->GetSectionAdvisery();

        $getProgramIdBySectionId = $section->GetProgramIdBySectionId($section_id);

        $section_acronym = $section->GetAcronymByProgramId($getProgramIdBySectionId);

        $totalStudent = $section->GetTotalNumberOfStudentInSection($section_id, 
            $current_school_year_id);

        $query_school_year = $con->prepare("SELECT period, term FROM school_year
            WHERE school_year_id=:school_year_id
            LIMIT 1
            ");
        $query_school_year->bindValue(":school_year_id", $school_year_id);
        $query_school_year->execute();

        // $data[] = [];
        if($query_school_year->rowCount() > 0){

            $select_row = $query_school_year->fetch(PDO::FETCH_ASSOC);
            $select_period = $select_row['period'];

            // echo $select_period;
          
            $sql = $con->prepare("SELECT 
            
                t1.*
                -- t2.subject_code as t2_subject_code,
                -- t2.subject_id as t2_subject_id

                FROM subject_program as t1


                -- LEFT JOIN subject as t2 ON t2.subject_program_id = t1.subject_program_id

                WHERE t1.program_id=:program_id

                AND t1.course_level=:course_level
                AND t1.semester=:semester
                -- AND t2.course_id=:course_id

                -- AND (t1.semester='First'
                --     OR
                --     t1.semester='Second'
                -- )
                ORDER BY t1.course_level DESC,
                t1.semester
                ");
            
            $sql->bindValue(":program_id", $getProgramIdBySectionId);
            $sql->bindValue(":course_level", $section_level);
            $sql->bindValue(":semester", $select_period);
            // $sql->bindValue(":course_id", $section_id);
            
            $sql->execute();

            if($sql->rowCount() > 0){

                // echo $sql->rowCount();
                // echo "qwee";

                $t1_subject_id = "";
                $t1_subject_title = "";
                $t1_subject_program_id = "";
                // $t1_subject_code = "";
                $t1_pre_requisite = "";
                $t1_course_level = "";
                $t1_semester = "";
                $t1_subject_type = "";

                while($row = $sql->fetch(PDO::FETCH_ASSOC)){

                    // $t2_subject_code = $row['t2_subject_code'];
                    // $t2_subject_id = $row['t2_subject_id'];
                    // $subject_id = $row['subject_id'];
                    
                    $subject_program_id = $row['subject_program_id'];
                    $subject_code = $row['subject_code'];
                    $subject_title = $row['subject_title'];
                    $course_level = $row['course_level'];
                    $semester = $row['semester'];
                    // $pre_requisite = $row['pre_requisite'];
                    $pre_requisite = $row['pre_req_subject_title'];
                    $subject_type = $row['subject_type'];
                    // $subject_subject_program_id = $row['subject_subject_program_id'];
                    // $subject_subject_title = $row['subject_subject_title'];

                    # Find missing subjects based on subject_program

                    $statuss = "N/A";

                    // echo $subject_program_id;
                    // echo "<br>";


                    $subject_real = $con->prepare("SELECT 
                            
                        t1.subject_id as t1_subject_id,
                        t1.subject_title as t1_subject_title,
                        t1.subject_program_id as t1_subject_program_id,
                        t1.subject_code as t1_subject_code

                        FROM subject as t1

                        

                        WHERE t1.subject_program_id=:subject_program_id
                        AND t1.course_id=:course_id
                        AND t1.semester=:semester

                        LIMIT 1");
                                    
                    $subject_real->bindValue(":subject_program_id", $subject_program_id);
                    $subject_real->bindValue(":course_id", $course_id);
                    $subject_real->bindValue(":semester", $select_period);
                    $subject_real->execute();

                    $t1_subject_program_id = null;
                    $t1_subject_code = null;
                    $get_enrolled_students_in_subject = 0;
                    if($subject_real->rowCount() > 0){

                        $row = $subject_real->fetch(PDO::FETCH_ASSOC);

                        $t1_subject_title = $row['t1_subject_title'];
                        $t1_subject_code = $row['t1_subject_code'];

                        $t1_subject_id = $row['t1_subject_id'];
                        

                        $get_enrolled_students_in_subject = $subject->GetEnrolledStudentInSubjects($t1_subject_id);
                        
                        $t1_subject_program_id = $row['t1_subject_program_id'];

                    }

                    if($t1_subject_program_id != null && $t1_subject_program_id == $subject_program_id){
                        $statuss = "
                            <i class='fas fa-check'></i>
                        ";
                    }

                    else{
                        $statuss = "
                            <button class='btn btn-sm btn-primary'>Populate</button>
                        ";
                    }

                    $data[] = array(
                        'subject_id' => $t1_subject_id,
                        'subject_code' => $t1_subject_code,
                        'subject_title' => $subject_title,
                        'course_level' => $course_level,
                        'semester' => $semester,
                        'pre_requisite' => $pre_requisite,
                        'subject_type' => $subject_type,
                        'statuss' => $statuss,
                        'enrolled_students' => $get_enrolled_students_in_subject,
                    );

                    // echo $t1_subject_program_id;
                    // echo "<br>";
                    // echo $t1_subject_title;
                    // echo "<br>";

                    // echo $subject_program_id;
                    // echo "<br>";
                    // echo $subject_title;
                    // echo "<br>";

                }

                echo json_encode($data);

            }

        }

    }
?>