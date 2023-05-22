<?php 

    require_once("../../includes/config.php");

    require_once("../../enrollment/classes/StudentEnroll.php");


    if(isset($_POST['student_username'])
        && isset($_POST['student_id'])){

        $student_username = $_POST['student_username'];
        $student_id = $_POST['student_id'];



        $enrol = new StudentEnroll($con);

        $recommendedList = $enrol->GetSHSNewStudentSubjectProgramBased($student_username);
        $student_course_id = $enrol->GetStudentCourseId($student_username);

        
        // echo json_encode($recommendedList);

        // print_r($recommendedList);

        $school_year = $con->prepare("SELECT school_year_id FROM school_year
            ORDER BY school_year_id DESC
            LIMIT 1");

        $school_year->execute();

        $school_year_id = $school_year->fetchColumn();

        // echo sizeof($recommendedList);
        if(sizeof($recommendedList) > 0){

            // TODO: Check if already been inserted to avoid duplication.
            $enrolledSuccess = false;
            foreach ($recommendedList as $key => $value) {
                $subject_id = $value['subject_id'];
                $course_level = $value['course_level'];

                # $subject_schedule_id = $value['subject_schedule_id'];
                
                $sql = $con->prepare("INSERT INTO student_subject (student_id, subject_id, course_level,
                    school_year_id)
                    VALUES(:student_id, :subject_id, :course_level, :school_year_id)");

                $sql->bindValue(":student_id", $student_id);
                $sql->bindValue(":subject_id", $subject_id);
                $sql->bindValue(":course_level", $course_level);
                // Client said Enrolled the subject first to the student and decided next
                // for the schedule
                // $sql->bindValue(":subject_schedule_id", $subject_schedule_id);
                $sql->bindValue(":school_year_id", $school_year_id);

                $enrolledSuccess = $sql->execute();
                // $enrolledSuccess = true;
                if($enrolledSuccess == true){
                    echo "success";
                }
            }

            if($enrolledSuccess){
                $createUrl = base_url . "/index.php";

                $base_url = 'http://localhost/elms/admin';
                $redirectPage = $base_url . '/enrollees/index.php';

                // Mark stuydent as old student '0'
                // $statement = $con->prepare("SELECT * FROM student
                //     WHERE student_id=:student_id
                //     LIMIT 1");
                // $statement->bindValue(":student_id", $student_id);
                // Auto update as long as student was enrolledSuccess.

                $update = $con->prepare("UPDATE student 
                    SET new_enrollee=:new_enrollee_update
                    WHERE new_enrollee=:new_enrollee
                    AND student_id=:student_id");

                // Update as Old equivalent to 0.
                $update->bindValue(":new_enrollee_update", 0);
                // 1 for New.
                $update->bindValue(":new_enrollee", 1);
                $update->bindValue(":student_id", $student_id);

                if($update->execute()){

                    // Insert to the enrollment page

                    $add_enrolled = $con->prepare("INSERT INTO enrollment 
                        (student_id, course_id, school_year_id)

                        VALUES(:student_id, :course_id, :school_year_id)");

                    $add_enrolled->bindValue(":student_id", $student_id);
                    $add_enrolled->bindValue(":course_id", $student_course_id);
                    $add_enrolled->bindValue(":school_year_id", $school_year_id);
                    if($add_enrolled->execute()){
                        echo $redirectPage;

                    }
                }

                // if(true){
                //     header("Location: http://localhost/elms/admin/enrollees/index.php");
                //     exit();
                // }
            }
        }

        // echo $student_username;
        // if(sizeof($recommendedListId) > 0){
        //     foreach ($recommendedListId as $key => $value) {
        //     }
           
        // }
    }else{
        echo "nothing";
    }


?>