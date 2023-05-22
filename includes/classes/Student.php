<?php

class Student{

    private $con, $sqlData;

    public function __construct($con, $input)
    {
        $this->con = $con;
        $this->sqlData = $input;

        // echo "hey";
        // print_r($input);
        if(!is_array($input)){
            $query = $this->con->prepare("SELECT * FROM student
            WHERE username=:username");

            $query->bindValue(":username", $input);
            $query->execute();

            $this->sqlData = $query->fetch(PDO::FETCH_ASSOC);
        }
    }

    public function GetId() {
        return isset($this->sqlData['student_id']) ? $this->sqlData["student_id"] : 0; 
    }
    public function GetUsername() {
        return isset($this->sqlData['username']) ? $this->sqlData["username"] : ""; 
    }
    public function GetEmail() {
        return isset($this->sqlData['email']) ? $this->sqlData["email"] : ""; 
    }
    public function GetFirstName() {
        return isset($this->sqlData['firstname']) ? $this->sqlData["firstname"] : ""; 
    }
    public function GetStudentAddress() {
        return isset($this->sqlData['address']) ? $this->sqlData["address"] : "N/A"; 
    }
    public function GetName() {
        // return isset($this->sqlData['firstname']) ? $this->sqlData["firstname"] ." " . $this->sqlData["lastname"]: "N/A"; 
    return isset($this->sqlData['firstname']) ? ucfirst(strtolower($this->sqlData["firstname"])) ." " . $this->sqlData["lastname"]: "N/A";

    }
    public function GetGuardianName() {
        return isset($this->sqlData['guardian_name']) ? $this->sqlData["guardian_name"] : "N/A"; 

    }
    public function GetGuardianNameContact() {
        return isset($this->sqlData['guardian_contact_number']) ? $this->sqlData["guardian_contact_number"] : "N/A"; 

    } 

    public function GetContactNumber() {
        return isset($this->sqlData['contact_number']) ? $this->sqlData["contact_number"] : "N/A"; 

    } 

    public function GetStudentUniqueId(){

        return isset($this->sqlData['student_unique_id']) ? $this->sqlData["student_unique_id"] : "N/A"; 
    }

    public function ResetPassword($student_username){

        $array = [];
        $new_password =  $this->generate_random_password();

        // Hash the new password
        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

        // Update the student's password in the database
        $query = $this->con->prepare("UPDATE student 
            SET password=:password
            WHERE username=:username
            ");
        $query->bindValue(":password", $hashed_password);
        $query->bindValue(":username", $student_username);

        if($query->execute()){
            echo "<br>";
            echo "Temporary Password: $new_password";
            echo "<br>";

            // Sent via email
            // return $new_password;
            array_push($array, $new_password);
            array_push($array, true);
        }
        return $array;
    }
    private function generate_random_password($length = 8) {
    $password = '';

    try {
        // Generate a string of random bytes
        $bytes = random_bytes($length);
        // Convert the random bytes to a string of ASCII characters
        $password = bin2hex($bytes);
    } catch (Exception $e) {
        // Handle the exception if the random_bytes() function fails
        // For example, you can fallback to using the original function
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        for ($i = 0; $i < $length; $i++) {
            $password .= $characters[rand(0, strlen($characters) - 1)];
        }
    }

    return $password;
}
}
?>