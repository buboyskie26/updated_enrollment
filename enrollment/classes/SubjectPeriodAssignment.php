<?php

    class SubjectPeriodAssignment{

    private $con, $subject_period_assignment_id, $sqlData;
   
    public function __construct($con, $subject_period_assignment_id){
        $this->con = $con;
        $this->subject_period_assignment_id = $subject_period_assignment_id;

        $query = $this->con->prepare("SELECT t1.*, t2.subject_id 
        
                FROM subject_period_assignment as t1

                INNER JOIN subject_period as t2 ON t2.subject_period_id = t1.subject_period_id

                WHERE t1.subject_period_assignment_id=:subject_period_assignment_id");

        $query->bindValue(":subject_period_assignment_id", $subject_period_assignment_id);
        $query->execute();

        $this->sqlData = $query->fetch(PDO::FETCH_ASSOC);
    }


    public function GetAssignmentName() {
        return isset($this->sqlData['assignment_name']) ? $this->sqlData["assignment_name"] : ""; 
    }


    public function GetSubjectId() {
        return isset($this->sqlData['subject_id']) ? $this->sqlData["subject_id"] : ""; 
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

    
}
?>