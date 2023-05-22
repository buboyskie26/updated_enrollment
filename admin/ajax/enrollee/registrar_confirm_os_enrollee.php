<?php 

    require_once("../../../includes/config.php");

    require_once("../../../enrollment/classes/StudentEnroll.php");

     
    if(isset($_POST['course_id']) && isset($_POST['student_id'])){

        $enrol = new StudentEnroll($con);


        $school_year_obj = $enrol->GetActiveSchoolYearAndSemester();
        $current_school_year_id = $school_year_obj['school_year_id'];

        $course_id = $_POST['course_id'];
        $student_id = $_POST['student_id'];

        $SET_TO_YES = "yes";
        $not_evaluated = "no";

        $is_new_enrollee = 0;
        $is_transferee = 0;


        // If student course_id = STEM11-A STEM12-A
        $in_active = "yes";

        $get_student_course = $con->prepare("SELECT course_id FROM course
            WHERE previous_course_id=:previous_course_id
                
            AND active=:active");

        $get_student_course->bindValue(":previous_course_id", $course_id);
        $get_student_course->bindValue(":active", $in_active);
        $get_student_course->execute();
        
        if($get_student_course->rowCount() > 0){

            $moveUpCourseId = $get_student_course->fetchColumn();

            echo $moveUpCourseId;
            $enrollment_status = "tentative";

            # All enrollees enrolled from prev sem and wanted to enroll
            # in the current sem will be registrar_evaluted: yes

            // echo $moveUpCourseId;
            // Update Move_Up_Course_Id of student in Enrollment Course_Id
            $update_enrollment_student_course = $con->prepare("UPDATE enrollment
                SET course_id=:new_course_id,
                    registrar_evaluated=:set_registrar_evaluated
                WHERE course_id=:course_id
                AND student_id=:student_id
                AND school_year_id=:school_year_id
                AND enrollment_status=:enrollment_status
                AND registrar_evaluated=:registrar_evaluated
                ");

            $update_enrollment_student_course->bindValue(":new_course_id", $moveUpCourseId);
            $update_enrollment_student_course->bindValue(":set_registrar_evaluated", $SET_TO_YES);
            $update_enrollment_student_course->bindValue(":course_id", $course_id);
            $update_enrollment_student_course->bindValue(":student_id", $student_id);
            $update_enrollment_student_course->bindValue(":school_year_id", $current_school_year_id);
            $update_enrollment_student_course->bindValue(":enrollment_status", $enrollment_status);
            $update_enrollment_student_course->bindValue(":registrar_evaluated", $not_evaluated);
            
            
            // Returnee, Grade 11 1st sub passed, 2nd passed, but did not move up[ to grade twelve.
            // Transferee I think has the same scenario like this.

            // if(false){
            if($update_enrollment_student_course->execute()){
                // Update Move_Up in Student Course_Id
                // echo $moveUpCourseId;
                $update_student_course = $con->prepare("UPDATE student
                    SET course_id=:move_up_course_id
                    WHERE course_id=:course_id
                    AND student_id=:student_id
                    ");
                
                $update_student_course->bindValue(":move_up_course_id", $moveUpCourseId);
                $update_student_course->bindValue(":course_id", $course_id);
                $update_student_course->bindValue(":student_id", $student_id);
                $update_student_course->execute();

                if($update_student_course->execute()){
                    echo "success registrar evaluated this ongoing enrollee";
                    echo "student course_id moves up to $moveUpCourseId";
                }
            }
        }
        
        // }

        //
        //

        // $registrar_confirm = $con->prepare("UPDATE enrollment
        //     SET registrar_evaluated=:set_registrar_evaluated
        //     WHERE student_id=:student_id
        //     AND is_new_enrollee=:is_new_enrollee
        //     AND is_transferee=:is_transferee
        //     AND course_id=:course_id
        //     AND registrar_evaluated=:registrar_evaluated
        // ");

        // $registrar_confirm->bindValue(":set_registrar_evaluated", $SET_TO_YES);
        // $registrar_confirm->bindValue(":student_id", $student_id);
        // $registrar_confirm->bindValue(":course_id", $course_id);
        // $registrar_confirm->bindValue(":is_new_enrollee", $is_new_enrollee);
        // $registrar_confirm->bindValue(":is_transferee", $is_transferee);
        // $registrar_confirm->bindValue(":registrar_evaluated", $not_evaluated);
 
        // if($registrar_confirm->execute()){
            
        //     echo "success registrar evaluated this ongoing enrollee";

        //     // If student course_id = STEM11-A STEM12-A
        //     $in_active = "yes";

        //     $get_student_course = $con->prepare("SELECT course_id FROM course
        //         WHERE previous_course_id=:previous_course_id
        //         AND active=:active");

        //     $get_student_course->bindValue(":previous_course_id", $course_id);
        //     $get_student_course->bindValue(":active", $in_active);
        //     $get_student_course->execute();
    
        //     if($get_student_course->rowCount() > 0){
        //         $moveUpCourseId = $get_student_course->fetchColumn();
        //         $enrollment_status = "tentative";

        //         // Update Move_Up_Course_Id of student in Enrollment Course_Id
        //         $update_enrollment_student_course = $con->prepare("UPDATE enrollment
        //             SET course_id=:new_course_id
        //             WHERE course_id=:course_id
        //             AND student_id=:student_id
        //             AND school_year_id=:school_year_id
        //             AND enrollment_status=:enrollment_status
        //             ");

        //         $update_enrollment_student_course->bindValue(":new_course_id", $moveUpCourseId);
        //         $update_enrollment_student_course->bindValue(":course_id", $course_id);
        //         $update_enrollment_student_course->bindValue(":student_id", $student_id);
        //         $update_enrollment_student_course->bindValue(":school_year_id", $current_school_year_id);
        //         $update_enrollment_student_course->bindValue(":enrollment_status", $enrollment_status);

        //         if($update_enrollment_student_course->execute()){
        //             // Update Move_Up in Student Course_Id
        //             // echo $moveUpCourseId;
        //             $update_student_course = $con->prepare("UPDATE student
        //                 SET course_id=:move_up_course_id
        //                 WHERE course_id=:course_id
        //                 AND student_id=:student_id
        //                 ");
                    
        //             $update_student_course->bindValue(":move_up_course_id", $moveUpCourseId);
        //             $update_student_course->bindValue(":course_id", $course_id);
        //             $update_student_course->bindValue(":student_id", $student_id);
        //             $update_student_course->execute();
        //             if($update_student_course->execute()){
        //                 echo "student course_id moves up to $moveUpCourseId";
        //             }
        //         }
                
        //     }
        // }



        
    }
     
?>