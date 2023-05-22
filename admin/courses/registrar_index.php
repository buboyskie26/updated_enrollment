<?php 
    include('../registrar_enrollment_header.php');
    include('../../enrollment/classes/StudentEnroll.php');
    include('../../enrollment/classes/Schedule.php');
    include('../classes/Course.php');

    if(!AdminUser::IsRegistrarAuthenticated()){
        header("location: /dcbt/registrarLogin.php");
        exit();
    }
    echo "courses_page registrar";
?>