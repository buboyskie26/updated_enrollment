<?php

    class Transferee{

    private $con, $studentEnroll, $sqlData;
   
    public function __construct($con, $studentEnroll){
        $this->con = $con;
        $this->studentEnroll = $studentEnroll;
    }

    public function GetAllTransfereeForThisYearSemester(){

        $school_year_obj = $this->studentEnroll->GetActiveSchoolYearAndSemester();

        $current_semester = $school_year_obj['period'];


        
    }
    public function GetTransfereeSubjectSemesterv2($username){

        $totalSubjectsToInsert = [];
        // If student transfered in Grade 11 1st sem
        // If student transfered in Grade 11 2nd sem
        // * All remaining subject of Grade 11 1st sem must be show-up

        
        $school_year_obj = $this->studentEnroll->GetActiveSchoolYearAndSemester();
        $current_semester = $school_year_obj['period'];
        $school_year_term = $school_year_obj['term'];

        $student_course_id = $this->studentEnroll->GetStudentCourseId($username);
        $student_course_name = $this->studentEnroll->GetStudentCourseName($username);
        $student_program_id = $this->studentEnroll->GetStudentProgramId($student_course_id);
        $student_course_level = $this->studentEnroll->GetStudentCourseLevel($username);
        $student_program_section = $this->studentEnroll->GetStudentProgramSection($student_course_id);

        // echo $student_course_id;

        $FIRST_SEMESTER = "First";
        $SECOND_SEMESTER = "Second";
        
        // This wil lserve as indicator for the front end
        $additionalValues = array(
            "transferee" => true
        );

        // Get the Grade 11 1st sem of current TRACK (HUMMS11-A)
        // Getting the subject of prev semester based on transferee course id
        if($student_course_level == 11 && $current_semester == "Second"){

            $get_prev_section = $this->con->prepare("SELECT 
                subject_id, subject_title,
                unit, subject_code
                FROM subject

                WHERE course_id=:course_id
                AND program_id=:program_id
                AND semester=:semester");

            $get_prev_section->bindValue(":course_id", $student_course_id);
            $get_prev_section->bindValue(":program_id", $student_program_id);
            $get_prev_section->bindValue(":semester", $FIRST_SEMESTER);
            $get_prev_section->execute();

            if($get_prev_section->rowCount() > 0){

                // $row2 = array_merge($row2, $additionalValues);
                // $all = $get_prev_section->fetchAll(PDO::FETCH_ASSOC);
                while($row2 = $get_prev_section->fetch(PDO::FETCH_ASSOC)){

                    $row2 = array_merge($row2, $additionalValues);

                    array_push($totalSubjectsToInsert, $row2);

                }
                // echo "wewe";
            }
        }

        else if($student_course_level == 12 && $current_semester == "First"){

            $years = explode("-", $school_year_term);
            // Subtract 1 from each year
            $startYearMinusOne = $years[0] - 1;
            $endYearMinusOne = $years[1] - 1;
            $prev_school_term = $startYearMinusOne . "-" . $endYearMinusOne;

            // echo "wee";
            // Student transferred for Grade 12 1st sem, so Grade 11 1st and 2nd sem.

            // HUMMS12-A -> HUMMS11-A or STEM12-A -> STEM11-A
            $prev_program_section = str_replace("2", "1", $student_program_section);
            $prev_program_section = "STEM11-A";

            # STEM11 || HUMMS11 etc
            // $program_section_without_suffix = substr($prev_program_section, 0, -2); // Remove last two characters (-A)
            $program_section_without_suffix = rtrim($prev_program_section, '-ABCDEFGHIJKLMNOPQRSTUVWXYZ'); // Remove suffix ("-A", "-B", "-C", etc.)
          
            // echo $program_section_without_suffix;
            // Get the first section of latest year (HUMMS11-A).
            $get_prev_section = $this->con->prepare("SELECT course_id, program_section

                FROM course

                -- WHERE program_section=:program_section
                WHERE program_section LIKE :program_section
                AND school_year_term=:school_year_term
                LIMIT 1");    

            // Create a new string with the updated years

           $get_prev_section->bindValue(":program_section", $program_section_without_suffix . '%');
            // $get_prev_section->bindValue(":school_year_term", $prev_school_term);
            $get_prev_section->bindValue(":school_year_term", $school_year_term);
            $get_prev_section->execute();

            if($get_prev_section->rowCount() > 0){
                // echo "eee";
                $get_prev_row = $get_prev_section->fetch(PDO::FETCH_ASSOC);

                // $previous_school_year_course_id = $get_prev_section->fetchColumn();
                $previous_school_year_course_id = $get_prev_row['course_id'];
                $previous_school_year_section = $get_prev_row['course_id'];

                // echo $previous_school_year_section;


                // echo $previous_school_year_course_id;
                $FIRST_SEMESTER = "FIRST";
                // Get the first and second semester subjects
                $get_prev_school_year_section = $this->con->prepare("SELECT 
                    subject_id, subject_title,
                    unit, subject_code

                    FROM subject
                    WHERE course_id=:course_id
                    AND semester=:first_semester
                    OR course_id=:course_id
                    AND semester=:second_semester
                    ");   
                
                $get_prev_school_year_section->bindValue(":course_id", $previous_school_year_course_id);
                $get_prev_school_year_section->bindValue(":first_semester", $FIRST_SEMESTER);
                $get_prev_school_year_section->bindValue(":second_semester", $SECOND_SEMESTER);
                $get_prev_school_year_section->execute();

                if($get_prev_school_year_section->rowCount() > 0){

                    // $all = $get_prev_school_year_section->fetchAll(PDO::FETCH_ASSOC);

                    // print_r($all);
                    while($row2 = $get_prev_school_year_section->fetch(PDO::FETCH_ASSOC)){
                        $row2 = array_merge($row2, $additionalValues);
                        array_push($totalSubjectsToInsert, $row2);
                    }
                }
            }else{
                // echo "eqwe";
            }
        }

        else if($student_course_level == 12 && $current_semester == "Second"){

            // Student transferred for Grade 12 2nd sem,
            // so Grade 11 1st, 2nd sem and Grade 12 1st sem

              // HUMMS12-A -> HUMMS11-A or STEM12-a -> STEM11-A
            $prev_program_section = str_replace("2", "1", $student_program_section);

            // Get the first section of latest year (HUMMS11-A).
            $get_prev_section = $this->con->prepare("SELECT course_id FROM course

                WHERE program_section=:program_section
                AND program_id=:program_id
                AND school_year_term=:school_year_term
                LIMIT 1");    

            // echo $school_year_term;
            // $previous_school_year_term = "";

            $years = explode("-", $school_year_term);
            // Subtract 1 from each year
            $startYearMinusOne = $years[0] - 1;
            $endYearMinusOne = $years[1] - 1;

            // Create a new string with the updated years
            $prev_school_term = $startYearMinusOne . "-" . $endYearMinusOne;

            // echo $newString;

            $get_prev_section->bindValue(":program_section", $prev_program_section);
            $get_prev_section->bindValue(":program_id", $student_program_id);
            $get_prev_section->bindValue(":school_year_term", $prev_school_term);
            $get_prev_section->execute();

        

            if($get_prev_section->rowCount() > 0){
                $previous_school_year_course_id = $get_prev_section->fetchColumn();

                $FIRST_SEMESTER = "FIRST";
                // Get the first and second semester subjects
                $get_prev_school_year_section = $this->con->prepare("SELECT 
                    subject_id, subject_title,
                    unit, subject_code

                    FROM subject
                    WHERE course_id=:course_id
                    AND semester=:first_semester
                    OR course_id=:course_id
                    AND semester=:second_semester
                    ");   
                
                $get_prev_school_year_section->bindValue(":course_id", $previous_school_year_course_id);
                $get_prev_school_year_section->bindValue(":first_semester", $FIRST_SEMESTER);
                $get_prev_school_year_section->bindValue(":second_semester", $SECOND_SEMESTER);
                $get_prev_school_year_section->execute();

                if($get_prev_school_year_section->rowCount() > 0){

                    // $all = $get_prev_school_year_section->fetchAll(PDO::FETCH_ASSOC);

                    // print_r($all);
                    echo "<br>";
                    echo "<br>";
                    while($row2 = $get_prev_school_year_section->fetch(PDO::FETCH_ASSOC)){
                        $row2 = array_merge($row2, $additionalValues);
                        array_push($totalSubjectsToInsert, $row2);
                    }

                }
            }

            // Grade 12 1st sem (Will get to 1st sem current course_id)
            $get_prev_gr12_section = $this->con->prepare("SELECT 
             subject_id, subject_title,
                    unit, subject_code
            
             FROM subject
                WHERE course_id=:course_id
                AND program_id=:program_id
                AND semester=:semester");

            $get_prev_gr12_section->bindValue(":course_id", $student_course_id);
            $get_prev_gr12_section->bindValue(":program_id", $student_program_id);
            $get_prev_gr12_section->bindValue(":semester", $FIRST_SEMESTER);
            $get_prev_gr12_section->execute();

            if($get_prev_gr12_section->rowCount() > 0){

                // $all = $get_prev_gr12_section->fetchAll(PDO::FETCH_ASSOC);

                // print_r($all);
                while($row2 = $get_prev_gr12_section->fetch(PDO::FETCH_ASSOC)){
                    $row2 = array_merge($row2, $additionalValues);
                    array_push($totalSubjectsToInsert, $row2);

                }
            }

        }


        // Default subjects to be taken by current semester.
        $currentSemesterSubjectBasedOnLevel = $this->con->prepare("SELECT 
                subject_id, subject_title,
                unit, subject_code

                FROM subject

                WHERE course_id=:course_id
                AND program_id=:program_id
                AND course_level=:course_level
                AND semester=:semester
            ");
            
        $currentSemesterSubjectBasedOnLevel->bindValue(":course_id", $student_course_id);
        $currentSemesterSubjectBasedOnLevel->bindValue(":program_id", $student_program_id);
        $currentSemesterSubjectBasedOnLevel->bindValue(":course_level", $student_course_level);
        $currentSemesterSubjectBasedOnLevel->bindValue(":semester", $current_semester);
        $currentSemesterSubjectBasedOnLevel->execute();

        if($currentSemesterSubjectBasedOnLevel->rowCount() > 0){
            // echo "have";
            // $current_semester_subject = $currentSemesterSubjectBasedOnLevel->fetchAll(PDO::FETCH_ASSOC);
            while($row = $currentSemesterSubjectBasedOnLevel->fetch(PDO::FETCH_ASSOC)){
                // $row = array_merge($row, $additionalValuesv2);
                array_push($totalSubjectsToInsert, $row);
            }
        }

        // print_r($totalSubjectsToInsert);

        // print_r($totalSubjectsToInsert);
        return $totalSubjectsToInsert;
    }

    public function GetTransfereeSubjectSemesterv3($username){


        // $school_year_term = "";
        // $get_prev_section = $this->con->prepare("SELECT course_id FROM course
        //     WHERE program_section=:program_section
        //     AND school_year_term=:school_year_term
        //     LIMIT 1");

        // $get_prev_section->bindValue(":program_section", $prev_program_section);
        // $get_prev_section->bindValue(":school_year_term", $school_year_term);
        // $get_prev_section->execute();
        
    }

    public function GetTransfereeSubjectSemester($username){

        // If student transfered in Grade 11 1st sem
        // If student transfered in Grade 11 2nd sem
        // * All remaining subject of Grade 11 1st sem must be show-up

        $school_year_obj = $this->studentEnroll->GetActiveSchoolYearAndSemester();
        $current_semester = $school_year_obj['period'];
        $school_year_term = $school_year_obj['term'];

        $student_course_id = $this->studentEnroll->GetStudentCourseId($username);
        $student_course_name = $this->studentEnroll->GetStudentCourseName($username);
        $student_program_id = $this->studentEnroll->GetStudentProgramId($student_course_id);
        $student_course_level = $this->studentEnroll->GetStudentCourseLevel($username);

        $prev_program_section = str_replace("12", "11", $student_course_name);

        // It should be only one
        $get_prev_section = $this->con->prepare("SELECT course_id FROM course
            WHERE program_section=:program_section
            AND school_year_term=:school_year_term
            LIMIT 1");

        $get_prev_section->bindValue(":program_section", $prev_program_section);
        $get_prev_section->bindValue(":school_year_term", $school_year_term);
        $get_prev_section->execute();

        $prev_course_level = 11;
        $totalSubjectsToInsert = [];

        if($get_prev_section->rowCount()){

            $prev_course_id = $get_prev_section->fetchColumn();

            $get_prev_section_subject = $this->con->prepare("SELECT 
                subject_id, subject_title,
                unit, subject_code

                FROM subject
                WHERE course_id=:course_id
                AND program_id=:program_id
                AND course_level=:course_level

                ");
            $get_prev_section_subject->bindValue(":course_id", $prev_course_id);
            $get_prev_section_subject->bindValue(":program_id", $student_program_id);
            $get_prev_section_subject->bindValue(":course_level", $prev_course_level);
            $get_prev_section_subject->execute();
             
            $samp = [];
            if($get_prev_section_subject->rowCount() > 0){
                // append
                // $prev_section_subjects = $get_prev_section_subject->fetchAll(PDO::FETCH_ASSOC);
                // print_r($prev_section_subjects);
                // echo "<br>";

                // Subjects that Transferee will be inserted
                while($row = $get_prev_section_subject->fetch(PDO::FETCH_ASSOC)){
                   
                    // $row = array_merge($row, $additionalValues);
                    // array_push($totalSubjectsToInsert, $row);
                }
                // array_push($totalSubjectsToInsert, $prev_section_subjects);
            }
            // print_r($samp);
            // Current Sem = 1st Sem
            // If student transfered in Grade 12 1st sem
            // If registrar place that student to HUMMS12-A,
            // The subject in the past years would be HUMMS11-A but must mark as (transferee)
            // Query must all the subjects from Grade 11 to this Grade 12 1st sem


            // Select the course level and course id of that tranasferee student
            // anmd combined it

            $currentSemesterSubjectBasedOnLevel = $this->con->prepare("SELECT 
                subject_id, subject_title,
                unit, subject_code

                FROM subject

                WHERE course_id=:course_id
                AND program_id=:program_id
                AND course_level=:course_level
                AND semester=:semester
            ");
            
            $currentSemesterSubjectBasedOnLevel->bindValue(":course_id", $student_course_id);
            $currentSemesterSubjectBasedOnLevel->bindValue(":program_id", $student_program_id);
            $currentSemesterSubjectBasedOnLevel->bindValue(":course_level", $student_course_level);
            $currentSemesterSubjectBasedOnLevel->bindValue(":semester", $current_semester);
            $currentSemesterSubjectBasedOnLevel->execute();

            if($currentSemesterSubjectBasedOnLevel->rowCount() > 0){

                echo "have";

                // $current_semester_subject = $currentSemesterSubjectBasedOnLevel->fetchAll(PDO::FETCH_ASSOC);

                while($row = $currentSemesterSubjectBasedOnLevel->fetch(PDO::FETCH_ASSOC)){
                    // $row = array_merge($row, $additionalValuesv2);
                    array_push($totalSubjectsToInsert, $row);
                }
                // echo "<br>";
                // print_r($current_semester_subject);
            }else{

                echo "none";
            }
            // return $totalSubjectsToInsert;
        }else{
            // if did not have, looked for previous S.Y Term section with the same program_section
            echo "Look for prev S.Y Term with the same program section";
        }
        // print_r($totalSubjectsToInsert);

        return $totalSubjectsToInsert;

    } 
    
    public function CheckIfNotTransfereeSubject($username, $subject_id){

        $student_course_id = $this->studentEnroll->GetStudentCourseId($username);
        $student_course_level = $this->studentEnroll->GetStudentCourseLevel($username);

        $student_id = $this->studentEnroll->GetStudentId($username);


        $get_subject = $this->con->prepare("SELECT course_id FROM subject
            WHERE subject_id=:subject_id
            -- AND course_id=:course_id
            -- AND course_level = :course_level
            LIMIT 1");

        $get_subject->bindValue(":subject_id", $subject_id);
        // $get_subject->bindValue(":course_id", $student_course_id);
        // $get_subject->bindValue(":course_level", $student_course_level);
        $get_subject->execute();

        $value = false;
        if($get_subject->rowCount() > 0){

            $subject_course_id = $get_subject->fetchColumn();

            $get_subject = $this->con->prepare("SELECT enrollment_id FROM enrollment
                WHERE student_id=:student_id
                AND course_id=:course_id");
            
            $get_subject->bindValue(":student_id", $student_id);
            $get_subject->bindValue(":course_id", $student_course_id);
            $get_subject->execute();

            $count = $get_subject->rowCount();
            
            // echo $count;

            // Please fix the bug.

            // Transferee transfered on Grade 12 2nd sem
            // The Grade 12 1st sem and below should be is_Transferee subject.
            
            if($subject_course_id === $student_course_id ){
                // no
                // echo $subject_course_id . " true";

                // Not transferee subject.
                $value = true;
            }
            if($subject_course_id !== $student_course_id){
                // yes
                // echo $subject_course_id . " false ";

                // Transferee subject.
                $value = false;
            }
            // If no data it means the subject that the registrar
            // trying to insert to you is your section.
            // return false;

        }else{
            echo "Subject error";
        }

        return $value;
    }

    // public function CheckIfTransfereeSubject($username, $subject_id, $current_school_year_period) : bool{

    //     $student_course_id = $this->studentEnroll->GetStudentCourseId($username);
    //     $student_course_level = $this->studentEnroll->GetStudentCourseLevel($username);

    //     $student_id = $this->studentEnroll->GetStudentId($username);
    //     $transfereeSubject = false;


    //     // $get_subject = $this->con->prepare("SELECT subject_title FROM subject
    //     //     WHERE subject_id=:subject_id
    //     //     -- AND course_id=:course_id
    //     //     -- AND course_level = :course_level
    //     //     LIMIT 1");

    //     // $get_subject->bindValue(":subject_id", $subject_id);
    //     // $get_subject->execute();

    //     // if($get_subject->rowCount() > 0){

    //     //     if($student_course_level === 11 
    //     //         && $current_school_year_period === "Second"){

    //     //         // Get all Grade 11 1st Sem.
    //     //         $selected_subject_title = $get_subject->fetchColumn();

                
    //     //         // Get the appropriate subjects for current semester 
    //     //         // and course level of student.
                


    //     //     }        
             
    //     // }


    //     return $transfereeSubject;
    // }

    public function GetSHSStudentEnrolledSubjects($username, $subject_id){

        $student_id = $this->studentEnroll->GetStudentId($username);

        $query = $this->con->prepare("SELECT subject_id

            FROM student_subject
            WHERE student_id=:student_id
            AND subject_id=:subject_id
            ");

        $query->bindValue(":student_id", $student_id);
        $query->bindValue(":subject_id", $subject_id);
        $query->execute();

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function GetSHSStudentEnrolledSubjectsTitle($username, $subject_id){

        $student_id = $this->studentEnroll->GetStudentId($username);

        $query = $this->con->prepare("SELECT t2.subject_title, t1.subject_id

            FROM student_subject as t1

            LEFT JOIN subject as t2 ON t2.subject_id = t1.subject_id

            WHERE t1.student_id=:student_id
            AND t1.subject_id=:subject_id
            ");

        $query->bindValue(":student_id", $student_id);
        $query->bindValue(":subject_id", $subject_id);
        $query->execute();

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    

}
?>