<?php 

class Course{

    private $con, $studentEnroll;

    public function __construct($con, $studentEnroll)
    {
        $this->con = $con;
        $this->studentEnroll = $studentEnroll;
    }

    public function CheckSectionPopulatedBySubject($course_id){


        $sql = $this->con->prepare("SELECT * FROM subject
            WHERE course_id=:course_id
            LIMIT 1");

        $sql->bindValue(":course_id", $course_id);
        $sql->execute();

        if($sql->rowCount() > 0){
            return true;
        }
        return false;
    }

    public function CheckIfSectionIsFull($course_id){

        $sql = $this->con->prepare("SELECT capacity, program_section FROM course
            WHERE course_id=:course_id
            LIMIT 1");

        $sql->bindValue(":course_id", $course_id);
        $sql->execute();


        if($sql->rowCount() > 0){
            $row = $sql->fetch(PDO::FETCH_ASSOC);
            $capacity = $row['capacity'];
            // $total_student = $row['total_student'];
            $program_section = $row['program_section'];
        }

        $school_year_obj = $this->GetActiveSchoolYearAndSemester();

        // 1st sem
        // Course id 1st sem -> enrolled student
        // Course id 2nd sem -> enrolled student

        $current_period = $school_year_obj['period'];
        $current_school_year_term = $school_year_obj['term'];
        
        // $enrollment_status = "enrolled";
        $enrollment_status = "tentative";


        $query = "SELECT sy.*
                FROM enrollment e
                JOIN school_year sy ON e.school_year_id = sy.school_year_id
                WHERE sy.period = 'First'
                AND sy.term = :current_school_year_term
                AND e.course_id = :course_id
                AND e.enrollment_status=:enrollment_status
                " ;

        $query = $this->con->prepare($query);

        $query->bindValue(":current_school_year_term", $current_school_year_term);
        $query->bindValue(":course_id", $course_id);
        $query->bindValue(":enrollment_status", $course_id);
        $query->execute();

        if($query->rowCount() > 0){

            $row = $query->fetchAll(PDO::FETCH_ASSOC);
            print_r($row);
        }


        $enrollment_course = $this->con->prepare("SELECT * FROM enrollment
            WHERE course_id=:course_id
            AND enrollment_status=:enrollment_status

            LIMIT 1");

        $enrollment_course->bindValue(":course_id", $course_id);
        $enrollment_course->bindValue(":enrollment_status", $enrollment_status);
        $enrollment_course->execute();

        if($enrollment_course->rowCount() > 0){

            $enrollment_row = $enrollment_course->fetch(PDO::FETCH_ASSOC);

            

        }

    }

    public function GetCourseAvailableSelectionForCurrentSY(){

        $school_year_obj = $this->GetActiveSchoolYearAndSemester();


        // $current_period = $school_year_obj['period'];
        $current_school_year_term = $school_year_obj['term'];
        $active = "yes";
        $query = $this->con->prepare("SELECT * FROM course
            WHERE school_year_term=:school_year_term
            AND active=:active
            ORDER BY program_section ASC
        ");
        $query->bindValue(":school_year_term", $current_school_year_term);
        $query->bindValue(":active", $active);
        $query->execute();

        if($query->rowCount() > 0){
            $html = "<div class='form-group'>
                <select style='width: 35%;' class='form-control' name='create_student_course_id' id='create_student_course_id'>
                <option value=''selected>Select Section</option>
                ";
                
            while($row = $query->fetch(PDO::FETCH_ASSOC)){
                $html .= "
                    <option value='".$row['course_id']."'>".$row['program_section']."</option>
                ";
            }

            $html .= "</select>
                    </div>";
            return $html;
        }
        return "";
    }

    public function GetStudentCourse($username){

        $school_year_obj = $this->GetActiveSchoolYearAndSemester();

        $studentObj = $this->studentEnroll->GetStudentCourseLevelYearIdCourseId($username);
        $student_course_id = $studentObj['course_id'];
        $student_course_level = $studentObj['course_level'];
 
        $current_school_year_term = $school_year_obj['term'];
        $active = "yes";


        // echo $student_course_level;

        // If returnee, it should reflected all the sections related 
        // to his course_level
        $query = $this->con->prepare("SELECT * FROM course

            WHERE school_year_term=:school_year_term
            AND active=:active
            AND course_level=:course_level
            ORDER BY program_section ASC
        ");
        $query->bindValue(":school_year_term", $current_school_year_term);
        $query->bindValue(":active", $active);
        $query->bindValue(":course_level", $student_course_level);
        $query->execute();

        if($query->rowCount() > 0){
            $html = "<div class='form-group'>
                <select class='form-control' name='course_id'>";

            $check_course = $this->con->prepare("SELECT program_section FROM course
                    WHERE course_id=:course_id
                    LIMIT 1");

            while($row = $query->fetch(PDO::FETCH_ASSOC)){

                $check_course->bindValue(":course_id", $student_course_id);
                $check_course->execute();
                $match= "";
                if($check_course->rowCount() > 0){
                    $match = $check_course->fetchColumn();
                    // echo $match;
                }
                // Check if current course_id matches the default value
                $selected = ($row['program_section'] == $match) ? 'selected' : ''; 
                // $selected = "";
                $html .= "
                    <option value='".$row['course_id']."' $selected>".$row['program_section']."</option>
                ";
            }
            $html .= "</select>
                    </div>";
            return $html;
        }else{
            // echo "did not get GetStudentCourse()";

        }
        return null;
    }
    public function GetStudentSectionLevel($student_course_id){

        $query = $this->con->prepare("SELECT course_level FROM course
            WHERE course_id=:course_id
            LIMIT 1
        ");

        $query->bindValue(":course_id", $student_course_id);
        $query->execute();

        if($query->rowCount() > 0){
            return $query->fetchColumn();
        }
        return -1;
    }
    public function GetStudentCourseLevel($username){

        $query = $this->con->prepare("SELECT course_level FROM student
            WHERE username=:username
            LIMIT 1
        ");

        $query->bindValue(":username", $username);
        $query->execute();

        if($query->rowCount() > 0){
            $match = $query->fetchColumn();
            // echo $match;
            $html = "<div class='form-group'>
                <select class='form-control' name='student_course_level'>";
 
            // <option value="11">Grade 11</option>
            // <option value="12">Grade 12</option> 
            while($row = $query->fetch(PDO::FETCH_ASSOC)){
           
            }

            $array = array(11, 12);

            foreach ($array as $key => $value) {
                $selected = ($value == $match) ? 'selected' : ''; 
                
                // echo $selected;
                $html .= "
                    <option value='".$value."' $selected>Grade $value</option>
                ";
            }
            
            $html .= "</select>
                </div>";
            return $html;
        }
        return null;
    }
    public function GetCourseCourseLevel($course_id){

        $school_year_obj = $this->GetActiveSchoolYearAndSemester();


        // $current_period = $school_year_obj['period'];
        $current_school_year_term = $school_year_obj['term'];

        $query = $this->con->prepare("SELECT course_level FROM course
            WHERE school_year_term=:school_year_term
            AND course_id=:course_id
            ORDER BY program_section ASC
        ");
        $query->bindValue(":school_year_term", $current_school_year_term);
        $query->bindValue(":course_id", $course_id);
        $query->execute();

        if($query->rowCount() > 0){

            return $query->fetchColumn();
        }
        return null;

     }
    private function GetActiveSchoolYearAndSemester(){

        $query = $this->con->prepare("SELECT school_year_id,
            term, period

            FROM school_year
            WHERE statuses='Active'
            -- ORDER BY school_year_id DESC
            LIMIT 1");

        $query->execute();
        
        return $query->fetch(PDO::FETCH_ASSOC);
    }
}
?>