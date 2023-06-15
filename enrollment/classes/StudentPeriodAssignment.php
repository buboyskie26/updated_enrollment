<?php

    class StudentPeriodAssignment{

    private $con, $student_period_assignment_id, $sqlData;
   
    public function __construct($con, $student_period_assignment_id){
        $this->con = $con;
        $this->student_period_assignment_id = $student_period_assignment_id;

        $query = $this->con->prepare("SELECT * FROM student_period_assignment
                WHERE student_period_assignment_id=:student_period_assignment_id");

        $query->bindValue(":student_period_assignment_id", $student_period_assignment_id);
        $query->execute();

        $this->sqlData = $query->fetch(PDO::FETCH_ASSOC);
    }


    public function GetFileName() {
        return isset($this->sqlData['file_name']) ? $this->sqlData["file_name"] : ""; 
    }

    
    public function GetAllStudentSubmitted($subject_period_assignment_id) {
    
        $sql = $this->con->prepare("SELECT DISTINCT t1.student_id

            FROM student_period_assignment as t1

            -- INNER JOIN student as t3 ON t3.student_id = t1.student_id
            WHERE t1.subject_period_assignment_id=:subject_period_assignment_id
        ");

        $sql->bindValue(":subject_period_assignment_id", $subject_period_assignment_id);
        $sql->execute();
        
        return $sql->rowCount();
    }

    public function GetAllMyStudent($subject_id, $school_year_id) {
    
        $sql = $this->con->prepare("SELECT t1.student_subject_id

            FROM student_subject as t1

            INNER JOIN student as t2 ON t2.student_id = t1.student_id
            WHERE t1.subject_id=:subject_id
            AND t1.school_year_id=:school_year_id

        ");

        $sql->bindValue(":subject_id", $subject_id);
        $sql->bindValue(":school_year_id", $school_year_id);

        $sql->execute();
        
        return $sql->rowCount();
    }

    public function CheckIfSubmitted($subject_period_assignment_id, $student_id) {
    
        $sql = $this->con->prepare("SELECT t1.*

            FROM student_assignment_grade as t1

            
            WHERE t1.subject_period_assignment_id=:subject_period_assignment_id
            AND t1.student_id=:student_id
            LIMIT 1
        ");

        $sql->bindValue(":subject_period_assignment_id", $subject_period_assignment_id);
        $sql->bindValue(":student_id", $student_id);

        $sql->execute();
        
        return $sql->rowCount();
    }

    public function CheckIfGraded($subject_period_assignment_id, $student_id) {
    
        $sql = $this->con->prepare("SELECT t1.*

            FROM student_assignment_grade as t1

            
            WHERE t1.subject_period_assignment_id=:subject_period_assignment_id
            AND t1.student_id=:student_id
            AND t1.grade > 0
            LIMIT 1
        ");

        $sql->bindValue(":subject_period_assignment_id", $subject_period_assignment_id);
        $sql->bindValue(":student_id", $student_id);

        $sql->execute();
        
        return $sql->rowCount() > 0;
    }

    
}
?>