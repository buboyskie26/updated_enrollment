<?php 

    require_once('./classes/StudentEnroll.php');
    require_once('./classes/Schedule.php');
    require_once('../includes/config.php');

?>


<div class="signInContainer">
    <div class="column">
        <div class="header">
            <!-- <img src="assets/images/icons/VideoTubeLogo.png" title="logo" alt="Site logo"> -->
            <h3 class="text-center">Address</h3>
        </div>
        
        <div class="loginForm">
            <form method="POST">
                <label for="">Complete Address</label>
                <textarea class="form-control" type="text" name="complete_address" 
                    placeholder="Complete Address" autocomplete="off"></textarea>
                
                <label for="">Contact</label>
                <input type="text" name="contact_number" 
                    placeholder="Contact #">
                <input type="submit" name="pending_submit_btn" value="Save">
            </form>
        </div>
    </div>
</div>