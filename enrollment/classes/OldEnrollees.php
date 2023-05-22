<?php

    class OldEnrollees{

    private $con, $userLoggedIn, $sqlData, $studentEnroll;

    public function __construct($con, $studentEnroll){
        $this->con = $con;
        $this->studentEnroll = $studentEnroll;
    }

    public function GetOldStudentStatus($username){
        $query = $this->con->prepare("SELECT student_status FROM student
            WHERE username=:username");
     
        $query->bindValue(":username", $username);
        $query->execute();

        return $query->fetchColumn();
    }
    public function DoesStudentNewEnrollee($username){
        $query = $this->con->prepare("SELECT new_enrollee FROM student
            WHERE username=:username");
     
        $query->bindValue(":username", $username);
        $query->execute();

        return $query->rowCount() > 0;
    }
    // Should: All functions SHS is seperated for the Tertiary.
    public function UpdateSHSStudentStatus($username){

        $studentObj = $this->studentEnroll
            ->GetStudentCourseLevelYearIdCourseId($username);

        $current_school_year_id = $this->studentEnroll->GetCurrentYearId();

        // $student_year_id = $studentObj['school_year_id'];
        // $student_year_id = 0;
        $student_id = $studentObj['student_id'];

        //
        $enrollment_status = "tentative";
        $sql_enrollment = $this->con->prepare("SELECT school_year_id FROM 
            enrollment
            WHERE student_id=:student_id
            AND enrollment_status=:enrollment_status
            LIMIT 1");

        $sql_enrollment->bindValue(":student_id", $student_id);
        $sql_enrollment->bindValue(":enrollment_status", $enrollment_status);
        $sql_enrollment->execute();
        $student_applying_school_year_id = null;

        if($sql_enrollment->rowCount() > 0){
            $student_applying_school_year_id = $sql_enrollment->fetchColumn();   
        }

        $student_course_level = $studentObj['course_level'];

        $student_year_period = "";

        if($student_applying_school_year_id != null){
            $student_year_obj = $this->studentEnroll
            ->GetSchoolYearOfStudent($student_applying_school_year_id);

            $student_year_period = $student_year_obj['period'];
        }

        $normal_status_for_shs = "Regular";
        $current_status = "Waiting";
        // Update possilbe changes of course_id because of moving to grade 11
        // course_level, semester, school_year_id

        // If Grade 11 second sem, course_level should be 12. not stayed in 11
        $GRADE_ELEVEN = 11;
        $GRADE_TWELVE = 12;
 

       if($current_school_year_id == $student_applying_school_year_id){

            if($student_course_level == $GRADE_ELEVEN 
                && $student_year_period == "First"){
                
                // Check if new Enrollee
                if($this->studentEnroll->CheckIfStudentIsNewEnrollee($username) > 0){

                    // Update to Old
                    $newToOldSuccess = $this->UpdateSHSStudentNewToOld($student_id);

                    if($newToOldSuccess == true){
                        // echo "newToOldSuccess true";
                    }else{
                        // echo "newToOldSuccess false";
                    }

                    $wasSuccess = $this->EnrolledStudentInTheEnrollment($current_school_year_id, $student_id);
                    return $wasSuccess;
                    
                }
                
            }
            // It should be on the Marking as Grade 12 Passed to be able to move up to Grade 12.
            # The system should sensed if they passed their Grade 11 1st and 2nd sem
            # so in their apply button, they can see if they are eligible for the Grade 12 1st sem
            # Registrar based on the form
            # if they wanted to apply for the next semester which is Grade 12 1st sem standing

            else if($student_course_level == $GRADE_ELEVEN 
                && $student_year_period == "Second"){

                $wasSuccess = $this->EnrolledStudentInTheEnrollment($current_school_year_id, $student_id);

                return $wasSuccess;
            }
            else if($student_course_level == $GRADE_TWELVE 
                && $student_year_period == "First"){
                $wasSuccess = $this->EnrolledStudentInTheEnrollment($current_school_year_id, $student_id);
                return $wasSuccess;
            }
            // Graduate Next.
            else if($student_course_level == $GRADE_TWELVE 
                && $student_year_period == "Second"){
                
                $wasSuccess = $this->EnrolledStudentInTheEnrollment($current_school_year_id, $student_id);
                return $wasSuccess;
            }
       }
    }

    public function UpdateTertiaryStudentStatus($username){

        $studentObj = $this->studentEnroll
            ->GetStudentCourseLevelYearIdCourseId($username);

        $school_year_obj = $this->studentEnroll->GetActiveSchoolYearAndSemester();
        $current_school_year_id = $school_year_obj['school_year_id'];
        $current_school_year_period = $school_year_obj['period'];

        // $student_year_id = $studentObj['school_year_id'];
        // $student_year_id = 0;
        $student_id = $studentObj['student_id'];

        //
        $enrollment_status = "tentative";
        
        // $sql_enrollment = $this->con->prepare("SELECT t2.period FROM 
        //     enrollment_tertiary as t1

        //     INNER JOIN school_year as t2 ON t1.school_year_id = t2.school_year_id
        //     WHERE student_id=:student_id
        //     AND enrollment_status=:enrollment_status
        //     LIMIT 1");

        // $sql_enrollment->bindValue(":student_id", $student_id);
        // $sql_enrollment->bindValue(":enrollment_status", $enrollment_status);
        // $sql_enrollment->execute();

        // $student_applying_school_year_id = null;
        $student_applying_school_year_period = "";
     
        $student_course_level = $studentObj['course_level'];

        $normal_status_for_shs = "Regular";
        $current_status = "Waiting";

        // Update possilbe changes of course_id because of moving to grade 11
        // course_level, semester, school_year_id

        // If Grade 11 second sem, course_level should be 12. not stayed in 11
        $FIRST_YEAR = 1;
        $GRADE_TWELVE = 12;
 
        if($student_course_level == $FIRST_YEAR 
            && $current_school_year_period == "First"){
            
            // Check if new Enrollee
            if($this->studentEnroll->CheckIfStudentIsNewEnrollee($username) > 0){
                // Update to Old
                $newToOldSuccess = $this->UpdateSHSStudentNewToOld($student_id);

                // if($newToOldSuccess == true){
                //     echo "newToOldSuccess true";
                // }else{
                //     echo "newToOldSuccess false";
                // }
                $wasSuccess = $this->EnrolledStudentInTheEnrollment($current_school_year_id, $student_id);
                return $wasSuccess;
            }
            
        }
        # Regular Old enrollees
        else{
            $wasSuccess = $this->EnrolledStudentInTheEnrollment($current_school_year_id, $student_id);

        }

            // It should be on the Marking as Grade 12 Passed to be able to move up to Grade 12.
            # The system should sensed if they passed their Grade 11 1st and 2nd sem
            # so in their apply button, they can see if they are eligible for the Grade 12 1st sem
            # Registrar based on the form
            # if they wanted to apply for the next semester which is Grade 12 1st sem standing

            // else if($student_course_level == $GRADE_ELEVEN 
            //     && $student_year_period == "Second"){

            //     $wasSuccess = $this->EnrolledStudentInTheEnrollment($current_school_year_id, $student_id);

            //     return $wasSuccess;
            // }
            // else if($student_course_level == $GRADE_TWELVE 
            //     && $student_year_period == "First"){
            //     $wasSuccess = $this->EnrolledStudentInTheEnrollment($current_school_year_id, $student_id);
            //     return $wasSuccess;
            // }
            // // Graduate Next.
            // else if($student_course_level == $GRADE_TWELVE 
            //     && $student_year_period == "Second"){
                
            //     $wasSuccess = $this->EnrolledStudentInTheEnrollment($current_school_year_id, $student_id);
            //     return $wasSuccess;
            // }

    }

    public function UpdateSHSStudentNewToOld($student_id){

        $new_enrollee = 0;
        $current_status = 1;
        $new_to_old = $this->con->prepare("UPDATE student
            SET new_enrollee=:new_enrollee
            
            WHERE student_id=:student_id
            AND new_enrollee=:current_status

            ");

        $new_to_old->bindValue(":new_enrollee", $new_enrollee);
        $new_to_old->bindValue(":student_id", $student_id);
        $new_to_old->bindValue(":current_status", $current_status);
        return $new_to_old->execute(); 
    }
    public function IncrementSection($username){
        
    }
    public function StudentMoveUpToGrade12($username){

        $GRADE_ELEVEN = 11;

        $query = $this->con->prepare("SELECT student_id

            FROM student
            WHERE username=:username
            AND course_level=:course_level
            ");
        $query->bindValue(":username", $username);
        $query->bindValue(":course_level", $GRADE_ELEVEN);
        $query->execute();

        if($query->rowCount() > 0){
            $GRADE_TWELVE = 12;
            $moveUp_Update = $this->con->prepare("UPDATE student
                SET course_level=:course_level
                WHERE username=:username
                ");
        
            $moveUp_Update->bindValue(":course_level", $GRADE_TWELVE);
            $moveUp_Update->bindValue(":username", $username);

            return $moveUp_Update->execute();
        }
    }

    public function EnrolledStudentInTheEnrollmentTertiary($current_school_year_id, $student_id){
        $enrollment_status = "enrolled";
        $update_tentative = $this->con->prepare("UPDATE enrollment_tertiary
            SET enrollment_status=:enrollment_status
            
            WHERE student_id=:student_id
            AND school_year_id=:school_year_id");

        $update_tentative->bindValue(":enrollment_status", $enrollment_status);
        $update_tentative->bindValue(":student_id", $student_id);
        $update_tentative->bindValue(":school_year_id", $current_school_year_id);
        return $update_tentative->execute(); 
      
    }

    public function EnrolledStudentInTheEnrollment($current_school_year_id, $student_id){
        $enrollment_status = "enrolled";
        $update_tentative = $this->con->prepare("UPDATE enrollment
            SET enrollment_status=:enrollment_status
            
            WHERE student_id=:student_id
            AND school_year_id=:school_year_id");

        $update_tentative->bindValue(":enrollment_status", $enrollment_status);
        $update_tentative->bindValue(":student_id", $student_id);
        $update_tentative->bindValue(":school_year_id", $current_school_year_id);
        return $update_tentative->execute(); 
      
    }

    public function EnrolledStudentInTheEnrollmentv2(
            $current_school_year_id, $student_id, $unique_enrollment_form_id){

        $enrollment_status = "enrolled";
        $update_tentative = $this->con->prepare("UPDATE enrollment
            SET enrollment_status=:enrollment_status,
                enrollment_form_id=:enrollment_form_id
            
            WHERE student_id=:student_id
            AND school_year_id=:school_year_id");

        $update_tentative->bindValue(":enrollment_status", $enrollment_status);
        $update_tentative->bindValue(":enrollment_form_id", $unique_enrollment_form_id);
        $update_tentative->bindValue(":student_id", $student_id);
        $update_tentative->bindValue(":school_year_id", $current_school_year_id);
        return $update_tentative->execute(); 
    }

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

    public function GetTertiaryStudentEnrolledSubjects($username, $subject_tertiary_id){

        $student_id = $this->studentEnroll->GetStudentId($username);

        $query = $this->con->prepare("SELECT subject_tertiary_id

            FROM student_subject_tertiary
            WHERE student_id=:student_id
            AND subject_tertiary_id=:subject_tertiary_id
            ");

        $query->bindValue(":student_id", $student_id);
        $query->bindValue(":subject_tertiary_id", $subject_tertiary_id);
        $query->execute();

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    // If teacher or registrar marks as failed in one of the subject
    public function UpdateIrregularSHS($username){
    
        $status = "Irregular";

        $update_query = $this->con->prepare("UPDATE student
            SET student_status=:student_status

            WHERE username=:username");
            
        $update_query->bindValue(":username", $username);
        $update_query->bindValue(":student_status", $status);
        return $update_query->execute();

    }

    public function DoesStudentFinishedAllSubjectLoads($username){

        // List of all Strand(STEM) Subject
        // List of all Student_Subject_Grade

        $doesFinish = false;
        $studentStrandSubjects = $this->studentEnroll->GetStudentsStrandSubjects($username);
        $studentsStrandSubjectPassed = $this->GetStudentSubjectGradePassed($username);
       
        $first_arr = [];
        $second_arr = [];


        // echo sizeof($studentsStrandSubjectPassed);

        foreach ($studentStrandSubjects as $key => $tb1) {
            $strand_subject_id = $tb1['subject_id'];
            array_push($first_arr, $strand_subject_id);
        }
        foreach ($studentsStrandSubjectPassed as $key => $passed_subject_id) {
            // echo $passed_subject_id['subject_id'];
            array_push($second_arr, $passed_subject_id['subject_id']);
        }

        if(!empty($first_arr)){
            // sort($first_arr);
            // echo "first arr is not empty";
            if(!empty($second_arr)){
                // sort($second_arr);
                // echo "second arr is not empty";

                // print_r($first_arr);
                // echo "<br>";
                // print_r($second_arr);
                // echo "<br>";

                if(sizeof($second_arr) == sizeof($first_arr)){
                    if (empty(array_diff($first_arr, $second_arr)) 
                        && empty(array_diff($second_arr, $first_arr))) {
                        $doesFinish = true;
                    }
                }
            }
        }

        // $array1 = array(1, 2, 4);
        // $array2 = array(1, 2, 4);

        // $difference = array_diff($array1, $array2);
        // print_r($difference);

        if(empty($difference)){
            // echo "emty 1";
        }

        return $doesFinish;
        
    }
    public function GetStudentSubjectGradePassed($username){

        $student_id = $this->studentEnroll->GetStudentId($username);
        $passedRemarks = "Passed";

        $query = $this->con->prepare("SELECT subject_id
            FROM student_subject_grade
            WHERE student_id=:student_id
            AND remarks=:remarks
        ");

     
        $query->bindValue(":student_id", $student_id);
        $query->bindValue(":remarks", $passedRemarks);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }
    public function CheckIfStudentEnrolledWithTheSameSchoolYearId($student_id){

        $current_school_year_id = $this->studentEnroll->GetCurrentYearId();


        $sql = $this->con->prepare("SELECT COUNT(*) FROM enrollment
            WHERE student_id = :student_id 
            AND school_year_id = :school_year_id");

        $sql->bindParam(':student_id', $student_id);
        $sql->bindParam(':school_year_id', $current_school_year_id);

        $sql->execute();
        $count = $sql->fetchColumn();
        if($count > 0){
            return true;
        }
        return false;
    }

    public function CheckStudentUpdatedSection($username){

        $student_course_id = $this->studentEnroll->GetStudentCourseId($username);
        $not_active = "no";

        $check_course = $this->con->prepare("SELECT * FROM course
            WHERE course_id=:course_id
            AND active=:active
            LIMIT 1");

        $check_course->bindValue(":course_id", $student_course_id);
        $check_course->bindValue(":active", $not_active);

        $check_course->execute();
        if($check_course->rowCount() > 0){
            // Student Course id did not update on his profile.
            // echo "Qweqwe";
            return true;
        }

        return false;
    }

    public function UpdateStudentSectionDropdown($username, $student_id){

        $url = "http://localhost/elms/admin/enrollees/subject_insertion.php?username=jana1&id=16";

        $student_course_id = $this->studentEnroll->GetStudentCourseId($username);

        if(isset($_POST['update_section']) && isset($_POST['course_id'])){
             
            $course_id = $_POST['course_id'];

            // echo $course_id;
            $update = $this->con->prepare("UPDATE student
                SET course_id=:course_id
                WHERE student_id=:student_id");

            $update->bindValue(":course_id", $course_id);
            $update->bindValue(":student_id", $student_id);

            if($update->execute()){
                echo "success section update";
                echo "<br>";
                // Update the course id in the enrollment
                $enrollment_status = "tentative";

                $update_course_id = $this->con->prepare("UPDATE enrollment
                    SET course_id=:course_id
                    WHERE student_id=:student_id
                    AND enrollment_status=:enrollment_status
                    ");

                $update_course_id->bindValue(":course_id", $course_id);
                $update_course_id->bindValue(":student_id", $student_id);
                $update_course_id->bindValue(":enrollment_status", $enrollment_status);
                $update_course_id->execute();

                if($update_course_id->execute()){
                    echo "student enrollment course id update";
                    echo "<br>";
                }

            }

            // header("Location: http://localhost/elms/admin/enrollees/subject_insertion.php?username=jana1&id=16");
        }


        $get_course = $this->con->prepare("SELECT program_section FROM course
            WHERE course_id=:student_course_id");

        $get_course->bindValue(":student_course_id", $student_course_id);
        $get_course->execute();

        if($get_course->rowCount() > 0){

            $program_section = $get_course->fetchColumn();

            $school_year_obj = $this->studentEnroll->GetActiveSchoolYearAndSemester();

            $current_school_year_term = $school_year_obj['term'];

            $moveUpProgramSection = str_replace("11", "12", $program_section);
            // echo $moveUpProgramSection;

            $course_level = 12;
            $active = "yes";

            $query = $this->con->prepare("SELECT * FROM course
                WHERE course_level=:course_level
                AND program_section=:program_section
                AND active=:active
                AND school_year_term=:school_year_term
            ");

            $query->bindValue(":course_level", $course_level);
            $query->bindValue(":program_section", $moveUpProgramSection);
            $query->bindValue(":active", $active);
            $query->bindValue(":school_year_term", $current_school_year_term);
            
            $query->execute();

            $html = "<div class='form-group'>
                        <form method='POST'>
                            <select class='form-control' name='course_id'>";

            while($row = $query->fetch(PDO::FETCH_ASSOC)){
                $html .= "
                    <option value='".$row['course_id']."'>".$row['program_section']."</option>
                ";
            }
            $html .= "
                    </select>
                        <button name='update_section' class='btn btn-sm btn-primary'>
                            Update
                        </button>
                </form>
            </div>";

            return $html;
        }
        return null;
    }

    public function CheckIfDroppedStudentEnrolledInCurrentSY($student_id, $school_year_id){

        $checkIfInTheEnrollmentDb = $this->con->prepare("SELECT enrollment_id FROM enrollment
                            WHERE student_id = :student_id
                            AND school_year_id = :school_year_id
                            LIMIT 1
                            ");   

        $checkIfInTheEnrollmentDb->bindValue(":student_id", $student_id);
        $checkIfInTheEnrollmentDb->bindValue(":school_year_id", $school_year_id);
        $checkIfInTheEnrollmentDb->execute(); 

        return $checkIfInTheEnrollmentDb->rowCount();
    }

    public function CheckIfStudentAlreadyApplied($username){
        
        $student_obj = $this->studentEnroll->GetStudentCourseLevelYearIdCourseId($username);

        $student_id = $student_obj['student_id'];
        $current_course_id = $student_obj['course_id'];
        $enrollment_status = "tentative";
        $is_new_enrollee = 0;

        $sql = $this->con->prepare("SELECT enrollment_id FROM enrollment

            WHERE student_id=:student_id
            AND course_id=:course_id
            AND enrollment_status=:enrollment_status
            AND is_new_enrollee=:is_new_enrollee
            LIMIT 1");

        $sql->bindValue(":student_id", $student_id);
        $sql->bindValue(":course_id", $current_course_id);
        $sql->bindValue(":enrollment_status", $enrollment_status);
        $sql->bindValue(":is_new_enrollee", $is_new_enrollee);
        $sql->execute();

        return $sql->rowCount() > 0;
    }   

    public function CheckIfStudentApplicableToApplyNextSemester($username,
        $current_school_year_id){


        $student_obj = $this->studentEnroll->GetStudentCourseLevelYearIdCourseId($username);

        $student_id = $student_obj['student_id'];
        $course_id = $student_obj['course_id'];
        $enrollment_status = "enrolled";

        // School Year Creation should be consistent.
        // Creating Sections.

        $sql = $this->con->prepare("SELECT school_year_id FROM enrollment

            WHERE student_id=:student_id
            AND course_id=:course_id
            AND enrollment_status=:enrollment_status
            ORDER BY school_year_id DESC
            LIMIT 1");

        $sql->bindValue(":student_id", $student_id);
        $sql->bindValue(":course_id", $course_id);
        $sql->bindValue(":enrollment_status", $enrollment_status);
        $sql->execute();

        $student_subject_arr = [];
        $student_subject_grade_arr = [];

        $isOkay = false;
        $array_error = [];
        if($sql->rowCount() > 0){

            // echo "qwe";

            $school_year_id = $sql->fetchColumn();

            if($school_year_id == $current_school_year_id){
                array_push($array_error, "Same school year id");
            }

            // Get all my 1st Sem Subjects 
            $myFirstSemSubject = $this->con->prepare("SELECT 

                ss.subject_id, ss.student_subject_id,
                ssg.student_subject_id AS marked_student_subject_id

                FROM student_subject AS ss

                LEFT JOIN student_subject_grade AS ssg ON ss.student_subject_id = ssg.student_subject_id
                AND ssg.remarks = 'Passed'

                WHERE ss.student_id=:student_id
                AND ss.school_year_id=:school_year_id
            ");

            $myFirstSemSubject->bindValue(":student_id", $student_id);
            $myFirstSemSubject->bindValue(":school_year_id", $school_year_id);
            $myFirstSemSubject->execute();
            $myFirstSemSubjects = $myFirstSemSubject->fetchAll(PDO::FETCH_ASSOC);

            $student_subject_arr = array_column($myFirstSemSubjects, 'student_subject_id');

            // $student_subject_grade_arr = array_column(array_filter($myFirstSemSubjects, function($myFirstSemSubject){
            //     return !empty($myFirstSemSubject['marked_student_subject_id']);
            // }), 'marked_student_subject_id');

            $student_subject_grade_arr = [];

            foreach ($myFirstSemSubjects as $subject) {
                if (!empty($subject['marked_student_subject_id'])) {
                    $student_subject_grade_arr[] = $subject['marked_student_subject_id'];
                }
            }

            // print_r($student_subject_grade_arr);
            // echo "<br>";
            // echo "<br>";
            // print_r($student_subject_arr);

            if(count($student_subject_arr) == count($student_subject_grade_arr)
                && array_values($student_subject_arr) == array_values($student_subject_grade_arr)
            ){
                // echo "matched";
                $isOkay = true;

            }else{

                echo "Your Subject is not graded yet but changed the semester.";
                $isOkay = false;
                array_push($array_error, "not matched");
            }

            # Check if your 1st sem subjects were all remark as Passed
            // Get all my 1st Sem Subjects 
            $myFirstSemSubject = $this->con->prepare("SELECT subject_id, student_subject_id  FROM student_subject
            
                WHERE student_id=:student_id
                AND school_year_id=:school_year_id
                ");

            $myFirstSemSubject->bindValue(":student_id", $student_id);
            $myFirstSemSubject->bindValue(":school_year_id", $school_year_id);
            $myFirstSemSubject->execute();

            if($myFirstSemSubject->rowCount() > 0){
                while($row = $myFirstSemSubject->fetch(PDO::FETCH_ASSOC)){

                    $subject_id = $row['subject_id'];
                    $student_subject_id = $row['student_subject_id'];

                    // echo $student_subject_id;
                    // echo "<br>";
                    array_push($student_subject_arr, $student_subject_id);


                    $myFirstSemSubjectPassed = $this->con->prepare("SELECT student_subject_id  FROM student_subject_grade
                        WHERE student_id=:student_id
                        AND student_subject_id=:student_subject_id
                        AND remarks=:remarks
                        LIMIT 1
                        ");

                    $myFirstSemSubjectPassed->bindValue(":student_id", $student_id);
                    $myFirstSemSubjectPassed->bindValue(":student_subject_id", $student_subject_id);
                    $myFirstSemSubjectPassed->bindValue(":remarks", "Passed");
                    $myFirstSemSubjectPassed->execute();

                    if($myFirstSemSubjectPassed->rowCount() > 0){

                        $marked_student_subject_id = $myFirstSemSubjectPassed->fetchColumn();
                        array_push($student_subject_grade_arr, $marked_student_subject_id);
                    }
                }
            }

            if(count($student_subject_arr) == count($student_subject_grade_arr)
                && array_values($student_subject_arr) == array_values($student_subject_grade_arr)
            ){
                // echo "matched";
                
            }
           

        }else{
            array_push($array_error, "school year id");
        }
        // True or False
        return empty($array_error);
    }


    public function CheckStudentGrade11PassedSecondSem($username){
        
        $student_obj = $this->studentEnroll->GetStudentCourseLevelYearIdCourseId($username);

        $student_id = $student_obj['student_id'];
        $course_id = $student_obj['course_id'];
        $enrollment_status = "enrolled";

        // School Year Creation should be consistent.
        // Creating Sections.

        $sql = $this->con->prepare("SELECT school_year_id FROM enrollment

            WHERE student_id=:student_id
            AND course_id=:course_id
            AND enrollment_status=:enrollment_status
            ORDER BY school_year_id DESC
            LIMIT 1");

        $sql->bindValue(":student_id", $student_id);
        $sql->bindValue(":course_id", $course_id);
        $sql->bindValue(":enrollment_status", $enrollment_status);
        $sql->execute();
        
        if($sql->rowCount() > 0){

        }
    }

    public function CheckGrade12AlignedSections($student_id){

        $student_username = $this->studentEnroll->GetStudentUsername($student_id);

        $student_obj = $this->studentEnroll
            ->GetStudentCourseLevelYearIdCourseId($student_username);
        
        // $student_course_level = $student_obj['course_level'];
        $student_course_id = $student_obj['course_id'];

        $result = false;

        $check_course = $this->con->prepare("SELECT course_level FROM course
            WHERE course_id=:course_id");
            
        $check_course->bindValue(":course_id", $student_course_id);
        $check_course->execute();

        if($check_course->rowCount() > 0){
            // echo "sss";

            $course_course_level = $check_course->fetchColumn();

            // echo $course_course_level;
            if($course_course_level == 11){
                $result =  false;
            }else if($course_course_level == 12){
                $result = true;
            }
        }

        return $result;
    }

    public function CheckInActiveStudentEligibleForSemester($student_id, $course_id){

        $school_year_obj = $this->studentEnroll->GetActiveSchoolYearAndSemester();

        $current_school_year_period = $school_year_obj['period'];

        $result = false;

        # student were enrolled before but they decided to stop.
        $enrollment_status = "enrolled";

        $sql = $this->con->prepare("SELECT school_year_id FROM enrollment as t1

            INNER JOIN student as t2 ON t2.student_id = t1.student_id
            WHERE t1.student_id=:student_id
            AND t1.course_id=:course_id
            AND t1.enrollment_status=:enrollment_status

            LIMIT 1");

        $sql->bindValue(":student_id", $student_id);
        $sql->bindValue(":course_id", $course_id);
        $sql->bindValue(":enrollment_status", $enrollment_status);
        $sql->execute();



        if($sql->rowCount() > 0){

            $inactive_sy_id = $sql->fetchColumn();

            echo $inactive_sy_id;

            $sy = $this->con->prepare("SELECT period FROM school_year
                WHERE school_year_id=:school_year_id
                -- AND period = :current_school_year_period
                ");

            $sy->bindValue(":school_year_id", $inactive_sy_id);
            // $sy->bindValue(":current_school_year_period", $current_school_year_period);
            $sy->execute();

            if($sy->rowCount() > 0){
                // Student Finished 1st sem and stopped in 2nd sem.
                # current period must be second to be eligible for him to return
                
                $student_period = $sy->fetchColumn();

                // echo $student_period;
                if($student_period == "First"){
                    // This term should student eligible to apply
                    $student_period = "Second";
                    if($student_period == $current_school_year_period){
                        $result = true;
                    }else{
                        $result = false;
                    }
                }

                // else if($student_period == "Second"){
                //     // This term should student eligible to apply
                //     $student_period = "First";
                //     if($student_period == $current_school_year_period){
                //         $result = true;
                //     }else{
                //         $result = false;
                //     }
                // }
                

            }else if($sy->rowCount() == 0){
                $result = false;
                // echo "qwe";
            }
        }

        return $result;
    }

    public function CheckGrade11StudentFinishedSubject($username){

        // $student_program_id = $this->studentEnroll->
        $student_obj = $this->studentEnroll->GetStudentCourseLevelYearIdCourseId($username);

        $student_course_id = $student_obj['course_id'];
        $student_id = $student_obj['student_id'];

        $student_course_level = $student_obj['course_level'];
        // $course_id = $this->studentEnroll->GetStudentCourseId($username);
        $program_id = $this->studentEnroll->GetStudentProgramId($student_course_id);

        $subject_program = $this->con->prepare("SELECT subject_title 

            FROM subject_program

            WHERE program_id=:program_id
            AND course_level=:course_level
        ");
        $subject_program->bindValue(":program_id", $program_id);
        $subject_program->bindValue(":course_level", 11);
        $subject_program->execute();

        $missing_subject = [];

        $isMet = false;

        if($subject_program->rowCount() > 0){

            // $grade11ListOfProgramSubject = $subject_program->fetchAll(PDO::FETCH_ASSOC);
            
            $my_subject_graded = $this->con->prepare("SELECT 
                
                subject_title 
                
                FROM student_subject_grade
            
                WHERE subject_title=:subject_title
                AND student_id=:student_id
                LIMIT 1
            ");
            while($row = $subject_program->fetch(PDO::FETCH_ASSOC)){

                $subject_title = $row['subject_title'];

                $my_subject_graded->bindValue(":subject_title", $subject_title);
                $my_subject_graded->bindValue(":student_id", $student_id);
                $my_subject_graded->execute();

                if($my_subject_graded->execute() && $my_subject_graded->rowCount() == 0){
                    // $qwe = $my_subject_graded->fetchColumn();
                    // echo $qwe;
                    // echo "<br>";
                    $missing_subject[] = $subject_title;
                    // $isMet = false;
                }
                // else{
                //     $isMet = true;
                //     echo "qwe";
                // }
               
            }

            // if(empty($missing_subject) == false){
            //     echo "Some subjects are not met";
            // }else{
            //     echo "all subjects are met";
            // }

            if(count($missing_subject) > 0){
                // echo "This subjects are not met: " . implode(", ", $missing_subject);

            }else if(count($missing_subject) == 0){
                // echo "All subjects are met";
                $isMet = true;
            }

            // print_r($missing_subject);

        }

        return $isMet;
    }

    public function CheckIfGradeLevelSemesterSubjectWereAllPassed($student_id,
        $selected_course_level, $selected_semester){

        # Get all subjects from the enrollment

        $query = $this->con->prepare("SELECT 

                t1.enrollment_id
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
                ");

        $query->bindValue("student_id", $student_id); 
        $query->bindValue("enrollment_status", "enrolled"); 
        $query->bindValue("period", $selected_semester); 
        $query->bindValue("course_level", $selected_course_level); 
        $query->execute(); 

        $my_enrolled_subjects = [];
        $my_passed_enrolled_subjects = [];
        
        $isEmpty = false;
        if($query->rowCount() > 0){

            $row = $query->fetch(PDO::FETCH_ASSOC);
            $enrollment_id = $row['enrollment_id'];

            // echo $enrollment_id;

            $check_subject = $this->con->prepare("SELECT student_subject_id
            
                FROM student_subject as t1

                WHERE t1.enrollment_id=:enrollment_id
            
                -- INNER JOIN 
            ");

            $check_subject->bindValue("enrollment_id", $enrollment_id); 
            $check_subject->execute(); 

            if($check_subject->rowCount() > 0){

                while($row_subject = $check_subject->fetch(PDO::FETCH_ASSOC)){

                    $student_subject_id = $row_subject['student_subject_id'];
                    array_push($my_enrolled_subjects, $student_subject_id);

                    $check_passed_subject = $this->con->prepare("SELECT student_subject_id
                    
                        FROM student_subject_grade

                        WHERE student_subject_id=:student_subject_id
                        AND remarks=:remarks
                        LIMIT 1
                    ");

                    $check_passed_subject->bindValue("student_subject_id", $student_subject_id); 
                    $check_passed_subject->bindValue("remarks", "Passed"); 
                    $check_passed_subject->execute(); 
                    if($check_passed_subject->rowCount() > 0){

                        $check_passed_row = $check_passed_subject->fetch(PDO::FETCH_ASSOC);
                        $graded_student_subject_id = $check_passed_row['student_subject_id'];
                        array_push($my_passed_enrolled_subjects, $graded_student_subject_id);

                    }

                }

                // print_r($my_enrolled_subjects);
                // print_r($my_passed_enrolled_subjects);

                if(count($my_enrolled_subjects) == count($my_passed_enrolled_subjects) 
                    && empty(array_diff($my_enrolled_subjects, $my_passed_enrolled_subjects))
                    &&empty(array_diff($my_passed_enrolled_subjects, $my_enrolled_subjects))
                ){
                   $isEmpty = true;
                //    echo "Subjects in this semester grade level is all passed";
                }else{
                    $isEmpty = false;
                    // echo "Subjects in this semester grade level is not all passed";

                }
            }

            return $isEmpty;
          

        }
        // $query = $this->con->prepare("SELECT * FROM student_subject as t1
        
        //     -- INNER JOIN 
        // ");

        // $query->bindValue("student_id", $student_id); 
        // $query->bindValue("period", $selected_semester); 
        // $query->bindValue("enrollment_status", "enrolled"); 
        // $query->bindValue("course_level", $selected_course_level); 
        // $query->execute(); 

        // if($query->rowCount() > 0){


        // }

        # Compare all those subject in the subject_grade
        # Check if all remarked as passed.
        
    }



    public function GetStudentCurrentSemesterSubjects($username, $current_school_year_period){


        $student_obj = $this->studentEnroll->GetStudentCourseLevelYearIdCourseId($username);
        $student_id = $student_obj['student_id'];
        $student_course_level = $student_obj['course_level'];
        $student_course_id = $student_obj['course_id'];
        
        $student_program_id = $this->studentEnroll->GetStudentProgramId($student_course_id);
        
        $query = $this->con->prepare("SELECT subject_title

            FROM subject_program
            WHERE course_level=:course_level
            AND program_id=:program_id
            AND semester=:semester
            ");

        $query->bindValue(":course_level", $student_course_level);
        $query->bindValue(":program_id", $student_program_id);
        $query->bindValue(":semester", $current_school_year_period);
        $query->execute();

        $arr = [];

        if($query->rowCount() > 0){

            while($row = $query->fetch(PDO::FETCH_ASSOC)){
                array_push($arr, $row['subject_title']);
            }
        }
        return $arr;
    }

    public function GetSubjectCount($course_id, $school_year_id){

        $get_sy = $this->con->prepare("SELECT period FROM school_year
        
            WHERE school_year_id=:school_year_id
            
            ");

        $get_sy->bindValue(":school_year_id", $school_year_id);
        $get_sy->execute();
        if($get_sy->rowCount() > 0){

            $period = $get_sy->fetchColumn();

            $query = $this->con->prepare("SELECT * FROM subject
        
                WHERE course_id=:course_id
                AND semester=:semester
            
            ");

            $query->bindValue(":course_id", $course_id);
            $query->bindValue(":semester", $period);
            $query->execute();
            return $query->rowCount();
        }

    }

    public function UpdateSHSStudentCourseId($student_id, $course_id, $course_level){

        $update = $this->con->prepare("UPDATE student
        
            SET course_id=:course_id,
                course_level=:course_level

            WHERE student_id=:student_id");
        
        $update->bindValue("course_id", $course_id);
        $update->bindValue("course_level", $course_level);
        $update->bindValue("student_id", $student_id);
        return $update->execute();
    }


    
}

?>