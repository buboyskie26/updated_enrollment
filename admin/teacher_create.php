<?php  
 
    include('../includes/classes/Teacher.php');
    include('./classes/Department.php');
    include('./adminHeader.php');

    $teacher = new Teacher($con, $adminLoggedIn);

    $table =  $teacher->createTable();

    $createForm = $teacher->createForm();

    echo "
        <div class='column'>
            $createForm
        </div>
    ";   
?>