<?php 

    require_once("../../../includes/config.php");

    require_once("../../../enrollment/classes/StudentEnroll.php");

     
    // if(isset($_POST['course_id']) && isset($_POST['student_id'])){

    //     $enrol = new StudentEnroll($con);


    //     $school_year_obj = $enrol->GetActiveSchoolYearAndSemester();
    //     $current_school_year_id = $school_year_obj['school_year_id'];

    //     $course_id = $_POST['course_id'];
    //     $student_id = $_POST['student_id'];

    //     $SET_TO_YES = "yes";
    //     $not_evaluated = "no";

    //     $is_new_enrollee = 0;
    //     $is_transferee = 0;


    //         // If student course_id = STEM11-A STEM12-A
    //         $in_active = "yes";

    //         $get_student_course = $con->prepare("SELECT course_id FROM course
    //             WHERE previous_course_id=:previous_course_id
                    
    //             AND active=:active");

    //         $get_student_course->bindValue(":previous_course_id", $course_id);
    //         $get_student_course->bindValue(":active", $in_active);
    //         $get_student_course->execute();
            
    //         if($get_student_course->rowCount() > 0){

    //             $moveUpCourseId = $get_student_course->fetchColumn();

    //             echo $moveUpCourseId;
    //             $enrollment_status = "tentative";

    //             // Get the moveupCOurseId
    //             // Insert in the enrollment
    //             // Update student info

    //             $insert_enrollment = $con->prepare("INSERT INTO enrollment
    //                 (student_id, course_id, school_year_id, enrollment_status, registrar_evaluated)
    //                 VALUES (:student_id, :course_id, :school_year_id, :enrollment_status, :registrar_evaluated)");

    //             $insert_enrollment->bindValue(":student_id", $student_id);
    //             $insert_enrollment->bindValue(":course_id", $moveUpCourseId);
    //             $insert_enrollment->bindValue(":school_year_id", $current_school_year_id);
    //             $insert_enrollment->bindValue(":enrollment_status", $enrollment_status);
    //             $insert_enrollment->bindValue(":registrar_evaluated", $SET_TO_YES);
                
    //             // if(false){
    //             if($insert_enrollment->execute()){

    //                 // Update Move_Up in Student Course_Id
    //                 // echo $moveUpCourseId;
    //                 $update_student_course = $con->prepare("UPDATE student
    //                     SET course_id=:move_up_course_id
    //                     WHERE course_id=:course_id
    //                     AND student_id=:student_id
    //                     ");
                    
    //                 $update_student_course->bindValue(":move_up_course_id", $moveUpCourseId);
    //                 $update_student_course->bindValue(":course_id", $course_id);
    //                 $update_student_course->bindValue(":student_id", $student_id);
    //                 $update_student_course->execute();

    //                 if($update_student_course->execute()){
    //                     echo "success registrar evaluated this ongoing enrollee";
    //                     echo "student course_id moves up to $moveUpCourseId";
    //                 }
    //             }
    //         }
    // }


    if (isset($_POST['studentId'])) {

        $studentId = $_POST['studentId'];

        $stmt = $con->prepare('SELECT 
            t1.firstname, t1.lastname, t1.course_level, t1.student_status,
        
            t2.program_section,

            t4.period
            FROM student as t1

            LEFT JOIN course as t2 ON t2.course_id = t1.course_id
            LEFT JOIN enrollment as t3 ON t3.course_id = t1.course_id
            LEFT JOIN school_year as t4 ON t4.school_year_id = t3.school_year_id

            WHERE t1.student_id = ?
            AND t3.student_id = t1.student_id

            ORDER BY enrollment_id DESC
            LIMIT 1');
        
        $stmt->execute([$studentId]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);


        if ($result) {

            $response = [
                'firstname' => $result['firstname'],
                'lastname' => $result['lastname'],
                'course_level' => $result['course_level'],
                'student_status' => $result['student_status'],
                'program_section' => $result['program_section'],
                'period' => $result['period'],
            ];

            header('Content-Type: application/json');
            
            echo json_encode($response);

        } else {
            http_response_code(404);
        }

    }else{
        http_response_code(400);
    }
     
?>