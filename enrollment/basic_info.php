<?php 

    require_once('./classes/Pending.php');
    // require_once('../includes/config.php');
    // require_once('../includes/classes/Student.php');
    require_once('../includes/studentHeader.php');

    // echo "basic info";

    // Pending Status
    if(isset($_SESSION['username']) && 
        isset($_SESSION['status']) && $_SESSION['status'] == "pending"){

        $username = $_SESSION['username'];

        // $student = new Student($con, $username);

        // echo $student->GetStudentAddress();
        // echo "
        //     <h5>Rule 1. Be Consistent</h5><span>$username</span>
        // ";
        $pending = new Pending($con);

        $sql = $con->prepare("SELECT * FROM pending_enrollees
            WHERE firstname=:firstname");
        
        $sql->bindValue(":firstname", $username);
        $sql->execute();
        $row = $sql->fetch(PDO::FETCH_ASSOC);

        if(isset($_POST['pending_submit_btn'])){
             
            $pending_firstname_bi = $_POST['pending_firstname_bi'];
            $pending_lastname_bi = $_POST['pending_lastname_bi'];
            $pending_mi_bi = $_POST['pending_mi_bi'];
            $address = $_POST['address'];
            $contact_number = $_POST['contact_number'];

            // echo $pending_firstname_bi;
            // echo $pending_lastname_bi;

            $update = $con->prepare("UPDATE pending_enrollees
                SET firstname=:firstname,
                    lastname=:lastname,
                    middle_name=:middle_name,
                    address=:address,
                    contact_number=:contact_number
                
                WHERE firstname=:firstname");
            
            $update->bindValue(":firstname", $pending_firstname_bi);
            $update->bindValue(":lastname", $pending_lastname_bi);
            $update->bindValue(":middle_name", $pending_mi_bi);
            $update->bindValue(":address", $address);
            $update->bindValue(":contact_number", $contact_number);
            $update->bindValue(":firstname", $username);
            if($update->execute()){

                header("Location: profile.php");
                // $_SESSION['username'] = $pending_firstname_bi;
                // echo "success";
            }
        }

        ?>
        <div class="signInContainer">
            <div class="column">
                <div class="header">
                    <!-- <img src="assets/images/icons/VideoTubeLogo.png" title="logo" alt="Site logo"> -->
                    <h3 class="text-center">Basic Information</h3>
                </div>
                <div class="loginForm">
                    <form method="POST">
                        <label for="">Firstname</label>
                        <input readonly type="text" value="<?php echo $row['firstname']?>" name="pending_firstname_bi" placeholder="Firstname" autocomplete="off">
                        <label for="">Lastname</label>
                        <input  type="text" value="<?php echo $row['lastname']?>" readonly name="pending_lastname_bi" placeholder="Lastname" autocomplete="off">
                        <label for="">Middle Initial</label>
                        <input readonly type="text" value="<?php echo $row['middle_name']?>" value="" name="pending_mi_bi" placeholder="Middle Initial" autocomplete="off">

                        <label for="">Address</label>
                        <input type="text" value="<?php echo $row['address']?>" name="address" 
                            placeholder="Current Address" autocomplete="off">
                        <label for="">Contact</label>
                        <input type="text" value="<?php echo $row['contact_number']?>" name="contact_number" 
                            placeholder="Contact Number" autocomplete="off">

                            
                        <input type="submit" name="pending_submit_btn" value="Save">
                    </form>
                </div>
                
            </div>
        </div>
        <?php
    }

    // Enrolled Status
    else if(isset($_SESSION['username']) 
        && isset($_SESSION['status']) && $_SESSION['status'] == "enrolled"){

        $username = $_SESSION['username'];


        $sql = $con->prepare("SELECT * FROM student
            WHERE username=:username");
        
        $sql->bindValue(":username", $username);
        $sql->execute();
        $row = $sql->fetch(PDO::FETCH_ASSOC);

        if(isset($_POST['enrolled_submit_btn'])){
             
            $pending_firstname_bi = $_POST['pending_firstname_bi'];
            $pending_lastname_bi = $_POST['pending_lastname_bi'];
            $pending_mi_bi = $_POST['pending_mi_bi'];
            $address = $_POST['address'];
            $contact_number = $_POST['contact_number'];

            // echo $pending_firstname_bi;
            // echo $pending_lastname_bi;

            $update = $con->prepare("UPDATE student
                SET firstname=:firstname,
                    lastname=:lastname,
                    middle_name=:middle_name,
                    address=:address,
                    contact_number=:contact_number
                
                WHERE username=:username");
            
            $update->bindValue(":firstname", $pending_firstname_bi);
            $update->bindValue(":lastname", $pending_lastname_bi);
            $update->bindValue(":middle_name", $pending_mi_bi);
            $update->bindValue(":address", $address);
            $update->bindValue(":username", $username);
            $update->bindValue(":contact_number", $contact_number);
            if($update->execute()){
                header("Location: profile.php");
                // echo "success edited basic info as enrolled";
            }
        }

        ?>
        <div class="signInContainer">
            <div class="column">
                <div class="header">
                    <!-- <img src="assets/images/icons/VideoTubeLogo.png" title="logo" alt="Site logo"> -->
                    <h3 class="text-center">Basic Information</h3>
                </div>
                <div class="loginForm">
                    <form method="POST">
                        <label for="">Firstname</label>
                        <input readonly type="text" value="<?php echo $row['firstname']?>" name="pending_firstname_bi" placeholder="Firstname" autocomplete="off">
                        <label for="">Lastname</label>
                        <input  type="text" value="<?php echo $row['lastname']?>" readonly name="pending_lastname_bi" placeholder="Lastname" autocomplete="off">
                        <label for="">Middle Initial</label>
                        <input readonly type="text" value="<?php echo $row['middle_name']?>" value="" name="pending_mi_bi" placeholder="Middle Initial" autocomplete="off">

                        <label for="">Address</label>
                        <input type="text" value="<?php echo $row['address'];?>" name="address" 
                            placeholder="Current Address" autocomplete="off">

                        <label for="">Contact</label>
                        <input type="text" value="<?php echo $row['contact_number']?>" name="contact_number" 
                            placeholder="Contact Number" autocomplete="off">
                        <input type="submit" name="enrolled_submit_btn" value="Save">
                    </form>
                </div>
            </div>
        </div>
        <?php
    }

?>

