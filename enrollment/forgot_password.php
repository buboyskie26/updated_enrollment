<?php 


    include('../includes/classes/form-helper/Constants.php');
    include('../includes/classes/form-helper/FormSanitizer.php');
    require_once('../includes/studentHeader.php');

    require_once('./classes/Pending.php');
    require_once('./classes/Email.php');

    require "../vendor/autoload.php";

    use PHPMailer\PHPMailer\PHPMailer;
 
    $mail = new PHPMailer(true);

    if(isset($_POST['forgot_email_btn']) && isset($_POST['email'])){

        $user_email = $_POST['email'];

        $sql = $con->prepare("SELECT email,username FROM student
            WHERE email=:email
            AND active=:active
            LIMIT 1
            ");
        $sql->bindValue(":email", $user_email);
        $sql->bindValue(":active", 1);
        $sql->execute();

        if($sql->rowCount() > 0){
            $row = $sql->fetch(PDO::FETCH_ASSOC);

            $student_username = $row['username'];

            try {

                $email = new Email();
                $student = new Student($con, $student_username);

                $temporaryPassword = $student->ResetPassword($student_username);

                if(count($temporaryPassword) > 0 
                    && $temporaryPassword[1] == true){

                    $isEmailSent = $email->SendTemporaryPassword($user_email,
                        $temporaryPassword[0]);
                    if($isEmailSent == true){
                        # SWEET ALERT
                        echo "Email reset password has been sent to: $user_email";
                    }else{
                        // echo "Sending reset password via email went wrong";
                    }
                }else{;
                    // echo "Password did not reset";
                }
            } catch (Exception $th) {
               echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";

            }
        }else{
            echo "You`re email is incorrect.";
        }
    }
?>

<div class="signInContainer">
    <div class="column">
        <div class="header">
            <!-- <img src="assets/images/icons/VideoTubeLogo.png" title="logo" alt="Site logo"> -->
            <h3 class="text-center">Forget Password</h3>
            <p class="text-center">Enter your registered email</p>
        </div>

        <div class="loginForm">
            <form  method="POST">

                <input type="text" name="email" value="hypersirios15@gmail.com" 
                    placeholder="Email Address" autocomplete="off" required>

                <input type="submit" name="forgot_email_btn" value="Request">
            </form>
        </div>
    </div>
</div>


