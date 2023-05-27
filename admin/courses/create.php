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

        // $acronym = $_POST['acronym'];

        ## It should have a strand selection, Accountancy Business.. 
        # Established in Array dropdown but in stored in DB for flexibility.
        # Track should be depend on the chosen strand selection.
    
        $program_name = $_POST['program_name'];
        $dean = $_POST['dean'];
        $department_id = $_POST['department_id'];

        $track = "";
        $acronym = "";


        if($program_name == "Science, Technology, Engineering, and Mathematics"){
            $acronym = "STEM";
            $department_id = 4;
            $track = "Academic";
        }
        else if($program_name == "Humanities and Social Sciences"){
            $acronym = "HUMMS";
            $department_id = 4;
            $track = "Academic";
        }
        else if($program_name == "Accountancy, Business, and Management"){
            $acronym = "ABM";
            $department_id = 4;
            $track = "Academic";
        }

        $sql = $con->prepare("INSERT INTO program
            (program_name, department_id, track, acronym, dean)
            VALUES(:program_name, :department_id, :track, :acronym, :dean)");
        
        $sql->bindValue(":program_name", $program_name);
        $sql->bindValue(":department_id", $department_id);
        $sql->bindValue(":track", $track);
        $sql->bindValue(":acronym", $acronym);
        $sql->bindValue(":dean", $dean);

        if($sql->execute()){

            AdminUser::success("Program has been created.", "course_list.php");
            exit();
        }
        
    }
?>

<div class='col-md-8 row offset-md-1'>
    <h4 class='text-center mb-3'>Create Strand</h4>
    <form method='POST'>
            <div class="modal-body">

               <div class="mb-3">
                    <div class="form-group">
                        <label for="strand-name">Strand Name</label>
                        <!-- <input type="text" name="program_name" class="form-control"> -->
                        <select name="program_name" id="program_name" class="form-control">
                            <option value="" selected>Choose Program</option>
                            <option value="Accountancy, Business, and Management">Accountancy, Business, and Management</option>
                            <option value="Science, Technology, Engineering, and Mathematics">Science, Technology, Engineering, and Mathematics</option>
                            <option value="Humanities and Social Sciences">Humanities and Social Sciences</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="strand-name">Dean Name</label>
                        <input type="text" name="dean" class="form-control">
                    </div>

                    <div class="form-group">
                        <label for="strand-name">Department</label>
                        <select class="form-select" name="department_id" aria-label="Select strand name">
                            <?php 
                            $sql = $con->prepare("SELECT * FROM department");
                            $sql->execute();

                            echo "<option selected>Choose Department</option>";
                            
                            while($row = $sql->fetch(PDO::FETCH_ASSOC)){
                                $department_name = $row['department_name'];
                                $department_id = $row['department_id'];
                                echo "<option value='$department_id'>$department_name</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button name="create_program_btn" type="submit" class="btn btn-primary">Save</button>
            </div>
    </form>
</div>
 