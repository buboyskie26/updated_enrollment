<?php

    class Pending{

    private $con;

    public function __construct($con){
        $this->con = $con;
    }

    public function GetPendingFullname($username){

        $query = $this->con->prepare("SELECT firstname, lastname 
        
            FROM pending_enrollees
            WHERE firstname=:firstname");

        $query->bindValue(":firstname", $username);
        $query->execute();

        if($query->rowCount() > 0){

            $row = $query->fetch(PDO::FETCH_ASSOC);

            return $row['firstname'] . " " . $row['lastname'];
        }
        return "N/A";
    }
    public function PendingRegularFormSubmitv2($fname, $lname, $mi,
        $password, $email_address, $token){

        $expiration_time = strtotime("+5 minutes");
        $expiration_time = date('Y-m-d H:i:s', $expiration_time);

        $query = $this->con->prepare("INSERT INTO pending_enrollees 
            (firstname, lastname, middle_name, password, email, token, expiration_time) 
            VALUES (:firstname, :lastname, :middle_name, :password, :email, :token, :expiration_time)");
        
        $hash_password = password_hash($password, PASSWORD_BCRYPT);

        $query->bindValue(":firstname", $fname);
        $query->bindValue(":lastname", $lname);
        $query->bindValue(":middle_name", $mi);
        $query->bindValue(":password", $hash_password);
        $query->bindValue(":email", $email_address);
        $query->bindValue(":token", $token);
        $query->bindValue(":expiration_time", $expiration_time);

        $execute =  $query->execute();

        return $execute;
    }


    public function PendingRegularFormSubmit($fname, $lname, $mi,
            $sex, $b_day, $nationality, $s_contact,
            $civil_status, $guardian, $g_contact,
            $password, $program_id){
 
    // All new students shs would be grade 11 as default
        $default_course_level = 11;

        $query = $this->con->prepare("INSERT INTO pending_enrollees (firstname, lastname, middle_name,
            password, program_id, sex,
            birthday, age, nationality, contact_number,
            guardian_name, guardian_contact_number, civil_status, student_status) 

            VALUES(:firstname, :lastname,:middle_name,
            :password,:program_id, :sex,
            :birthday, :age, :nationality, :contact_number,
            :guardian_name, :guardian_contact_number, :civil_status, :student_status)");

                    
        $student_status = "Regular";

        $b_day = date('Y-m-d', strtotime($b_day));

        $age = $this->getCurrentAge($b_day);

        // This could not retrieve by php function, but it can be compared
        $hash_password = password_hash($password, PASSWORD_BCRYPT);

        // Based on the latest semester that was establish in the system.
        // $semester = "First";

        $query->bindValue(":firstname", $fname);
        $query->bindValue(":lastname", $lname);
        $query->bindValue(":middle_name", $mi);
        $query->bindValue(":password", $hash_password);
        $query->bindValue(":program_id", $program_id);

        $query->bindValue(":sex", $sex);
        $query->bindValue(":birthday", $b_day);
        $query->bindValue(":age", $age);
        $query->bindValue(":nationality", $nationality);
        $query->bindValue(":contact_number", $s_contact);
        $query->bindValue(":guardian_name", $guardian);
        $query->bindValue(":guardian_contact_number", $g_contact);
        $query->bindValue(":civil_status", $civil_status);
        $query->bindValue(":student_status", $student_status);

        $execute =  $query->execute();

        return $execute;
    }

    public function PendingTransfereeFormSubmit($fname, $lname, $mi,
            $sex, $b_day, $nationality, $s_contact,
            $civil_status, $guardian, $g_contact,
            $password, $program_id){
 
    // All new students shs would be grade 11 as default
        $default_course_level = 11;

        $query = $this->con->prepare("INSERT INTO pending_enrollees (firstname, lastname, middle_name,
            password, program_id, sex,
            birthday, age, nationality, contact_number,
            guardian_name, guardian_contact_number, civil_status, student_status) 

            VALUES(:firstname, :lastname,:middle_name,
            :password,:program_id, :sex,
            :birthday, :age, :nationality, :contact_number,
            :guardian_name, :guardian_contact_number, :civil_status, :student_status)");

                    
        $student_status = "Transferee";

        $b_day = date('Y-m-d', strtotime($b_day));

        $age = $this->getCurrentAge($b_day);

        // This could not retrieve by php function, but it can be compared
        $hash_password = password_hash($password, PASSWORD_BCRYPT);

        // Based on the latest semester that was establish in the system.
        // $semester = "First";

        $query->bindValue(":firstname", $fname);
        $query->bindValue(":lastname", $lname);
        $query->bindValue(":middle_name", $mi);
        $query->bindValue(":password", $hash_password);
        $query->bindValue(":program_id", $program_id);

        $query->bindValue(":sex", $sex);
        $query->bindValue(":birthday", $b_day);
        $query->bindValue(":age", $age);
        $query->bindValue(":nationality", $nationality);
        $query->bindValue(":contact_number", $s_contact);
        $query->bindValue(":guardian_name", $guardian);
        $query->bindValue(":guardian_contact_number", $g_contact);
        $query->bindValue(":civil_status", $civil_status);
        $query->bindValue(":student_status", $student_status);

        $execute =  $query->execute();

        return $execute;
    }

  
    public function GetSubmittedOn($firstname){

        $query = $this->con->prepare("SELECT date_creation 
        
            FROM pending_enrollees
            WHERE firstname=:firstname");

        $query->bindValue(":firstname", $firstname);
        $query->execute();

        if($query->rowCount() > 0){

            // $row = $query->fetch(PDO::FETCH_ASSOC);
            return $query->fetchColumn();
        }
        return "N/A";
    }
    private function getCurrentAge($b_day){

            $age = -1;
        
            $birthdate = $b_day;

            // Assume $birthdate is a string that contains the user's birthdate in the format 'YYYY-MM-DD'
            // $birthdate = '2000-01-01';

            // Create a DateTime object representing the user's birthdate
            $birth_date = new DateTime($birthdate);

            // Create a DateTime object representing the current date and time
            $current_date = new DateTime();

            // Calculate the difference between the two dates
            $interval = $current_date->diff($birth_date);

            // Get the user's age in years
            $age = $interval->y;

            return $age;
        }

        public function CreateRegisterStrand($program_id = null){

            $query = $this->con->prepare("SELECT * FROM program
                -- WHERE department_id != 1
                ");

            $query->execute();

            $html = "<div class='form-group'>
                        <select class='form-control' name='STRAND'>";
            $html .= " <option value='0' >Choose Strand</option>";
            if($query->rowCount() > 0){
                while($row = $query->fetch(PDO::FETCH_ASSOC)){

                    $row_program_id  = $row['program_id'];

                    if($row['program_name']){
                        // $program_name = "STEM";
                    }   
                    $selected = ($row_program_id == $program_id) ? 'selected' : ''; // Check if the row_program_id matches the provided program_id

                    $html .= "
                        <option $selected value='".$row['program_id']."'>".$row['program_name']."</option>
                    ";
                }
            }
            $html .= "</select>
                    </div>";

            return $html;
        }


        public function UpdatePendingNewStep1($student_status, $type,
            $program_id, $pending_enrollees_id){

            $query = $this->con->prepare("UPDATE pending_enrollees

                SET student_status=:student_status,
                    type=:type, program_id=:program_id
                
                WHERE pending_enrollees_id=:pending_enrollees_id");

            $query->bindValue(":pending_enrollees_id", $pending_enrollees_id);
            $query->bindValue(":student_status", $student_status);
            $query->bindValue(":type", $type);
            $query->bindValue(":program_id", $program_id);
            $execute =  $query->execute();
            
            return $execute;
        }

        public function UpdatePendingNewStep2($pending_enrollees_id, $firstname, 
            $middle_name, $lastName, $civil_status, $nationality, $sex,
            $birthday, $birthplace, $religion, $address,
                $contact_number, $email, $age, $lrn) {
                
                $query = $this->con->prepare("UPDATE pending_enrollees
                        SET firstname=:firstname,
                            middle_name=:middle_name,
                            lastName=:lastName,
                            civil_status=:civil_status,
                            nationality=:nationality,
                            sex=:sex,
                            birthday=:birthday,
                            birthplace=:birthplace,
                            religion=:religion,
                            address=:address,
                            contact_number=:contact_number,
                            email=:email,
                            age=:age,
                            lrn=:lrn
                        WHERE pending_enrollees_id=:pending_enrollees_id");

                $query->bindValue(":pending_enrollees_id", $pending_enrollees_id);
                $query->bindValue(":firstname", $firstname);
                $query->bindValue(":middle_name", $middle_name);
                $query->bindValue(":lastName", $lastName);
                $query->bindValue(":civil_status", $civil_status);
                $query->bindValue(":nationality", $nationality);
                $query->bindValue(":sex", $sex);
                $query->bindValue(":birthday", $birthday);
                $query->bindValue(":birthplace", $birthplace);
                $query->bindValue(":religion", $religion);
                $query->bindValue(":address", $address);
                $query->bindValue(":contact_number", $contact_number);
                $query->bindValue(":email", $email);
                $query->bindValue(":age", $age);
                $query->bindValue(":lrn", $lrn);

                $execute = $query->execute();

                return $execute;
            
        }


        public function UpdatePendingNewStep3($pending_enrollees_id, $firstname, 
            $middle_name, $lastName, $civil_status, $nationality, $sex,
            $birthday, $birthplace, $religion, $address,
            $contact_number, $email, $lrn) {
                
                $is_finished = 1;

                $query = $this->con->prepare("UPDATE pending_enrollees
                        SET firstname=:firstname,
                            middle_name=:middle_name,
                            lastName=:lastName,
                            civil_status=:civil_status,
                            nationality=:nationality,
                            sex=:sex,
                            birthday=:birthday,
                            birthplace=:birthplace,
                            religion=:religion,
                            address=:address,
                            contact_number=:contact_number,
                            email=:email,
                            lrn=:lrn,
                            is_finished=:is_finished
                        WHERE pending_enrollees_id=:pending_enrollees_id");

                $query->bindValue(":pending_enrollees_id", $pending_enrollees_id);
                $query->bindValue(":firstname", $firstname);
                $query->bindValue(":middle_name", $middle_name);
                $query->bindValue(":lastName", $lastName);
                $query->bindValue(":civil_status", $civil_status);
                $query->bindValue(":nationality", $nationality);
                $query->bindValue(":sex", $sex);
                $query->bindValue(":birthday", $birthday);
                $query->bindValue(":birthplace", $birthplace);
                $query->bindValue(":religion", $religion);
                $query->bindValue(":address", $address);
                $query->bindValue(":contact_number", $contact_number);
                $query->bindValue(":email", $email);
                $query->bindValue(":lrn", $lrn);
                $query->bindValue(":is_finished", $is_finished);

                $execute = $query->execute();

                return $execute;
            
        }
        public function CheckFormStep1Complete($pending_enrollees_id){

            $sql = $this->con->prepare("SELECT * FROM pending_enrollees
                WHERE firstname != ''
                AND lastname != ''
                AND middle_name != ''
                AND email != ''
                AND token != ''
                AND is_finished = 0
                AND activated = 1
                AND password != ''
                AND program_id != 0
                AND student_status != ''
                AND type != ''
                AND pending_enrollees_id =:pending_enrollees_id
                ");
        
            $sql->bindParam(":pending_enrollees_id", $pending_enrollees_id);
            $sql->execute();

            if($sql->rowCount() > 0){
                return true;
            }else{
                return false;
            }

        }
        public function CheckAllStepsComplete($pending_enrollees_id){


            # PARENT

            $sql_parent = $this->con->prepare("SELECT * FROM parent
                WHERE pending_enrollees_id=:pending_enrollees_id
                AND firstname != '' 
                AND middle_name != '' 
                AND lastname != '' 
                AND contact_number != '' 
                ");
            $sql_parent->bindValue(":pending_enrollees_id", $pending_enrollees_id);
            $sql_parent->execute();


            $sql = $this->con->prepare("SELECT * FROM pending_enrollees
                WHERE firstname != ''
                AND lastname != ''
                AND middle_name != ''
                AND email != ''
                AND token != ''
                AND is_finished = 0
                AND activated = 1
                AND password != ''
                AND program_id != 0
                AND civil_status != ''
                AND nationality != ''
                AND contact_number != ''
                AND birthday != ''
                AND age != 0
                -- AND guardian_name != ''
                -- AND guardian_contact_number != ''
                AND student_status != ''
                AND address != ''
                AND lrn != ''
                AND type != ''
                AND religion != ''
                AND birthplace != ''
                ");
        
            $sql->execute();

            

            if($sql_parent->rowCount() > 0){
                if($sql->rowCount() > 0){
                    return true;
                }else{
                    return false;
                }
            }

        }


        public function PendingParentInput($pending_enrollees_id, $fname,
            $lname, $mi, $contact_number){

          
            $query = $this->con->prepare("INSERT INTO parent 
                (pending_enrollees_id, firstname, lastname, middle_name, contact_number) 
                VALUES (:pending_enrollees_id, :firstname, :lastname, :middle_name, :contact_number)");
            

            $query->bindValue(":pending_enrollees_id", $pending_enrollees_id);
            $query->bindValue(":firstname", $fname);
            $query->bindValue(":middle_name", $mi);
            $query->bindValue(":lastname", $lname);
            $query->bindValue(":contact_number", $contact_number);

            return $query->execute();

        }

        public function CalculateAge($b_day){

            $age = -1;
        
            $birthdate = $b_day;

            $birth_date = new DateTime($birthdate);

            $current_date = new DateTime();

            $interval = $current_date->diff($birth_date);

            $age = $interval->y;

            return $age;
        }
    }
?>