<?php 

    require_once("../../../includes/config.php");
    require_once("../../../enrollment/classes/Enrollment.php");
    require_once("../../../enrollment/classes/OldEnrollees.php");
    require_once("../../../includes/classes/Student.php");
    
    if(isset($_POST['pending_enrollees_id'])
        && isset($_POST['student_id'])
        && isset($_POST['enrollment_id'])
        && isset($_POST['doesHaveRemovedSubjects'])
    ){
        
            $enrollment = new Enrollment($con, null);
            $oldEnrollees = new OldEnrollees($con, null);

            $pending_enrollees_id = $_POST['pending_enrollees_id'];
            $student_id = $_POST['student_id'];
            $enrollment_id = $_POST['enrollment_id'];
            $doesHaveRemovedSubjects = $_POST['doesHaveRemovedSubjects'];

            // echo $doesHaveRemovedSubjects;
            // echo "success";

            $wasSuccess = $enrollment->MarkAsRegistrarEvaluatedByEnrollmentId($enrollment_id);

            // if($doesHaveRemovedSubjects == true){
            //     $updateToIrregular = $enrollment->UpdateTransfereeStudentIntoIrregular($enrollment_id);
            // }

            if($wasSuccess == true 
                // && $updateToIrregular
            ){

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


    if(isset($_POST['transferee_student_status'])
        && isset($_POST['studentId'])){

        $transferee_student_status = $_POST['transferee_student_status'];
        $studentId = $_POST['studentId'];

        $student = new Student($con, $studentId);


        $updateSuccess = $student->UpdateStudentStatusv2($studentId,
            $transferee_student_status);

        if($updateSuccess){
            echo "success";
        }

    }
?>