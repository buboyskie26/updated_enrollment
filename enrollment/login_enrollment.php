<!DOCTYPE html>
<html lang="en">
<head>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/11.4.24/sweetalert2.all.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</head>

<?php 

    require_once('./classes/StudentEnroll.php');
    require_once('../includes/config.php');
    require_once('../includes/classes/form-helper/Constants.php');

    $enroll = new StudentEnroll($con);

    if (isset($_POST['login_enrollment'])) {

        $username =  $_POST['username'];
        $password =  $_POST['password'];

        // $object = $enroll->loginStudentUser($username, $password);
        $wasSuccess = $enroll->loginStudentUser($username, $password);

        // if(sizeof($object) > 0 && $object[1] == true){
        if(sizeof($wasSuccess) > 0 && $wasSuccess[1] == true && $wasSuccess[2] == "enrolled"){

            $_SESSION['username'] = $wasSuccess[0];
            $_SESSION['status'] = "enrolled";

            // $current_url = "http://" . $_SERVER['HTTP_HOST'] ;
            $current_url = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

            $url = "http://localhost/dcbt/enrollment/profile.php";
            header("Location: " . $url . "");
            // echo $current_url;
            // header("Location: " . $current_url . "profile.php");
        }

        if(sizeof($wasSuccess) > 0 && $wasSuccess[1] == true && $wasSuccess[2] != "enrolled"){
            $_SESSION['username'] = $wasSuccess[0];
            $_SESSION['status'] = "pending";

            // $current_url = "http://" . $_SERVER['HTTP_HOST'] ;
            $current_url = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

            $url = "http://localhost/dcbt/enrollment/profile.php?fill_up_state=finished";
            header("Location: " . $url . "");
        }
    }


?>

<h3>Login</h3>
<form method="post" action="">

    <div class="form-group">
        <label for="">Username:</label>
        <input type="text" name="username" class="form-control">
    </div>
    <br>
    <div class="form-group">
        <label for="">Password:</label>
        <input value="123456" type="password" name="password" class="form-control">
    </div>
    <br>

    <button name="login_enrollment" class="btn btn-success">Login</button>
    <br>
    <br>
</form>
