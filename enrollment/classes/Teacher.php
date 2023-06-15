<?php

    class Teacher{

        private $con, $teacher_id, $sqlData;


        public function __construct($con, $teacher_id){
            $this->con = $con;
            $this->teacher_id = $teacher_id;

            $query = $this->con->prepare("SELECT * FROM teacher
                 WHERE teacher_id=:teacher_id");

            $query->bindValue(":teacher_id", $teacher_id);
            $query->execute();

            $this->sqlData = $query->fetch(PDO::FETCH_ASSOC);

            if($this->sqlData == null){

                $query = $this->con->prepare("SELECT * FROM teacher
                 WHERE username=:username");

                $query->bindValue(":username", $teacher_id);
                $query->execute();

                $this->sqlData = $query->fetch(PDO::FETCH_ASSOC);
            }
        }

        public function GetTeacherId() {
            return isset($this->sqlData['teacher_id']) ? $this->sqlData["teacher_id"] : "qwe"; 
        }

        public function GetTeacherFirstName() {
            return isset($this->sqlData['firstname']) ? $this->sqlData["firstname"] : "qwe"; 
        }

        public function GetTeacherLastName() {
            return isset($this->sqlData['lastname']) ? $this->sqlData["lastname"] : ""; 
        }
        public function GetStatus() {
            return isset($this->sqlData['teacher_status']) ? $this->sqlData["teacher_status"] : ""; 
        }     

        public function GetDepartmentId() {
            return isset($this->sqlData['department_id']) ? $this->sqlData["department_id"] : ""; 
        }  

        public function GetCreation() {
            return isset($this->sqlData['date_creation']) ? $this->sqlData["date_creation"] : ""; 
        }  
                
        public function GetDepartmentName() {

            $department_id = $this->GetDepartmentId();
            $sql = $this->con->prepare("SELECT department_name FROM department
                WHERE department_id=:department_id");
            
            $sql->bindValue(":department_id", $department_id);
            $sql->execute();
            if($sql->rowCount() > 0){

                return $sql->fetchColumn();
            }
            return "N/A";

        } 

        public function GetTeacherFullName() {
           $firstname = $this->GetTeacherFirstName();
           $lastname = $this->GetTeacherLastName();

           return $firstname . " " . $lastname;
        }
    }
?>