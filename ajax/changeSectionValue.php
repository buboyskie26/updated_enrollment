<?php

    require_once("../includes/config.php");

    if(isset($_POST['subject_period_id']) 
        && isset($_POST['setted_value'])){
        
        $subject_period_id = $_POST['subject_period_id'];
        $section_num = $_POST['setted_value'];

        $query = $con->prepare("UPDATE subject_period 
            SET section_num=:section_num
            WHERE subject_period_id=:subject_period_id");
        
        $query->bindValue(":section_num", $section_num);
        $query->bindValue(":subject_period_id", $subject_period_id);
        $yehey = $query->execute();
        
        echo $yehey;
        
    }
?>