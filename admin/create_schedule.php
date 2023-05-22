<?php  
 
    include('../includes/classes/Teacher.php');
    include('classes/Schedule.php');
    include('./adminHeader.php');

    // $teacher = new Teacher($con, $adminLoggedIn);
    $schedule = new Schedule($con, $adminLoggedInObj);

    // $table =  $teacher->createTable();

    $createForm = $schedule->createForm();

    echo "
        <div class='column'>
            $createForm
        </div>
    ";   
?>