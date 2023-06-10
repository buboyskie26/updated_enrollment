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

    public function GetPrevSchoolYearId() {
       
        
        $school_year_obj = $this->GetActiveSchoolYearAndSemester();

        $current_period = $school_year_obj['period'];
        $current_term = $school_year_obj['term'];

        # 2021-2022 

        # 2020-2021 
        $years = explode("-", $current_term);
        $firstYear = intval($years[0]);
        $lastYear = intval($years[1]);

        // Decrement the last values by 1
        $decrementedYearRange = ($firstYear - 1) . "-" . ($lastYear - 1);
        // echo $decrementedYearRange;
        if($current_period == "First"){

            $query = $this->con->prepare("SELECT school_year_id

                FROM school_year
                WHERE statuses='InActive'
                AND term=:term
                AND period='Second'
                LIMIT 1");

            $query->bindValue(":term", $decrementedYearRange);
            $query->execute();
            if($query->rowCount() > 0){
                return $query->fetchColumn();
            }
        }

        else if($current_period == "Second"){

            $query = $this->con->prepare("SELECT school_year_id

                FROM school_year
                WHERE statuses='InActive'
                AND term=:term
                AND period='First'
                LIMIT 1");

            $query->bindValue(":term", $current_term);
            $query->execute();
            if($query->rowCount() > 0){

                return $query->fetchColumn();
            }
        }

        return -1;

    }

    public function DoesStartEnrollmentComesIn(){

        $student_enroll = new StudentEnroll($this->con, null);
        $student = new Student($this->con, null);

        $enrollment = new Enrollment($this->con, $student_enroll);

        $start_enrollment = $this->GetStartEnrollment();
        $end_enrollment = $this->EnrollmentDateFinished();

        $school_year_obj = $student_enroll->GetActiveSchoolYearAndSemester();

        $current_school_year_id = $school_year_obj['school_year_id'];
        $current_school_year_period = $school_year_obj['period'];
        $current_school_year_term = $school_year_obj['term'];

        $currentDateTime = new DateTime();
        $current_time = $currentDateTime->format('Y-m-d H:i:s');

        # ESTABLISHED START ENROLLMENT DATE is REACHED

        if ($end_enrollment == null && $start_enrollment != null && 
                $current_time >= $start_enrollment) {

            // echo "enrollment_status becomes 1 *(ACTIVE)";

            $prev_sy_id = $this->GetPrevSchoolYearId();
            
            // echo $prev_sy_id;

            $set_active = $this->SetSYEnrollmentStatusActive($current_school_year_id);
            if($set_active){}

            # Should applicable for First and Second Semester.
                    
            if($current_school_year_period == "Second" || $current_school_year_period == "First"
                ){

                # Applicable only For First Semester.
                $allRegularNewStudents = $student->GetAllOngoingRegularStudent(
                    $prev_sy_id, $current_school_year_id);
            
                // print_r($allRegularNewStudents);

                // if(false){
                if(count($allRegularNewStudents) > 0){

                    // print_r($allRegularNewStudents);

                    $now = date("Y-m-d H:i:s");

                    $sql = $this->con->prepare("INSERT INTO enrollment
                        (student_id, course_id, school_year_id, enrollment_form_id, enrollment_approve,
                            is_transferee, registrar_evaluated, is_tertiary)
                        VALUES(:student_id, :course_id, :school_year_id, :enrollment_form_id, :enrollment_approve,
                            :is_transferee, :registrar_evaluated, :is_tertiary)");

                    $isSuccess = false;

                    foreach ($allRegularNewStudents as $key => $value) {

                        $enrollment_form_id = $enrollment->GenerateEnrollmentFormId();

                        $student_id = $value['student_id'];
                        $admission_status = $value['admission_status'];
                        $is_tertiary = $value['is_tertiary'];

                        $student_course_id = $student_enroll->GetStudentCourseIdById($student_id);

                        $section = new Section($this->con, $student_course_id);

                        $student_course_level = $student->GetStudentLevel($student_id);
                        $tertiary_program_section = $section->GetSectionName();


                        if($section->CheckSectionAligned($student_course_id,
                            $student_course_level) == false){

                            // echo "$student_id course level is not aligned";
                             
                            # Aligned the recently moved-up student to their
                            # respective section.
                            $movedUpSectionName = $section->TertiarySectionMovedUp($tertiary_program_section);
                            
                            $movedUpSectionId = $section->GetMovedUpSectionId($movedUpSectionName, $current_school_year_term);

                            // echo $movedUpSectionId;
                            // echo "<br>";
                            
                            if($movedUpSectionId != null 
                                && $admission_status === "Transferee"){

                                $sql->bindValue(":student_id", $student_id);
                                $sql->bindValue(":course_id", $movedUpSectionId);
                                $sql->bindValue(":school_year_id", $current_school_year_id);
                                $sql->bindValue(":enrollment_form_id", $enrollment_form_id);
                                $sql->bindValue(":enrollment_approve", $now);
                                $sql->bindValue(":is_transferee", 0);
                                $sql->bindValue(":registrar_evaluated", "no");

                                // Note. if($is_tertiary == 0){
                                $sql->bindValue(":is_tertiary", 0);

                                // if(false){
                                if($sql->execute() && $sql->rowCount() > 0){
                                    $isSuccess = true;
                                }  
                            }else if($movedUpSectionId != null 
                                && $admission_status !== "Transferee"){

                                if($is_tertiary == 0){
                                    $sql->bindValue(":student_id", $student_id);
                                    $sql->bindValue(":course_id", $movedUpSectionId);
                                    $sql->bindValue(":school_year_id", $current_school_year_id);
                                    $sql->bindValue(":enrollment_form_id", $enrollment_form_id);
                                    $sql->bindValue(":enrollment_approve", $now);
                                    $sql->bindValue(":is_transferee", 0);
                                    $sql->bindValue(":registrar_evaluated", "yes");
                                    $sql->bindValue(":is_tertiary", 0);

                                    // if(false){
                                    if($sql->execute() && $sql->rowCount() > 0){

                                        $wasSuccess = $student->UpdateStudentCourseIdv2($student_id, 
                                            $movedUpSectionId);
                                        
                                        if($wasSuccess){
                                            $isSuccess = true;
                                        }
                                    }
                                }else if($is_tertiary == 1){
                                    $sql->bindValue(":student_id", $student_id);
                                    $sql->bindValue(":course_id", $movedUpSectionId);
                                    $sql->bindValue(":school_year_id", $current_school_year_id);
                                    $sql->bindValue(":enrollment_form_id", $enrollment_form_id);
                                    $sql->bindValue(":enrollment_approve", $now);
                                    $sql->bindValue(":is_transferee", 0);
                                    $sql->bindValue(":registrar_evaluated", "yes");
                                    $sql->bindValue(":is_tertiary", 1);

                                    // if(false){
                                    if($sql->execute() && $sql->rowCount() > 0){

                                        $isSuccess = true;

                                        $wasSuccess = $student->UpdateStudentCourseIdv2($student_id, 
                                            $movedUpSectionId);

                                        if($wasSuccess){
                                            $isSuccess = true;
                                        }
                                    }
                                }
                            }
                        }else{

                            $student_current_course_id = $student_enroll->GetStudentCourseIdById($student_id);
                             
                            # TODO on Transferee LOGIC (REGULAR) ONLY.
                            if($admission_status === "Transferee"){

                                $sql->bindValue(":student_id", $student_id);
                                $sql->bindValue(":course_id", $student_current_course_id);
                                $sql->bindValue(":school_year_id", $current_school_year_id);
                                $sql->bindValue(":enrollment_form_id", $enrollment_form_id);
                                $sql->bindValue(":enrollment_approve", $now);
                                $sql->bindValue(":is_transferee", 0);
                                $sql->bindValue(":registrar_evaluated", "no");

                                // if(false){
                                if($sql->execute() && $sql->rowCount() > 0){
                                    $isSuccess = true;
                                }           
                            }

                            else if($admission_status !== "Transferee"){

                                if($is_tertiary == 0){
                                    $sql->bindValue(":student_id", $student_id);
                                    $sql->bindValue(":course_id", $student_current_course_id);
                                    $sql->bindValue(":school_year_id", $current_school_year_id);
                                    $sql->bindValue(":enrollment_form_id", $enrollment_form_id);
                                    $sql->bindValue(":enrollment_approve", $now);
                                    $sql->bindValue(":is_transferee", 0);
                                    $sql->bindValue(":registrar_evaluated", "yes");
                                    $sql->bindValue(":is_tertiary", 0);

                                    // if(false){
                                    if($sql->execute() && $sql->rowCount() > 0){
                                        $isSuccess = true;
                                    }
                                }
                                else if($is_tertiary == 1){
                                    $sql->bindValue(":student_id", $student_id);
                                    $sql->bindValue(":course_id", $student_current_course_id);
                                    $sql->bindValue(":school_year_id", $current_school_year_id);
                                    $sql->bindValue(":enrollment_form_id", $enrollment_form_id);
                                    $sql->bindValue(":enrollment_approve", $now);
                                    $sql->bindValue(":is_transferee", 0);
                                    $sql->bindValue(":registrar_evaluated", "yes");
                                    $sql->bindValue(":is_tertiary", 1);

                                    // if(false){
                                    if($sql->execute() && $sql->rowCount() > 0){
                                        $isSuccess = true;
                                    }
                                }

                                // $sql->bindValue(":student_id", $student_id);
                                // $sql->bindValue(":course_id", $student_current_course_id);
                                // $sql->bindValue(":school_year_id", $current_school_year_id);
                                // $sql->bindValue(":enrollment_form_id", $enrollment_form_id);
                                // $sql->bindValue(":enrollment_approve", $now);
                                // $sql->bindValue(":is_transferee", 0);
                                // $sql->bindValue(":registrar_evaluated", "yes");
                                // if(false){
                                // // if($sql->execute() && $sql->rowCount() > 0){

                                //     $isSuccess = true;
                                // }
                            }
                        }
                        // echo $student_current_course_id;
                    }

                    if($isSuccess == true){
                        // Replace this as NOTIFICATION. 
                        AdminUser::success("Success Automatic Enrollment", "");
                    }else{
                        // echo "wrong";
                    }
                }
            }

            // AdminUser::success("Todays Semester Enrollment is now started.",
            //     "");
            // AdminUser::success("Todays Semester Enrollment is now started.",
            //     "indexv2.php");

            // exit();
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

        $student = new Student($this->con, null);

        $school_year_obj = $this->GetActiveSchoolYearAndSemester();
        $current_school_year_term = $school_year_obj['term'];
        $current_school_year_period = $school_year_obj['period'];

        $section = new Section($this->con, null);

        $current_school_year_id = $school_year_obj['school_year_id'];

        $end_period = $this->GetEndPeriod();

        // $fullSections = $section->GetCurrentSYFullSection($current_school_year_term);
        // print_r($fullSections);

        $currentDateTime = new DateTime();
        $current_time = $currentDateTime->format('Y-m-d H:i:s');

        if ($end_period !== null && $current_time >= $end_period) {

            # 1. First Semester
            // Should switch into 2nd Semester.

            if($current_school_year_period == "First"){

                $get_next_sy = $this->con->prepare("SELECT school_year_id 
                    
                    FROM school_year
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

                        // echo "NEW S.Y ACTIVE";
                        // echo "<br>";
                        $school_year_objv2 = $this->GetActiveSchoolYearAndSemester();

                        $current_school_year_termv2 = $school_year_objv2['term'];
                        $current_school_year_periodv2 = $school_year_objv2['period'];


                        $section->ResetSectionIsFull($current_school_year_term,
                            $current_school_year_periodv2);

                        // if($section == true){
                        //     echo "IS HIT";
                        // }else{
                        //     echo "IS NOT HIT";
                        // }

                        # Subjects Taken has expired in every end of semester.
                        $newToOldSuccess = $student->UpdateActiveNewEnrolleeStudentToOngoing();

                        AdminUser::success("System semester is set to: $current_school_year_periodv2 of SY $current_school_year_termv2", "");

                        # Get all student who are Regular and Passed First Semester.

                        # Enrolled Each of them with the same course_id but S.Y will
                        # be set in todays S.Y

                        # Registrar Evaluated = 'yes'

                        # Get all student who are Regular and Passed their  First Semester Subject.



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

                        // if(false){
                        if($isSuccess){

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

                                $getMoveUpShsSection = $this->con->prepare("SELECT * 
                                
                                    FROM course
                                    WHERE school_year_term=:school_year_term
                                    AND active=:active
                                    AND course_level != 12
                                    AND is_tertiary=0
                                    ");

                                $getMoveUpShsSection->bindValue(":school_year_term", $current_school_year_term);
                                $getMoveUpShsSection->bindValue(":active", "yes");
                                $getMoveUpShsSection->execute();

                                $isMoveUpShsFinished = false;

                                $isSetToInactive = false;

                                // if(false){
                                if($getMoveUpShsSection->rowCount() > 0){

                                    $update_shs_course = $this->con->prepare("UPDATE course
                                        SET active=:active
                                        WHERE school_year_term=:school_year_term
                                        AND course_level >= 11
                                        
                                        ");

                                    $update_shs_course->bindValue(":active", "no");
                                    $update_shs_course->bindValue(":school_year_term", $current_school_year_term);
                                    if($update_shs_course->execute()){
                                        $isSetToInactive = true;
                                    }


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

                                    $getShsCoursesForMovingUp = $getMoveUpShsSection->fetchAll(PDO::FETCH_ASSOC);


                                    foreach ($getShsCoursesForMovingUp as $key => $value) {

                                            $tertiary_program_id = $value['program_id'];
                                            $tertiary_course_level = $value['course_level'];
                                            $course_id = $value['course_id'];

                                            $previous_course_tertiary_id = $value['course_id'];
                                           
                                            $tertiary_program_section = $value['program_section'];

                                            $new_program_section = str_replace('11', 12, $tertiary_program_section);

                                            // $update_shs_course->bindValue(":active", "no");
                                            // $update_shs_course->bindValue(":course_id", $course_id);

                                            if($isSetToInactive == true){

                                                $moveUpShsSection->bindValue(":program_section", $new_program_section);
                                                $moveUpShsSection->bindValue(":program_id", $tertiary_program_id);
                                                $moveUpShsSection->bindValue(":course_level", $tertiary_course_level + 1);
                                                $moveUpShsSection->bindValue(":capacity", "2");
                                                $moveUpShsSection->bindValue(":school_year_term", $new_school_year_term);
                                                $moveUpShsSection->bindValue(":active", "yes");
                                                $moveUpShsSection->bindValue(":is_full", "no");
                                                $moveUpShsSection->bindValue(":previous_course_id", $previous_course_tertiary_id);

                                                // if(false){
                                                if($moveUpShsSection->execute()){
                                                    // echo "New Tertiary $new_program_section section has been established at new $new_school_year_term";
                                                    // echo "<br>";
                                                    $moveupCourseId = $this->con->lastInsertId();
                                                    
                                                    if($moveupCourseId != null){

                                                        $newly_created_shs_program = $this->con-> prepare("SELECT 
                                                            course_id, course_level, program_id, program_section
                                                            
                                                            FROM course
                                                            WHERE course_id=:course_id
                                                            LIMIT 1
                                                            ");

                                                        $newly_created_shs_program->bindValue(":course_id", $moveupCourseId);
                                                        $newly_created_shs_program->execute();

                                                        // if(false){
                                                        if($newly_created_shs_program->rowCount() > 0){

                                                            $newly_shs_section_row = $newly_created_shs_program->fetch(PDO::FETCH_ASSOC);

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
                                                                        $isMoveUpShsFinished = true;
                                                                    }
                                                                }

                                                            }
                                                        }
                                                    }
                                            
                                                }
                                            }
                                    }
                                }

                                ### Tertiary Moving Up
                                $getMovingUpTertiarySection = $this->con->prepare("SELECT * 
                                
                                    FROM course
                                    WHERE school_year_term=:school_year_term
                                    AND active=:active
                                    AND is_tertiary=1
                                    AND course_level != 4
                                    ");

                                $getMovingUpTertiarySection->bindValue(":school_year_term", $current_school_year_term);
                                $getMovingUpTertiarySection->bindValue(":active", "yes");
                                $getMovingUpTertiarySection->execute();

                                $isMovedUpTertiaryFinished = false;

                                // if(false){

                                $isSetToInactiveTertiary = false;

                                if($getMovingUpTertiarySection->rowCount() > 0){

                                    // $update_tertiary_course = $this->con->prepare("UPDATE course
                                    //     SET active=:active
                                    //     -- WHERE course_level=:course_level
                                    //     WHERE school_year_term=:school_year_term
                                    //     WHERE course_id=:course_id");

                                    $update_tertiary_course = $this->con->prepare("UPDATE course
                                        SET active=:active
                                        WHERE school_year_term=:school_year_term
                                        AND course_level <= 4
                                        
                                        ");

                                    $update_tertiary_course->bindValue(":active", "no");
                                    $update_tertiary_course->bindValue(":school_year_term", $current_school_year_term);

                                    if($update_tertiary_course->execute()){
                                        $isSetToInactiveTertiary = true;
                                    }

                                    $moveUpTertiarySection = $this->con->prepare("INSERT INTO course
                                        (program_section, program_id, course_level, capacity, school_year_term, active, is_full, previous_course_id, is_tertiary)
                                        VALUES(:program_section, :program_id, :course_level, :capacity, :school_year_term, :active, :is_full, :previous_course_id, :is_tertiary)");

                                    $moveupTertiaryCourseId = null;

                                    $insert_tertiary_section_subject = $this->con->prepare("INSERT INTO subject
                                        (subject_title, description, subject_program_id, unit, semester, program_id, course_level, course_id, subject_type, subject_code)
                                        VALUES(:subject_title, :description, :subject_program_id, :unit, :semester, :program_id, :course_level, :course_id, :subject_type, :subject_code)");

                                    $get_subject_program = $this->con->prepare("SELECT * FROM subject_program
                                        WHERE program_id=:program_id
                                        AND course_level=:course_level
                                        ");

                                    $getTertiaryCoursesForMovingUp = $getMovingUpTertiarySection->fetchAll(PDO::FETCH_ASSOC);


                                    foreach ($getTertiaryCoursesForMovingUp as $key => $value) {

                                            $tertiary_program_id = $value['program_id'];
                                            $tertiary_course_level = $value['course_level'];
                                            $course_id = $value['course_id'];

                                            $previous_course_tertiary_id = $value['course_id'];
                                           
                                            $tertiary_program_section = $value['program_section'];

                                            $pattern = '/(\d+)/';
                                            $replacement = '${1}';

                                            $newString = preg_replace_callback($pattern, function($matches) {
                                                return intval($matches[0]) + 1;
                                            }, $tertiary_program_section);

                                            // $update_tertiary_course->bindValue(":active", "no");
                                            // $update_tertiary_course->bindValue(":course_id", $course_id);

                                            // if(false){
                                            // if($update_tertiary_course->execute()){
                                            if($isSetToInactiveTertiary){

                                                // echo "ter";

                                                $moveUpTertiarySection->bindValue(":program_section", $newString);
                                                $moveUpTertiarySection->bindValue(":program_id", $tertiary_program_id);
                                                $moveUpTertiarySection->bindValue(":course_level", $tertiary_course_level + 1);
                                                $moveUpTertiarySection->bindValue(":capacity", "2");
                                                $moveUpTertiarySection->bindValue(":school_year_term", $new_school_year_term);
                                                $moveUpTertiarySection->bindValue(":active", "yes");
                                                $moveUpTertiarySection->bindValue(":is_full", "no");
                                                $moveUpTertiarySection->bindValue(":previous_course_id", $previous_course_tertiary_id);
                                                $moveUpTertiarySection->bindValue(":is_tertiary", 1);

                                                if($moveUpTertiarySection->execute()){
                                                    // echo "moved-up";
                                                    // echo "New Tertiary $new_program_section section has been established at new $new_school_year_term";
                                                    // echo "<br>";
                                                    $moveupTertiaryCourseId = $this->con->lastInsertId();
                                                    
                                                    if($moveupTertiaryCourseId != null){

                                                        $newly_created_shs_program = $this->con-> prepare("SELECT 
                                                            course_id, course_level, program_id, program_section
                                                            
                                                            FROM course
                                                            WHERE course_id=:course_id
                                                            LIMIT 1
                                                            ");

                                                        $newly_created_shs_program->bindValue(":course_id", $moveupTertiaryCourseId);
                                                        $newly_created_shs_program->execute();

                                                        // if(false){
                                                        if($newly_created_shs_program->rowCount() > 0){

                                                            $newly_shs_section_row = $newly_created_shs_program->fetch(PDO::FETCH_ASSOC);

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

                                                                    $insert_tertiary_section_subject->bindValue(":subject_title", $program_subject_title);
                                                                    $insert_tertiary_section_subject->bindValue(":description", $program_subject_description);
                                                                    $insert_tertiary_section_subject->bindValue(":subject_program_id", $program_program_id);
                                                                    $insert_tertiary_section_subject->bindValue(":unit", $program_subject_unit);
                                                                    $insert_tertiary_section_subject->bindValue(":semester", $program_semester);
                                                                    $insert_tertiary_section_subject->bindValue(":program_id", $newly_created_shs_program_id);
                                                                    $insert_tertiary_section_subject->bindValue(":course_level", $program_course_level);
                                                                    $insert_tertiary_section_subject->bindValue(":course_id", $moveupTertiaryCourseId);
                                                                    $insert_tertiary_section_subject->bindValue(":subject_type", $program_subject_type);
                                                                    $insert_tertiary_section_subject->bindValue(":subject_code", $program_subject_code);

                                                                    if($insert_tertiary_section_subject->execute()){
                                                                        $isSubjectCreated = true;
                                                                        $isMovedUpTertiaryFinished = true;
                                                                    }
                                                                }

                                                            }
                                                        }
                                                    }
                                            
                                                }
                                            }
                                    }
                                }


                                // Manual Default Creation (SHS).
                                $section = new Section($this->con, null);
                                # IT SHOULD DYNAMIC, WHATEVER sets by admin
                                # Should automatically created for every Fresh S.Y
                                // if(false){
                                if($isMoveUpShsFinished){

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

                                                $isMoveUpShsFinished = true;
                                            }
                                }

                                // Manual Default Creation (TERTIARY).
                                $section = new Section($this->con, null);

                                # IT SHOULD DYNAMIC, WHATEVER sets by admin
                                # Should automatically created for every Fresh S.Y

                                // if(false){
                                if($isMovedUpTertiaryFinished){

                                    $ABE_PROGRAM_ID = $section->GetStrandrogramId("ABE");

                                    $defaulABECourse = $this->con->prepare("INSERT INTO course
                                        (program_section, program_id, course_level, capacity, school_year_term, active, is_full, is_tertiary)
                                        VALUES(:program_section, :program_id, :course_level, :capacity, :school_year_term, :active, :is_full, :is_tertiary)");

                                    $defaulABECourse->bindValue(":program_section", "ABE1-A");
                                    $defaulABECourse->bindValue(":program_id", $ABE_PROGRAM_ID, PDO::PARAM_INT);
                                    $defaulABECourse->bindValue(":course_level", 1, PDO::PARAM_INT);
                                    $defaulABECourse->bindValue(":capacity", 2);
                                    $defaulABECourse->bindValue(":school_year_term", $new_school_year_term);
                                    $defaulABECourse->bindValue(":active", "yes");
                                    $defaulABECourse->bindValue(":is_full", "no");
                                    $defaulABECourse->bindValue(":is_tertiary", 1);

                                    if($defaulABECourse->execute()){}

                                        $newlyTertiaryCourse = $this->con->prepare("SELECT 

                                            c.course_id, c.course_level, c.program_id, c.program_section, sp.subject_program_id,
                                            sp.semester, sp.subject_type, sp.subject_title, sp.description, sp.unit, sp.subject_code

                                            FROM course c
                                            INNER JOIN subject_program sp ON sp.program_id = c.program_id AND sp.course_level = c.course_level
                                            WHERE c.school_year_term = :school_year_term
                                            AND c.course_level = 1
                                            AND c.active = 'yes'
                                        ");

                                        $newlyTertiaryCourse->bindValue(":school_year_term", $new_school_year_term);
                                        $newlyTertiaryCourse->execute();

                                        if ($newlyTertiaryCourse->rowCount() > 0) {
                                            $newlyShsSectionList = $newlyTertiaryCourse->fetchAll(PDO::FETCH_ASSOC);

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

                                                $newlyCreatedTertiaryProgramId = $value['program_id'];
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
                                                $insertSectionSubject->bindValue(":program_id", $newlyCreatedTertiaryProgramId);
                                                $insertSectionSubject->bindValue(":course_level", $newlyCreatedStemShsCourseLevel);
                                                $insertSectionSubject->bindValue(":course_id", $newlyCreatedStemShsCourseId);
                                                $insertSectionSubject->bindValue(":subject_type", $programSubjectType);
                                                $insertSectionSubject->bindValue(":subject_code", $programSubjectCode);

                                                $insertSectionSubject->execute();
                                            }
                                        }
                                    }

                                }
                        }
                    }
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
                    "indexv2.php");
                
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