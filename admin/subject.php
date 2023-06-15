<?php  
    include('./adminHeader.php');
    include('../includes/classes/Subject.php');

    $subject = new Subject($con, $adminLoggedInObj, null);

    if(isset($_POST['submit_subject'])){

        $wasSuccessful = $subject->insertSubject(
            $_POST['subject_code'],
            $_POST['subject_title'],
            $_POST['units'],
            $_POST['semester'],
            $_POST['subject_description'],
            $_POST['pre_requisite'],
            $_POST['course_main_id'],
            $_POST['course_level'],
            
        );
 
    }


   
?>



<div class="column">
    <?php
        echo $subject->createForm();
    ?>
</div>

<?php  include('../includes/footer.php');?>