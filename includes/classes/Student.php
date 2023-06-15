<?php

class Student{

    private $con, $sqlData;

    public function __construct($con, $input)
    {
        $this->con = $con;
        $this->sqlData = $input;

        // echo "hey";
        // print_r($input);
        if(!is_array($input)){
            $query = $this->con->prepare("SELECT * FROM student
            WHERE username=:username");

            $query->bindValue(":username", $input);
            $query->execute();

            $this->sqlData = $query->fetch(PDO::FETCH_ASSOC);
        }
    }

    public function GetId() {
        return isset($this->sqlData['student_id']) ? $this->sqlData["student_id"] : 0; 
    }

    public function GetIsGraduated() {
        return isset($this->sqlData['is_graduated']) ? $this->sqlData["is_graduated"] : null; 
    }

    public function CheckIfActive() {
        return isset($this->sqlData['active']) ? $this->sqlData["active"] : null; 
    }

    public function CheckIfTertiary() {
        return isset($this->sqlData['is_tertiary']) ? $this->sqlData["is_tertiary"] : null; 
    }
    public function GetUsername() {
        return isset($this->sqlData['username']) ? $this->sqlData["username"] : ""; 
    }
    public function GetEmail() {
        return isset($this->sqlData['email']) ? $this->sqlData["email"] : ""; 
    }
    public function GetFirstName() {
        return isset($this->sqlData['firstname']) ? $this->sqlData["firstname"] : ""; 
    }

    public function GetStudentLevel($student_id) {

        $sql = $this->con->prepare("SELECT course_level FROM student
            WHERE student_id=:student_id");
        
        $sql->bindValue(":student_id", $student_id);
        $sql->execute();

        if($sql->rowCount() > 0){
            return $sql->fetchColumn();
        }
        return -1;
    }

    public function GetStudentCourseLevel() {
        return isset($this->sqlData['course_level']) ? $this->sqlData["course_level"] : ""; 
    }

    public function GetLastName() {
        return isset($this->sqlData['lastname']) ? $this->sqlData["lastname"] : ""; 
    }

  


    public function GetMiddleName() {
        return isset($this->sqlData['middle_name']) ? $this->sqlData["middle_name"] : ""; 
    }

    

    public function GetStudentAddress() {
        return isset($this->sqlData['address']) ? $this->sqlData["address"] : "N/A"; 
    }

    public function GetStudentBirthdays() {
        return isset($this->sqlData['birthday']) ? $this->sqlData["birthday"] : "N/A"; 
    }
        public function GetStudentSex() {
        return isset($this->sqlData['sex']) ? $this->sqlData["sex"] : "N/A"; 
    }

    public function GetName() {
        // return isset($this->sqlData['firstname']) ? $this->sqlData["firstname"] ." " . $this->sqlData["lastname"]: "N/A"; 
    return isset($this->sqlData['firstname']) ? ucfirst(strtolower($this->sqlData["firstname"])) ." " . $this->sqlData["lastname"]: "N/A";

    }
    public function GetGuardianName() {
        return isset($this->sqlData['guardian_name']) ? $this->sqlData["guardian_name"] : "N/A"; 

    }
    public function GetGuardianNameContact() {
        return isset($this->sqlData['guardian_contact_number']) ? $this->sqlData["guardian_contact_number"] : "N/A"; 

    } 

    public function GetContactNumber() {
        return isset($this->sqlData['contact_number']) ? $this->sqlData["contact_number"] : "N/A"; 

    } 

    public function GetStudentUniqueId(){

        return isset($this->sqlData['student_unique_id']) ? $this->sqlData["student_unique_id"] : "N/A"; 
    }

    public function GetStudentStatusv2(){

        return isset($this->sqlData['student_statusv2']) ? $this->sqlData["student_statusv2"] : "N/A"; 
    }

    public function GetStudentAdmissionStatus(){

        return isset($this->sqlData['admission_status']) ? $this->sqlData["admission_status"] : "N/A"; 
    }


    public function GetStudentNewEnrollee(){

        return isset($this->sqlData['new_enrollee']) ? $this->sqlData["new_enrollee"] : "N/A"; 
    }

    public function ResetPassword($student_username){

        $array = [];
        $new_password =  $this->generate_random_password();

        // Hash the new password
        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

        // Update the student's password in the database
        $query = $this->con->prepare("UPDATE student 
            SET password=:password
            WHERE username=:username
            ");
        $query->bindValue(":password", $hashed_password);
        $query->bindValue(":username", $student_username);

        if($query->execute()){
            echo "<br>";
            echo "Temporary Password: $new_password";
            echo "<br>";

            // Sent via email
            // return $new_password;
            array_push($array, $new_password);
            array_push($array, true);
        }
        return $array;
    }

    private function generate_random_password($length = 8) {
        $password = '';

        try {
            // Generate a string of random bytes
            $bytes = random_bytes($length);
            // Convert the random bytes to a string of ASCII characters
            $password = bin2hex($bytes);
        } catch (Exception $e) {
            // Handle the exception if the random_bytes() function fails
            // For example, you can fallback to using the original function
            $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            for ($i = 0; $i < $length; $i++) {
                $password .= $characters[rand(0, strlen($characters) - 1)];
            }
        }

        return $password;
    }

    public function UpdateStudentCourseId($course_id, $student_id,
        $update_course_id){

        $sql = $this->con->prepare("UPDATE student 
            SET course_id=:update_course_id
            WHERE student_id = :student_id
            AND course_id = :current_course_id
            "
            );

        $sql->bindValue(":update_course_id", $update_course_id);
        $sql->bindValue(":student_id", $student_id);
        $sql->bindValue(":current_course_id", $course_id);
        
        return $sql->execute();
    }


    public function UpdateStudentCourseIdv2($student_id,
        $update_course_id){
            
        $sql = $this->con->prepare("UPDATE student 
            SET course_id=:update_course_id
            WHERE student_id = :student_id
            "
            );

        $sql->bindValue(":update_course_id", $update_course_id);
        $sql->bindValue(":student_id", $student_id);
        // $sql->bindValue(":current_course_id", $course_id);
        
        return $sql->execute();
    }

    public function UpdateStudentStatusv2($student_id, $student_statusv2){

        $sql = $this->con->prepare("UPDATE student 
                    SET student_statusv2 = :update_status
                    WHERE student_id = :student_id
                    -- AND student_statusv2 = :student_statusv2
                    ");

        $sql->bindValue(":update_status", $student_statusv2);
        $sql->bindValue(":student_id", $student_id);
        // $sql->bindValue(":student_statusv2", NULL);

        // echo $student_statusv2;
        return $sql->execute();
    }
    public function UpdateActiveNewEnrolleeStudentToOngoing(){

        $sql = $this->con->prepare("UPDATE student 
                    SET new_enrollee = :update_new_enrollee
                    WHERE active = :active
                    AND new_enrollee = :new_enrollee
                    ");

        $sql->bindValue(":update_new_enrollee", 0);
        $sql->bindValue(":active", 1);
        $sql->bindValue(":new_enrollee", 1);

        return $sql->execute();
    }

    public function UpdateStudentSection($student_id, $course_id){


        $sql = $this->con->prepare("UPDATE student 
                    SET course_id = :update_course_id
                    WHERE student_id = :student_id
                    ");

        $sql->bindValue(":update_course_id", $course_id);
        $sql->bindValue(":student_id", $student_id);

        return $sql->execute();
    }

    public function MarkAsGraduate($student_id){

        $is_graduated = 1;
        $sql = $this->con->prepare("UPDATE student 
                    SET is_graduated = :is_graduated,
                        active=:set_unactive
                    WHERE student_id = :student_id
                    AND active = :active
                    ");

        $sql->bindValue(":is_graduated", $is_graduated);
        $sql->bindValue(":set_unactive", 0);
        $sql->bindValue(":student_id", $student_id);
        $sql->bindValue(":active", 1);

        return $sql->execute();
    }

    public function GetAllOngoingRegularStudent($prev_sy_id, $current_school_year_id){

        $toEnrollStudentsv2 = [];
        $new_enrollee = 0;

        if($prev_sy_id == -1){

            $prev_enrollment = $this->con->prepare("SELECT t1.student_id
        
                FROM enrollment as t1

                INNER JOIN student as t2 ON t2.student_id = t1.student_id
            
                WHERE t1.school_year_id=:school_year_id
                AND t2.new_enrollee=:new_enrollee
                -- AND t1.school_year_id=:current_school_year_id
            ");

            $prev_enrollment->bindValue(":school_year_id", $current_school_year_id);
            $prev_enrollment->bindValue(":new_enrollee", $new_enrollee);

            // $prev_enrollment->bindValue(":current_school_year_id", $current_school_year_id);

            $prev_enrollment->execute();
            # If no Previous Enrollment, then it is the First School Year.
            if($prev_enrollment->rowCount() > 0){
                while($row = $prev_enrollment->fetch(PDO::FETCH_ASSOC)){
                    array_push($toEnrollStudentsv2, $row['student_id']);
                }
            }


        }

        $toEnrollStudents = [];

        $current_enrollment = $this->con->prepare("SELECT 
        
            t1.*
            -- t1.is_tertiary,
            -- t1.admission_status
            FROM enrollment as t1
            INNER JOIN student as t2 ON t2.student_id = t1.student_id

            WHERE t1.school_year_id=:school_year_id
            AND t2.active=1
        ");

        $current_enrollment->bindValue(":school_year_id", $current_school_year_id);

        $current_enrollment->execute();

        $prev_enrollment = $this->con->prepare("SELECT 
                t1.student_id,
                t2.username,
                t2.is_tertiary,
                t1.enrollment_id,
                t2.admission_status
        
                FROM enrollment as t1

                INNER JOIN student as t2 ON t2.student_id = t1.student_id
            
                WHERE t1.school_year_id=:school_year_id
                AND t2.new_enrollee=:new_enrollee
                AND t2.active=1
                -- AND t1.school_year_id=:current_school_year_id
            ");

        $prev_enrollment->bindValue(":school_year_id", $prev_sy_id);
        $prev_enrollment->bindValue(":new_enrollee", $new_enrollee);
        // $prev_enrollment->bindValue(":current_school_year_id", $current_school_year_id);

        $prev_enrollment->execute();

        if($prev_enrollment->rowCount() > 0 && $prev_sy_id != -1){

            $allPrevEnrollmentStudents = $prev_enrollment->fetchAll(PDO::FETCH_ASSOC);

            if($current_enrollment->rowCount() == 0){
                $isAllEnrolled = false; 

                foreach ($allPrevEnrollmentStudents as $row) {
                    $toEnrollStudents[] = $row;
                }

                // return $allPrevEnrollmentStudents;
            }
            else if($current_enrollment->rowCount() > 0){
                # It means that there`s left student id left behind
                # in the automatic enrollment.

                $allCurrentEnrollmentStudent = $current_enrollment->fetchAll(PDO::FETCH_ASSOC);

                # Get Left Behind Student Ids
                $leftBehindPrevStudentIds = array_diff(array_column($allPrevEnrollmentStudents, 'student_id'),
                            array_column($allCurrentEnrollmentStudent, 'student_id'));

                if(count($leftBehindPrevStudentIds) > 0){
                    // echo "There a missing left.";

                    $isAllEnrolled = false;

                    foreach ($leftBehindPrevStudentIds as $key => $student_id) {
                        # code...

                        $get_student = $this->con->prepare("SELECT 
                        
                            student_id, admission_status,
                            is_tertiary,username

                            FROM student
                            WHERE student_id=:student_id
                            LIMIT 1"); 

                        $get_student->bindValue(":student_id", $student_id);
                        $get_student->execute();

                        if($get_student->rowCount() > 0){

                            $row = $get_student->fetch(PDO::FETCH_ASSOC);

                            array_push($toEnrollStudents, $row);
                        }
                    }

                }else{
                    // $isAllEnrolled = true;
                    // echo "Current is > 0 & All Prev Enrollment Student Ids is present to the Current Enrollment Id";
                    return [];
                }
            }

            $allPrevEnrollmentStudents = $prev_enrollment->fetchAll(PDO::FETCH_ASSOC);

            // print_r($allPrevEnrollmentStudents);
        }else{
            return [];
        }

        // print_r($toEnrollStudents);

        return $toEnrollStudents;
    }

    public function GetAllRecentlyMovedUpStudents($prev_sy_id, $current_school_year_id){

        if($prev_sy_id == -1){
            return;
        }

        $old_enroll = new OldEnrollees($this->con, null);

        // $checkSemesterSubjectPassed = $old_enroll->CheckCurrentSemesterAllPassed(
        //     $userLoggedInId, $student_course_id, $current_school_year_id);




        $toEnrollStudents = [];

        $new_enrollee = 0;

        $prev_enrollment = $this->con->prepare("SELECT t1.student_id
    
            FROM enrollment as t1

            INNER JOIN student as t2 ON t2.student_id = t1.student_id
        
            WHERE t1.school_year_id=:school_year_id
            AND t2.new_enrollee=:new_enrollee
            -- AND t1.school_year_id=:current_school_year_id
        ");

        $prev_enrollment->bindValue(":school_year_id", $prev_sy_id);
        $prev_enrollment->bindValue(":new_enrollee", $new_enrollee);
        // $prev_enrollment->bindValue(":current_school_year_id", $current_school_year_id);

        $prev_enrollment->execute();

        # If no Previous Enrollment, then it is the First School Year.

        if($prev_enrollment->rowCount() > 0){
            // $allPrevEnrollmentStudents = $prev_enrollment->fetchAll(PDO::FETCH_ASSOC);

            while($row = $prev_enrollment->fetch(PDO::FETCH_ASSOC)){

                $prev_student_id = $row['student_id'];

                echo $prev_student_id;
            }
        }

        // return $toEnrollStudents;
         
    }


    public function CheckShsStudentGraduateCandidate($student_id, $program_id){
       
        $studentCurriculumSubject = [];
        
        $subjects = $this->GetStudentWholeCurriculumStrandBased($program_id);

        foreach ($subjects as $key => $value) {
            # code...
            array_push($studentCurriculumSubject, $value['subject_program_id']);
        }


        $sql = $this->con->prepare("SELECT t1.subject_program_id
    
            FROM student_subject as t1
            INNER JOIN student_subject_grade as t2 ON t2.student_subject_id = t1.student_subject_id
            AND t2.remarks = 'Passed'
            AND t1.student_id =:student_id
        ");

        $sql->bindValue(":student_id", $student_id);
        $sql->execute();

        $studentCurrentSubject = [];

        $studentWholePassedSubject = $this->GetWholeStudentPassedSubjectGrade($program_id,
            $student_id);

  
        if($sql->rowCount() > 0){

            $subjects = $sql->fetchAll(PDO::FETCH_ASSOC);
            // var_dump($subjects);

            foreach ($subjects as $key => $value) {
                # code...
                // array_push($studentCurrentSubject, $value);
                array_push($studentCurrentSubject, $value['subject_program_id']);
            }
        }

 
        // echo count($studentCurrentSubject);
        // echo count($studentCurriculumSubject);
 
        $difference = array_diff($studentCurriculumSubject, $studentCurrentSubject);

        if(count($studentCurriculumSubject) == count($studentCurrentSubject)
            && empty($difference)
            ){
            // echo "uu";
            return true;
        }else{
            // echo "nut";

            return false;
        }

        return false;

    }

    public function CheckShsStudentGraduateCandidatev2($student_id, $program_id){
       
        $studentCurriculumSubject = [];
        
        $subjects = $this->GetStudentWholeCurriculumStrandBased($program_id);

        foreach ($subjects as $key => $value) {
            # code...
            array_push($studentCurriculumSubject, $value['subject_title']);
        }


        $sql = $this->con->prepare("SELECT t1.subject_program_id
    
            FROM student_subject as t1
            INNER JOIN student_subject_grade as t2 ON t2.student_subject_id = t1.student_subject_id
            AND t2.remarks = 'Passed'
            AND t1.student_id =:student_id
        ");

        $sql->bindValue(":student_id", $student_id);
        $sql->execute();

        $studentCurrentSubject = [];

        $studentWholePassedSubject = $this->GetWholeStudentPassedSubjectGrade($program_id,
            $student_id);

        foreach ($studentWholePassedSubject as $key => $value) {
            # code...
            array_push($studentCurrentSubject, $value['subject_title']);
        }

        // print_r($studentWholePassedSubject);

  
        // if($sql->rowCount() > 0){

        //     $subjects = $sql->fetchAll(PDO::FETCH_ASSOC);
        //     // var_dump($subjects);

        //     foreach ($subjects as $key => $value) {
        //         # code...
        //         // array_push($studentCurrentSubject, $value);
        //         array_push($studentCurrentSubject, $value['subject_program_id']);
        //     }
        // }

 
        // echo count($studentCurrentSubject);
        // echo count($studentCurriculumSubject);
 
        $difference = array_diff($studentCurriculumSubject, $studentCurrentSubject);

        if(count($studentCurriculumSubject) == count($studentCurrentSubject)
            && empty($difference)
            ){
            // echo "uu";
            return true;
        }else{
            // echo "nut";

            return false;
        }

        return false;

    }

    public function CheckShsStudentGraduateCandidateTitleBased($student_id,
        $program_id){
       
        $studentCurriculumSubject = [];
        
        $subjects = $this->GetStudentWholeCurriculumStrandBased($program_id);

        foreach ($subjects as $key => $value) {
            # code...
            array_push($studentCurriculumSubject, $value['subject_title']);
        }

        // print_r($studentCurriculumSubject);

        $sql = $this->con->prepare("SELECT t1.subject_title
    
            FROM student_subject_grade as t1
            WHERE t1.remarks = 'Passed'
            AND t1.student_id =:student_id
        ");

        $sql->bindValue(":student_id", $student_id);
        $sql->execute();

        $studentCurrentSubject = [];
  
        if($sql->rowCount() > 0){

            $subjects = $sql->fetchAll(PDO::FETCH_ASSOC);
            // var_dump($subjects);

            foreach ($subjects as $key => $value) {
                # code...
                // array_push($studentCurrentSubject, $value);
                array_push($studentCurrentSubject, $value['subject_title']);
            }
        }
        
      
        $difference = array_diff($studentCurriculumSubject,
            $studentCurrentSubject);

        if(count($studentCurrentSubject) == count($studentCurriculumSubject)
            && empty($difference)
            ){
                // echo "same";
            return true;
        }else{
            // echo "not";
            return false;
        }

        return false;

    }

    public function CheckShsEligibleForMoveUp($student_id,
        $program_id){
       
        $studentCurriculumSubject = [];
        
        $subjects = $this->GetGrade11FirstAndSecondSemCurriculum($program_id);

        // print_r($subjects);

        foreach ($subjects as $key => $value) {
            # code...
            array_push($studentCurriculumSubject, $value['subject_title']);
        }

        // print_r($studentCurriculumSubject);

        $sql = $this->con->prepare("SELECT t1.subject_title
    
            FROM student_subject_grade as t1
            WHERE t1.remarks = 'Passed'
            AND t1.student_id =:student_id
        ");

        $sql->bindValue(":student_id", $student_id);
        $sql->execute();

        $studentCurrentSubject = [];
  
        if($sql->rowCount() > 0){

            $subjects = $sql->fetchAll(PDO::FETCH_ASSOC);
            // var_dump($subjects);

            foreach ($subjects as $key => $value) {
                # code...
                // array_push($studentCurrentSubject, $value);
                array_push($studentCurrentSubject, $value['subject_title']);
            }
        }
        
        // print_r($studentCurrentSubject);
        
        $difference = array_diff($studentCurriculumSubject,
            $studentCurrentSubject);

        if(empty($difference)){
            // echo "true";
            return true;
        }else{
            // echo "false";
            return false;

        }

        return false;
    }

    
    public function CheckTertiaryEligibleForMoveUp($student_id,
        $program_id, $course_level){
       
        $courseLevelCurriculumSubject = [];
        
        $subjects = $this->GetTertiarySemesterBasedOnYearLevel($program_id,
            $course_level);

        // print_r($subjects);

        foreach ($subjects as $key => $value) {
            # code...
            array_push($courseLevelCurriculumSubject, $value['subject_title']);
        }

        // print_r($courseLevelCurriculumSubject);

        $sql = $this->con->prepare("SELECT t1.subject_title
    
            FROM student_subject_grade as t1
            WHERE t1.remarks = 'Passed'
            AND t1.student_id =:student_id
        ");

        $sql->bindValue(":student_id", $student_id);
        $sql->execute();

        $studentCurrentSubject = [];
  
        if($sql->rowCount() > 0){

            $subjects = $sql->fetchAll(PDO::FETCH_ASSOC);
            // var_dump($subjects);

            foreach ($subjects as $key => $value) {
                # code...
                // array_push($studentCurrentSubject, $value);
                array_push($studentCurrentSubject, $value['subject_title']);
            }
        }
        
        // print_r($studentCurrentSubject);
        
        $difference = array_diff($courseLevelCurriculumSubject,
            $studentCurrentSubject);

        if(empty($difference)){
            // echo "true";
            return true;
        }else{
            // echo "false";
            return false;

        }
        return false;

    }

    public function GetStudentWholeCurriculumStrandBased($program_id){

        $subject_query = $this->con->prepare("SELECT subject_program_id, subject_title

            FROM subject_program
            WHERE program_id=:program_id
            ");

        $subject_query->bindValue(":program_id", $program_id); 
        $subject_query->execute();

        if($subject_query->rowCount() > 0){

            $row_sub = $subject_query->fetchAll(PDO::FETCH_ASSOC);
            return $row_sub;
        }
        return [];
    }

    public function GetGrade11FirstAndSecondSemCurriculum($program_id){

        $course_level = 11;

        $subject_query = $this->con->prepare("SELECT subject_program_id, subject_title

            FROM subject_program
            WHERE program_id=:program_id
            AND course_level=:course_level
            ");

        $subject_query->bindValue(":program_id", $program_id); 
        $subject_query->bindValue(":course_level", $course_level); 
        $subject_query->execute();

        if($subject_query->rowCount() > 0){

            $row_sub = $subject_query->fetchAll(PDO::FETCH_ASSOC);

            // print_r($row_sub);
            return $row_sub;
        }

        return [];
    }

    public function GetTertiarySemesterBasedOnYearLevel($program_id,
        $course_level){

        // $course_level = 11;

        $subject_query = $this->con->prepare("SELECT subject_program_id, subject_title

            FROM subject_program
            WHERE program_id=:program_id
            AND course_level=:course_level
            ");

        $subject_query->bindValue(":program_id", $program_id); 
        $subject_query->bindValue(":course_level", $course_level); 
        $subject_query->execute();

        if($subject_query->rowCount() > 0){

            $row_sub = $subject_query->fetchAll(PDO::FETCH_ASSOC);

            // print_r($row_sub);
            return $row_sub;
        }

        return [];
    }

    public function GetWholeStudentPassedSubjectGrade($program_id, $student_id){

        // $course_level = 11;

        $subject_query = $this->con->prepare("SELECT subject_title

            FROM student_subject_grade
            -- WHERE program_id=:program_id
            WHERE student_id=:student_id
            AND remarks='Passed'
            ");

        // $subject_query->bindValue(":program_id", $program_id); 
        $subject_query->bindValue(":student_id", $student_id); 
        $subject_query->execute();

        if($subject_query->rowCount() > 0){

            $row_sub = $subject_query->fetchAll(PDO::FETCH_ASSOC);
            // print_r($row_sub);
            return $row_sub;
        }

        return [];
    }
}
?>