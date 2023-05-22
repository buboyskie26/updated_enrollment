<?php
    // include('../includes/config.php');
    include('../includes/classes/form-helper/Constants.php');
    include('../includes/classes/form-helper/FormSanitizer.php');
    require_once('../includes/studentHeader.php');

    require_once('./classes/Pending.php');
    require_once('./classes/Email.php');

    $pending = new Pending($con);
    // $email = new Email();

    require "../vendor/autoload.php";

    use PHPMailer\PHPMailer\PHPMailer;
 
    $mail = new PHPMailer(true);

    if(isset($_POST['resend_email_btn'])
        && isset($_POST['resend_email'])
    ){
        $resend_email = $_POST['resend_email'];
        $not_activated = 0;

        // Check Email if registered and activated = no
        $sql = $con->prepare("SELECT expiration_time, token, firstname FROM pending_enrollees
            WHERE email=:email
            AND activated=:activated
            AND token !=:token
            LIMIT 1
            ");
        $sql->bindValue(":email", $resend_email);
        $sql->bindValue(":activated", $not_activated);
        $sql->bindValue(":token", '');

        $sql->execute();

        if($sql->rowCount() > 0){

            // echo "yes";
            try {

                $email = new Email();

                // Create a new Token and New expiration time and 
                $new_token = $email->generateToken();
                $new_token = $new_token . "_new";
                $isEmailSent = true;

                $row = $sql->fetch(PDO::FETCH_ASSOC);
                $old_token = $row['token'];
                $old_expiration_time = $row['expiration_time'];
                $requested_user_firstname = $row['firstname'];

                $new_expiration_time = strtotime("+5 minutes");
                $new_expiration_time = date('Y-m-d H:i:s', $new_expiration_time);

                $update = $con->prepare("UPDATE pending_enrollees
                    SET token=:token,
                        expiration_time=:expiration_time
                    WHERE firstname=:firstname
                    AND activated=:activated
                    AND expiration_time=:old_expiration_time
                    ");

                $update->bindValue(":token", $new_token);
                $update->bindValue(":expiration_time", $new_expiration_time);
                $update->bindValue(":firstname", $requested_user_firstname);
                $update->bindValue(":activated", $not_activated);
                $update->bindValue(":old_expiration_time", $old_expiration_time);

                if($update->execute()){

                    $isEmailSent = $email->ReSendVerificationEmail($resend_email, $new_token);

                    if ($isEmailSent) {
                        echo "
                        <script>
                            alert('We resend the token. Please check your email now.');
                        </script>
                        ";
                    }
                }else{
                    echo "went wrong in updating";
                }


                //code...
            } catch (Exception $th) {
               echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";

            }
        }else{
        // Check Email is already activated
            $sql = $con->prepare("SELECT firstname FROM pending_enrollees
                WHERE email=:email
                AND activated=:activated
                LIMIT 1
                ");
            $sql->bindValue(":email", $resend_email);
            $sql->bindValue(":activated", 1);

            $sql->execute();
            if($sql->rowCount() > 0){
                echo "You`re email is already activated. You can log in now.";
            }
        }

        // Send the email

        // Throw Success sent
        
    };
?>
        <div class="signInContainer">
            <div class="column">
                <div class="header">
                    <!-- <img src="assets/images/icons/VideoTubeLogo.png" title="logo" alt="Site logo"> -->
                    <h3 class="text-center">Resend Email</h3>
                </div>

                <div class="loginForm">
                    <form  method="POST">

                        <label for="">Student Type</label>
                        <input  type="text" value="New" name="username" 
                            placeholder="Student Type" autocomplete="off" required>

                        <label for="">Registered Email</label>
                        <input type="text" name="resend_email" value="hypersirios15@gmail.com" 
                            placeholder="Email Resend" autocomplete="off" required>

                        <input type="submit" name="resend_email_btn" value="Resend">
                    </form>
                </div>
            </div>
        </div>

