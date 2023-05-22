<?php  
 
    include('../includes/classes/Student.php');

    include('./adminHeader.php');

    // $student = new Student($con, $adminLoggedInObj);
    $student = new AdminUser($con, $adminLoggedIn);

    // echo $student->GetName();
    if(isset($_POST['submit_student'])){
        
        $wasSuccessful = $student->insertStudent($_POST['course_id'],
            $_POST['username'], $_POST['firstname'], $_POST['lastname']);
        if($wasSuccessful){
            header("Location: student.php");
        }
    }
   
?>

<div class="column">
    <?php
        echo $student->createForm();
    ?>
</div>

<?php  include('../includes/footer.php');?>