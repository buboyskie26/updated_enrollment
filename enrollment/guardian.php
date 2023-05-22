<?php 

    require_once('./classes/StudentEnroll.php');
    require_once('./classes/Schedule.php');
    require_once('../includes/config.php');


  if(isset($_SESSION['username']) && isset($_SESSION['status'])
        && $_SESSION['status'] == "enrolled")
  {

        $username = $_SESSION['username'];

        $student = new Student($con, $username);

        $sql = $con->prepare("SELECT * FROM student
            WHERE username=:username");
        
        $sql->bindValue(":username", $username);
        $sql->execute();
        $row = $sql->fetch(PDO::FETCH_ASSOC);

        if(isset($_POST['enrolled_guardian_submit_btn'])){
                
            $guardian_name = $_POST['guardian_name'];
            $guardian_contact_number = $_POST['guardian_contact_number'];

            $update = $con->prepare("UPDATE student
                SET guardian_name=:guardian_name,
                    guardian_contact_number=:guardian_contact_number
                
                WHERE username=:username");
            
            $update->bindValue(":guardian_name", $guardian_name);
            $update->bindValue(":guardian_contact_number", $guardian_contact_number);
            $update->bindValue(":username", $username);
            if($update->execute()){
                header("Location: profile.php");
                // echo "success";
            }
        }

        ?>
            <div class="signInContainer">
                <div class="column">
                    <div class="header">
                        <!-- <img src="assets/images/icons/VideoTubeLogo.png" title="logo" alt="Site logo"> -->
                        <h3 class="text-center">Parent Info</h3>
                    </div>
                    
                    <div class="loginForm">
                        <form method="POST">

                            <label for="">* Guardian Name</label>
                            <input type="text" name="guardian_name" value="<?php echo $student->GetGuardianName();?>"
                                placeholder="Guardian Name" autocomplete="off">     

                            <label for="">* Guardian Contact</label>
                            <input value="<?php echo $student->GetGuardianNameContact();?>" type="text" name="guardian_contact_number" 
                                placeholder="Guardian Contact #">

                            <input type="submit" 
                                name="enrolled_guardian_submit_btn" value="Save">
                        </form>
                    </div>
                </div>
            </div>
        <?php
    }

    else if(isset($_SESSION['username']) && isset($_SESSION['status'])
        && $_SESSION['status'] == "pending")
    {
        $username = $_SESSION['username'];

        $student = new Student($con, $username);

        $sql = $con->prepare("SELECT * FROM pending_enrollees
            WHERE firstname=:firstname");
        
        $sql->bindValue(":firstname", $username);
        $sql->execute();
        $row = $sql->fetch(PDO::FETCH_ASSOC);

        if(isset($_POST['pending_guardian_submit_btn'])){
                
            $guardian_name = $_POST['guardian_name'];
            $guardian_contact_number = $_POST['guardian_contact_number'];

            $update = $con->prepare("UPDATE pending_enrollees
                SET guardian_name=:guardian_name,
                    guardian_contact_number=:guardian_contact_number
                
                WHERE firstname=:firstname");
            
            $update->bindValue(":guardian_name", $guardian_name);
            $update->bindValue(":guardian_contact_number", $guardian_contact_number);
            $update->bindValue(":firstname", $username);
            if($update->execute()){
                header("Location: profile.php");
                // echo "success";
            }
        }

        ?>
            <div class="signInContainer">
                <div class="column">
                    <div class="header">
                        <!-- <img src="assets/images/icons/VideoTubeLogo.png" title="logo" alt="Site logo"> -->
                        <h3 class="text-center">Parent Info</h3>
                    </div>
                    
                    <div class="loginForm">
                        <form method="POST">

                            <label for="">* Guardian Name</label>
                            <input type="text" name="guardian_name" value="<?php echo $row['guardian_name'];?>"
                                placeholder="Guardian Name" autocomplete="off">     

                            <label for="">* Guardian Contact</label>
                            <input value="<?php echo $row['guardian_contact_number'];?>" type="text" name="guardian_contact_number" 
                                placeholder="Guardian Contact #">

                            <input type="submit" 
                                name="pending_guardian_submit_btn" value="Save">
                        </form>
                    </div>
                </div>
            </div>
        <?php
    }
?>

