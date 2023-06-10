<?php 
    include('../registrar_enrollment_header.php');
    include('../../enrollment/classes/StudentEnroll.php');
    include('../../enrollment/classes/Section.php');
    include('../classes/Course.php');

    
    if(!AdminUser::IsRegistrarAuthenticated()){
        header("location: /dcbt/registrarogin.php");
        exit();
    }

    $studentEnroll = new StudentEnroll($con);

 
?>



