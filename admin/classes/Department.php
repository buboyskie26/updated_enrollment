<?php

class Department{

    private $con, $sqlData;

    public function __construct($con, $input)
    {
        $this->con = $con;
        $this->sqlData = $input;

        // echo "hey";
        // print_r($input);
        if(!is_array($input)){
            $query = $this->con->prepare("SELECT * FROM department
            WHERE department_id=:department_id");

            $query->bindValue(":department_id", $input);
            $query->execute();

            $this->sqlData = $query->fetch(PDO::FETCH_ASSOC);
        }
    }

    public function GetDepartmentName() {
        return isset($this->sqlData['department_name']) ? $this->sqlData["department_name"] : ""; 
    }
}
?>