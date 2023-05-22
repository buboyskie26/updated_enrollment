<?php

    class Enrollment{

    private $con, $userLoggedIn, $studentEnroll;

    public function __construct($con, $studentEnroll)
    {
        $this->con = $con;
        $this->studentEnroll = $studentEnroll;
    }

    public function createPage(){

        $nav = $this->createHomePageNav();
        $tabs = $this->createTabs();


        return "
            $nav
        ";

    }

    public function createTabs(){

        $reg_form = "";
 
        $asd = include 'regular_form.php';

        return "
            <div class='tab-content'><br/>
                <div class='tab-pane active' id='New'>

               
            </div>

            <div class='tab-pane' id='Old'><br/>
                <h1>ISDOP</h1>
            </div>
        ";
    }

    private function createRegularForm(){

        if (isset($_POST['regsubmit'])) {
            $_SESSION['STUDID'] 	  =  $_POST['IDNO'];
            $_SESSION['FNAME'] 	      =  $_POST['FNAME'];
            $_SESSION['LNAME']  	  =  $_POST['LNAME'];
            $_SESSION['MI']           =  $_POST['MI'];
            $_SESSION['PADDRESS']     =  $_POST['PADDRESS'];
            $_SESSION['SEX']          =  $_POST['optionsRadios'];
            // $_SESSION['BIRTHDATE']    = date_format(date_create($_POST['BIRTHDATE']),'Y-m-d'); 
            $_SESSION['BIRTHDATE']    = $_POST['BIRTHDATE']; 
            $_SESSION['NATIONALITY']  =  $_POST['NATIONALITY'];
            $_SESSION['BIRTHPLACE']   =  $_POST['BIRTHPLACE'];
            $_SESSION['RELIGION']     =  $_POST['RELIGION'];
            $_SESSION['CONTACT']      =  $_POST['CONTACT'];
            $_SESSION['CIVILSTATUS']  =  $_POST['CIVILSTATUS'];
            $_SESSION['GUARDIAN']     =  $_POST['GUARDIAN'];
            $_SESSION['GCONTACT']     =  $_POST['GCONTACT'];
            $_SESSION['COURSEID'] 	  =  $_POST['COURSE'];
            // $_SESSION['SEMESTER']     =  $_POST['SEMESTER'];  
            $_SESSION['USER_NAME']    =  $_POST['USER_NAME']; 
            $_SESSION['PASS']    	  =  $_POST['PASS']; 
        }


        $_SESSION['SY'] = "2023-2024";
        $autonum = 100002;

    }

    //

    public function createHomePageNav(){
        return "
            <ul class='nav'>
                <li class='nav-item'>
                    <a class='nav-link active' aria-current='page' href='#New'>New</a>
                </li>
                <li class='nav-item'>
                    <a class='nav-link' data-toggle='tab' href='#Old'>Old</a>
                </li>
                <li class='nav-item'>
                    <a class='nav-link' href='#'>Transferees</a>
                </li>
            </ul>
        ";
    }

    public function PopulateSubjects($course_id){

        echo $course_id;
    }

    public function StudentQualifiedEnrollment(){

        # Has passed remarks from start until current semester enrollment date.
        $active = 1;

        $sql = $this->con->prepare("SELECT * FROM student
            WHERE active=:active");

        $sql->bindValue(":active", $active);
        $sql->execute();
        return $sql->fetchAll(PDO::FETCH_ASSOC);

    }

    public function CheckStudentEnrolledSubject($student_username){

        // Get Subject_Program
        $student_course_id = $this->studentEnroll->GetStudentCourseId($student_username);
        $student_program_id = $this->studentEnroll->GetStudentProgramId($student_course_id);
        $school_year_obj = $this->studentEnroll->GetActiveSchoolYearAndSemester();

        $student_course_level = $this->studentEnroll->GetStudentCourseLevel($student_username);

        $current_semester = $school_year_obj['period'];
        
        $sql = $this->con->prepare("SELECT subject_title FROM subject_program
            WHERE program_id=:program_id
            AND semester=:semester
            AND course_level=:course_level
            ");

        $sql->bindValue(":program_id", $student_program_id);
        $sql->bindValue(":semester", $current_semester);
        $sql->bindValue(":course_level", $student_course_level);
        $sql->execute();
        if($sql->rowCount() > 0){

            $result = $sql->fetchAll(PDO::FETCH_ASSOC);

        }
        $student_current_subject = $this->GetStudentCurrentSubjectLoadInCurrentSemester($student_username);
        if($student_current_subject != null){

            print_r($student_current_subject);

        }

    }
    #
    public function GetStudentCurrentSubjectLoadInCurrentSemester($student_username){

        $student_course_id = $this->studentEnroll->GetStudentCourseId($student_username);
        $student_program_id = $this->studentEnroll->GetStudentProgramId($student_course_id);
        $school_year_obj = $this->studentEnroll->GetActiveSchoolYearAndSemester();

        $student_course_level = $this->studentEnroll->GetStudentCourseLevel($student_username);

        $current_semester = $school_year_obj['period'];
        
        $sql = $this->con->prepare("SELECT subject_title FROM subject_program
            WHERE program_id=:program_id
            AND semester=:semester
            AND course_level=:course_level
            ");

        $arr = [];

        $sql->bindValue(":program_id", $student_program_id);
        $sql->bindValue(":semester", $current_semester);
        $sql->bindValue(":course_level", $student_course_level);
        $sql->execute();
        if($sql->rowCount() > 0){

            $result = $sql->fetchAll(PDO::FETCH_ASSOC);
            return $result;
            
        }
        return null;
    }
    
    public function GetStudentSubjectLoad($student_username){

        $student_course_id = $this->studentEnroll->GetStudentCourseId($student_username);
        $student_program_id = $this->studentEnroll->GetStudentProgramId($student_course_id);
        $school_year_obj = $this->studentEnroll->GetActiveSchoolYearAndSemester();

        $student_course_level = $this->studentEnroll->GetStudentCourseLevel($student_username);

        $current_semester = $school_year_obj['period'];
        
        $sql = $this->con->prepare("SELECT subject_title FROM subject_program
            WHERE program_id=:program_id
            AND semester=:semester
            AND course_level=:course_level
            ");

        $arr = [];

        $sql->bindValue(":program_id", $student_program_id);
        $sql->bindValue(":semester", $current_semester);
        $sql->bindValue(":course_level", $student_course_level);
        $sql->execute();
        if($sql->rowCount() > 0){

            $result = $sql->fetchAll(PDO::FETCH_ASSOC);
            return $result;
            
        }
        return null;
    }
    public function GenerateEnrollmentFormId(){

        // $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        // $length = 6;
        // $enrollmentFormId = substr(str_shuffle($characters), 0, $length);

        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $length = 6;
        $maxAttempts = 100;

        // $sql = $this->con->prepare("SELECT * FROM enrollment
        //     WHERE enrollment_form_id=:enrollment_form_id");

        // $sql->bindValue(":enrollment_form_id", $enrollmentFormId);
        // $sql->execute();

        // if($sql->rowCount() > 0){

        //     # Changed the enrollment_form_id
        // }

        $enrollmentFormId = $this->generateUniqueId($characters, $length);
        $attempt = 1;

        while ($this->isUniqueIdExists($enrollmentFormId) 
            && $attempt <= $maxAttempts) {
            $enrollmentFormId = $this->generateUniqueId($characters, $length);
            $attempt++;
        }

        if ($attempt > $maxAttempts) {
            // Maximum attempts reached, handle the error accordingly
            // You can throw an exception, display an error message, or take any other desired action
            
        }

        return $enrollmentFormId;
    }

    function generateUniqueId($characters, $length) {
        return substr(str_shuffle($characters), 0, $length);
    }   

    function isUniqueIdExists($enrollmentFormId) {
        // Check if the enrollment form ID already exists in the database
        $sql = $this->con->prepare("SELECT * FROM enrollment 
            WHERE enrollment_form_id = :enrollment_form_id");
        $sql->bindValue(":enrollment_form_id", $enrollmentFormId);
        $sql->execute();

        return $sql->rowCount() > 0;
    }

    function GetEnrollmentId($student_id, $course_id, $school_year_id) {
        // Check if the enrollment form ID already exists in the database
        $sql = $this->con->prepare("SELECT enrollment_id FROM enrollment 
            WHERE course_id = :course_id
            AND student_id = :student_id
            AND school_year_id = :school_year_id
            
            ");
        $sql->bindValue(":course_id", $course_id);
        $sql->bindValue(":student_id", $student_id);
        $sql->bindValue(":school_year_id", $school_year_id);
        $sql->execute();
        
        return $sql->fetchColumn();
    }

    function GetEnrollmentFormId($student_id, $course_id, $school_year_id) {
        // Check if the enrollment form ID already exists in the database
        $sql = $this->con->prepare("SELECT enrollment_form_id FROM enrollment 
            WHERE course_id = :course_id
            AND student_id = :student_id
            AND school_year_id = :school_year_id
            
            ");
        $sql->bindValue(":course_id", $course_id);
        $sql->bindValue(":student_id", $student_id);
        $sql->bindValue(":school_year_id", $school_year_id);
        $sql->execute();

        return $sql->fetchColumn();
    }

    function MarkAsRegistrarEvaluatedByEnrollmentId($enrollment_id) {

        $registrar_evaluated = "yes";
        // Check if the enrollment form ID already exists in the database
        $sql = $this->con->prepare("UPDATE enrollment 
            SET registrar_evaluated=:registrar_evaluated
            WHERE enrollment_id = :enrollment_id");

        $sql->bindValue(":registrar_evaluated", $registrar_evaluated);
        $sql->bindValue(":enrollment_id", $enrollment_id);
        
        return $sql->execute();
    }

    function MarkAsCashierEvaluatedByEnrollmentId($enrollment_id) {

        $cashier_evaluated = "yes";
        // Check if the enrollment form ID already exists in the database
        $sql = $this->con->prepare("UPDATE enrollment 
            SET cashier_evaluated=:cashier_evaluated
            WHERE enrollment_id = :enrollment_id");

        $sql->bindValue(":cashier_evaluated", $cashier_evaluated);
        $sql->bindValue(":enrollment_id", $enrollment_id);
        
        return $sql->execute();
    }

    function UpdateSHSStudentEnrollmentCourseId($enrollment_id,
        $course_id){

        $sql = $this->con->prepare("UPDATE enrollment 
            SET course_id=:course_id
            WHERE enrollment_id = :enrollment_id");

        $sql->bindValue(":course_id", $course_id);
        $sql->bindValue(":enrollment_id", $enrollment_id);
        
        return $sql->execute();
    }
}


?>