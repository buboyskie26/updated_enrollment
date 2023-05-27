<?php

    class StudentSubject{

    private $con, $userLoggedIn, $sqlData;
   
    public function __construct($con){
        $this->con = $con;
    }


    public function InsertStudentSubject($student_id, $subject_id, $enrollment_id,
        $course_level, $subject_program_id, $current_school_year_id, $is_final){

        $insert = $this->con->prepare("INSERT INTO student_subject
            (student_id, subject_id , enrollment_id,
                course_level,school_year_id, is_final, subject_program_id)
            VALUES (:student_id, :subject_id , :enrollment_id,
                :course_level,:school_year_id, :is_final, :subject_program_id)");

        $insert->bindValue("student_id", $student_id);
        $insert->bindValue("subject_id", $subject_id);
        $insert->bindValue("enrollment_id", $enrollment_id);
        $insert->bindValue("course_level", $course_level);
        $insert->bindValue("subject_program_id", $subject_program_id);
        $insert->bindValue("school_year_id", $current_school_year_id);
        $insert->bindValue("is_final", $is_final);

        return $insert->execute();
    }

    public function GetNonFinalizedStudentSubject($student_id, $subject_id,
        $enrollment_id, $school_year_id){

        $sql = $this->con->prepare("SELECT * FROM student_subject as t1

            -- INNER JOIN subject as t2 ON t2.subject_id = t1.subject_id

            WHERE t1.student_id=:student_id
            AND t1.subject_id=:subject_id
            AND t1.enrollment_id=:enrollment_id
            AND t1.school_year_id=:school_year_id
            LIMIT 1
            ");

        $sql->bindValue("student_id", $student_id);
        $sql->bindValue("subject_id", $subject_id);
        $sql->bindValue("enrollment_id", $enrollment_id);
        $sql->bindValue("school_year_id", $school_year_id);
        $sql->execute();

        if($sql->rowCount() > 0){

            return $sql->fetch(PDO::FETCH_ASSOC);
        }

        return [];
    }

    public function CheckStudentSubject($student_id, $subject_id,
        $enrollment_id, $school_year_id){

        $sql = $this->con->prepare("SELECT * FROM student_subject as t1

            -- INNER JOIN subject as t2 ON t2.subject_id = t1.subject_id
            WHERE t1.student_id=:student_id
            AND t1.subject_id=:subject_id
            AND t1.enrollment_id=:enrollment_id
            AND t1.school_year_id=:school_year_id
            LIMIT 1
            ");

        $sql->bindValue("student_id", $student_id);
        $sql->bindValue("subject_id", $subject_id);
        $sql->bindValue("enrollment_id", $enrollment_id);
        $sql->bindValue("school_year_id", $school_year_id);
        $sql->execute();

        $isThere = false;
        if($sql->rowCount() > 0){
            // echo "Subject ID: $subject_id already there";
            // exit();
            $isThere = true;

        }else{
            // echo "not there";
            $isThere = false;
        }
        return $isThere;
    }

    public function CheckIfStudentInsertedSubject($student_id,
        $enrollment_id, $school_year_id){

        $sql = $this->con->prepare("SELECT * FROM student_subject as t1

            -- INNER JOIN subject as t2 ON t2.subject_id = t1.subject_id
            WHERE t1.student_id=:student_id
            AND t1.enrollment_id=:enrollment_id
            AND t1.school_year_id=:school_year_id
            LIMIT 1
            ");

        $sql->bindValue("student_id", $student_id);
        $sql->bindValue("enrollment_id", $enrollment_id);
        $sql->bindValue("school_year_id", $school_year_id);
        $sql->execute();

        $isThere = false;
        if($sql->rowCount() > 0){

            // echo "Subject ID: $subject_id already there";
            // exit();
            $isThere = true;

        }else{
            // echo "not there";
            $isThere = false;
        }
        return $isThere;
    }


    public function CheckIfPrevSemesterSubjectsRemarked($student_id, $course_id,
        $prev_sy_id){

        $isEligible = false;

        # Get All previous Subjects.
        # Get All student_subject_grade

        # Count should be matched for it was eligible to Apply Next Semester.

        $get_student_prev_enrollment_id = $this->con->prepare("SELECT 
            enrollment_id 
        
            FROM enrollment

            WHERE student_id=:student_id
            AND course_id=:course_id
            AND enrollment_status=:enrollment_status
            AND school_year_id=:school_year_id
            LIMIT 1");


        $get_student_prev_enrollment_id->bindValue(":student_id", $student_id);
        $get_student_prev_enrollment_id->bindValue(":course_id", $course_id);
        $get_student_prev_enrollment_id->bindValue(":enrollment_status", "enrolled");
        $get_student_prev_enrollment_id->bindValue(":school_year_id", $prev_sy_id);
        $get_student_prev_enrollment_id->execute();


        $student_subject_array = [];
        $student_subject_grade_array = [];

        if($get_student_prev_enrollment_id->rowCount() > 0){

            $enrollment_id = $get_student_prev_enrollment_id->fetchColumn();

            // echo $enrollment_id;
            
            $get_student_subject = $this->con->prepare("SELECT 

                student_subject_id 
            
                FROM student_subject

                WHERE enrollment_id=:enrollment_id
                AND student_id=:student_id

                ");

            $get_student_subject->bindValue(":enrollment_id", $enrollment_id);
            $get_student_subject->bindValue(":student_id", $student_id);
            $get_student_subject->execute();

            if($get_student_subject->rowCount() > 0){

                while($row = $get_student_subject->fetch(PDO::FETCH_ASSOC)){

                    $student_subject_id = $row['student_subject_id'];

                    array_push($student_subject_array, $row['student_subject_id']);

                    $get_student_subject_grade = $this->con->prepare("SELECT 

                        student_subject_id 
                    
                        FROM student_subject_grade

                        WHERE student_subject_id=:student_subject_id
                        AND student_id=:student_id
                        LIMIT 1");
                    
                    $get_student_subject_grade->bindValue(":student_subject_id", $student_subject_id);
                    $get_student_subject_grade->bindValue(":student_id", $student_id);
                    $get_student_subject_grade->execute();

                    if($get_student_subject_grade->rowCount() > 0){

                        array_push($student_subject_grade_array, $row['student_subject_id']);

                    }


                    
                }

                // print_r($student_subject_array);
                // print_r($student_subject_grade_array);
            }

            if(count($student_subject_array) === count($student_subject_grade_array)){
                $isEligible = true;

            }


            return $isEligible;

        }

    }

    public function GetPrevSYID($current_semester_period, $current_term){

        $previous_sy_id = -1;

        if($current_semester_period == "Second"){

            // GET the First Period.
            $semester_to_get = "First";

            $get_prev_sy = $this->con->prepare("SELECT school_year_id 
            
                FROM school_year
                WHERE period=:period
                AND term=:term
                ORDER BY school_year_id DESC
                LIMIT 1
                ");
            
            $get_prev_sy->bindValue(":period", $semester_to_get);
            $get_prev_sy->bindValue(":term", $current_term);
            $get_prev_sy->execute();

            if($get_prev_sy->rowCount() > 0){
                $prev_sy_id = $get_prev_sy->fetchColumn();

                $previous_sy_id = $prev_sy_id;
            }
        }
        
        if($current_semester_period == "First"){

            // GET the First Period.
            $semester_to_get = "Second";

            $years = explode("-", $current_term);
            $startYear = intval($years[0]); // Convert the start year to an integer
            $endYear = intval($years[1]); // Convert the end year to an integer

            // Calculate the previous school year
            $previousStartYear = $startYear - 1;
            $previousEndYear = $endYear - 1;

            $current_term = $previousStartYear . "-" . $previousEndYear;

            $get_prev_sy = $this->con->prepare("SELECT school_year_id 
            
                FROM school_year
                WHERE period=:period
                AND term=:term
                ORDER BY school_year_id DESC
                LIMIT 1
                ");
            
            $get_prev_sy->bindValue(":period", $semester_to_get);
            $get_prev_sy->bindValue(":term", $current_term);
            $get_prev_sy->execute();

            if($get_prev_sy->rowCount() > 0){
                $prev_sy_id = $get_prev_sy->fetchColumn();

                // $previous_sy_id = $prev_sy_id;
                $previous_sy_id = $prev_sy_id;

            }
        }

        return $previous_sy_id;

    }
}
?>