<?php 

    require_once("../../../includes/config.php");

    require_once("../../../enrollment/classes/StudentEnroll.php");
    require_once("../../../enrollment/classes/StudentSubject.php");
    require_once("../../../admin/classes/Subject.php");

    // echo "yehey";

    if(
        // isset($_POST['subject_id']) && 
        isset($_POST['student_id']) && 
        isset($_POST['enrollment_id']) && 

        isset($_POST['subject_title']) 
    ){


        // $subject_id = $_POST['subject_id'];
        $student_id = $_POST['student_id'];
        $subject_title = $_POST['subject_title'];
        $enrollment_id = $_POST['enrollment_id'];
 

        $subject = new Subject($con, null, null);
        $studentEnroll= new StudentEnroll($con);
        $studentSubject = new StudentSubject($con);

        $school_year_obj = $studentEnroll->GetActiveSchoolYearAndSemester();

        $current_school_year_id = $school_year_obj['school_year_id'];
        $current_school_year_term = $school_year_obj['term'];
        $current_school_year_period = $school_year_obj['period'];

        // echo $subject_title;

        $is_final = "no";
        $is_transferee = true;

        $checkCreditedSubjectExists = $studentSubject->CheckAlreadyCreditedSubject(
                        $student_id, $subject_title);

        if($checkCreditedSubjectExists == true){
            // echo "is present";

            $wasRemoved = $studentSubject->RemoveCreditedStudentSubjectGradeProgramBased($student_id,
                $subject_title);

            if($wasRemoved){

                echo "success_undo_credit";
            }
        }






        // $get_course_level = $subject->GetSubjectCourseLevel($subject_id);

        // $subject_title = $subject->GetSubjectTitle($subject_id);

        // $getSubjectProgramId = $subject->GetSubjectProgramId($subject_id);

        // $checkSubjectExists = $studentSubject->CheckStudentSubject($student_id, $subject_id,
        //     $enrollment_id, $current_school_year_id);

        // if($checkSubjectExists == true){

        //     // echo "there is";
        //     # REMOVED.

        //     $doesSuccess = $studentSubject->RemoveCreditedStudentSubject($student_id,
        //         $subject_id, $enrollment_id);

        //     if($doesSuccess == true){

          
        //         if($doesSuccess == true){

        //             $wasDeleted = $studentSubject->RemoveCreditedStudentSubjectGrade($student_id,
        //                 $subject_id, $subject_title);
        //             if($wasDeleted){
        //                 echo "success_deleted";
        //             }
        //         }
        //     }

                 
        //     // }
        // }else{
        //     echo "Subject is not inserted in the db. you`re trying to removed";
        // }

        // echo json_encode($data);
    }

    

?>