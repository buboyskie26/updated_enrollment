<?php 

    require_once('./classes/StudentEnroll.php');
    require_once('./classes/Schedule.php');
    require_once('../includes/config.php');


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

        if(isset($_POST['credentials_pending_submit_btn'])){
             
            $email = $_POST['email'];
            $pending_lastname_bi = $_POST['pending_lastname_bi'];
            $pending_mi_bi = $_POST['pending_mi_bi'];
            $address = $_POST['address'];

            // echo $email;
            // echo $pending_lastname_bi;

            $update = $con->prepare("UPDATE pending_enrollees
                SET email=:email
                
                WHERE firstname=:firstname");
            
            $update->bindValue(":email", $email);
            $update->bindValue(":firstname", $username);
           
            if($update->execute()){
                header("Location: profile.php");
                // $_SESSION['username'] = $pending_firstname_bi;
                // echo "success";
            }
        }

        ?>
        <div class="signInContainer"   >
            <div class="column">
                <div class="header">
                    <h3 class="text-center">Credentials</h3>
                </div>
                
                <div class="loginForm">
                    <form method="POST">

                        <label for="">Username</label>
                        <input type="text" name="email" 
                            value="<?php echo $row['email'];?>" 
                            placeholder="Username" autocomplete="off">

                        <label for="">Password</label>
                        <input readonly type="password" readonly name="pending_password"
                         value="<?php echo $row['password']?>" placeholder="Password" autocomplete="off">

                        <input type="submit" name="credentials_pending_submit_btn" value="Save">
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

            // echo $pending_firstname_bi;
            // echo $pending_lastname_bi;

            $update = $con->prepare("UPDATE student
                SET firstname=:firstname,
                    lastname=:lastname,
                    middle_name=:middle_name,
                    address=:address
                
                WHERE username=:username");
            
            $update->bindValue(":firstname", $pending_firstname_bi);
            $update->bindValue(":lastname", $pending_lastname_bi);
            $update->bindValue(":middle_name", $pending_mi_bi);
            $update->bindValue(":address", $address);
            $update->bindValue(":username", $username);
            if($update->execute()){
                header("Location: profile.php");
                // echo "success edited basic info as enrolled";
            }
        }

        ?>
        <div class="signInContainer"   >
            <div class="column">
                <div class="header">
                    <h3 class="text-center">Credentials</h3>
                </div>
                
                <div class="loginForm">
                    <form method="POST">

                        <label for="">Username</label>
                        <input type="text" readonly name="username" value="<?php echo $student->GetUsername();?>" placeholder="Username" autocomplete="off">

                        <label for="">Password</label>
                        <input type="password" readonly name="pending_password" value="123456" placeholder="Password" autocomplete="off">

                        <input type="submit" name="enrolled_submit_btn" value="Save">
                    </form>
                </div>
            </div>
        </div>
        <?php
    }
?>



