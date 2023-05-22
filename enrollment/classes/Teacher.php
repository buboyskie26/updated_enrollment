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
        }

        public function GetTeacherFirstName() {
            return isset($this->sqlData['firstname']) ? $this->sqlData["firstname"] : "qwe"; 
        }

        public function GetTeacherLastName() {
            return isset($this->sqlData['lastname']) ? $this->sqlData["lastname"] : ""; 
        }

        public function GetTeacherFullName() {
           $firstname = $this->GetTeacherFirstName();
           $lastname = $this->GetTeacherLastName();

           return $firstname . " " . $lastname;
        }
    }
?>