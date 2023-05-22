<?php 

    require_once("../../../includes/config.php");
    require_once("../../../enrollment/classes/Enrollment.php");
    require_once("../../../enrollment/classes/OldEnrollees.php");
    
    if(isset($_POST['pending_enrollees_id'])
        && isset($_POST['student_id'])
        && isset($_POST['enrollment_id'])
    ){

            $enrollment = new Enrollment($con, null);
            $oldEnrollees = new OldEnrollees($con, null);

            $pending_enrollees_id = $_POST['pending_enrollees_id'];
            $student_id = $_POST['student_id'];
            $enrollment_id = $_POST['enrollment_id'];

            $wasSuccess = $enrollment->MarkAsRegistrarEvaluatedByEnrollmentId($enrollment_id);

            if($wasSuccess == true){

                // $newToOld = $oldEnrollees->UpdateSHSStudentNewToOld($student_id);
                $approved = "APPROVED";
                $update_pending = $con->prepare("UPDATE pending_enrollees
                        SET student_status=:student_status
                        WHERE pending_enrollees_id=:pending_enrollees_id
                    ");

                $update_pending->bindParam(":student_status", $approved);
                $update_pending->bindParam(":pending_enrollees_id", $pending_enrollees_id);
                
                if($update_pending->execute()){
                    echo "success";
                }
            }
    }
?>