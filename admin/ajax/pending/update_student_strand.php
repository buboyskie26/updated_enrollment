<?php 

    require_once("../../../includes/config.php");
    require_once("../../../enrollment/classes/Enrollment.php");
    require_once("../../../enrollment/classes/Pending.php");
    require_once("../../../enrollment/classes/OldEnrollees.php");
    require_once("../../../includes/classes/Student.php");
 
    if(isset($_POST['strand'])
        && isset($_POST['pending_enrollees_id'])){

        $strand = $_POST['strand'];
        $pending_enrollees_id = $_POST['pending_enrollees_id'];

        $pending = new Pending($con);


        $update = $pending->UpdatePendingStrand($pending_enrollees_id, $strand);

        if($update){
            echo "success";
        }
    }
?>