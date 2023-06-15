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
 

        $subject= new Subject($con, null, null);
        $studentEnroll= new StudentEnroll($con);
        $studentSubject = new StudentSubject($con);

        $school_year_obj = $studentEnroll->GetActiveSchoolYearAndSemester();

        $current_school_year_id = $school_year_obj['school_year_id'];
        $current_school_year_term = $school_year_obj['term'];
        $current_school_year_period = $school_year_obj['period'];

        $is_final = "no";
        $is_transferee = true;

        
        $checkCreditedSubjectExists = $studentSubject->CheckAlreadyCreditedSubject(
                        $student_id, $subject_title);

        if($checkCreditedSubjectExists == false){
            
            $wasInserted = $studentSubject->InsertStudentCreditedSubjectProgramBased($student_id,
                $subject_title);

            if($wasInserted == true){
                echo "success_credit";
            }
        }else{
            echo "subject_exists";
        }

        // $get_course_level = $subject->GetSubjectCourseLevel($subject_id);

        // $subject_title = $subject->GetSubjectTitle($subject_id);

        // $getSubjectProgramId = $subject->GetSubjectProgramId($subject_id);

        // $checkSubjectExists = $studentSubject->CheckStudentSubject($student_id, $subject_id,
        //     $enrollment_id, $current_school_year_id);

        // if($checkSubjectExists == false){

        //     $wasInserted = $studentSubject->InsertStudentSubject($student_id, $subject_id,
        //         $enrollment_id, $get_course_level, $getSubjectProgramId,
        //             $current_school_year_id, $is_final, $is_transferee);

        //     if($wasInserted == true){

        //         $student_subject_id = $con->lastInsertId();
        //         // echo "success";

        //         $checkCreditedSubjectExists = $studentSubject->CheckAlreadyCreditedSubject(
        //                         $student_id, $subject_title);
                            
        //         if(!$checkCreditedSubjectExists){

        //             $wasCredited = $studentSubject->InsertStudentCreditedSubject($student_id,
        //                 $subject_title, $student_subject_id, $subject_id);

        //             if($wasCredited){
        //                 echo "success";
        //             }
        //         }
        //     }
        // }

        // echo json_encode($data);
    }

    

?>