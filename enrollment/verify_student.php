<?php 

    require_once('./classes/Pending.php');
    // require_once('../admin/classes/Course.php');
    require_once('../includes/config.php');

    if(isset($_GET['token'])){
        $token = $_GET['token'];

        $sql = $con->prepare("SELECT * FROM pending_enrollees
            WHERE token=:token");

        $sql->bindValue(":token", $token);
        $sql->execute();

        if($sql->rowCount() > 0){
            
            $row = $sql->fetch(PDO::FETCH_ASSOC);

            // Check if the record exists and if the expiration time has passed
            if ($row && strtotime($row['expiration_time']) < time()) {
                
                // Remove the record from the pending_enrollees table
                $sql = $con->prepare("DELETE FROM pending_enrollees 
                    WHERE token=:token");

                $sql->bindValue(':token', $token);
                if($sql->execute()){
                    // Redirect the user to the enrollment form

                    header('Location: enrollment/index.php');
                    exit();
                }
            }
            
            $_SESSION['authenticated'] = true;
            $_SESSION['username'] = $row['firstname'];
            $_SESSION['status'] = "pending";

            $update = $con->prepare("UPDATE pending_enrollees
                SET activated=:activated
                WHERE firstname=:firstname
                AND activated=:not_active
                AND token=:token");

            $update->bindValue(":activated", 1);
            $update->bindValue(":firstname", $row['firstname']);
            $update->bindValue(":not_active", 0);
            $update->bindValue(":token", $token);

            if($update->execute()){
            
                header("Location: process.php?new_student=true&step=1");
                // header("Location: profile.php");
                // header("Location: process.php");

                exit();
            }else{
                echo "Updating token went wrong";
            }

        }
        else{
            echo "
                <h3>Wrong Token. If you multiple requested an token, Please Click the latest email from DCBT.</h3>
            ";
        }
    }
?>