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
}
?>