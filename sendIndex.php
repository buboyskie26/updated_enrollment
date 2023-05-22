<?php 
    include('includes/config.php');

    require "vendor/autoload.php";

    use PHPMailer\PHPMailer\PHPMailer;
 
    $mail = new PHPMailer(true);
 
    $my_gmail_username = "hypersirios15@gmail.com";
    if(isset($_POST['send'])){
        
        $email = $_POST['email'];
        $subject = $_POST['subject'];
        $email_message = $_POST['email_message'];


        $mail->isSMTP();
        $mail->Host = "smtp.gmail.com";
        $mail->SMTPAuth = true;

        $mail->Username = $my_gmail_username;
        $mail->Password = "etenqjzyinxookzo";
        $mail->SMTPSecure = 'ssl';
        $mail->Port = 465 ;

        $mail->setFrom($my_gmail_username);
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $email_message;

        $mail->send();
        echo "set";

        // echo "
        //     <script>
        //         alert('success sent')
        //         document.location.href = 'sendIndex.php';
        //     </script>
        // ";
    }

    
?>
<html>
    <head>
        <title>ELMS_THESIS</title>
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
        <link rel="stylesheet" type="text/css" href="assets/css/style.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script> 
    </head>
</html>

  <form method="POST" >
    <label for="">Email</label>
    <input  type="text" name="email" placeholder="Firstname" autocomplete="off">
    <label for="">Subject</label>
    <input  type="text" name="subject" placeholder="Lastname" autocomplete="off">
    <label for="">Message</label>
    <input  type="text" name="email_message" placeholder="Message" autocomplete="off">
    <button type="submit" name="send" class="btn btn-sm btn-success">Send</button>
</form>

