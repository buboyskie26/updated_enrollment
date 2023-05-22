<?php

    class Section{

        private $con, $course_id, $sqlData;


        public function __construct($con, $course_id){
            $this->con = $con;
            $this->course_id = $course_id;

            $query = $this->con->prepare("SELECT * FROM course
                 WHERE course_id=:course_id");

            $query->bindValue(":course_id", $course_id);
            $query->execute();

            $this->sqlData = $query->fetch(PDO::FETCH_ASSOC);
        }

        public function GetCourseId() {
            return isset($this->sqlData['course_id']) ? $this->sqlData["course_id"] : ""; 
        }
        public function GetSectionName() {
            return isset($this->sqlData['program_section']) ? $this->sqlData["program_section"] : ""; 
        }
        public function GetSectionGradeLevel() {
            return isset($this->sqlData['course_level']) ? $this->sqlData["course_level"] : ""; 
        }
        public function GetSectionSY() {
            return isset($this->sqlData['school_year_term']) ? $this->sqlData["school_year_term"] : ""; 
        }
        public function GetGuardianName() {
            return isset($this->sqlData['guardian_name']) ? $this->sqlData["guardian_name"] : ""; 
        }
        public function GetGuardianContact() {
            return isset($this->sqlData['guardian_contact_number']) ? $this->sqlData["guardian_contact_number"] : ""; 
        }


        // public function GetSectionPeriod() {
        //     return isset($this->sqlData['program_section']) ? $this->sqlData["program_section"] : ""; 
        // }
        public function GetSectionAdvisery() {

            $adviser_id =isset($this->sqlData['adviser_teacher_id']) ? $this->sqlData["adviser_teacher_id"] : null;
    
            // echo $adviser_id;
            $sql = $this->con->prepare("SELECT firstname, lastname FROM teacher
                WHERE teacher_id=:teacher_id
                LIMIT 1");

            $sql->bindValue(":teacher_id", $adviser_id);
            $sql->execute();
            if($sql->rowCount() > 0){
                $row = $sql->fetch(PDO::FETCH_ASSOC);
                return $row['firstname'] . " " .$row['lastname'];
            }else{

                return "No Adviser Yet";
            }
        }

        public function createForm($subject_id){

            $course_id = $this->GetCourseId();

            if(isset($_POST['edit_subject_program']) 
                && isset($_POST['subject_code']) && isset($_POST['subject_title'])){

                $subject_code = $_POST['subject_code'];
                $subject_title = $_POST['subject_title'];


                $edit_subject = $this->con->prepare("UPDATE subject
                    SET subject_code=:subject_code,
                        subject_title=:subject_title

                    WHERE subject_id=:subject_id
                    AND course_id=:course_id
                    ");
                $edit_subject->bindValue(":subject_code", $subject_code); 
                $edit_subject->bindValue(":subject_title", $subject_title); 
                $edit_subject->bindValue(":subject_id", $subject_id); 
                $edit_subject->bindValue(":course_id", $course_id); 

                if($edit_subject->execute()){

                } 

            }
   
            $subject_query = $this->con->prepare("SELECT * FROM subject
                WHERE subject_id=:subject_id
                AND course_id=:course_id
                LIMIT 1");

            $subject_query->bindValue(":subject_id", $subject_id); 
            $subject_query->bindValue(":course_id", $course_id); 
            $subject_query->execute(); 

            $createProgramSelection = "";

            if($subject_query->rowCount() > 0){
                $row = $subject_query->fetch(PDO::FETCH_ASSOC);

                $subject_code = $row['subject_code'];
                $subject_title = $row['subject_title'];

                return "
                    <h4 class='text-center mb-3'>Edit Section Subject</h4>
                    <form method='POST'>

                        $createProgramSelection

                        <div class='form-group mb-2'>
                            <input class='form-control' value='$subject_code' type='text' placeholder='Subject Code' name='subject_code'>
                        </div>

                        <div class='form-group mb-2'>
                            <input class='form-control' value='$subject_title' type='text' placeholder='Subject Title' name='subject_title'>
                        </div>
                        <button type='submit' class='btn btn-primary' name='edit_subject_program'>Save</button>
                    </form>
                ";
            }
            
        }

        public function createProgramSelection(){

            $SHS_DEPARTMENT = 4;

            $query = $this->con->prepare("SELECT * FROM program
                WHERE department_id=:department_id
            ");

            $query->bindValue(":department_id", $SHS_DEPARTMENT);
            $query->execute();
            
            if($query->rowCount() > 0){

                $html = "<div class='form-group mb-2'>
                    <select id='program_id' class='form-control' name='program_id'>";

                $html .= "<option value='Course-Section' disabled selected>Select-Subject</option>";

                while($row = $query->fetch(PDO::FETCH_ASSOC)){
                    $html .= "
                        <option value='".$row['program_id']."'>".$row['program_name']."</option>
                    ";
                }
                $html .= "</select>
                        </div>";
                return $html;
            }
 
            return null;
        }

        public function editProgramSelection($program_id){

            $SHS_DEPARTMENT = 4;

            $query = $this->con->prepare("SELECT * FROM program
                WHERE department_id=:department_id
            ");

            $query->bindValue(":department_id", $SHS_DEPARTMENT);
            $query->execute();
            
            if($query->rowCount() > 0){

                $html = "<div class='form-group mb-2'>
                    <select id='program_id' class='form-control' name='program_id'>";

                $html .= "<option value='Course-Section' disabled selected>Select-Subject</option>";

                while($row = $query->fetch(PDO::FETCH_ASSOC)){

                    $selected = ($row['program_id'] == $program_id) ? 'selected' : '';

                    $html .= "
                        <option value='".$row['program_id']."' $selected>".$row['program_name']."</option>
                    ";
                    // echo "<option value='{$get_row['course_id']}' {$selected}>{$get_row['program_section']}</option>";

                }
                $html .= "</select>
                        </div>";
                return $html;
            }
 
            return null;
        }

        public function createSectionDropdown(){

            $school_year_obj = $this->GetActiveSchoolYearAndSemester();

            $current_school_year_term = $school_year_obj['term'];

            $query = $this->con->prepare("SELECT * FROM course
                WHERE school_year_term=:school_year_term
                AND active=:active
            ");
            $query->bindValue(":school_year_term", $current_school_year_term);
            $query->bindValue(":active", "yes");
            $query->execute();

            if($query->rowCount() > 0){

                $html = "<div class='form-group mb-2'>
                    <select id='course_id' class='form-control' name='course_id'>";

                $html .= "<option value='Course-Section' disabled selected>Select-Strand</option>";

                while($row = $query->fetch(PDO::FETCH_ASSOC)){
                    $html .= "
                        <option value='".$row['course_id']."'>".$row['program_section']."</option>
                    ";
                }

                $html .= "</select>
                        </div>";
                return $html;

            }
 
            return null;
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

        public function GetTotalNumberOfStudentInSection($course_id,
            $current_school_year_id){
        
            // Based on the enrollment.

            $query = $this->con->prepare("SELECT t1.enrollment_id, 
                t1.course_id,
                 t1.student_id
                -- t1.school_year_id 
                
                FROM enrollment as t1

                INNER JOIN course as t2 ON t2.course_id = t1.course_id

                WHERE t1.course_id=:course_id
                AND (t1.enrollment_status=:enrollment_status
                    OR t1.enrollment_status=:enrollment_status2)

                AND t1.school_year_id=:school_year_id
                ");

            $query->bindValue(":course_id", $course_id);
            $query->bindValue(":enrollment_status", "tentative");
            $query->bindValue(":enrollment_status2", "enrolled");
            $query->bindValue(":school_year_id", $current_school_year_id);
            $query->execute();

            return $query->rowCount();
            // if($query->rowCount() > 0){
            //     $asd = $query->fetchAll(PDO::FETCH_ASSOC);
            //     print_r($asd);
            //     echo "<br>";
            // }
        }

        public function GetSectionTotalSubjects($course_id){

            $query = $this->con->prepare("SELECT * FROM subject
                WHERE course_id=:course_id");

            $query->bindValue(":course_id", $course_id);
            $query->execute();
            return $query->rowCount();
        }
        public function GetSectionTotalScheduleSubjects($course_id){

            $query = $this->con->prepare("SELECT * FROM subject as t1

                INNER JOIN subject_schedule as t2 ON t2.subject_id = t1.subject_id
                WHERE t1.course_id=:course_id");

            $query->bindValue(":course_id", $course_id);
            $query->execute();
            return $query->rowCount();

        }

        public function AutoCreateAnotherSectionv2($row){

            // STEM11-A -> STEM11-B
            // STEM11-C -> STEM11-D

            $program_section = $row['program_section'];
            // $program_section = "HUMMS11-D";
            $program_id = $row['program_id'];
            $course_level = $row['course_level'];
            $capacity = $row['capacity'];
            $school_year_term = $row['school_year_term'];

            // $program_section = "F";

            if($program_section != ""){
                $last_letter = substr($program_section, -1);

                $next_letter = chr(ord($last_letter) + 1);

                // echo $next_letter;
                // echo "<br>";
                $prefix = substr($program_section, 0, -1);
                // echo $prefix;
                // echo $prefix . $next_letter;
                return $prefix . $next_letter;
            }
        }
        public function AutoCreateAnotherSection($program_section){

            // STEM11-A -> STEM11-B
            // STEM11-C -> STEM11-D

            // $program_section = $row['program_section'];
            
            if($program_section != ""){
                $last_letter = substr($program_section, -1);

                $next_letter = chr(ord($last_letter) + 1);

                // echo $next_letter;
                // echo "<br>";
                $prefix = substr($program_section, 0, -1);
                // echo $prefix;
                // echo $prefix . $next_letter;
                return $prefix . $next_letter;
            }
        }

        public function RemoveSection($course_id){

            $sql = $this->con->prepare("DELETE FROM course
                WHERE course_id=:course_id");
                
            $sql->bindValue(":course_id", $course_id);
            return $sql->execute();
        }


        public function GetSectionNameByProgramId($program_id){

            $sql = $this->con->prepare("SELECT program_section FROM course
                WHERE program_id=:program_id");
                
            $sql->bindValue(":program_id", $program_id);
            $sql->execute();

            if($sql->rowCount() > 0)
                return $sql->fetchColumn();
            
            return "N/A";
        }

        public function GetAcronymByProgramId($program_id){

            $sql = $this->con->prepare("SELECT acronym FROM program
                WHERE program_id=:program_id");
                
            $sql->bindValue(":program_id", $program_id);
            $sql->execute();

            if($sql->rowCount() > 0)
                return $sql->fetchColumn();
            
            return "N/A";
        }
    }
?>