<?php

    include('../admin_enrollment_header.php');
    include('../../enrollment/classes/StudentEnroll.php');
    require_once('../../admin/classes/AdminUser.php');

    if(!AdminUser::IsAuthenticated()){
        header("Location: /dcbt/adminLoggedIn.php");
        exit();
    }
    // TODO: 
    if(isset($_POST['create_program_btn'])){
        
        $enroll = new StudentEnroll($con);

        $createUrl = base_url . "/index.php";

        $acronym = $_POST['acronym'];
    
        $program_name = "";
        $department_id = null;
        $track = "";
        if($acronym == "STEM"){
            $program_name = "Science, Technology, Engineering, and Mathematics";
            $department_id = 4;
            $track = "Academic";
        }
        else if($acronym == "HUMMS"){
            $program_name = "Humanities and Social Sciences";
            $department_id = 4;
            $track = "Academic";
        }
        else if($acronym == "ABM"){
            $program_name = "Accountancy, Business, and Management";
            $department_id = 4;
            $track = "Academic";
        }

        $sql = $con->prepare("INSERT INTO program
            (program_name, department_id, track, acronym)
            VALUES(:program_name, :department_id, :track, :acronym)");
        
        $sql->bindValue(":program_name", $program_name);
        $sql->bindValue(":department_id", $department_id);
        $sql->bindValue(":track", $track);
        $sql->bindValue(":acronym", $acronym);
        $sql->execute();
        
    }
?>

<div class='col-md-8 row offset-md-1'>
    <h4 class='text-center mb-3'>Create Subject Schedule</h4>
    <form method='POST'>
            <div class="modal-body">

               <div class="mb-3">
                    <label for="strand-name">Strand Name</label>
                    <select class="form-select" name="acronym" aria-label="Select strand name">
                        <option selected>Choose strand...</option>
                        <option value="STEM">Science, Technology, Engineering, and Mathematics</option>
                        <option value="HUMMS">Humanities and Social Sciences</option>
                        <option value="ABM">Accountancy, Business, and Management</option>
                    </select>
                </div>
               
            </div>

            <div class="modal-footer">
                <button name="create_program_btn" type="submit" class="btn btn-primary">Save</button>
            </div>
    </form>
</div>
 