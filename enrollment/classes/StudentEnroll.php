<?php

    class StudentEnroll{

    private $con, $userLoggedIn, $sqlData;
   
    public function __construct($con){
        $this->con = $con;
    }

    public function createNewStudent($student_unique_id, $fname, $lname, $mi, $address,
            $sex, $b_day, $nationality, $birthplace,$religion, $s_contact,
            $civil_status, $guardian, $g_contact,
            $course_id,$username, $password, $current_semester){
 
    // All new students shs would be grade 11 as default
    $default_course_level = 11;

    $query = $this->con->prepare("INSERT INTO student (firstname, lastname, middle_name, username,
        profilePic, password, student_status, course_id ,student_unique_id, sex,
        birthday, birthplace, age, nationality, religion, contact_number, address,
        guardian_name, guardian_contact_number, civil_status, course_level) 

        VALUES(:firstname, :lastname,:middle_name,:username,
        :profilePic, :password, :student_status, :course_id, :student_unique_id, :sex,
        :birthday, :birthplace, :age, :nationality, :religion, :contact_number, :address,
        :guardian_name, :guardian_contact_number, :civil_status, :course_level)");


    $b_day = date('Y-m-d', strtotime($b_day));

    $age = $this->getCurrentAge($b_day);

    // This could not retrieve by php function, but it can be compared
    $hash_password = password_hash($password, PASSWORD_BCRYPT);

    // Based on the latest semester that was establish in the system.
    // $semester = "First";

    $query->bindValue(":firstname", $fname);
    $query->bindValue(":lastname", $lname);
    $query->bindValue(":middle_name", $mi);
    $query->bindValue(":username", $username);
    $query->bindValue(":profilePic", "");
    $query->bindValue(":password", $hash_password);
    $query->bindValue(":student_status", "Regular");
    $query->bindValue(":course_id", $course_id);
    // $query->bindValue(":semester", $semester);
    // $query->bindValue(":semester", $current_semester);

    $query->bindValue(":student_unique_id", $student_unique_id);
    $query->bindValue(":sex", $sex);
    $query->bindValue(":birthday", $b_day);
    $query->bindValue(":birthplace", $birthplace);
    $query->bindValue(":age", $age);
    $query->bindValue(":nationality", $nationality);
    $query->bindValue(":religion", $religion);
    $query->bindValue(":contact_number", $s_contact);
    $query->bindValue(":address", $address);
    $query->bindValue(":guardian_name", $guardian);
    $query->bindValue(":guardian_contact_number", $g_contact);
    $query->bindValue(":civil_status", $civil_status);
    // $query->bindValue(":school_year_id", $school_year_id);
    $query->bindValue(":course_level", $default_course_level);

    $execute =  $query->execute();

    return $execute;
  }

    public function CheckNumberOfStudentInSection($course_id, $period){

        $school_year_obj = $this->GetActiveSchoolYearAndSemester();

        // 1st sem
        // Course id 1st sem -> enrolled student
        // Course id 2nd sem -> enrolled student
        // $current_period = $school_year_obj['period'];
        $current_school_year_term = $school_year_obj['term'];
        $current_school_year_id = $school_year_obj['school_year_id'];
        
        $enrollment_status = "enrolled";
        // $enrollment_status = "tentative";

        $query = "SELECT e.student_id, e.course_id   

                FROM enrollment e

                JOIN school_year sy ON e.school_year_id = sy.school_year_id

                WHERE sy.period =:period
                AND sy.term = :current_school_year_term
                AND sy.school_year_id = :current_school_year_id
                AND e.course_id = :course_id
                AND e.enrollment_status=:enrollment_status
                " ;

        $query = $this->con->prepare($query);

        $query->bindValue(":period", $period);
        $query->bindValue(":current_school_year_term", $current_school_year_term);
        $query->bindValue(":current_school_year_id", $current_school_year_id);
        $query->bindValue(":course_id", $course_id);
        $query->bindValue(":enrollment_status", $enrollment_status);
        $query->execute();

        return $query->rowCount();
    }

    public function CheckNumberOfStudentInSectionTertiary($course_id, $period){

        $school_year_obj = $this->GetActiveSchoolYearAndSemester();

        // 1st sem
        // Course id 1st sem -> enrolled student
        // Course id 2nd sem -> enrolled student
        // $current_period = $school_year_obj['period'];
        $current_school_year_term = $school_year_obj['term'];
        $current_school_year_id = $school_year_obj['school_year_id'];
        
        $enrollment_status = "enrolled";
        // $enrollment_status = "tentative";

        $query = "SELECT e.student_id, e.course_tertiary_id   

                FROM enrollment_tertiary e

                JOIN school_year sy ON e.school_year_id = sy.school_year_id

                WHERE sy.period =:period
                AND sy.term = :current_school_year_term
                AND sy.school_year_id = :current_school_year_id
                AND e.course_tertiary_id = :course_tertiary_id
                AND e.enrollment_status=:enrollment_status
                " ;

        $query = $this->con->prepare($query);

        $query->bindValue(":period", $period);
        $query->bindValue(":current_school_year_term", $current_school_year_term);
        $query->bindValue(":current_school_year_id", $current_school_year_id);
        $query->bindValue(":course_tertiary_id", $course_id);
        $query->bindValue(":enrollment_status", $enrollment_status);
        $query->execute();

        return $query->rowCount();
    }

    // Regular form registration
    public function RegularStudentFormSubmit($student_unique_id, $fname, $lname, $mi, $address,
            $sex, $b_day, $nationality, $birthplace,$religion, $s_contact,
            $civil_status, $guardian, $g_contact,

            $program_id,

            $username, $password, $current_semester){
 
        // All new students shs would be grade 11 as default
        $default_course_level = 11;

        $studentExecute = false;

        $school_year_obj = $this->GetActiveSchoolYearAndSemester();

        $current_school_year_term = $school_year_obj['term'];
        $current_period = $school_year_obj['period'];

        
        $not_full = "no";
        $full_yes = "yes";
        $active_yes = "yes";

        // Default start would be the Grade 11 sections
        // that offers the client, HUMMS,ABM,GAS etc, 11-A
        $default_section = $this->con->prepare("SELECT *
            FROM course
            WHERE program_id=:program_id
            AND active=:active
            AND course_level=:course_level
            AND is_full=:is_full
            AND school_year_term=:school_year_term
            -- AND program_section='HUMMS11-A'
            -- ORDER BY course_id DESC
            LIMIT 1");

        // $program_id = 0;
        $default_section->bindValue(":program_id", $program_id);
        $default_section->bindValue(":course_level", $default_course_level);
        $default_section->bindValue(":active", $active_yes);
        $default_section->bindValue(":is_full", $not_full);
        $default_section->bindValue(":school_year_term", $current_school_year_term);
        $default_section->execute();
        
        if($default_section->rowCount() > 0){

            $default_row = $default_section->fetch(PDO::FETCH_ASSOC);
            
            $default_course_id = $default_row['course_id'];
            $default_program_section = $default_row['program_section'];


            $capacity = $default_row['capacity'];
            $default_school_year_term = $default_row['school_year_term'];

            $FIRST_SEMESTER = "First";
 
            # TODO: Create a query that checks if enrolled student in that course
            // exceed the setted capacity the section.

            $enrolledStudents = $this->CheckNumberOfStudentInSection($default_course_id, "First");
            
            echo "<br>";
            echo $enrolledStudents;
            echo "<br>";

            if($capacity == $enrolledStudents && $current_period == $FIRST_SEMESTER){
                // Becomes full

                echo "section is now full";
                // echo "<br>";

                $update_course = $this->con->prepare("UPDATE course
                    SET is_full=:is_full
                    WHERE course_id=:course_id
                    AND active=:current_active_status
                    AND is_full=:current_is_ful
                    AND school_year_term=:school_year_term
                    ");

                $update_course->bindValue(":is_full", $full_yes);
                $update_course->bindValue(":course_id", $default_course_id);
                $update_course->bindValue(":current_active_status", $active_yes);
                $update_course->bindValue(":current_is_ful", $not_full);
                $update_course->bindValue(":school_year_term", $current_school_year_term);

                if($update_course->execute()){
                    // echo "default_section == 0";
                        $get_prev_default_section = $this->con->prepare("SELECT *
                        
                        FROM course
                        WHERE program_id=:program_id
                        AND active=:active
                        AND is_full=:is_full
                        -- AND program_section='HUMMS11-A'
                        ORDER BY course_id DESC
                        LIMIT 1");

                    // $program_id = 0;
                    $get_prev_default_section->bindValue(":program_id", $program_id);
                    $get_prev_default_section->bindValue(":active", $active_yes);
                    $get_prev_default_section->bindValue(":is_full", $full_yes);
                    $get_prev_default_section->execute();

                    // if(false){
                    if($get_prev_default_section->rowCount() > 0){
                        
                        $prev_row = $get_prev_default_section->fetch(PDO::FETCH_ASSOC);

                        // If newly_created course id is HUMMS11-E
                        // then prev_course_id must be HUMMS11-D course_id
                        $prev_course_id = $prev_row['course_id'];
                        $prev_school_year_term = $prev_row['school_year_term'];
                        $prev_capacity = $prev_row['capacity'];
                        $prev_program_section = $prev_row['program_section'];

                        $generateNextStrandSection = $this->GetNextProgramSection($prev_program_section);

                        // Before creating new section.
                        $checkIfSectionExists = $this->con->prepare("SELECT course_id FROM course
                            WHERE program_section=:program_section

                            AND active='yes'
                            LIMIT 1");

                        $checkIfSectionExists->bindValue(":program_section", $generateNextStrandSection);
                        $checkIfSectionExists->execute();

                        if($checkIfSectionExists->rowCount() > 0){

                            // The student registered will be stuck at the moment
                            // but the moment they refresh and fill-up again, it will be fine.
                            echo  $generateNextStrandSection . " already exists";
                            return;

                        }
                        else if($checkIfSectionExists->rowCount() == 0){
                            // echo  $generateNextStrandSection . " is new";

                            # Bug: What if 2 sections went full at the same time.
                            // System will only cater one. so the other will need to refresh the page.

                            // create a new one with  HUMMS11-B as the next program_section.
                            $create_new_section = $this->con->prepare("INSERT INTO course
                                (program_section, program_id, course_level, capacity, school_year_term, active, is_full)
                                VALUES(:program_section, :program_id, :course_level, :capacity, :school_year_term, :active, :is_full)");

                            $create_new_section->bindValue(":program_section", $generateNextStrandSection);

                            $create_new_section->bindValue(":program_id", $program_id);
                            $create_new_section->bindValue(":course_level", $default_course_level);
                            $create_new_section->bindValue(":capacity", $prev_capacity);
                            // Should be placed if student were officially enrolled by registrar.
                            // $create_new_section->bindValue(":total_student", 1);
                            $create_new_section->bindValue(":school_year_term", $prev_school_year_term);
                            $create_new_section->bindValue(":active", "yes");
                            $create_new_section->bindValue(":is_full", "no");
                            // $create_new_section->execute();

                            if($create_new_section->execute() && $create_new_section->rowCount() == 1){

                                # BUG: If the total_student is only -1 left to make the capacity becomes full, 
                                # and two students clicked at the same time, 
                                echo "Created new section: $generateNextStrandSection ";

                                    // echo "created $last_inserted_course_id";
                                    $course_level_eleven = 11;

                                    // course_id of newly created course (HUMMS11-B)
                                    $getNewlyCreatedCourse = $this->con->prepare("SELECT course_id FROM course
                                        WHERE program_section=:program_section
                                        AND course_level=:course_level
                                        AND active='yes'
                                        LIMIT 1");
                                    $getNewlyCreatedCourse->bindValue(":program_section", $generateNextStrandSection);
                                    $getNewlyCreatedCourse->bindValue(":course_level", $course_level_eleven);
                                    $getNewlyCreatedCourse->execute();

                                    $second_strand_course_id = null;

                                    if($getNewlyCreatedCourse->rowCount() > 0){

                                        // newly created course id
                                        $get_newly_created_course_id = $getNewlyCreatedCourse->fetchColumn();
                                        $active = "yes";

                                        // echo "get_newly_created_course_id " . $get_newly_created_course_id;
                                        // echo "<br>";

                                        // Even you`re at the -E, Get the -A Grade 11 strand.
                                        // Move it up in logical place.
                                        $get_A_section = $this->con->prepare("SELECT course_id FROM course
                                            WHERE program_id=:program_id
                                            AND course_level=:course_level
                                            AND active=:active
                                            ORDER BY course_id ASC
                                            LIMIT 1");
                                            
                                        $get_A_section->bindValue(":program_id", $program_id);
                                        $get_A_section->bindValue(":course_level", $default_course_level);
                                        $get_A_section->bindValue(":active", $active);
                                        $get_A_section->execute();

                                        if($get_A_section->rowCount() == 0){
                                            echo "Cant get the Strand11-A section";
                                            return;
                                        }

                                        $a_section_course_id = $get_A_section->fetchColumn();

                                        // echo "prev_course_id " . $prev_course_id;
                                        // echo "a_section_course_id=" . $a_section_course_id . " ";

                                        // Make sure the it will create ONLY ONCE the subject course referencing
                                        // to the course_id 
                                        $subjectQuery =  "SELECT * FROM subject
                                            WHERE course_id=:course_id
                                            AND course_level=:course_level";
                                        
                                        $subjectQueryState = $this->con->prepare("SELECT * FROM subject
                                            WHERE course_id=:course_id
                                            AND course_level=:course_level");
                                        $subjectQueryState->bindParam(":course_id", $a_section_course_id);
                                        $subjectQueryState->bindParam(":course_level", $course_level_eleven);
                                        $subjectQueryState->execute();

                                        // // $all =  $subject->fetchAll(PDO::FETCH_ASSOC);
                                        
                                        $subject_copy_create = "INSERT INTO subject
                                                (subject_title, pre_subject_id, unit, semester, program_id, course_level, subject_type, course_id, subject_code, subject_program_id)
                                                VALUES(:subject_title, :pre_subject_id, :unit, :semester, :program_id, :course_level, :subject_type, :course_id, :subject_code, :subject_program_id)";

                                        $subject_copy_create = $this->con->prepare($subject_copy_create);

                                        // // Make sure the subject creation is perfectly reference
                                        // // to new created course_id (HUMMSS11-B)

                                        if($subjectQueryState->rowCount() == 0){
                                            echo "subjectQuery variable is zero. (it did not reference correctly the course_id)";
                                            echo "<br>";
                                            return;
                                        }

                                        if($get_newly_created_course_id != null && $subjectQueryState->rowCount() > 0){

                                            // subject_code must be depend on
                                            // registrar subject convetion.
                                            foreach ($subjectQueryState as $subject_row) {

                                                // Auto Create of Subjects for the Auto create of course_section
                                                // If HUMMS11-A has 3 subjects eventually becomes full.
                                                // It creates HUMMSS11-B with all the subjects of HUMMSS11-A
                                                // Registrar will just insert a schedule, subject_code and pre_subject_id (pre_Requisite) on the subject_table,
                                                $subject_copy_create->execute([
                                                    ':subject_title' => $subject_row['subject_title'],
                                                    ':pre_subject_id' => $subject_row['pre_subject_id'],
                                                    ':unit' => $subject_row['unit'],
                                                    ':semester' => $subject_row['semester'],
                                                    ':program_id' => $subject_row['program_id'],
                                                    ':course_level' => $subject_row['course_level'],
                                                    ':subject_type' => $subject_row['subject_type'],
                                                    ':course_id' => $get_newly_created_course_id,
                                                    ':subject_code' => $generateNextStrandSection,
                                                    ':subject_program_id' => $subject_row['subject_program_id']
                                                ]);
                                            }

                                            // After the auto creation of subjects
                                            // Create the student and insert that new course_id $get_newly_created_course_id

                                            $doesCreatingStudentSuccess = $this->AddRegularStudent(
                                                $fname, $lname, $mi, $username, $password, $get_newly_created_course_id,
                                                $student_unique_id, $sex, $b_day, $birthplace, $nationality, $religion,
                                                $s_contact, $address, $guardian, $g_contact, $civil_status, $default_course_level
                                            );

                                            if($doesCreatingStudentSuccess == true){
                                                $studentExecute = true;
                                                return $studentExecute;
                                            }
                                        }
                                    }
                            }
                        }
                    }
                // }
                }
            }

            if($capacity > $enrolledStudents){

                echo "capacity > enrolled student";

                $doesCreatingStudentSuccess = $this->AddRegularStudent(
                    $fname, $lname, $mi, $username, $password, $default_course_id,
                    $student_unique_id, $sex, $b_day, $birthplace, $nationality, $religion,
                    $s_contact, $address, $guardian, $g_contact, $civil_status, $default_course_level
                );

                if($doesCreatingStudentSuccess == true){
                    $studentExecute = true;
                    return $studentExecute;
                }
            }
        }
 
        return $studentExecute;
    }

    // Transferee Student Fill-up Form

    public function TransfereeStudentFormSubmit($student_unique_id, $fname, $lname, $mi, $address,
            $sex, $b_day, $nationality, $birthplace,$religion, $s_contact,
            $civil_status, $guardian, $g_contact,$program_id, $username, $password, $current_semester){

        $transfereeRegisterSuccess = false;

        $default_course_level = 11;
        $course_id = 0;

        $course_level_add = 0;
        // echo "capacity > enrolled student";
        $student_Transferee = true;
        $lrn = 0;
        $doesCreatingStudentSuccess = $this->AddTransfereeStudent(
            $fname, $lname, $mi, $username, $password, $course_id,
            $student_unique_id, $sex, $b_day, $birthplace, $nationality, $religion,
            $s_contact, $address, $guardian, $g_contact, $civil_status,
            $default_course_level, $student_Transferee, $course_level_add, $lrn
        );

        if($doesCreatingStudentSuccess == true){
            $transfereeRegisterSuccess = true;
            return $transfereeRegisterSuccess;
        }
        
        return $transfereeRegisterSuccess;
    }
    
    public function RegistrarCreatingStudentFormSubmit($student_unique_id, $fname, $lname, $mi, $address,
            $sex, $b_day, $nationality, $birthplace,$religion, $s_contact,
            $civil_status, $guardian, $g_contact,

            $course_id, 

            $username, $password, $get_course_level, $course_level,
            
            $student_status, $lrn){

        $transfereeRegisterSuccess = false;

        $default_course_level = $get_course_level;

        if($student_status === "Transferee"){
            $student_transferee = true;
            $course_level_add = 0;
            $wasSuccess =  $this->AddTransfereeStudent(
                $fname, $lname, $mi, $username, $password, $course_id,
                $student_unique_id, $sex, $b_day, $birthplace, $nationality, $religion,
                $s_contact, $address, $guardian, $g_contact, $civil_status,
                $default_course_level, $student_transferee, $course_level_add, $lrn
            );
            if($wasSuccess){
                $transfereeRegisterSuccess = true;
            }
        }
        
        else if($student_status === "Regular"){

            $doesCreatingStudentSuccess = $this->AddRegularStudent(
                $fname, $lname, $mi, $username, $password, $course_id,
                $student_unique_id, $sex, $b_day, $birthplace, $nationality, $religion,
                $s_contact, $address, $guardian, $g_contact, $civil_status, $default_course_level,
                $course_level
            );

            if($doesCreatingStudentSuccess == true){
                $transfereeRegisterSuccess = true;
            }
        }
       
        // $student_course_level = $this->GetStudentCourseLevel();
        $student_Transferee = false;


        
        return $transfereeRegisterSuccess;
    }

    public function AddTransfereeStudent(
            $fname, $lname, $mi, $username, $password,
            $course_id, $student_unique_id,$sex, $b_day, $birthplace,
            $nationality, $religion, $s_contact, $address, $guardian, 
            $g_contact, $civil_status, $default_course_level, $isTransferee, $course_level,
            $lrn) {
        
        $query = $this->con->prepare("INSERT INTO student 
            (firstname, lastname, middle_name, username, profilePic, password, student_status, course_id ,student_unique_id, sex, birthday, birthplace, age, nationality, religion, contact_number, address, guardian_name, guardian_contact_number, civil_status, course_level, lrn) 
            VALUES (:firstname, :lastname, :middle_name, :username, :profilePic, :password, :student_status, :course_id, :student_unique_id, :sex, :birthday, :birthplace, :age, :nationality, :religion, :contact_number, :address, :guardian_name, :guardian_contact_number, :civil_status, :course_level, :lrn)");

        $b_day = date('Y-m-d', strtotime($b_day));
        $age = $this->getCurrentAge($b_day);
        $hash_password = password_hash($password, PASSWORD_BCRYPT);

        $student_status_type = "";
        $TRANSFEREE = "Transferee";
        $REGULAR = "Regular";

        if($isTransferee == true){
            $student_status_type = $TRANSFEREE;
        }else if($isTransferee == false){
            $student_status_type = $REGULAR;
        }
        $query->bindValue(":firstname", $fname);
        $query->bindValue(":lastname", $lname);
        $query->bindValue(":middle_name", $mi);
        $query->bindValue(":username", $username);
        $query->bindValue(":profilePic", "");
        $query->bindValue(":password", $hash_password);
        $query->bindValue(":student_status", $student_status_type);
        $query->bindValue(":course_id", $course_id);
        $query->bindValue(":student_unique_id", $student_unique_id);
        $query->bindValue(":sex", $sex);
        $query->bindValue(":birthday", $b_day);
        $query->bindValue(":birthplace", $birthplace);
        $query->bindValue(":age", $age);
        $query->bindValue(":nationality", $nationality);
        $query->bindValue(":religion", $religion);
        $query->bindValue(":contact_number", $s_contact);
        $query->bindValue(":address", $address);
        $query->bindValue(":guardian_name", $guardian);
        $query->bindValue(":guardian_contact_number", $g_contact);
        $query->bindValue(":civil_status", $civil_status);
        $query->bindValue(":course_level", $course_level);
        $query->bindValue(":lrn", $lrn);

        return $query->execute();
    }
    // Helper function to calculate the next program section value
    public function GetNextProgramSection($currentProgramSection) {
        $lastCharacter = substr($currentProgramSection, -1);
        if ($lastCharacter == "Z") {
            throw new Exception("Maximum number of program sections exceeded");
        }
        $nextCharacter = chr(ord($lastCharacter) + 1);
        return substr_replace($currentProgramSection, $nextCharacter, -1);
    }


    public function AddRegularStudent($fname, $lname, $mi, $username, $password,
        $course_id, $student_unique_id, $sex, $b_day, $birthplace,
        $nationality, $religion, $s_contact, $address, $guardian,
        $g_contact, $civil_status, $default_course_level) {

        $query = $this->con->prepare("INSERT INTO student 
            (firstname, lastname, middle_name, username, profilePic, password, student_status, course_id ,student_unique_id, sex, birthday, birthplace, age, nationality, religion, contact_number, address, guardian_name, guardian_contact_number, civil_status, course_level) 
            VALUES (:firstname, :lastname, :middle_name, :username, :profilePic, :password, :student_status, :course_id, :student_unique_id, :sex, :birthday, :birthplace, :age, :nationality, :religion, :contact_number, :address, :guardian_name, :guardian_contact_number, :civil_status, :course_level)");

        $b_day = date('Y-m-d', strtotime($b_day));
        $age = $this->getCurrentAge($b_day);
        $hash_password = password_hash($password, PASSWORD_BCRYPT);
        $REGULAR = "Regular";

        $query->bindValue(":firstname", $fname);
        $query->bindValue(":lastname", $lname);
        $query->bindValue(":middle_name", $mi);
        $query->bindValue(":username", $username);
        $query->bindValue(":profilePic", "");
        $query->bindValue(":password", $hash_password);
        $query->bindValue(":student_status", $REGULAR);
        $query->bindValue(":course_id", $course_id);
        $query->bindValue(":student_unique_id", $student_unique_id);
        $query->bindValue(":sex", $sex);
        $query->bindValue(":birthday", $b_day);
        $query->bindValue(":birthplace", $birthplace);
        $query->bindValue(":age", $age);
        $query->bindValue(":nationality", $nationality);
        $query->bindValue(":religion", $religion);
        $query->bindValue(":contact_number", $s_contact);
        $query->bindValue(":address", $address);
        $query->bindValue(":guardian_name", $guardian);
        $query->bindValue(":guardian_contact_number", $g_contact);
        $query->bindValue(":civil_status", $civil_status);
        $query->bindValue(":course_level", $default_course_level);

        return $query->execute();
    }

    public function createFirstYearCourseSelection(){

        $query = $this->con->prepare("SELECT * FROM course
            WHERE course_level= 1
            OR course_level=11");

        $query->execute();

        $html = "<div class='form-group'>
                    <select class='form-control' name='COURSE'>";
 
        if($query->rowCount() > 0){
            while($row = $query->fetch(PDO::FETCH_ASSOC)){
                $html .= "
                    <option value='".$row['course_id']."'>".$row['program_section']."</option>
                ";
            }
        }
        
        $html .= "</select>
                </div>";

        return $html;
    }

    public function CreateRegisterStrand(){

        $query = $this->con->prepare("SELECT * FROM program
            WHERE department_id= 4");

        $query->execute();

        $html = "<div class='form-group'>
                    <select class='form-control' name='STRAND'>";
        // $html .= " <option value='0' >Select</option>";

        if($query->rowCount() > 0){
            while($row = $query->fetch(PDO::FETCH_ASSOC)){

                if($row['program_name']){
                    // $program_name = "STEM";
                }   
                $html .= "
                    <option value='".$row['program_id']."'>".$row['program_name']."</option>
                ";
            }
        }
        
        $html .= "</select>
                </div>";

        return $html;
    }

    private function getCurrentAge($b_day){

        $age = -1;
    
        $birthdate = $b_day;

        // Assume $birthdate is a string that contains the user's birthdate in the format 'YYYY-MM-DD'
        // $birthdate = '2000-01-01';

        // Create a DateTime object representing the user's birthdate
        $birth_date = new DateTime($birthdate);

        // Create a DateTime object representing the current date and time
        $current_date = new DateTime();

        // Calculate the difference between the two dates
        $interval = $current_date->diff($birth_date);

        // Get the user's age in years
        $age = $interval->y;

        return $age;
    }

    // public function CheckIfStudentIsNewEnrollee($username){

    //     $result = $this->con->prepare("SELECT new_enrollee FROM student
    //         WHERE username=:username
    //         LIMIT 1");

    //     $result->bindValue(":username", $username);
    //     $result->execute();
    //     if($result->rowCount() > 0){
    //         $new_enrollee = $result->fetchColumn();
    //         if($new_enrollee == 1)
    //             return true;
    //     }
    //     return false;
    // }

    public function GenerateUniqueStudentNumber(){

        // Get the last student_unique_id

        // $query = $this->con->prepare("SELECT * FROM course");
        // $query->execute();

        $result = $this->con->prepare("SELECT student_unique_id FROM student
            ORDER BY student_id DESC 
            LIMIT 1");
        $result->execute();

        if($result->rowCount() > 0){
        
            while($row = $result->fetch(PDO::FETCH_ASSOC)){
                
                // echo $row['student_unique_id'];

                // Extract the last six digits from the ID, or set it to 0 if no students exist
                $last_id = ($row['student_unique_id']) ? $row['student_unique_id'] : 0;
                $last_six_digits = substr($last_id, -6);

                // echo $last_six_digits;
                // echo "<br>";

                // If the last ID was less than 100000, set it to 99999 to start a new series
                if ($last_six_digits < 100000) {
                    $last_six_digits = 99999;
                }

                // Generate a new seven-digit ID by adding 1 to the last six digits
                $new_six_digits = sprintf('%06d', intval($last_six_digits) + 1);
                // $new_id = '100' . $new_six_digits;

                // echo 'Studet User ID: ' . $result['student_id'] . '<br>';
                return $new_six_digits;
            }
        }else{
            // If no student in the student table, the first student_unique_id = 100001
            // Follow by 100002 so on,
            return 100001;
        }
        

    }


    public function loginStudentUser($username, $password){

        $in_active = 0;
        $isLoggedIn = false;
        // $username = strtolower($username);

        $arr = [];

        $username = strtolower($username);

        $query_student = $this->con->prepare("SELECT 

            student_unique_id, username, password 

            FROM student
            WHERE username=:username
            AND active !=:active

            LIMIT 1");
     
        $query_student->bindValue(":username", $username);
        $query_student->bindValue(":active", $in_active);
        $query_student->execute();

        if($query_student->rowCount() > 0){
            $user = $query_student->fetch(PDO::FETCH_ASSOC);    
            // echo $user['password'];
            if($user['password'] == $password){
                // echo "<br>";
                // echo "equal";
                // echo "<br>";
            }
            if ($user && password_verify($password, $user['password'])) {
                // echo "we";
                // Password is correct, log in the user
                array_push($arr, $username);
                array_push($arr, true);
                array_push($arr, "enrolled");
            }
            
            // if (password_verify($password, $hashed_password)) {
            //     // Passwords match, log the user in
            //     // ...
            // }
            else{
                echo "not cocrrect";
            }
        }
        if($query_student->rowCount() == 0){

            // $isLoggedIn = true;
            $activated = 1;
            $query = $this->con->prepare("SELECT firstname, password 
            
                FROM pending_enrollees

                WHERE student_status !=:student_status
                AND firstname=:firstname
                AND activated=:activated
                LIMIT 1");
        
            $query->bindValue(":student_status", "APPROVED");
            $query->bindValue(":firstname", $username);
            $query->bindValue(":activated", $activated);
            $query->execute();

            if($query->rowCount() > 0){
                // echo "wee";

                $userPending = $query->fetch(PDO::FETCH_ASSOC);    
                echo $userPending['password'];
                if($userPending && password_verify($password, $userPending['password'])) {
                    
                    // Password is correct, log in the user
                    array_push($arr, $username);
                    array_push($arr, true);
                    array_push($arr, "pending");
                }else{
                    echo "not cocrrect pending";
                }
            }
            else{
                // Display alert box with two options
                
                Constants::error("Credentials Error", "");


                // echo '
                // <script>
                //     if (confirm("Your email is not verified yet. Would you like to resend the verification email?")) {
                        
                //         window.location.href = "resend_verification_email.php";
                //     } else {
                //         // Redirect to account page
                //         window.location.href = "account.php";
                //     }
                // </script>';  

                }
            
        }
        // print_r($arr);
        return $arr;

        // $arr = [];

        // if($query->rowCount() == 0){

        //     echo "It seems your account is de-activated. Please go to registrar for assistance.";
        //     echo "<br>";
        //     // exit();
        // }else{
        //     return true;
        // }

        // return $isLoggedIn;

        
        // if( $query->rowCount() != 0 && $query->rowCount() > 0){

        //     // echo "has in the db";
        //     $row = $query->fetch(PDO::FETCH_ASSOC);
            
        //     $stored_hash = $row['password'];

        //     if (password_verify($password, $stored_hash)) {

        //         // Passwords match
        //         // echo $password;
        //         // echo "<br>";
        //         // echo $stored_hash;
        //         // echo "password match";

        //         array_push($arr, $row['username']);
        //         array_push($arr, "true");
        //         // return true;

        //     } else {
        //         echo $password;
        //         echo "<br>";
        //         echo $stored_hash;
        //         // Passwords do not match
        //         echo "Invalid password or username";
        //     }

        //     // while($row = $query->fetch(PDO::FETCH_ASSOC)){
        //     //     $storedPassword = $row['password'];
        //     //     echo "nice";
        //     //     echo $storedPassword;
        //     // }

        //     // $storedPassword = $row['password'];
        //     // echo $storedPassword;
        //     // echo "same pass";
        // }
        
        // return $arr;
    }

    public function GetStudentCurrentYearId($username){
        // 
        // $query = $this->con->prepare("SELECT school_year_id FROM student
        //     WHERE username=:username");
     
        // $query->bindValue(":username", $username);
        // $query->execute();

        // return $query->fetchColumn();
    }

    public function GetStudentCourseId($username){
        $query = $this->con->prepare("SELECT course_id FROM student
            WHERE username=:username");
     
        $query->bindValue(":username", $username);
        $query->execute();

        if($query->rowCount() > 0){
            return $query->fetchColumn();
        }
    }

    public function GetStudentCourseTertiaryId($username){
        $query = $this->con->prepare("SELECT course_tertiary_id FROM student
            WHERE username=:username");
     
        $query->bindValue(":username", $username);
        $query->execute();

        if($query->rowCount() > 0){
            return $query->fetchColumn();
        }
    }
    

    public function GetStudentProgramSection($course_id){
        $query = $this->con->prepare("SELECT program_section FROM course
            WHERE course_id=:course_id");
     
        $query->bindValue(":course_id", $course_id);
        $query->execute();

        return $query->fetchColumn();
    }

    public function GetStudentCourseIdById($student_id){
        $query = $this->con->prepare("SELECT course_id FROM student
            WHERE student_id=:student_id");
     
        $query->bindValue(":student_id", $student_id);
        $query->execute();
        if($query->rowCount() > 0){
            return $query->fetchColumn();
        }
    }
    
    public function GetStudentByUniqueId($student_unique_id){

        $query = $this->con->prepare("SELECT student_id FROM student
            WHERE student_unique_id=:student_unique_id");
     
        $query->bindValue(":student_unique_id", $student_unique_id);
        $query->execute();

        return $query->fetchColumn();
    }

    public function GetCurrentYearId(){
        
        $basis = "Active";
        $query = $this->con->prepare("SELECT school_year_id FROM school_year
            WHERE statuses=:statuses
            LIMIT 1");
     
        $query->bindValue(":statuses", $basis);
        $query->execute();

        return $query->fetchColumn();
    }
    public function GetStudentCourseName($username){

        $student_course_id= $this->GetStudentCourseId($username);

        $query = $this->con->prepare("SELECT program_section FROM course
            WHERE course_id=:course_id");
     
        $query->bindValue(":course_id", $student_course_id);
        $query->execute();

        return $query->fetchColumn();
    }

    public function GetStudentCourseNameByCourseId($student_course_id){


        $query = $this->con->prepare("SELECT program_section FROM course
            WHERE course_id=:course_id");
     
        $query->bindValue(":course_id", $student_course_id);
        $query->execute();

        return $query->fetchColumn();
    }

    public function GetStudentCourseLevel($username){
        $query = $this->con->prepare("SELECT course_level FROM student
            WHERE username=:username");
     
        $query->bindValue(":username", $username);
        $query->execute();

        if($query->rowCount() == 0){
            $student_id = $username;
            $query2 = $this->con->prepare("SELECT course_level FROM student
                WHERE student_id=:student_id");
        
            $query2->bindValue(":student_id", $student_id);
            $query2->execute();  
            if($query2->rowCount() > 0){
                return $query2->fetchColumn();
            }
        }else if($query->rowCount() > 0){
            return $query->fetchColumn();
        }

    }

    public function GetStudentCourseLevelYearIdCourseId($username){

        $query = $this->con->prepare("SELECT student_id, course_level,course_tertiary_id, course_id,
            student_status, username, is_tertiary
            FROM student
            WHERE username=:username");
     
        $query->bindValue(":username", $username);
        $query->execute();

        return $query->fetch(PDO::FETCH_ASSOC);
    }


    public function GetStudentProgramId($course_id){
        $query = $this->con->prepare("SELECT program_id FROM course
            WHERE course_id=:course_id");
     
        $query->bindValue(":course_id", $course_id);
        $query->execute();

        return $query->fetchColumn();
    }

    public function GetStudentTertiaryProgramId($course_tertiary_id){
        $query = $this->con->prepare("SELECT program_id FROM course_tertiary
            WHERE course_tertiary_id=:course_tertiary_id");
     
        $query->bindValue(":course_tertiary_id", $course_tertiary_id);
        $query->execute();

        return $query->fetchColumn();
    }

    public function GetStudentId($username){

        $query = $this->con->prepare("SELECT student_id, student_status FROM student
            WHERE username=:username");
     
        $query->bindValue(":username", $username);
        $query->execute();

        return $query->fetchColumn();
    }

    public function GetStudentStatus($username){

        $query = $this->con->prepare("SELECT student_status FROM student
            WHERE username=:username");
     
        $query->bindValue(":username", $username);
        $query->execute();

        return $query->fetchColumn();
    }

    public function GetStudentUsername($student_id){

        $query = $this->con->prepare("SELECT username FROM student
            WHERE student_id=:student_id");
     
        $query->bindValue(":student_id", $student_id);
        $query->execute();

        return $query->fetchColumn();
    }

    public function GetStudentFirstname($student_id){

        $firstname = "";
        $query = $this->con->prepare("SELECT firstname FROM student
            WHERE student_id=:student_id");
     
        $query->bindValue(":student_id", $student_id);
        $query->execute();

        if($query->rowCount() > 0){
            $row = $query->fetch(PDO::FETCH_ASSOC);

            $firstname = $row['firstname'];

        }

        return $firstname;
    }
 public function GetStudentLastname($student_id){

        $lastname = "";
        $query = $this->con->prepare("SELECT lastname FROM student
            WHERE student_id=:student_id");
     
        $query->bindValue(":student_id", $student_id);
        $query->execute();

        if($query->rowCount() > 0){
            $row = $query->fetch(PDO::FETCH_ASSOC);

            $lastname = $row['lastname'];

        }

        return $lastname;
    }

    public function GetStudentMiddlename($student_id){

        $middle_name = "";
        $query = $this->con->prepare("SELECT middle_name FROM student
            WHERE student_id=:student_id");
     
        $query->bindValue(":student_id", $student_id);
        $query->execute();

        if($query->rowCount() > 0){
            $row = $query->fetch(PDO::FETCH_ASSOC);

            $middle_name = $row['middle_name'];

        }

        return $middle_name;
    }

    public function GetStudentFullName($student_id){

        $fullname = "N/A";
        $query = $this->con->prepare("SELECT firstname, lastname FROM student
            WHERE student_id=:student_id");
     
        $query->bindValue(":student_id", $student_id);
        $query->execute();

        if($query->rowCount() > 0){
            $row = $query->fetch(PDO::FETCH_ASSOC);

            $fullname = $row['firstname'] ." ". $row['lastname'];

        }

        return $fullname;
    }

    public function GetStudentListOfSubjectToTake($username){

        $course_id = $this->GetStudentCourseId($username);
        $program_id = $this->GetStudentProgramId($course_id);

        $query = $this->con->prepare("SELECT subject_code, semester, subject_title, unit, course_level FROM subject
            WHERE program_id=:program_id
            ORDER BY course_level ASC");
     
        $query->bindValue(":program_id", $program_id);
        $query->execute();

        return $query->fetchAll();
    }

    public function GetStudentSubjectListWithEnrolledSubject($username){

        $course_id = $this->GetStudentCourseId($username);
        $program_id = $this->GetStudentProgramId($course_id);

        $get_student_id = $this->GetStudentId($username);

        // $query = $this->con->prepare("SELECT 
        //     subject_code, semester, subject_title, unit, course_level FROM subject
        //     WHERE course_main_id=:course_main_id
        //     ORDER BY course_level ASC
        //     ");
        $student_id = $this->GetStudentId($username);

        $query = $this->con->prepare("SELECT 
                t1.subject_id, 
                t1.subject_code, 
                t1.semester, t1.subject_title,
                t1.unit, t1.course_level,


                -- t2.time_from,
                -- t2.time_to,
                -- t2.subject_schedule_id,
                -- t2.schedule_day,
                -- t2.schedule_time,
                -- t2.room,
                -- t2.teacher_id,
                -- t2.section
                t2.subject_schedule_id,
                t3.schedule_time,
                t2.student_id,
                t2.subject_id as student_enrolled_subject_id,
                t3.room,
                t3.section,
                t3.schedule_day
            -- Todo: login student must populated all the subjects under his strand
            -- fill up the possible enrolled subjects together with the subjects

            FROM subject as t1
            LEFT JOIN student_subject as t2 on t1.subject_id = t2.subject_id
            LEFT JOIN subject_schedule as t3 on t2.subject_schedule_id = t3.subject_schedule_id

            WHERE program_id=:program_id
            AND t2.student_id=:student_id
            

            -- AND t2.student_id=:student_id
            -- WHERE course_level=:course_level
            -- AND semester=:semester
            -- AND course_main_id=:course_main_id
            ORDER BY t1.course_level ASC, t1.semester
            
            ");
     
        $query->bindValue(":program_id", $program_id);
        $query->bindValue(":student_id", $get_student_id);
        $query->execute();

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function GetStudent($username){
    }

    public function GetStudentSubjectListWithGradesv2($username, $ifStudentCourseGrade12){

        $school_year_obj = $this->GetActiveSchoolYearAndSemester();

        $current_school_year_id = $school_year_obj['school_year_id'];

        $student_id = $this->GetStudentId($username);

        $GRADE_ELEVEN = 11;
        $GRADE_TWELVE = 12;
        $student_course_obj = $this->GetStudentCourseLevelYearIdCourseId($username);

        $course_id = $student_course_obj['course_id'];
        $course_level = $student_course_obj['course_level'];

        $program_id = $this->GetStudentProgramId($course_id);

        $stored_course_id_arr = [];
        $result_arr = [];
        $stored_subject = [];

        $enrollment_status = "enrolled";

        if($course_level == $GRADE_ELEVEN){

            // Get all course id from enrollment
            $query = $this->con->prepare("SELECT 
                t1.subject_id, 
                t1.subject_code, 
                t1.semester,
                t1.subject_title,
                t1.unit,
                t1.course_level,
                t1.pre_subject_id,
                t1.course_id,
                t1.subject_type,

                t2.school_year_term, t2.program_section

                FROM subject as t1

                LEFT JOIN course as t2 ON t2.course_id = t1.course_id

                WHERE t1.program_id=:program_id
                AND t1.course_id=:first_course_id

                ORDER BY t1.course_level ASC, t1.semester");
        
            $query->bindValue(":program_id", $program_id);
            $query->bindValue(":first_course_id", $course_id);
            $query->execute();

            while($row2 = $query->fetch(PDO::FETCH_ASSOC)){
                // array_push($stored_course_id_arr, $row2);
                array_push($result_arr, $row2);
            }


            // $get_enrollment = $this->con->prepare("SELECT DISTINCT
            //     t1.course_id,

            //     t2.student_subject_grade_id,
            //     t2.subject_id,
            //     t2.course_id as grade_course_id
            //     -- t2.school_year_term, t2.program_section

            //     FROM enrollment as t1

            //     INNER JOIN student_subject_grade as t2 ON t2.course_id = t1.course_id

            //     WHERE t1.student_id = :student_id
            //     AND t2.remarks = :remarks
            //     AND t2.student_id = :grade_student_id

            //     AND t1.enrollment_status = :enrollment_status
            //     AND t1.school_year_id = :school_year_id

            //     -- GETTING GRADE 11
            //     AND t1.course_id IN (
            //         SELECT course_id
            //         FROM course
            //         WHERE course_level = 11
            //     )");
            
            // $get_enrollment->bindValue(":student_id", $student_id);
            // $get_enrollment->bindValue(":remarks", "Passed");
            // $get_enrollment->bindValue(":grade_student_id", $student_id);
            // $get_enrollment->bindValue(":enrollment_status", $enrollment_status);
            // $get_enrollment->bindValue(":school_year_id", $current_school_year_id);
            // $get_enrollment->execute();
            
            // if($get_enrollment->rowCount() > 0){

            //     // Push the previous Grade 12 course_id
            //     while($row = $get_enrollment->fetch(PDO::FETCH_ASSOC)){
                    
            //         array_push($stored_subject, $row['subject_id']);

            //         echo $row['subject_id'];
            //         echo "<br>";
            //     }
            // }

            // $query = $this->con->prepare("SELECT 
            //     t1.subject_id, 
            //     t1.subject_code, 
            //     t1.semester,
            //     t1.subject_title,
            //     t1.unit,
            //     t1.course_level,
            //     t1.pre_subject_id,
            //     t1.subject_type,
            //     t1.course_id, 
                
            //     t2.program_section, t2.school_year_term
            
            // FROM subject as t1
            // LEFT JOIN course as t2 ON t2.course_id = t1.course_id

            // WHERE t1.program_id=:program_id
            // AND t1.subject_id=:grade_eleven_subject_id

            // ORDER BY t1.course_level ASC, t1.semester");

            // foreach ($stored_subject as $key => $eleven_subject_ids) {
            //     # code...

            //     // echo $eleven_subject_ids;
            //     // echo "<br>";

            //     $query->bindValue(":program_id", $program_id);
            //     // $query->bindValue(":first_course_id", $course_id);
            //     $query->bindValue(":grade_eleven_subject_id", $eleven_subject_ids);
            //     $query->execute();

            //     while($row2 = $query->fetch(PDO::FETCH_ASSOC)){
            //         // array_push($stored_course_id_arr, $row2);

            //         array_push($result_arr, $row2);
            //     }
            // }


            // $query = $this->con->prepare("SELECT 

            //     t3.subject_id as t3Subject_Id, 
            //     t3.subject_code as t3SubjectCode,  
            //     t3.semester as t3Semester,
            //     t3.subject_title,
            //     t3.unit,
            //     t3.course_level,
            //     t3.pre_subject_id,
            //     t3.subject_type,
            //     t3.course_id

            //     FROM subject_program as t1

            //     LEFT JOIN student_subject_grade as t2 ON t2.subject_title=t1.subject_title
            //     LEFT JOIN subject as t3 ON t3.subject_id=t2.subject_id

            //     WHERE t1.program_id=:program_id
            //     AND t1.course_level=:course_level

            //     AND t2.student_id=:student_id
            //     AND t2.remarks=:remarks
               
            //    ");
            
            // $query->bindValue(":program_id", $program_id);
            // $query->bindValue(":course_level", 11);
            // $query->bindValue(":student_id", $student_id);
            // $query->bindValue(":remarks", "Passed");
            // $query->execute();

            // if($query->rowCount() > 0){
            //     while($row_q = $query->fetch(PDO::FETCH_ASSOC)){

            //         $subject_id = $row_q['t3Subject_Id'];

            //         // print_r($row_q);
            //         // echo "<br>";

            //         // TO BE CONTINUE, IT DID NOT GET THE ALL Subjects for GRADE 11 STEN STRAND.
            //         array_push($result_arr, $row_q);

            //     }
            // }
        }

        # Regular
        # Get all prev course_id of Grade 11. (only applies at Grade 12 standing)

        if($course_level == $GRADE_TWELVE){

            if($ifStudentCourseGrade12 == false){

                // echo "qwe";
                $query = $this->con->prepare("SELECT 
                    t1.subject_id, 
                    t1.subject_code, 
                    t1.semester,
                    t1.subject_title,
                    t1.unit,
                    t1.course_level,
                    t1.pre_subject_id, 
                    
                    -- t2.course_id,
                    t1.subject_type

                    -- t2.school_year_term, t2.program_section
                
                    FROM subject AS t1

                    -- LEFT JOIN student_subject_grade AS t2 ON t2.subject_title = t1.subject_title

                    WHERE t1.program_id=:program_id
                    AND t1.course_id=:first_course_id
                    ORDER BY t1.course_level ASC, t1.semester"
                );
        
                $query->bindValue(":program_id", $program_id);
                $query->bindValue(":first_course_id", $course_id);
                // $query->bindValue(":second_course_id", $course_ids);
                // $query->bindValue(":course_level", $course_level);
                // $query->bindValue(":student_id", $get_student_id);
                $query->execute();

                while($row2 = $query->fetch(PDO::FETCH_ASSOC)){
                    // array_push($stored_course_id_arr, $row2);
                    array_push($result_arr, $row2);

                    // echo $row2['subject_id'];
                    // echo "<br>";
                }

            }

            if($ifStudentCourseGrade12 == true){

                // echo "Ewee";
                # Once student move-up to grade 12
                # it means all grade 11 subjects are inserted in the system
                # For transferee that enrolled in Grade 11.
                $get_enrollment = $this->con->prepare("SELECT DISTINCT
                    t1.course_id,

                    t2.student_subject_grade_id,
                    t2.subject_id,
                    t2.course_id AS grade_course_id,
                    t3.school_year_term, t3.program_section

                    FROM enrollment AS t1

                    INNER JOIN student_subject_grade AS t2 ON t2.course_id = t1.course_id
                    INNER JOIN course AS t3 ON t3.course_id = t2.course_id

                    WHERE t1.student_id = :student_id
                    AND t2.remarks = :remarks
                    AND t2.student_id = :grade_student_id

                    AND t1.enrollment_status = :enrollment_status
                    AND t1.school_year_id != :school_year_id

                    -- GETTING GRADE 11
                    AND t1.course_id IN (
                        SELECT course_id
                        FROM course
                        WHERE course_level = 11
                    )");
                
                $get_enrollment->bindValue(":student_id", $student_id);
                $get_enrollment->bindValue(":remarks", "Passed");
                $get_enrollment->bindValue(":grade_student_id", $student_id);
                $get_enrollment->bindValue(":enrollment_status", $enrollment_status);
                $get_enrollment->bindValue(":school_year_id", $current_school_year_id);
                $get_enrollment->execute();
                
                if($get_enrollment->rowCount() > 0){

                    // Push the previous Grade 12 course_id
                    while($row = $get_enrollment->fetch(PDO::FETCH_ASSOC)){
                       
                        array_push($stored_subject, $row['subject_id']);

                        // echo $row['student_subject_grade_id'];
                        // echo "<br>";
                    }
                    // echo "eeqw";
                    // print_r($stored_subject);
                    // echo "<br>";
                }

                // Push the current Grade 12 course_id
                array_push($stored_course_id_arr, $course_id);

                
                // foreach ($stored_course_id_arr as $key => $course_ids) {
                //     # code...

                //     echo $course_ids;
                //     echo "<br>";
                //     $query = $this->con->prepare("SELECT 
                //         t1.subject_id, 
                //         t1.subject_code, 
                //         t1.semester,
                //         t1.subject_title,
                //         t1.unit,
                //         t1.course_level,
                //         t1.pre_subject_id,
                //         t1.subject_type,
                //         t1.course_id
                    
                //     FROM subject as t1

                //     -- LEFT JOIN student_subject as t2 ON t2.subject_id = t1.subject_id
                //     WHERE program_id=:program_id
                //     AND course_id=:first_course_id



                //     ORDER BY t1.course_level ASC, t1.semester");
            
                //     $query->bindValue(":program_id", $program_id);
                //     $query->bindValue(":first_course_id", $course_ids);
                //     $query->execute();

                //     while($row2 = $query->fetch(PDO::FETCH_ASSOC)){
                //         // array_push($stored_course_id_arr, $row2);


                //         array_push($result_arr, $row2);
                //     }
                // }

                $query = $this->con->prepare("SELECT 
                    t1.subject_id, 
                    t1.subject_code, 
                    t1.semester,
                    t1.subject_title,
                    t1.unit,
                    t1.course_level,
                    t1.pre_subject_id,
                    t1.subject_type,
                    t1.course_id, 
                    
                    t2.program_section, t2.school_year_term
                
                FROM subject as t1
                LEFT JOIN course as t2 ON t2.course_id = t1.course_id

                WHERE t1.program_id=:program_id
                AND t1.subject_id=:grade_eleven_subject_id

                ORDER BY t1.course_level ASC, t1.semester
                LIMIT 1");

                foreach ($stored_subject as $key => $eleven_subject_ids) {
                    # code...

                    // echo $eleven_subject_ids;
                    // echo "<br>";
                    $query->bindValue(":program_id", $program_id);
                    $query->bindValue(":grade_eleven_subject_id", $eleven_subject_ids);
                    $query->execute();

                    while($row2 = $query->fetch(PDO::FETCH_ASSOC)){
                        // array_push($stored_course_id_arr, $row2);

                        array_push($result_arr, $row2);
                    }
                    // print_r($result_arr);
                }


                # For transferee that enrolled in Grade 12 ONLY.
                if($get_enrollment->rowCount() == 0){
                    $twelveOnly = $this->con->prepare("SELECT DISTINCT
                    -- t1.student_subject_grade_id,
                    sub.subject_id, 
                    sub.subject_code, 
                    sub.semester,
                    sub.subject_title,
                    sub.unit,
                    sub.course_level,
                    sub.pre_subject_id,
                    sub.subject_type,
                    sub.course_id,

                    t1.course_id, t2.program_section, t2.school_year_term

                    FROM student_subject_grade as t1

                    INNER  JOIN course as t2 ON t1.course_id = t2.course_id
                    INNER  JOIN subject as sub ON sub.course_id = t1.course_id

                    WHERE t1.student_id=:student_id
                    AND t2.course_level=:course_level
                    ");
                
                    $twelveOnly->bindValue(":student_id", $student_id);
                    $twelveOnly->bindValue(":course_level", 11);
                    $twelveOnly->execute();

                    if($twelveOnly->rowCount() > 0){
                        while($row = $twelveOnly->fetch(PDO::FETCH_ASSOC)){

                            array_push($result_arr, $row);
                        }
                        // print_r($result_arr);
                    }
                }


                $currentSubject = $this->con->prepare("SELECT 
                    sub.subject_id, 
                    sub.subject_code, 
                    sub.semester,
                    sub.subject_title,
                    sub.unit,
                    sub.course_level,
                    sub.pre_subject_id,
                    sub.subject_type,
                    sub.course_id,
                    t2.program_section, t2.school_year_term


                    FROM subject as sub

                    LEFT JOIN course as t2 ON t2.course_id = sub.course_id

                    WHERE sub.program_id=:program_id
                    AND sub.course_id=:course_id
                    ORDER BY sub.course_level ASC, sub.semester
                ");

                $currentSubject->bindValue(":program_id", $program_id);
                // Current Student course_id
                $currentSubject->bindValue(":course_id", $course_id);
                $currentSubject->execute();
                if($currentSubject->rowCount() > 0){

                    // echo "qwe";
                    while($row_2 = $currentSubject->fetch(PDO::FETCH_ASSOC)){

                        array_push($result_arr, $row_2);
                    }
                        // print_r($result_arr);

                }else{
                    // echo "not";
                }
            }
        }

        return $result_arr;
    }




    public function GetTertiaryGradeReport($username, $student_program_id, $student_course_id){

        $school_year_obj = $this->GetActiveSchoolYearAndSemester();

        $current_school_year_id = $school_year_obj['school_year_id'];

        $student_id = $this->GetStudentId($username);

        $GRADE_ELEVEN = 11;
        $GRADE_TWELVE = 12;
        $student_course_obj = $this->GetStudentCourseLevelYearIdCourseId($username);

        $course_level = $student_course_obj['course_level'];

        $enrollment_status = "enrolled";

        $result_arr = [];
        // Get all course id from enrollment
        $query = $this->con->prepare("SELECT DISTINCT

            t1.subject_code,
            t1.subject_title,
            t1.unit,
            t1.semester,
            t1.course_level,

            t2.subject_tertiary_id as subjectId,
            -- t2.subject_code,
            -- t2.subject_title,
            -- t2.unit,
            -- t2.semester,

            -- t2.course_level,

            t3.student_subject_tertiary_id,

            t3.subject_tertiary_id as subjectTertiaryId,
            t4.remarks

            FROM subject_program as t1

            LEFT JOIN subject_tertiary as t2 ON t2.subject_program_id = t1.subject_program_id
            LEFT JOIN student_subject_tertiary as t3 ON t3.subject_tertiary_id = t2.subject_tertiary_id
            LEFT JOIN student_subject_grade_tertiary as t4 ON t4.student_subject_tertiary_id = t3.student_subject_tertiary_id

            WHERE t1.program_id=:program_id
            ");
    
        $query->bindValue(":program_id", $student_program_id);
        $query->execute();

        while($row2 = $query->fetch(PDO::FETCH_ASSOC)){
            // array_push($stored_course_id_arr, $row2);
            array_push($result_arr, $row2);
        }

        // echo $student_program_id;
        // print_r($result_arr);
         
        return $result_arr;
    }

    public function GetTertiaryGradeReportv2($username, 
        $student_program_id, $student_course_id){

        $school_year_obj = $this->GetActiveSchoolYearAndSemester();

        $current_school_year_id = $school_year_obj['school_year_id'];

        $student_id = $this->GetStudentId($username);

        $GRADE_ELEVEN = 11;
        $GRADE_TWELVE = 12;
        $student_course_obj = $this->GetStudentCourseLevelYearIdCourseId($username);

        $course_level = $student_course_obj['course_level'];

        $enrollment_status = "enrolled";

        $result_arr = [];
        // Get all course id from enrollment
        $query = $this->con->prepare("SELECT

            t1.subject_code,
            t1.subject_title,
            t1.unit,
            t1.semester,
            t1.course_level,

            -- t2.student_subject_tertiary_id as t2_student_subject_tertiary_id,
            -- t3.student_subject_tertiary_id as t3_student_subject_tertiary_id
 
            t2.student_subject_tertiary_id,
            t2.student_subject_tertiary_id as t2_student_subject_tertiary_id,
            t3.student_subject_tertiary_id as t3_student_subject_tertiary_id,

            t3.remarks,
            t4.subject_tertiary_id as subjectId

            FROM subject_program AS t1

            -- LEFT JOIN student_subject_grade_tertiary AS t2 ON t2.subject_program_id = t1.subject_program_id
            -- AND t2.student_id = :student_id
            -- LEFT JOIN student_subject_tertiary as t3 ON t3.subject_tertiary_id = t2.subject_tertiary_id



            LEFT JOIN student_subject_tertiary as t2 ON t2.subject_program_id = t1.subject_program_id
            AND t2.student_id = :student_id

            LEFT JOIN student_subject_grade_tertiary AS t3 ON t3.student_subject_tertiary_id = t2.student_subject_tertiary_id
            LEFT JOIN subject_tertiary AS t4 ON t4.subject_tertiary_id = t2.subject_tertiary_id


            WHERE t1.program_id = :program_id

            ");
    
        $query->bindValue(":program_id", $student_program_id);
        $query->bindValue(":student_id", $student_id);
        $query->execute();

        while($row2 = $query->fetch(PDO::FETCH_ASSOC)){
            // array_push($stored_course_id_arr, $row2);
            array_push($result_arr, $row2);
        }

        // echo $student_id;
        // print_r($result_arr);
         
        return $result_arr;
    }

    public function GetStudentsStrandSubjects($username){

        $student_obj = $this->GetStudentCourseLevelYearIdCourseId($username);

        $student_course_id = $student_obj['course_id'];

        $program_id = $this->GetStudentProgramId($student_course_id);

        $student_course_level = $student_obj['course_level'];

        $school_year_obj = $this->GetActiveSchoolYearAndSemester();
        $current_school_year_period = $school_year_obj['period'];

        $query = $this->con->prepare("SELECT 
            t1.subject_id, 
            t1.subject_code, 
            t1.semester,
            t1.subject_title,
            t1.unit,
            t1.course_level,
            t1.pre_subject_id,
            t1.subject_type,
            t1.subject_program_id,
            t1.pre_requisite,
            t1.subject_type,

            t2.schedule_day,
            t2.schedule_time,
            t2.time_from,
            t2.room,
            t2.time_to
            
        FROM subject as t1


        LEFT JOIN subject_schedule as t2 ON t2.subject_id = t1.subject_id

        WHERE t1.program_id=:program_id
        AND t1.course_id=:course_id
        -- AND course_level=:course_level
        AND t1.semester=:semester

        ORDER BY t1.course_level ASC, t1.semester");
    
        $query->bindValue(":program_id", $program_id);
        $query->bindValue(":course_id", $student_course_id);

        // $query->bindValue(":course_level", $student_course_level);
        
        $query->bindValue(":semester", $current_school_year_period);

        $query->execute();

        $result =  $query->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    // UUU
    public function GetStudentTertiarySubjects($username){

        $student_obj = $this->GetStudentCourseLevelYearIdCourseId($username);

        $student_course_id = $student_obj['course_tertiary_id'];

        $program_id = $this->GetStudentTertiaryProgramId($student_course_id);

        // $student_course_level = $student_obj['course_level'];

        $school_year_obj = $this->GetActiveSchoolYearAndSemester();
        $current_school_year_period = $school_year_obj['period'];

        $query = $this->con->prepare("SELECT 
            t1.subject_tertiary_id, 
            t1.subject_code, 
            t1.semester,
            t1.subject_title,
            t1.unit,
            t1.course_level,
            -- t1.pre_subject_id,
            t1.subject_type
            
        FROM subject_tertiary as t1

        WHERE program_id=:program_id
        AND course_tertiary_id=:course_tertiary_id
        -- AND course_level=:course_level
        AND semester=:semester

        ORDER BY t1.course_level ASC, t1.semester");
    
        $query->bindValue(":program_id", $program_id);
        $query->bindValue(":course_tertiary_id", $student_course_id);

        // $query->bindValue(":course_level", $student_course_level);
        
        $query->bindValue(":semester", $current_school_year_period);

        $query->execute();

        $result =  $query->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }


    public function GetStudentsStrandSubjectsForGradeEleven($username, $GRADE_ELEVEN){

        $course_id = $this->GetStudentCourseId($username);
        $program_id = $this->GetStudentProgramId($course_id);
        // $GRADE_ELEVEN = 11;

        $query = $this->con->prepare("SELECT 
                t1.subject_id, 
                t1.subject_code, 
                t1.semester, t1.subject_title,
                t1.unit, t1.course_level,
                t1.pre_subject_id,
                t1.subject_type
                
            FROM subject as t1

            WHERE program_id=:program_id
            AND course_level=:course_level
            AND course_id=:course_id
            ORDER BY t1.course_level ASC, t1.semester");
     
        $query->bindValue(":program_id", $program_id);
        $query->bindValue(":course_level", $GRADE_ELEVEN);
        $query->bindValue(":course_id", $course_id);

        // $query->bindValue(":student_id", $get_student_id);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function GetStudentsStrandSubjectsForGradeTwelve($username,
        $GRADE_TWELVE, $SEMESTER){

        $course_id = $this->GetStudentCourseId($username);
        $program_id = $this->GetStudentProgramId($course_id);
        // $GRADE_TWELVE = 11;

        $query = $this->con->prepare("SELECT 
                t1.subject_id, 
                t1.subject_code, 
                t1.semester, t1.subject_title,
                t1.unit, t1.course_level,
                t1.pre_subject_id,
                t1.subject_type
                
            FROM subject as t1

            WHERE program_id=:program_id
            AND course_level=:course_level
            AND semester=:semester
            AND course_id=:course_id
            ORDER BY t1.course_level ASC, t1.semester");
     
        $query->bindValue(":program_id", $program_id);
        $query->bindValue(":course_level", $GRADE_TWELVE);
        $query->bindValue(":semester", $SEMESTER);
        $query->bindValue(":course_id", $course_id);

        // $query->bindValue(":student_id", $get_student_id);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function GetStudentSectionGradeElevenFirstSem($username, $student_id, $GRADE_ELEVEN){

        $course_id = $this->GetStudentCourseId($username);
        $program_id = $this->GetStudentProgramId($course_id);
        $arr = [];

        $query = $this->con->prepare("SELECT course_id, school_year_id FROM enrollment
            WHERE student_id=:student_id");

        $query->bindValue(":student_id", $student_id);
        $query->execute(); 
        $result = $query->fetchAll(PDO::FETCH_ASSOC);

        // print_r($result);
        foreach ($result as $key => $t1) {
            # code...
            $course_id = $t1['course_id'];
            // echo $course_id;
            $school_year_id =  $t1['school_year_id'];

            $year_query = $this->con->prepare("SELECT period FROM school_year
                WHERE school_year_id=:school_year_id
                LIMIT 1");

            $year_query->bindValue(":school_year_id", $school_year_id);
            $year_query->execute(); 

            if($year_query->rowCount() > 0){
                $asd = $year_query->fetchColumn();

                $subject_query = $this->con->prepare("SELECT * FROM subject
                    WHERE course_id=:course_id
                    AND semester=:semester
                    LIMIT 1");

                $subject_query->bindValue(":course_id", $course_id);
                $subject_query->bindValue(":semester", $asd);
                // $subject_query->bindValue(":course_level", $GRADE_ELEVEN);
                // $subject_query->bindValue(":program_id", $program_id);
                $subject_query->execute(); 

                $subject_query = $subject_query->fetch(PDO::FETCH_ASSOC);
              
                array_push($arr, $subject_query);
            }
              
        }

        // print_r($arr);
        return $arr;
    }

    public function GetStudentSectionGradeElevenSemester($username,
        $student_id, $GRADE_ELEVEN, $SELECTED_SEMESTER){

        //. Student course_id
        $course_id = $this->GetStudentCourseId($username);
        $program_id = $this->GetStudentProgramId($course_id);
        $arr = [];

        // TODO: GRADE 11 HUMSS-101 1st Semester (Done)
        // TODO: Subject -> SChedule
 
        $query = $this->con->prepare("SELECT 

            e.student_id, e.course_id, sy.school_year_id, sy.period 
            FROM enrollment e

            INNER JOIN school_year sy ON e.school_year_id = sy.school_year_id
            -- INNER JOIN course c ON c.course_id = e.course_id

            WHERE e.student_id = :student_id
            AND e.enrollment_status=:enrollment_status
            AND sy.period =:period
            -- AND c.course_id =:course_id
            ");

        $query->bindValue("student_id", $student_id); 
        $query->bindValue("period", $SELECTED_SEMESTER); 
        $query->bindValue("enrollment_status", "enrolled"); 
        // $query->bindValue("course_id", 20); 
        $query->execute(); 
        $enrollment_student_course_id = null;

        if($query->rowCount() > 0){
            $result = $query->fetch(PDO::FETCH_ASSOC);
            $enrollment_student_course_id = $result['course_id'];
        }

        // Enrollment student course_id
        $subject_query = $this->con->prepare("SELECT *
            FROM subject

            WHERE course_id=:course_id
            AND semester=:semester
            AND program_id=:program_id
            AND course_level=:course_level
            ");

            // $enrollment_student_course_id;
            // echo "<br>";

            // echo $enrollment_student_course_id;
        $subject_query->bindValue("course_id", $enrollment_student_course_id); 
        $subject_query->bindValue("semester", $SELECTED_SEMESTER); 
        $subject_query->bindValue("program_id", $program_id); 
        $subject_query->bindValue("course_level", $GRADE_ELEVEN); 
        $subject_query->execute();

        if($subject_query->rowCount() > 0){
            $row_sub = $subject_query->fetchAll(PDO::FETCH_ASSOC);
            // print_r($row_sub);
            return $row_sub;
        }
        else{
            // echo "no data";
        }
        return null;

    }

    public function GetStudentCurriculumBasedOnSemesterSubject($username,
        $student_id, $GRADE_ELEVEN, $SELECTED_SEMESTER){
            
        //. Student course_id
        $course_id = $this->GetStudentCourseId($username);
        $program_id = $this->GetStudentProgramId($course_id);

        // Enrollment student course_id
        $subject_query = $this->con->prepare("SELECT 

            t3.subject_code as t3_subject_code,
            t3.subject_id,
        
            t1.*,

            t4.first,
            t4.second,
            t4.third,
            t4.fourth,
            t4.remarks as grade_remarks

            FROM subject_program as t1

            LEFT JOIN student_subject as t2 ON t2.subject_program_id = t1.subject_program_id
            AND t2.student_id=:student_id
            
            LEFT JOIN subject as t3 ON t3.subject_id = t2.subject_id
            LEFT JOIN student_subject_grade as t4 ON t4.student_subject_id = t2.student_subject_id
            -- LEFT JOIN course as t4 ON t4.course_id = t3.course_id

            WHERE t1.semester=:semester
            AND t1.program_id=:program_id
            AND t1.course_level=:course_level

            -- AND t2.student_id=:student_id

            ");

            // $enrollment_student_course_id;
            // echo "<br>";
            // echo $enrollment_student_course_id;

        $subject_query->bindValue(":semester", $SELECTED_SEMESTER); 
        $subject_query->bindValue(":program_id", $program_id); 
        $subject_query->bindValue(":course_level", $GRADE_ELEVEN); 
        $subject_query->bindValue(":student_id", $student_id); 
        $subject_query->execute();

        if($subject_query->rowCount() > 0){

            $row_sub = $subject_query->fetchAll(PDO::FETCH_ASSOC);
            // print_r($row_sub);
            return $row_sub;
        }
        else{
            // echo "no data";
        }
        return null;

    }

    public function GetStudentTransCurriculumBasedOnSemesterSubject($username,
    
        $student_id, $GRADE_ELEVEN, $SELECTED_SEMESTER, $enrollment_id){
            
        //. Student course_id
        $course_id = $this->GetStudentCourseId($username);
        $program_id = $this->GetStudentProgramId($course_id);

        $within_subject_program_ids = [];
        $allSubjectProgramIds = [];

        $student_enrollment_query = $this->con->prepare("SELECT t1.* 
        
            FROM subject as t1
            INNER JOIN student_subject as t2
            
            WHERE t2.enrollment_id=:enrollment_id
            AND t2.student_id=:student_id
            AND t1.semester=:semester
            AND t1.subject_id = t2.subject_id
            ");

        $student_enrollment_query->bindValue(":enrollment_id", $enrollment_id); 
        $student_enrollment_query->bindValue(":student_id", $student_id); 
        $student_enrollment_query->bindValue(":semester", $SELECTED_SEMESTER); 
        
        $student_enrollment_query->execute();
        if($student_enrollment_query->rowCount() > 0){

            $output = $student_enrollment_query->fetchAll(PDO::FETCH_ASSOC);

            return $output;
            // print_r($d);

        }
 
        return null;

    }

    public function GetSHSTransfereeEnrolledSubjectSemester($username,
        $student_id, $GRADE_LEVEL, $SEMESTER){

        //. Student course_id
        $course_id = $this->GetStudentCourseId($username);
        $program_id = $this->GetStudentProgramId($course_id);
        $arr = [];

        // TODO: GRADE 11 HUMSS-101 1st Semester (Done)
        // TODO: Subject -> SChedule
 
        $first_sem = "First";

        $query = $this->con->prepare("SELECT 

            t1.student_id, t1.course_id, t2.school_year_id, t2.period

            FROM enrollment as t1

            INNER JOIN school_year as t2 ON t1.school_year_id = t2.school_year_id

            WHERE t1.student_id = :student_id

            AND t1.enrollment_status=:enrollment_status
            AND t2.period =:period
            -- AND c.course_id =:course_id
            ");

        $query->bindValue("student_id", $student_id); 
        $query->bindValue("period", $SEMESTER); 
        $query->bindValue("enrollment_status", "enrolled"); 
        // $query->bindValue("course_id", 20); 
        $query->execute(); 

        $enrollment_student_course_id = null;

        if($query->rowCount() > 0){

            // echo "wee";
            $result = $query->fetch(PDO::FETCH_ASSOC);
            $enrollment_student_course_id = $result['course_id'];

            // echo $enrollment_student_course_id;
        }

        // Enrollment student course_id
        $subject_query = $this->con->prepare("SELECT *

            FROM subject

            WHERE course_id=:course_id
            AND semester=:semester
            AND program_id=:program_id
            AND course_level=:course_level
            ");

            // $enrollment_student_course_id;
            // echo "<br>";

            // echo $enrollment_student_course_id;
        $subject_query->bindValue("course_id", $enrollment_student_course_id); 
        $subject_query->bindValue("semester", $SEMESTER); 
        $subject_query->bindValue("program_id", $program_id); 
        $subject_query->bindValue("course_level", $GRADE_LEVEL); 
        $subject_query->execute();

        if($subject_query->rowCount() > 0){

            // echo "wq";
            $row_sub = $subject_query->fetchAll(PDO::FETCH_ASSOC);
            // print_r($row_sub);
            return $row_sub;
        }
        else{
            // echo "no data";
        }
        return null;

    }

    public function GetSHSTransfereeEnrolledSubjectSemesterv2($username,
        $student_id, $GRADE_LEVEL, $SEMESTER){

        $query = $this->con->prepare("SELECT 

            t1.enrollment_id,
            t1.student_id, t1.course_id, t2.school_year_id, t2.period 
            FROM enrollment as t1

            INNER JOIN school_year as t2 ON t1.school_year_id = t2.school_year_id
            INNER JOIN course as t3 ON t3.course_id = t1.course_id

            WHERE t1.student_id = :student_id

            AND t1.enrollment_status=:enrollment_status
            AND t2.period =:period
            AND t3.course_level =:course_level
            --  Get the latest
            ORDER BY t1.enrollment_id DESC
            LIMIT 1

            -- AND c.course_id =:course_id
            ");

        $query->bindValue("student_id", $student_id); 
        $query->bindValue("period", $SEMESTER); 
        $query->bindValue("enrollment_status", "enrolled"); 
        $query->bindValue("course_level", $GRADE_LEVEL); 
        $query->execute(); 

        if($query->rowCount() > 0){

            // echo "wee";

            $result = $query->fetch(PDO::FETCH_ASSOC);
            // $enrollment_student_course_id = $result['course_id'];
            $enrollment_id = $result['enrollment_id'];


            $get_student_load = $this->con->prepare("SELECT 
            
                t2.*

                FROM student_subject as t1
                
                INNER JOIN subject as t2 ON t2.subject_id = t1.subject_id

                WHERE t1.enrollment_id=:enrollment_id
                AND t1.student_id=:student_id
                AND t1.is_transferee=:is_transferee
                ");

            $get_student_load->bindValue("enrollment_id", $enrollment_id); 
            $get_student_load->bindValue("student_id", $student_id); 
            $get_student_load->bindValue("is_transferee", "no"); 
            
            $get_student_load->execute(); 

            if($get_student_load->rowCount() > 0){

                $result = $get_student_load->fetchAll(PDO::FETCH_ASSOC);
                // print_r($result);
                return $result;
            }

            echo $enrollment_id;
        }

        return null;
    }



    public function GetStudentSectionGradeElevenSchoolYear($username,
        $student_id, $GRADE_ELEVEN, $SEMESTER){

        $course_id = $this->GetStudentCourseId($username);
        $program_id = $this->GetStudentProgramId($course_id);
        $arr = [];
 
        $first_sem = "First";

        $query = $this->con->prepare("SELECT 

            e.student_id, e.enrollment_id,
            e.enrollment_form_id,
             e.course_id, sy.school_year_id, 
            sy.period, sy.term

            FROM enrollment e

            INNER JOIN school_year sy ON e.school_year_id = sy.school_year_id
            INNER JOIN course c ON c.course_id = e.course_id

            WHERE e.student_id = :student_id
            AND e.enrollment_status=:enrollment_status
            AND sy.period =:first_sem
            -- AND c.course_id =:course_id
            ");

        $query->bindValue(":student_id", $student_id); 
        $query->bindValue(":first_sem", $SEMESTER); 
        $query->bindValue(":enrollment_status", "enrolled"); 
        // $query->bindValue("course_id", 20); 
        $query->execute(); 

        if($query->rowCount() > 0){
            $result = $query->fetch(PDO::FETCH_ASSOC);
            // print_r($result);
            return $result;
        }
        return null;
    }


    public function GetStudentSectionGradeLevelSemester($username,
        $student_id, $grade_level, $SEMESTER){

        $course_id = $this->GetStudentCourseId($username);
        $program_id = $this->GetStudentProgramId($course_id);
        $arr = [];

        // TODO: GRADE 11 HUMSS-101 1st Semester (Done)
        // TODO: Subject -> Schedule 
 
        $first_sem = "First";

        $query = $this->con->prepare("SELECT 

            e.student_id, e.course_id, sy.school_year_id, sy.period, sy.term,
            e.enrollment_id

            FROM enrollment e

            INNER JOIN school_year sy ON e.school_year_id = sy.school_year_id
            INNER JOIN course c ON c.course_id = e.course_id

            WHERE e.student_id = :student_id
            AND e.enrollment_status=:enrollment_status
            AND sy.period =:first_sem
            AND c.course_level =:course_level
            -- AND c.course_id =:course_id
            ");

        $query->bindValue("student_id", $student_id); 
        $query->bindValue("first_sem", $SEMESTER); 
        $query->bindValue("enrollment_status", "enrolled"); 
        $query->bindValue("course_level", $grade_level); 
        $query->execute(); 

        if($query->rowCount() > 0){
            $result = $query->fetch(PDO::FETCH_ASSOC);
            // print_r($result);
            return $result;
        }
        return null;
    }

    public function GetStudentSectionGradeTwelveSchoolYear($username, $student_id,
        $GRADE_TWELVE, $SEMESTER){

        $course_id = $this->GetStudentCourseId($username);
        $program_id = $this->GetStudentProgramId($course_id);
        $arr = [];

        // TODO: GRADE 11 HUMSS-101 1st Semester (Done)
        // TODO: Subject -> Schedule 
 
        $first_sem = "First";
        $enrollment_status = "enrolled";
        // $query = $this->con->prepare("SELECT 

        //     e.student_id, e.course_id, sy.school_year_id, sy.period, sy.term

        //     FROM enrollment e

        //     INNER JOIN school_year sy ON e.school_year_id = sy.school_year_id
        //     INNER JOIN course c ON c.course_id = e.course_id

        //     WHERE e.student_id = :student_id
        //     AND e.enrollment_status=:enrollment_status
        //     AND sy.period =:first_sem
        //     -- AND c.course_id =:course_id
        // ");

        $query = $this->con->prepare("SELECT e.enrollment_id,
            e.student_id, e.course_id, sy.school_year_id, sy.period, sy.term
            FROM enrollment e

            INNER JOIN school_year sy ON e.school_year_id = sy.school_year_id
            INNER JOIN course c ON c.course_id = e.course_id
            
            WHERE e.student_id = :student_id
            AND e.enrollment_status=:enrollment_status
            AND sy.period =:first_sem

            AND c.course_id IN (
                SELECT course_id FROM course WHERE course_level = $GRADE_TWELVE
            )
            LIMIT 1
        ");

        $query->bindValue("student_id", $student_id); 
        $query->bindValue("first_sem", $SEMESTER); 
        $query->bindValue("enrollment_status", $enrollment_status); 
        // $query->bindValue("course_id", 20); 
        $query->execute(); 

        if($query->rowCount() > 0){
            $result = $query->fetch(PDO::FETCH_ASSOC);
            // print_r($result);
            return $result;
        }
        return null;
    }

    public function GetStudentsStrandSubjectsPerSemester($username){

        $course_id = $this->GetStudentCourseId($username);
        $program_id = $this->GetStudentProgramId($course_id);
    
        // Get current year semester
        $year_obj = $this->GetActiveSchoolYearAndSemester();
        $current_active_semester = $year_obj['period'];

        $query = $this->con->prepare("SELECT 
                subject_title
            FROM subject 

            WHERE program_id=:program_id
            AND semester=:semester");
     
        $query->bindValue(":program_id", $program_id);
        $query->bindValue(":semester", $current_active_semester);
        $query->execute();
        // FETCH_COLUMN to avoid creating an array for pushing.
        return $query->fetchAll(PDO::FETCH_COLUMN);
    }

    public function GetStudentsStrandSubjectsPerLevelSemester($username){

        $course_id = $this->GetStudentCourseId($username);
        $student_course_level = $this->GetStudentCourseLevel($username);
        $program_id = $this->GetStudentProgramId($course_id);
    
        // Get current year semester
        $year_obj = $this->GetActiveSchoolYearAndSemester();
        $current_active_semester = $year_obj['period'];

        $query = $this->con->prepare("SELECT 
                subject_id
            FROM subject 

            WHERE program_id=:program_id
            AND semester=:semester
            AND course_level=:course_level
            AND course_id=:course_id
            ");
     
        $query->bindValue(":program_id", $program_id);
        $query->bindValue(":semester", $current_active_semester);
        $query->bindValue(":course_level", $student_course_level);
        $query->bindValue(":course_id", $course_id);
        $query->execute();
        // FETCH_COLUMN to avoid creating an array for pushing.
        return $query->fetchAll(PDO::FETCH_COLUMN);
    }

    public function GetStudentStrandSubjectFrom1stTo2ndSem($username, $student_id){

        $course_id = $this->GetStudentCourseId($username);
        $program_id = $this->GetStudentProgramId($course_id);

        // Get current year semester
        $year_obj = $this->GetActiveSchoolYearAndSemester();
        $GRADE_ELEVEN = 11;
        $current_active_semester = $year_obj['period'];

        // GET Grade 11 1st sem to 2nd sem
        $query = $this->con->prepare("SELECT subject_id

            FROM subject 

            WHERE program_id=:program_id
            AND course_level=:course_level
            AND course_id=:course_id
            ");
     
        $query->bindValue(":program_id", $program_id);
        $query->bindValue(":course_level", $GRADE_ELEVEN);
        $query->bindValue(":course_id", $course_id);
        $query->execute();
        // FETCH_COLUMN to avoid creating an array for pushing.
        $result = $query->fetchAll(PDO::FETCH_COLUMN);

        // print_r($result);

        $remarks = "Passed";
        $arr = [];

        foreach ($result as $key => $subject_id) {
            # code...
            $query_subject_grade = $this->con->prepare("SELECT subject_id
                FROM student_subject_grade 

                WHERE subject_id=:subject_id
                AND remarks=:remarks
                AND student_id=:student_id
                LIMIT 1");
            
            $query_subject_grade->bindValue(":subject_id", $subject_id);
            $query_subject_grade->bindValue(":remarks", $remarks);
            $query_subject_grade->bindValue(":student_id", $student_id);
            $query_subject_grade->execute();

            array_push($arr, $query_subject_grade->fetch(PDO::FETCH_COLUMN));
        }

        // print_r($arr);

        if(sizeof($arr) == sizeof($result)){
            if (empty(array_diff($arr, $result)) && empty(array_diff($result, $arr))) {
               return true;
            }
        }
        return false;
    }

    public function GetStudentSubjectListWithGrades($username){

        $course_id = $this->GetStudentCourseId($username);
        $program_id = $this->GetStudentProgramId($course_id);
        $get_student_id = $this->GetStudentId($username);

        // $query = $this->con->prepare("SELECT 
        //     subject_code, semester, subject_title, unit, course_level FROM subject
        //     WHERE program_id=:program_id
        //     ORDER BY course_level ASC
        //     ");
        $student_id = $this->GetStudentId($username);

        $query = $this->con->prepare("SELECT 
                t1.subject_id, 
                t1.subject_code, 
                t1.semester, t1.subject_title,
                t1.unit, t1.course_level,


                -- t2.time_from,
                -- t2.time_to,
                -- t2.subject_schedule_id,
                -- t2.schedule_day,
                -- t2.schedule_time,
                -- t2.room,
                -- t2.teacher_id,
                -- t2.section
                t2.subject_schedule_id,
                -- t3.schedule_time,
                t2.student_id,
                t2.subject_id AS student_enrolled_subject_id,
                t3.remarks,
                t3.comment,
                t3.average,
                t3.student_id AS subject_grade_student_id
                

            FROM subject as t1
            LEFT JOIN student_subject as t2 on t1.subject_id = t2.subject_id
            LEFT JOIN student_subject_grade as t3 on t1.subject_id = t3.subject_id
            
            WHERE program_id=:program_id
            AND t3.student_id=:student_id

            -- AND t2.student_id=:student_id
            -- WHERE course_level=:course_level
            -- AND semester=:semester
            -- AND program_id=:program_id
            ORDER BY t1.course_level ASC, t1.semester
            
            ");
     
        $query->bindValue(":program_id", $program_id);
        $query->bindValue(":student_id", $get_student_id);
        // $query->bindValue(":student_id", $student_id);
        $query->execute();

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }



    public function GetStudentRequiredSubjectsPerYearSemester($username){

        $course_id = $this->GetStudentCourseId($username);
        $student_course_level = $this->GetStudentCourseLevel($username);
        $program_id = $this->GetStudentProgramId($course_id);

        // $current_semester = $this->GetStudentCurrentSemester($username);

        $query = $this->con->prepare("SELECT 

            t1.subject_code, t1.semester, t1.subject_title, t1.unit, t1.course_level

            FROM subject as t1

            LEFT JOIN subject_schedule as t2 ON t1.subject_id = t2.subject_id
            WHERE program_id=:program_id
            AND semester=:semester
            AND course_level=:course_level
            ORDER BY course_level ASC");
     
        $query->bindValue(":program_id", $program_id);
        // $query->bindValue(":semester", $current_semester);
        $query->bindValue(":course_level", $student_course_level);
        $query->execute();

        return $query->fetchAll();
    }

    public function GetSHSNewStudentSubjectProgramBased($username) : array{

        $course_id = $this->GetStudentCourseId($username);

        $student_program_id = $this->GetStudentProgramId($course_id);

        $student_course_level = $this->GetStudentCourseLevel($username);

        // echo $student_program_id;
        // Not reliable, it mnust be in the school_year_id
        $student_current_semester = "First";

        $query = $this->con->prepare("SELECT 
                t1.subject_id, t1.subject_code, 
                t1.semester, t1.subject_title,
                t1.unit, t1.course_level

                -- t2.time_from,
                -- t2.time_to,
                -- t2.subject_schedule_id,
                -- t2.schedule_day,
                -- t2.schedule_time,
                -- t2.room,
                -- t2.teacher_id,
                -- t2.section

            FROM subject as t1
            -- Daehan will decided the number of students enrolled before scheduling.

            -- LEFT JOIN subject_schedule as t2 on t1.subject_id = t2.subject_id

                WHERE course_level=:course_level
                -- AND semester=:semester
                AND program_id=:program_id
                AND semester=:semester
            ");
        
        $query->bindValue(":course_level", $student_course_level);
        $query->bindValue(":semester", $student_current_semester);
        $query->bindValue(":program_id", $student_program_id);
        $query->execute();

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function GetRecommendedOldSHSStudentForUpcomingSemester($username) {

        // $course_id = $this->GetStudentCourseId($username);

        $arr = [];
        $year_semester_obj = $this->GetActiveSchoolYearAndSemester();

        // $student_year_id = $this->GetStudentCurrentYearId($username);
        // $student_course_level = $this->GetStudentCourseLevel($username);
        $studentObj = $this->GetStudentCourseLevelYearIdCourseId($username);

        $course_id = $studentObj['course_id'];
        $student_program_id = $this->GetStudentProgramId($course_id);

        $student_course_level = $studentObj['course_level'];

        $student_id = $studentObj['student_id'];

        // Get student
        $student_year_id = 0;

        $student_year_obj = $this->GetSchoolYearOfStudent($student_year_id);

        if($student_year_obj != null){
            $student_year_period = $student_year_obj['period'];
            $student_year_year = $student_year_obj['term'];
        }
      
        $current_school_year_id = $year_semester_obj['school_year_id'];

        $year = $year_semester_obj['term'];
        $semester = $year_semester_obj['period'];
        $current_active_semester = $year_semester_obj['period'];
    
        $query_enrollment = $this->con->prepare("SELECT school_year_id
                FROM enrollment 
                WHERE student_id=:student_id
                AND enrollment_status=:enrollment_status
                ORDER BY enrollment_id DESC
                LIMIT 1");

        $query_enrollment->bindValue(":student_id", $student_id);
        $query_enrollment->bindValue(":enrollment_status", "enrolled");
        $query_enrollment->execute();

        $student_enrollment_previous_school_year_id = null;

        if($query_enrollment->rowCount() > 0){
            $student_enrollment_previous_school_year_id = $query_enrollment->fetchColumn();
        }

        if($student_enrollment_previous_school_year_id != $current_school_year_id){

            $query = $this->con->prepare("SELECT 
                t1.subject_id, t1.subject_code, 
                t1.semester, t1.subject_title,
                t1.unit, t1.course_level

                FROM subject as t1
                WHERE course_level=:course_level
                AND semester=:semester
                AND program_id=:program_id
                AND course_id=:course_id

            ");

            $query->bindValue(":course_level", $student_course_level);
            $query->bindValue(":semester", $current_active_semester);
            $query->bindValue(":program_id", $student_program_id);
            $query->bindValue(":course_id", $course_id);
            $query->execute();
            
            // array_push($arr, $query->fetchAll());
            if($query->rowCount() > 0){

                $results = $query->fetchAll(PDO::FETCH_ASSOC);
                return $results;
            }
        }
        
        return null;
    }
    public function 
        GetRecommendedOldSHSStudentForUpcomingSemesterProgramBased($username){


        $studentObj = $this->GetStudentCourseLevelYearIdCourseId($username);
        $year_semester_obj = $this->GetActiveSchoolYearAndSemester();

        $course_id = $studentObj['course_id'];
        $student_program_id = $this->GetStudentProgramId($course_id);
        
        $student_course_level = $studentObj['course_level'];

        $current_active_semester = $year_semester_obj['period'];

        $query_subject_program = $this->con->prepare("SELECT *
                FROM subject_program 
                WHERE program_id=:program_id
                AND course_level=:course_level
                AND semester=:semester
                ");

        $query_subject_program->bindValue(":program_id", $student_program_id);
        $query_subject_program->bindValue(":course_level", $student_course_level);
        $query_subject_program->bindValue(":semester", $current_active_semester);
        $query_subject_program->execute();

        if($query_subject_program->rowCount() > 0){
            return $query_subject_program->fetchAll(PDO::FETCH_ASSOC);
        }

        return null;
    }
    // lili
    public function GetIrregularSHSSubjectToTakeForUpcoming($username) {
        
        $student_id = $this->GetStudentId($username);

        // $course_id = $this->GetStudentCourseId($username);
        // $program_id = $this->GetStudentProgramId($course_id);
        
        $year_obj = $this->GetActiveSchoolYearAndSemester();
        $current_active_semester = $year_obj['period'];

        $query = $this->con->prepare("SELECT subject_id
                FROM student_subject_grade 
                WHERE remarks=:remarks
                AND student_id=:student_id
                ");
        $query->bindValue(":remarks", "Failed");
        $query->bindValue(":student_id", $student_id);
        $query->execute();

        // FETCH_COLUMN retrives the interger, to avoid pushing into an array.
        # 1. Failed subject, 2. Not yet taken

        $failedRemarkSubjects = $query->fetchAll(PDO::FETCH_COLUMN);

        $arr = [];
        
        foreach ($failedRemarkSubjects as $key => $failed_subject_id) {
            # code...
            $query_sub = $this->con->prepare("SELECT *
                FROM subject 
                WHERE subject_id=:subject_id
                AND semester=:semester
                LIMIT 1");
            
            $query_sub->bindValue(":subject_id", $failed_subject_id);
            $query_sub->bindValue(":semester", $current_active_semester);
            $query_sub->execute();

            $query_sub = $query_sub->fetch(PDO::FETCH_ASSOC);
            array_push($arr, $query_sub);
        }

        return $arr;
    }

    public function GetLatestSchoolYearAndSemester(){

        $arr = [];

        $query = $this->con->prepare("SELECT term, period,
            school_year_id FROM school_year
            WHERE statuses=:statuses	
            LIMIT 1");
     
        $query->bindValue(":statuses", "Active");
        $query->execute();
        $result = $query->fetch(PDO::FETCH_ASSOC);

        array_push($arr, $result['term']);
        array_push($arr, $result['period']);
        array_push($arr, $result['school_year_id']);
        return $arr;
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
    public function GetSchoolYearOfStudent($school_year_id){

        $query = $this->con->prepare("SELECT school_year_id, term, period
            FROM school_year

            WHERE school_year_id=:school_year_id
            -- ORDER BY school_year_id DESC
            LIMIT 1");

        $query->bindValue(":school_year_id", $school_year_id);
        $query->execute();

        if($query->rowCount() > 0){
            return $query->fetch(PDO::FETCH_ASSOC);
        }
        return null;
    }

    public function CheckIfStudentIsNewEnrollee($username){
        // 1 = True
        $new_enrollee = 1;
        $query = $this->con->prepare("SELECT * FROM student
            WHERE username=:username
            AND new_enrollee=:new_enrollee
            ");

        $query->bindValue(":username", $username);
        $query->bindValue(":new_enrollee", $new_enrollee);
        $query->execute();

        return $query->rowCount() > 0;
    }

    public function CheckStudentYearIdAndCurrentYearId($username){
        
        // All school_year_id in the student table is gone
        // It was placed on the enrollment table

        $school_year_obj = $this->GetActiveSchoolYearAndSemester();
        $school_year_id = $school_year_obj['school_year_id'];

        $student_year_id = $this->GetStudentCurrentYearId($username);

        if($student_year_id == $school_year_id)
            return true;
        
        return false;
    }

    public function GetSubjectForCurrentGradeAndSemester($course_level,
        $semester, $program_id){

        $query = $this->con->prepare("SELECT * FROM subject
            WHERE semester=:semester
            AND course_level=:course_level
            AND program_id=:program_id
            ");
     
        $query->bindValue(":semester", $semester);
        $query->bindValue(":course_level", $course_level);
        $query->bindValue(":program_id", $program_id);
        $query->execute();

        return $query->fetchAll(PDO::FETCH_ASSOC);

    }

    public function GetSHSStudentFailedSubject($username){

        $failed_remark = "Failed";
        $student_id = $this->GetStudentId($username);

        // echo $student_id;
        $query = $this->con->prepare("SELECT subject_id, grade_creation 

            FROM student_subject_grade
            -- WHERE semester=:semester
            WHERE student_id=:student_id
            AND remarks=:remarks

            ");
        // $query->bindValue(":semester", "");
        $query->bindValue(":student_id", $student_id);
        $query->bindValue(":remarks", $failed_remark);
        $query->execute();

        $results =  $query->fetchAll(PDO::FETCH_ASSOC);
        return $results;
    }

    // Bug
    public function CheckPreRequisiteSubject($username, $subject_id) : bool{
        
        // Transferee.
        // We should be based on the subject load.

        $school_year_obj = $this->GetActiveSchoolYearAndSemester();

        $current_active_semester = $school_year_obj['period'];

        $student_obj = $this->GetStudentCourseLevelYearIdCourseId($username);
        // oko
        $student_course_id = $student_obj['course_id'];

        $student_course_level = $student_obj['course_level'];

        $student_program_id = $this->GetStudentProgramId($student_course_id);

        $get_subject = $this->con->prepare("SELECT subject_program_id FROM subject
            WHERE subject_id=:subject_id
            AND course_level=:course_level
            LIMIT 1
            ");

        $get_subject->bindValue(":subject_id", $subject_id);
        $get_subject->bindValue(":course_level", $student_course_level);
        $get_subject->execute();

        if($get_subject->rowCount() > 0){
            $subject_program_id_from_subject = $get_subject->fetchColumn();


            $query = $this->con->prepare("SELECT pre_subject_id FROM subject_program
                WHERE program_id=:program_id
                AND course_level=:course_level
                AND subject_program_id=:subject_program_id
                AND semester=:semester

                -- LIMIT 1
                ");

            $query->bindValue(":program_id", $student_program_id);
            $query->bindValue(":course_level", $student_course_level);
            $query->bindValue(":subject_program_id", $subject_program_id_from_subject);
            $query->bindValue(":semester", $current_active_semester);

            $query->execute();

            
            // Subject Id -> subject_program_id -> based to subject_program
            // If Regular
            // If Grade 11 1st sem then 11 1st sem only the subjects needed to be selected
            if($query->rowCount() > 0){

                // echo "within the semester scope";
                return true;

            }else{
                // echo "not within the semester scope";
                // return false;
            }
        }
       
        return false;
    }

    function generateUniqueUsername($lastname, $con) {

        // Keep generating usernames until a unique one is found
        while (true) {
            // Generate a random 5-digit number
            $random_number = sprintf("%05d", rand(0, 99999));

            // Combine the last name and random number to create the username
            # sirios.12345@dcbt.ph;
            # emma.12345@dcbt.ph
            $username = strtolower($lastname) . '.' . $random_number . '@dcbt.ph';

            echo $username;


            $existing_user = $this->con->prepare("SELECT COUNT(*) FROM student 
                WHERE username = :username");
            
            $existing_user->bindParam(':username', $username);
            $existing_user->execute();
            $count = $existing_user->fetchColumn();

            if ($count == 0) {
                // If the username is unique, return it
                // return $username;
            }
        }
    }
}

?>