<?php 
    // require 'path/to/PHPMailer/src/PHPMailer.php';
    // require 'path/to/PHPMailer/src/SMTP.php';
    // require 'path/to/PHPMailer/src/Exception.php';

    use PHPMailer\PHPMailer\PHPMailer;
    // use PHPMailer\PHPMailer\SMTP;
    // use PHPMailer\PHPMailer\Exception;

class Email {
    private $mailer;
    private $my_gmail_username = 'hypersirios15@gmail.com';
    private $my_gmail_password = 'etenqjzyinxookzo';
    
    public function __construct() {
        // create mailer object and set its properties
        $this->mailer = new PHPMailer(true);
        $this->mailer->isSMTP();
        $this->mailer->Host = "smtp.gmail.com";
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = $this->my_gmail_username;
        $this->mailer->Password = $this->my_gmail_password;
        $this->mailer->SMTPSecure = 'ssl';
        $this->mailer->Port = 465;
        $this->mailer->setFrom($this->my_gmail_username, "Daehan College");
        $this->mailer->isHTML(true);
    }
    
    public function sendVerificationEmail($email_address, $token) {

        $link = "http://localhost/dcbt/enrollment/verify_student.php?token=" 
            . $token;

        // $image_path = '../admin/assets/images/mypeace.png';
        // $image_path = 'https://images.pexels.com/photos/15499750/pexels-photo-15499750.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1';
        // // Get the contents of the image file
        // $image_data = file_get_contents($image_path);
        // // Encode the image data using base64 encoding
        // $image_data = base64_encode($image_data);
        // // Add the image as an inline attachment
        // $this->mailer->addStringEmbeddedImage($image_data, 'image.png', 'image.png', 'base64', 'image/png');
        // // Add the image to the email body
        // $email_message = '<html><body><img src="cid:image.png"><br>(Verification) click if it was you. '.$link.' (The token will lasts only 5 minutes)</body></html>';
       
        $email_message = "(Verification) click if it was you. $link (The token will lasts only 5 minutes)";

        $this->mailer->addAddress($email_address);
        $this->mailer->Subject = "DCBT Enrollment Verification";
        $this->mailer->Body = $email_message;

        if ($this->mailer->send()) {
            return true;
        } else {
            return false;
        }
    }

    public function SendTemporaryPassword($email_address, $token) {

        $link = "http://localhost/dcbt/enrollment/verify_student.php?token=" 
            . $token;
 
        $email_message = "Please copy the full token that you will use for logging in: $token Note: Please changed your password immediately!";

        $this->mailer->addAddress($email_address);
        $this->mailer->Subject = "Temporary Password.";
        $this->mailer->Body = $email_message;

        if ($this->mailer->send()) {
            return true;
        } else {
            return false;
        }
    }

    public function ReSendVerificationEmail($email_address, $token) {

        $link = "http://localhost/dcbt/enrollment/verify_student.php?token=" 
            . $token;

        // $image_path = '../admin/assets/images/mypeace.png';
        // $image_path = 'https://images.pexels.com/photos/15499750/pexels-photo-15499750.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1';
        // // Get the contents of the image file
        // $image_data = file_get_contents($image_path);
        // // Encode the image data using base64 encoding
        // $image_data = base64_encode($image_data);
        // // Add the image as an inline attachment
        // $this->mailer->addStringEmbeddedImage($image_data, 'image.png', 'image.png', 'base64', 'image/png');
        // // Add the image to the email body
        // $email_message = '<html><body><img src="cid:image.png"><br>(Verification) click if it was you. '.$link.' (The token will lasts only 5 minutes)</body></html>';
       
        $email_message = "(Resend-Verification) click if it was you. $link (The token will lasts only 5 minutes)";

        $this->mailer->addAddress($email_address);
        $this->mailer->Subject = "DCBT Enrollment Verification";
        $this->mailer->Body = $email_message;

        if ($this->mailer->send()) {
            return true;
        } else {
            return false;
        }
    }
    
    public function generateToken() {
        return bin2hex(random_bytes(16));
    }
}
 

?>