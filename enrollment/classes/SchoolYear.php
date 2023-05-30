<?php

    class SchoolYear{

    // private $con, $userLoggedIn, $sqlData;
   
    // public function __construct($con){
    //     $this->con = $con;
    // }

    private $con, $sqlData;

    public function __construct($con, $input)
    {
        $this->con = $con;
        $this->sqlData = $input;
        
        if(!is_array($input)){
            $query = $this->con->prepare("SELECT * FROM school_year
            WHERE school_year_id=:school_year_id");

            $query->bindValue(":school_year_id", $input);
            $query->execute();
            $this->sqlData = $query->fetch(PDO::FETCH_ASSOC);
        }
    }

    public function GetSYEnrollmentStatus() {
        return isset($this->sqlData['enrollment_status']) 
            ? $this->sqlData["enrollment_status"] : null; 
    }

    public function GetStartEnrollment() {
        return isset($this->sqlData['start_enrollment_date']) 
            ? $this->sqlData["start_enrollment_date"] : null; 
    }

    public function GetEndEnrollment() {
        return isset($this->sqlData['end_enrollment_date']) 
            ? $this->sqlData["end_enrollment_date"] : null; 
    }

    public function GetEndPeriod() {
        return isset($this->sqlData['end_period']) 
            ? $this->sqlData["end_period"] : null; 
    }

    public function GetStartPeriod() {
        return isset($this->sqlData['start_period']) 
            ? $this->sqlData["start_period"] : null; 
    }

    public function EnrollmentDateFinished() {
        return isset($this->sqlData['is_finished']) 
            ? $this->sqlData["is_finished"] : null; 
    }

    public function DoesStartEnrollmentComesIn($current_school_year_id){

        $start_enrollment = $this->GetStartEnrollment();

        // echo $start_enrollment;

        $currentDateTime = new DateTime();
        $current_time = $currentDateTime->format('Y-m-d H:i:s');

        if ($start_enrollment != null && $current_time >= $start_enrollment) {

            // echo "enrollment_status becomes 1 *(ACTIVE)";


            $set_active = $this->SetSYEnrollmentStatusActive($current_school_year_id);
            if($set_active == true){
                AdminUser::success("Todays Semester Enrollment is now started.",
                    "index.php");
                exit();
            }

            // echo $this->EnrollmentDateFinished();
        } else if ($current_time < $start_enrollment){
            // echo "Start Enrollment is yet come.";

        }
    }

    public function CheckIfEnrollmentStatusIsActive($school_year_id){

        $query = $this->con->prepare("SELECT enrollment_status FROM school_year
            WHERE enrollment_status=:enrollment_status
            AND school_year_id=:school_year_id
            ");

        $query->bindValue(":enrollment_status", 1);
        $query->bindValue(":school_year_id", $school_year_id);
        $query->execute();

        return $query->rowCount() > 0;
    }
       
    public function GetActiveSchoolYearAndSemester(){

        $query = $this->con->prepare("SELECT school_year_id,
            term, period

            FROM school_year
            WHERE statuses='Active'
            -- ORDER BY school_year_id DESC
            LIMIT 1");

        $query->execute();
        
        return $query->fetch(PDO::FETCH_ASSOC);
    }

    public function EndOfCurrentSemesterInit(){

        $school_year_obj = $this->GetActiveSchoolYearAndSemester();
        $current_school_year_term = $school_year_obj['term'];
        $current_school_year_period = $school_year_obj['period'];


        $section = new Section($this->con, null);
        $section->ResetSectionIsFull($current_school_year_term, $current_school_year_period);


        $current_school_year_id = $school_year_obj['school_year_id'];


        $end_period = $this->GetEndPeriod();

        // echo $end_period;

        $currentDateTime = new DateTime();
        $current_time = $currentDateTime->format('Y-m-d H:i:s');

        if ($end_period != null && $current_time >= $end_period) {

            # 1. First Semester
            // Should switch into 2nd Semester.
            if($current_school_year_period == "First"){

                $get_next_sy = $this->con->prepare("SELECT school_year_id FROM school_year
                    WHERE term=:term
                    AND period='Second'
                    AND statuses='InActive'
                    -- AND start_enrollment_date IS NOT NULL
                    ");

                $get_next_sy->bindValue(":term", $current_school_year_term);
                $get_next_sy->execute();

                if($get_next_sy->rowCount() > 0){

                    $get_next_sy_id = $get_next_sy->fetchColumn();

                    # UPDATE.
                    $updatingSuccess = $this->ChangeSchoolYearActive(
                        $get_next_sy_id, $current_school_year_id);

                    if($updatingSuccess == true){
                        $school_year_objv2 = $this->GetActiveSchoolYearAndSemester();
                        $current_school_year_termv2 = $school_year_objv2['term'];
                        $current_school_year_periodv2 = $school_year_objv2['period'];
                        AdminUser::success("System semester is set to: $current_school_year_periodv2 of SY $current_school_year_termv2", "");
                    }
                }
            }

            #2. Second Semester
            if($current_school_year_period == "Second"){



                $newSchoolYear = $this->IncrementSchoolYearTerm($current_school_year_term);

                $get_next_sy = $this->con->prepare("SELECT school_year_id 
                    FROM school_year
                    WHERE term=:term
                    AND period='First'
                    AND statuses='InActive'

                    ORDER BY school_year_id ASC

                    LIMIT 1
                    ");

                $get_next_sy->bindValue(":term", $newSchoolYear);
                $get_next_sy->execute();

                if($get_next_sy->rowCount() > 0){

                    $get_next_sy_id = $get_next_sy->fetchColumn();

                    // echo $get_next_sy_id;

                    $get = $this->con->prepare("SELECT *
                            FROM school_year
                            WHERE school_year_id=:next_school_year_id
                            ");
                    $get->bindValue(":next_school_year_id", $get_next_sy_id);
                    $get->execute();

                    if($get->rowCount() > 0){
                        // echo $current_school_year_id;
                        // echo $get_next_sy_id;
                        
                        $isSuccess = $this->SetInActiveSchoolYear($current_school_year_id);

                        if($isSuccess){
                        // if(false){

                            $update = $this->con->prepare("UPDATE school_year
                                    SET statuses=:statuses
                                    WHERE school_year_id=:next_school_year_id
                                    ");
                                
                            $update->bindValue(":statuses", "Active");
                            $update->bindValue(":next_school_year_id", $get_next_sy_id);

                            if($update->execute()){

                                $newly_school_year_obj = $this->GetActiveSchoolYearAndSemester();
                                $new_school_year_term = $newly_school_year_obj['term'];

                                AdminUser::success("New S.Y and Semester has been set", "");

                                # Move Up Section.
                                # Populate Section Subjects.

                                # Algorithm for Moving Up the Current Tertiary Section Based on the Current Term (2021-2022)
                                # As registrar selects new School Year From Second Sem(S.Y2021) To (S.Y2022)First Sem

                                # 1. Select the current Section(s) based in todays S.Y
                                # 2. Loop each section. Update Section active='yes' column into active='no'
                                # 3. For every update of no.2, Create a newly tertiary section that will move-up the program_section (from ABE-1A to ABE-2A)
                                # 4. Get the newly created course_tertiary_id
                                # 5. Get the course_tertiary_id, course_level, program_id column on that newly created section
                                # 6. Get All Subject_Program Table referencing the program_id and course_level
                                # 7. Insert the subject_tertiary table that referenced the necessary column of Subject_Program Table (subject_title, subject_code etc)
                                # 8. In just changing the S.Y from 2nd sem to 1st sem. We created individual newly section based on the previous active tertiary_course section
                                # Which we have included its appropriate subjects.

                                $get_course_section = $this->con->prepare("SELECT * 
                                
                                    FROM course
                                    WHERE school_year_term=:school_year_term
                                    AND active=:active");

                                $get_course_section->bindValue(":school_year_term", $current_school_year_term);
                                $get_course_section->bindValue(":active", "yes");
                                $get_course_section->execute();

                                // if(false){
                                if($get_course_section->rowCount() > 0){

                                    $update_shs_course = $this->con->prepare("UPDATE course
                                        SET active=:active
                                        -- WHERE course_level=:course_level
                                        WHERE course_id=:course_id");

                                    $moveUpShsSection = $this->con->prepare("INSERT INTO course
                                        (program_section, program_id, course_level, capacity, school_year_term, active, is_full, previous_course_id)
                                        VALUES(:program_section, :program_id, :course_level, :capacity, :school_year_term, :active, :is_full, :previous_course_id)");

                                    $moveupCourseId = null;

                                    $insert_section_subject = $this->con->prepare("INSERT INTO subject
                                        (subject_title, description, subject_program_id, unit, semester, program_id, course_level, course_id, subject_type, subject_code)
                                        VALUES(:subject_title, :description, :subject_program_id, :unit, :semester, :program_id, :course_level, :course_id, :subject_type, :subject_code)");

                                    $get_subject_program = $this->con->prepare("SELECT * FROM subject_program
                                        WHERE program_id=:program_id
                                        AND course_level=:course_level
                                        ");

                                    $getShsCoursesForMovingUp = $get_course_section->fetchAll(PDO::FETCH_ASSOC);

                                    $isFinished = false;

                                    foreach ($getShsCoursesForMovingUp as $key => $value) {

                                            $tertiary_program_id = $value['program_id'];
                                            $tertiary_course_level = $value['course_level'];
                                            $course_id = $value['course_id'];



                                            $previous_course_tertiary_id = $value['course_id'];
                                           
                                            $tertiary_program_section = $value['program_section'];

                                            $new_program_section = str_replace('11', 12, $tertiary_program_section);

                                            $update_shs_course->bindValue(":active", "no");
                                            $update_shs_course->bindValue(":course_id", $course_id);

                                            if($update_shs_course->execute()){

                                                // echo "Tertiary Section $tertiary_program_section is de-activated";
                                                // echo "<br>";

                                                $moveUpShsSection->bindValue(":program_section", $new_program_section);
                                                $moveUpShsSection->bindValue(":program_id", $tertiary_program_id);
                                                $moveUpShsSection->bindValue(":course_level", $tertiary_course_level + 1);
                                                $moveUpShsSection->bindValue(":capacity", "2");
                                                $moveUpShsSection->bindValue(":school_year_term", $new_school_year_term);
                                                $moveUpShsSection->bindValue(":active", "yes");
                                                $moveUpShsSection->bindValue(":is_full", "no");
                                                $moveUpShsSection->bindValue(":previous_course_id", $previous_course_tertiary_id);

                                                if($moveUpShsSection->execute()){
                                                    // echo "New Tertiary $new_program_section section has been established at new $new_school_year_term";
                                                    // echo "<br>";
                                                    
                                                    $moveupCourseId = $this->con->lastInsertId();
                                                    
                                                    if($moveupCourseId != null){

                                                        $newly_created_tertiary_program = $this->con-> prepare("SELECT 
                                                                course_id, course_level, program_id, program_section
                                                                
                                                                FROM course
                                                                WHERE course_id=:course_id
                                                                LIMIT 1
                                                            ");

                                                        $newly_created_tertiary_program->bindValue(":course_id", $moveupCourseId);
                                                        $newly_created_tertiary_program->execute();

                                                        if($newly_created_tertiary_program->rowCount() > 0){

                                                            $newly_shs_section_row = $newly_created_tertiary_program->fetch(PDO::FETCH_ASSOC);

                                                            $newly_created_shs_program_id = $newly_shs_section_row['program_id'];
                                                            $newly_created_shs_course_level = $newly_shs_section_row['course_level'];
                                                            $newly_created_shs_program_section = $newly_shs_section_row['program_section'];



                                                            $get_subject_program->bindValue(":program_id", $newly_created_shs_program_id);
                                                            $get_subject_program->bindValue(":course_level", $newly_created_shs_course_level);
                                                            $get_subject_program->execute();


                                                            if($get_subject_program->rowCount() > 0){
                                                                
                                                                $isSubjectCreated = false;

                                                                while($row = $get_subject_program->fetch(PDO::FETCH_ASSOC)){

                                                                    $program_program_id = $row['subject_program_id'];
                                                                    $program_course_level = $row['course_level'];
                                                                    $program_semester = $row['semester'];
                                                                    $program_subject_type = $row['subject_type'];
                                                                    $program_subject_title = $row['subject_title'];
                                                                    $program_subject_description = $row['description'];
                                                                    $program_subject_unit = $row['unit'];

                                                                    $program_subject_code = $row['subject_code'] . "-" . $newly_created_shs_program_section; 
                                                                    // $program_subject_code = $row['subject_code'];

                                                                    $insert_section_subject->bindValue(":subject_title", $program_subject_title);
                                                                    $insert_section_subject->bindValue(":description", $program_subject_description);
                                                                    $insert_section_subject->bindValue(":subject_program_id", $program_program_id);
                                                                    $insert_section_subject->bindValue(":unit", $program_subject_unit);
                                                                    $insert_section_subject->bindValue(":semester", $program_semester);
                                                                    $insert_section_subject->bindValue(":program_id", $newly_created_shs_program_id);
                                                                    $insert_section_subject->bindValue(":course_level", $program_course_level);
                                                                    $insert_section_subject->bindValue(":course_id", $moveupCourseId);
                                                                    $insert_section_subject->bindValue(":subject_type", $program_subject_type);
                                                                    $insert_section_subject->bindValue(":subject_code", $program_subject_code);

                                                                    // $insert_section_subject->execute();
                                                                    if($insert_section_subject->execute()){
                                                                        $isSubjectCreated = true;
                                                                        $isFinished = true;
                                                                    }
                                                                }

                                                            }
                                                        }
                                                    }
                                            
                                                }
                                            }
                                    }

                                }

                                $section = new Section($this->con, null);

                                # IT SHOULD DYNAMIC, WHATEVER sets by admin
                                # Should automatically created for every Fresh S.Y
                                if($isFinished){


                                    $STEM_PROGRAM_ID = $section->GetStrandrogramId("STEM");
                                    $HUMMS_PROGRAM_ID = $section->GetStrandrogramId("HUMMS");

                                    $defaultGrade11StemStrand = $this->con->prepare("INSERT INTO course
                                        (program_section, program_id, course_level, capacity, school_year_term, active, is_full)
                                        VALUES(:program_section, :program_id, :course_level, :capacity, :school_year_term, :active, :is_full)");

                                    $defaultGrade11StemStrand->bindValue(":program_section", "STEM11-A");
                                    $defaultGrade11StemStrand->bindValue(":program_id", $STEM_PROGRAM_ID, PDO::PARAM_INT);
                                    $defaultGrade11StemStrand->bindValue(":course_level", 11, PDO::PARAM_INT);
                                    $defaultGrade11StemStrand->bindValue(":capacity", 2);
                                    $defaultGrade11StemStrand->bindValue(":school_year_term", $new_school_year_term);
                                    $defaultGrade11StemStrand->bindValue(":active", "yes");
                                    $defaultGrade11StemStrand->bindValue(":is_full", "no");


                                    if($defaultGrade11StemStrand->execute()){}

                                    $defaultGrade11HummsStrand = $this->con->prepare("INSERT INTO course
                                        (program_section, program_id, course_level, capacity, school_year_term, active, is_full)
                                        VALUES(:program_section, :program_id, :course_level, :capacity, :school_year_term, :active, :is_full)");

                                    $defaultGrade11HummsStrand->bindValue(":program_section", "HUMMS11-A");
                                    $defaultGrade11HummsStrand->bindValue(":program_id", $HUMMS_PROGRAM_ID, PDO::PARAM_INT);
                                    $defaultGrade11HummsStrand->bindValue(":course_level", 11, PDO::PARAM_INT);
                                    $defaultGrade11HummsStrand->bindValue(":capacity", 2);
                                    $defaultGrade11HummsStrand->bindValue(":school_year_term", $new_school_year_term);
                                    $defaultGrade11HummsStrand->bindValue(":active", "yes");
                                    $defaultGrade11HummsStrand->bindValue(":is_full", "no");

                                    if($defaultGrade11HummsStrand->execute()){}


                                        $newlyElevenCourse = $this->con->prepare("SELECT 

                                            c.course_id, c.course_level, c.program_id, c.program_section, sp.subject_program_id,
                                            sp.semester, sp.subject_type, sp.subject_title, sp.description, sp.unit, sp.subject_code

                                            FROM course c
                                            INNER JOIN subject_program sp ON sp.program_id = c.program_id AND sp.course_level = c.course_level
                                            WHERE c.school_year_term = :school_year_term
                                            AND c.course_level = 11
                                            AND c.active = 'yes'
                                        ");

                                        $newlyElevenCourse->bindValue(":school_year_term", $new_school_year_term);
                                        $newlyElevenCourse->execute();

                                        if ($newlyElevenCourse->rowCount() > 0) {
                                            $newlyShsSectionList = $newlyElevenCourse->fetchAll(PDO::FETCH_ASSOC);

                                            $insertSectionSubject = $this->con->prepare("INSERT INTO subject (
                                                    subject_title, description, subject_program_id, unit, semester,
                                                    program_id, course_level, course_id, subject_type, subject_code
                                                )
                                                VALUES (
                                                    :subject_title, :description, :subject_program_id, :unit, :semester,
                                                    :program_id, :course_level, :course_id, :subject_type, :subject_code
                                                )
                                            ");

                                            foreach ($newlyShsSectionList as $value) {
                                                $newlyCreatedStemShsProgramId = $value['program_id'];
                                                $newlyCreatedStemShsCourseLevel = $value['course_level'];
                                                $newlyCreatedStemShsProgramSection = $value['program_section'];
                                                $newlyCreatedStemShsCourseId = $value['course_id'];
                                                $programSubjectId = $value['subject_program_id'];
                                                $programCourseLevel = $value['course_level'];
                                                $programSemester = $value['semester'];
                                                $programSubjectType = $value['subject_type'];
                                                $programSubjectTitle = $value['subject_title'];
                                                $programSubjectDescription = $value['description'];
                                                $programSubjectUnit = $value['unit'];
                                                $programSubjectCode = $value['subject_code'] . "-" . $newlyCreatedStemShsProgramSection;

                                                $insertSectionSubject->bindValue(":subject_title", $programSubjectTitle);
                                                $insertSectionSubject->bindValue(":description", $programSubjectDescription);
                                                $insertSectionSubject->bindValue(":subject_program_id", $programSubjectId);
                                                $insertSectionSubject->bindValue(":unit", $programSubjectUnit);
                                                $insertSectionSubject->bindValue(":semester", $programSemester);
                                                $insertSectionSubject->bindValue(":program_id", $newlyCreatedStemShsProgramId);
                                                $insertSectionSubject->bindValue(":course_level", $newlyCreatedStemShsCourseLevel);
                                                $insertSectionSubject->bindValue(":course_id", $newlyCreatedStemShsCourseId);
                                                $insertSectionSubject->bindValue(":subject_type", $programSubjectType);
                                                $insertSectionSubject->bindValue(":subject_code", $programSubjectCode);

                                                $insertSectionSubject->execute();
                                            }

                                            $isFinished = true;
                                        }
                                }
                            }
                            # Default Grade 11 Section For Fresh S.Y First Semester.

                        }
                    }

                    # UPDATE.
                    // $updatingSuccess = $this->ChangeSchoolYearActive(
                    //     $get_next_sy_id, $current_school_year_id);

                    // if($updatingSuccess == true){
                    //     $school_year_objv2 = $this->GetActiveSchoolYearAndSemester();
                    //     $current_school_year_termv2 = $school_year_objv2['term'];
                    //     $current_school_year_periodv2 = $school_year_objv2['period'];
                    //     AdminUser::success("System semester is set to: $current_school_year_periodv2 of SY $current_school_year_termv2", "");
                    // }

                }else{
                    echo "get_ntext_sy not executed";
                }



            }  
            # Creating Of Section should be perform if Todays is 2nd Semester.

        }
    }


    public function ChangeSchoolYearActive($next_school_year_id,
        $current_school_year_id){

        $doesChange = false;

        $get = $this->con->prepare("SELECT *
                FROM school_year
                WHERE school_year_id=:next_school_year_id
                ");
        $get->bindValue(":next_school_year_id", $next_school_year_id);
        $get->execute();

        if($get->rowCount() > 0){

            # Active School of Now will become InActive
            $isSuccess = $this->SetInActiveSchoolYear($current_school_year_id);

            if($isSuccess == true){

                # next_school_year_id will be Active.
                $update = $this->con->prepare("UPDATE school_year
                        SET statuses=:statuses
                        WHERE school_year_id=:next_school_year_id
                        ");
                    
                $update->bindValue(":statuses", "Active");
                $update->bindValue(":next_school_year_id", $next_school_year_id);

                if($update->execute()){
                    $doesChange = true;
                }

            }else{
                echo "We dont have Active School Year.";
            }

            // echo $update->rowCount();

            # Reset prev into InActive.

        }else{
            echo "notCOunt";
        }
        return $doesChange;
    }

    public function SetInActiveSchoolYear($school_year_id){

        $update = $this->con->prepare("UPDATE school_year
                SET statuses=:statuses
                WHERE school_year_id=:next_school_year_id
                ");
            
        $update->bindValue(":statuses", "InActive");
        $update->bindValue(":next_school_year_id", $school_year_id);

        return $update->execute();

    }

    public function IncrementSchoolYearTerm($currentSchoolYearTerm) {
        $years = explode("-", $currentSchoolYearTerm);
        $newStartYear = $years[0] + 1;
        $newEndYear = $years[1] + 1;

        $newSchoolYearTerm = $newStartYear . "-" . $newEndYear;

        return $newSchoolYearTerm;
    }

    public function DoesEndEnrollmentComesIn($current_school_year_id){

        $end_enrollment = $this->GetEndEnrollment();

        $currentDateTime = new DateTime();
        $current_time = $currentDateTime->format('Y-m-d H:i:s');

        if ($end_enrollment != null && $end_enrollment <= $current_time) {

            // echo "enrollment_status becomes 0 *(INACTIVE)";
            $set_inactive = $this->SetSYEnrollmentStatusInactive($current_school_year_id);
            if($set_inactive == true){
                AdminUser::success("Todays Semester Enrollment has been ended.",
                    "index.php");
                exit();
            }
        } else if ($end_enrollment > $current_time){
            // echo "End Enrollment is yet come.";
        }
    }

    public function SetSYEnrollmentStatusActive($current_school_year_id){

        # Student Cant Input the Form
        # ENROLLMENT IS NOW CLOSED.

        # false = 0

        if($this->CheckIfEnrollmentStatusIsActive
            ($current_school_year_id) == false 
            && $this->EnrollmentDateFinished() != 1){

            $enrollment_status = 1;

            $update = $this->con->prepare("UPDATE school_year
                SET enrollment_status=:enrollment_status
                WHERE school_year_id=:school_year_id
                AND enrollment_status=:current_enrollment_status
                ");
            
            $update->bindParam(":enrollment_status", $enrollment_status);
            $update->bindParam(":school_year_id", $current_school_year_id);
            $update->bindValue(":current_enrollment_status", 0);

            return $update->execute();
        }
    }

    public function SetSYEnrollmentStatusInactive($current_school_year_id){

        # Student Cant Input the Form
        # ENROLLMENT IS NOW CLOSED.

        if($this->CheckIfEnrollmentStatusIsActive(
                $current_school_year_id) == true){

            // if($this->EnrollmentDateFinished() == 0){
                $enrollment_status = 0;
                $update = $this->con->prepare("UPDATE school_year
                    SET 
                        enrollment_status=:enrollment_status,
                        is_finished=:is_finished
                    WHERE school_year_id=:school_year_id
                    AND enrollment_status=:current_enrollment_status
                    AND is_finished=:current_is_finished
                    
                    ");
                
                $update->bindValue(":enrollment_status", $enrollment_status);
                $update->bindValue(":is_finished", 1);
                $update->bindValue(":school_year_id", $current_school_year_id);
                $update->bindValue(":current_enrollment_status", 1);
                $update->bindValue(":current_is_finished", 0);

                return $update->execute();
            // }

        }
    }

    public function DoesEndPeriodIsOver(){

        // $end_period = $sql->fetchColumn();
        $end_period = $this->GetEndPeriod();

        $currentDateTime = new DateTime();
        $current_time = $currentDateTime->format('Y-m-d H:i:s');

        if ($current_time >= $end_period) {
            echo "Change The Semester Into Next Row.";
        }else if ($end_period > $current_time){
            echo "Not time to change period.";
        }
        
    }

    public function EnrollmentIsClosed($enrollment_status, $startEnrollment){
        if($enrollment_status == 0 || $startEnrollment == null){
            echo "
                <div class='container'>
                    <div class='alert alert-danger mt-4'>
                        <strong>DCBT Online Enrollment is Closed</strong> Please check back later for enrollment availability.
                    </div>
                </div>
            ";
            exit();
        }else{
            // echo "
            //     <div class='container'>
            //         <div class='alert alert-danger mt-4'>
            //             <strong>DCBT Online Enrollment is now Ongoing</strong> Please enroll now..
            //         </div>
            //     </div>
            // ";
        }
    }
}
?>