<?php  
    include('../includes/classes/Course.php');
    include('./adminHeader.php');

    $course = new Course($con, $adminLoggedInObj);

    if(isset($_POST['submit_course'])){
        $wasSuccessful = $course->insertCourse($_POST['course_name']);
    }
   
?>

<div class="column">
    <?php
        echo $course->createForm();
    ?>
</div>

<?php  include('../includes/footer.php');?>