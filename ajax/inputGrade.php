<?php

    require_once("../includes/config.php");

    if(isset($_POST['student_period_assignment_id'])){

       $student_period_assignment_id = $_POST['student_period_assignment_id'];


        $query = $con->prepare("UPDATE FROM student_period_assignment
            SET grade=:grade");

        $query->execute();

        
    }

?>