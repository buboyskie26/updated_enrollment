<?php 

    require_once('./classes/StudentEnroll.php');
    require_once('./classes/Schedule.php');
    require_once('../includes/config.php');

?>


<div class="signInContainer">
    <div class="column">
        <div class="header">
            <h3 class="text-center">Elementary</h3>
        </div>
        
        <div class="loginForm">
            <form method="POST">
                <label for="">Elementary School</label>
                <input type="text" name="elem_school_name" 
                    placeholder="Contact #">
                <label for="">Elementary Address</label>
                <textarea class="form-control" type="text" name="elem_school_address" 
                    placeholder="Elementary School Address" autocomplete="off"></textarea>
              
                <!-- <input type="submit" name="pending_submit_btn" value="Save"> -->
            </form>
        </div>

        <div class="header">
            <h3 class="text-center">High School</h3>
        </div>
        
        <div class="loginForm">
            <form method="POST">
                <label for="">High School</label>
                <input type="text" name="hs_school_name" 
                    placeholder="Contact #">
                <label for="">High Address</label>
                <textarea class="form-control" type="text" name="hs_school_address" 
                    placeholder="High School Address" autocomplete="off"></textarea>
                
                <input type="submit" name="pending_submit_btn" value="Save">
            </form>
        </div>
        
    </div>
</div>