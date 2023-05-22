<?php
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\SMTP;
    $mail = new PHPMailer(true);

    // $name = $_POST["name"];
    // $email = $_POST["email"];
    // $subject = $_POST["subject"];
    // $message = $_POST["message"];

    require "vendor/autoload.php";

if(isset($_POST['send'])){
    
    echo "set";
    $email = $_POST['email'];
    $subject = $_POST['subject'];
    $email_message = $_POST['email_message'];

    // $mail->SMTPDebug = SMTP::DEBUG_SERVER;
    // $mail->SMTPAuth = true;

    // $mail->Host = "smtp.gmail.com";
    // $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    // $mail->Port = 587;

    // $mail->Username = "hyper15@gmail.com";
    // $mail->Password = "etenqjzyinxookzo";

    $mail->isSMTP();
    $mail->Host = "smtp.gmail.com";
    $mail->SMTPAuth = true;

    $mail->Username = "hyper15@gmail.com";
    $mail->Password = "etenqjzyinxookzo";
    $mail->SMTPSecure = 'ssl';
    $mail->Port = 465 ;


    $mail->setFrom("hyper15@gmail.com");
    $mail->addAddress($email);

    $mail->isHTML(true);
    $mail->Subject = $subject;
    $mail->Body = $email_message;

    $mail->send();

    // echo "
    //     <script>
    //         alert('success sent')
    //         document.location.href = 'sendIndex.php';
    //     </script>
    // ";
}



?>