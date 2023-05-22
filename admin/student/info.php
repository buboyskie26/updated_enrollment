<?php 

    include('../registrar_enrollment_header.php');
    include('../../enrollment/classes/StudentEnroll.php');
    include('../../enrollment/classes/OldEnrollees.php');
    include('../classes/Course.php');


    if(isset($_GET['id'])){

        $student_id = $_GET['id'];

        echo $student_id;
    }
?>