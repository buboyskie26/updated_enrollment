<?php 

    include('../registrar_enrollment_header.php');
    include('../../enrollment/classes/StudentEnroll.php');
    include('../../enrollment/classes/OldEnrollees.php');
    include('../classes/Course.php');

    $enroll = new StudentEnroll($con);
    $old_enroll = new OldEnrollees($con, $enroll);
    $course = new Course($con, $enroll);


	$school_year_obj = $enroll->GetLatestSchoolYearAndSemester();

	$current_term = $school_year_obj[0];
	$current_semester = $school_year_obj[1];
	$school_year_id = $school_year_obj[2];
	$strandSelection = $enroll->CreateRegisterStrand();

    $generateStudentUniqueId = $enroll->GenerateUniqueStudentNumber();

    $course_dropdown = $course->GetCourseAvailableSelectionForCurrentSY();
    
    // Ieven if its full, if registrar selects that student placed into that section
    // it should be adjustable.
    if($_GET['id']){
        
        $student_id = $_GET['id'];
        $student_name = $enroll->GetStudentFullName($student_id);

        // $course_dropdown = 


        if(isset($_POST['update_returnee_btn']) && isset($_POST['course_id'])){

            $course_id = $_POST['course_id'];
            $set_active = 1;
            $in_active = 0;

            // If the capacity is full, it needs to be adjustable. + 1 capacity.
            $update = $con->prepare("UPDATE student
                SET course_id=:course_id, active=:set_active
                WHERE student_id=:student_id
                AND active=:active");

            $update->bindValue(":course_id", $course_id);
            $update->bindValue(":set_active", $set_active);
            $update->bindValue(":student_id", $student_id);
            $update->bindValue(":active", $in_active);

            if($update->execute()){
                $enrollment_status = "tentative";
                $is_returnee = 1;
                $registrar_evaluated = "yes";

                $update_enrollment = $con->prepare("INSERT INTO enrollment
                    (student_id, course_id, school_year_id, enrollment_status, is_returnee, registrar_evaluated)
                    VALUES(:student_id, :course_id, :school_year_id, :enrollment_status, :is_returnee, :registrar_evaluated)");

                $update_enrollment->bindValue(":student_id", $student_id);
                $update_enrollment->bindValue(":course_id", $course_id);
                $update_enrollment->bindValue(":school_year_id", $school_year_id);
                $update_enrollment->bindValue(":enrollment_status", $enrollment_status);
                $update_enrollment->bindValue(":is_returnee", $is_returnee);
                $update_enrollment->bindValue(":registrar_evaluated", $registrar_evaluated);
                if($update_enrollment->execute()){
                    echo "$student_name becomes returnee, he has been placed on section $course_id and had been enrolled";
                }

            }
            
        }


        ?>
        <div class='col-md-10 row offset-md-1'>
            <h4 class='text-center mb-3'>Return Update of<?php echo $student_name;?> </h4>
            <form method='POST'>

                <?php echo $course_dropdown;?>


                <button type='submit' class='btn btn-primary' name='update_returnee_btn'>Save</button>
            </form>
        </div>
        <?php
    }

?>