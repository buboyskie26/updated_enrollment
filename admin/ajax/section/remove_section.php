<?php 

    require_once("../../../includes/config.php");

    require_once("../../../enrollment/classes/Section.php");


    if(isset($_POST['course_id'])){

        $section = new Section($con, $_POST['course_id']);

        if($section->RemoveSection($_POST['course_id']) == true){
            echo "success";
        }
    }


?>