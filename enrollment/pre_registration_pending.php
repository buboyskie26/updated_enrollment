<?php 

    require_once('./classes/Pending.php');
    require_once('./classes/StudentEnroll.php');
    // require_once('../includes/config.php');
    require_once('../includes/studentHeader.php');


    // echo "basic info";

    if(isset($_SESSION['username'])){

        $username = $_SESSION['username'];
    
        // echo $username;
        // echo "
        //     <h5>Rule 1. Be Consistent</h5><span>$username</span>
        // ";
        $pending = new Pending($con);
        $enroll = new StudentEnroll($con);

        $school_year_obj = $enroll->GetActiveSchoolYearAndSemester();

        $current_sy_period = $school_year_obj['period'];

        $sql = $con->prepare("SELECT * FROM pending_enrollees
            WHERE firstname=:firstname");
        
        $sql->bindValue(":firstname", $username);
        $sql->execute();
        $row = $sql->fetch(PDO::FETCH_ASSOC);

        if(isset($_POST['pending_submit_btn'])){
             
            $pending_firstname_bi = $_POST['pending_firstname_bi'];
            $pending_lastname_bi = $_POST['pending_lastname_bi'];
            $pending_mi_bi = $_POST['pending_mi_bi'];

            // echo $pending_firstname_bi;
            // echo $pending_lastname_bi;

            $update = $con->prepare("UPDATE pending_enrollees
                SET firstname=:firstname,
                    lastname=:lastname,
                    middle_name=:middle_name
                
                WHERE firstname=:username");
            
            $update->bindValue(":firstname", $pending_firstname_bi);
            $update->bindValue(":lastname", $pending_lastname_bi);
            $update->bindValue(":middle_name", $pending_mi_bi);
            $update->bindValue(":username", $username);
            if($update->execute()){
                // header("Location: ")

                $_SESSION['username'] = $pending_firstname_bi;
                echo "success";
            }

        }

        if(isset($_POST['pending_choose_strand_btn']) &&
            isset($_POST['STRAND']) && 
            isset($_POST['student_status'])){

                $strand = $_POST['STRAND'];
                $student_status = $_POST['student_status'];
                $type = $_POST['type'];

                // echo $strand;
                // echo $student_status;

                // TODO.
                $check_filled_up = $con->prepare("SELECT * FROM pending_enrollees
                        WHERE firstname !=:firstname
                        AND lastname !=:lastname
                        AND middle_name !=:middle_name
                        AND email !=:email
                        AND token !=:token
                        AND activated !=:activated
                        -- AND program_id !=:program_id
                        AND contact_number !=:contact_number
                        AND address !=:address
                        AND firstname =:user_firstname
                        ");

                $check_filled_up->bindValue(":firstname", "");
                $check_filled_up->bindValue(":lastname", "");
                $check_filled_up->bindValue(":middle_name", "");
                $check_filled_up->bindValue(":email", "");
                $check_filled_up->bindValue(":token", "");
                $check_filled_up->bindValue(":activated", 0);
                // $check_filled_up->bindValue(":program_id", 0);
                $check_filled_up->bindValue(":contact_number", "");
                $check_filled_up->bindValue(":address", "");
                $check_filled_up->bindValue(":user_firstname", $username);
                $check_filled_up->execute();

                if($check_filled_up->rowCount() > 0){
                    $update = $con->prepare("UPDATE pending_enrollees
                        SET program_id=:program_id,
                            student_status=:student_status,
                            type=:type
                        
                        WHERE firstname=:username");
            
                    $update->bindValue(":program_id", $strand);
                    $update->bindValue(":student_status", $student_status);
                    $update->bindValue(":type", $type);
                    $update->bindValue(":username", $username);

                    // if(false){
                    if($update->execute()){
                        // header("Location: ")
                        // $_SESSION['username'] = $pending_firstname_bi;
                        echo "success";
                    }
                }else{
                    echo "not";
                }


            }

         
        ?>
        <div class="signInContainer" style="    margin-left: 150px;
">
            <div class="column">
                <div class="header">
                    <h3 class="text-center">Strand Selection</h3>
                </div>
                <div class="loginForm">

                    <form method="POST">
                        <label class="mb-2" for="">Student Category</label>
                        <select class="mb-3 form-control" name="student_status" id="">
                            <option value="" selected>Select Category</option>
                            <option value="Regular">New Student</option>
                            <option value="Transferee">Transferee</option>
                        </select>
                        <label class="mb-2" for="">Student Type</label>
                        <select class="mb-3 form-control" name="type" id="">
                            <option value="" selected>Select Type</option>
                            <option value="SHS">SHS</option>
                            <option value="Tertiary">Tertiary</option>
                        </select>
                        <!-- #TODO: Flexible for Tertiary and College -->
                        <label class="mb-2" for="">Strand</label>
                        <?php 
                            echo $pending->CreateRegisterStrand();
                        ?>
                        <button class="mt-4" type="submit" name="pending_choose_strand_btn" >Save</button>
                    </form>
                </div>
            </div>
        </div>
        <?php
    }


?>

