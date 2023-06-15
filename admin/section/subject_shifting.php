  
  <?php

    include('../registrar_enrollment_header.php');
    include('../../enrollment/classes/Section.php');
    include('../../includes/classes/Student.php');
    include('../../enrollment/classes/StudentEnroll.php');
    include('../../enrollment/classes/Enrollment.php');
    include('../classes/Subject.php');

    $createProgramSelection = "2";


    $enroll = new StudentEnroll($con);

    $enrollment = new Enrollment($con, $enroll);

    $school_year_obj = $enroll->GetActiveSchoolYearAndSemester();

    $current_school_year_term = $school_year_obj['term'];
    $current_school_year_period = $school_year_obj['period'];
    $current_school_year_id = $school_year_obj['school_year_id'];



    if(isset($_GET['id'])

        && !isset($_GET['section_id'])
        && !isset($_GET['subject_id'])
        && !isset($_GET['section_id'])
        ){


        $student_id = $_GET['id'];

        $student_username = $enroll->GetStudentUsername($student_id);


        $student = new Student($con, $student_username);

        $student_statusv2 = $student->GetStudentStatusv2();
        $admission_status = $student->GetStudentAdmissionStatus();

        // echo $student_statusv2;

        $student_name = $enroll->GetStudentFullName($student_id);
        $student_obj = $enroll->GetStudentCourseLevelYearIdCourseId($student_username);

        $student_course_id = $student_obj['course_id'];
        $student_course_level = $student_obj['course_level'];

        $section = new Section($con, $student_course_id);

        // $program_id = $_GET['pid'];

        $program_id = $enroll->GetStudentProgramId($student_course_id);

        $student_section = $section->GetSectionName();

        $student_enrollment_id = $enrollment->GetEnrollmentId($student_id, 
            $student_course_id, $current_school_year_id);

        ?>

            <div class="row col-md-12">
                <div class="card">
                    <div class="card-header"><h4><?php echo $student_section;?></h4></div>

                    <div class="card-body">

                        <table class="table table-hover">
                            <thead>
                                <tr style="background-color:paleturquoise;" class="text-center">
                                    <td>Code</td>
                                    <td>Description</td>
                                    <td>Unit</td>
                                    <td>Choose</td>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                    $get_inserted_subjects = $con->prepare("SELECT 
                                        t2.*

                                        FROM student_subject  as t1

                                        INNER JOIN subject as t2 ON t2.subject_id = t1.subject_id

                                        WHERE t1.enrollment_id=:enrollment_id
                                        AND t1.student_id=:student_id");

                                    $get_inserted_subjects->bindValue(":enrollment_id", $student_enrollment_id);
                                    $get_inserted_subjects->bindValue(":student_id", $student_id);
                                    $get_inserted_subjects->execute();
                                    
                                    // $inserted_subjects = $get_inserted_subjects->fetchAll(PDO::FETCH_ASSOC);
                                    if($get_inserted_subjects->rowCount() > 0){


                                        while($row = $get_inserted_subjects->fetch(PDO::FETCH_ASSOC)){

                                            $subject_title = $row['subject_title'];
                                            $subject_id = $row['subject_id'];
                                            $unit = $row['unit'];
                                            $subject_code = $row['subject_code'];

                                            $url = "subject_shifting.php?subject_id=$subject_id&id=$student_id";
                                            echo "
                                                <tr class='text-center'>
                                                    <td>$subject_code</td>
                                                    <td>$subject_title</td>
                                                    <td>$unit</td>
                                                    <td>
                                                        <a href='$url'>
                                                            <button class='btn btn-sm btn-primary'>
                                                                <i class='fas fa-plus-circle'></i>
                                                            </button>
                                                        </a>
                                                    </td>
                                                </tr>
                                            ";
                                        }
                                    }
                                ?>
                            </tbody>
                        </table>
                        <?php
                        
                        
                        ?>
                    </div>
                </div>
            </div>
        <?php



    }

    if(
        isset($_GET['id'])
        && isset($_GET['subject_id'])
        && !isset($_GET['section_id'])

        ){
            
        if(isset($_SESSION['student_id'])){
            unset($_SESSION['student_id']);
        }

        $student_id = $_GET['id'];
        $subject_id = $_GET['subject_id'];

        $subject = new Subject($con, $registrarLoggedIn, null);

        $subject_title = $subject->GetSubjectTitle($subject_id);

        // $subject_title = $_GET['subject_title'];

        $student_username = $enroll->GetStudentUsername($student_id);

        $student = new Student($con, $student_username);

        $student_statusv2 = $student->GetStudentStatusv2();
        $admission_status = $student->GetStudentAdmissionStatus();

        // echo $student_statusv2;

        $student_name = $enroll->GetStudentFullName($student_id);
        $student_obj = $enroll->GetStudentCourseLevelYearIdCourseId($student_username);

        $student_course_id = $student_obj['course_id'];
        $student_course_level = $student_obj['course_level'];

        $section = new Section($con, $student_course_id);

        // $program_id = $_GET['pid'];

        $program_id = $enroll->GetStudentProgramId($student_course_id);

        $student_enrollment_id = $enrollment->GetEnrollmentId($student_id, $student_course_id,
            $current_school_year_id);

        $student_section = $section->GetSectionName();

        $student_enrollment_id = $enrollment->GetEnrollmentId($student_id, 
            $student_course_id, $current_school_year_id);


        if(
            isset($_POST['shift_subject_btn'])
            && isset($_POST['course_id'])
            && isset($_POST['subject_id'])
            
        ){

        
            $course_id = $_POST['course_id'];

            $selected_subject_id = $_POST['subject_id'];


            $currentStudentSubject = $con->prepare("SELECT student_subject_id FROM student_subject
                WHERE student_id=:student_id
                AND enrollment_id=:enrollment_id
                AND subject_id=:subject_id");

            $currentStudentSubject->bindValue(":student_id", $student_id);
            $currentStudentSubject->bindValue(":enrollment_id", $student_enrollment_id);
            $currentStudentSubject->bindValue(":subject_id", $subject_id);
            $currentStudentSubject->execute();

            if($currentStudentSubject->rowCount() > 0){

                $row = $currentStudentSubject->fetch(PDO::FETCH_ASSOC);

                $student_subject_id = $row['student_subject_id'];

                $update = $con->prepare("UPDATE student_subject
                    SET subject_id=:updated_subject_id
                    WHERE student_subject_id=:student_subject_id
                    AND enrollment_id=:enrollment_id
                ");

                $update->bindValue(":updated_subject_id", $selected_subject_id);
                $update->bindValue(":student_subject_id", $student_subject_id);
                $update->bindValue(":enrollment_id", $student_enrollment_id);
                
                if($update->execute()){
                    AdminUser::success("Successfully Shifted the selected subject",
                        "subject_shifting.php?id=$student_id");
                    exit();
                }
                
            }

            // echo $selected_subject_id;

        }
        ?>

            <div class="row col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5>Looking for: <?php echo $subject_title;?></h5>
                        <h3 class="text-muted text-center">Offered Sections</h3>
                    </div>

                    <div class="card-body">

                        <table class="table table-hover">
                            <thead>
                                <tr style="background-color:palegreen;" class="text-center">
                                    <td>Section</td>
                                    <td>Strand</td>
                                    <td>Code-Section</td>
                                    <td>Enrolled Student</td>
                                    <td>Choose</td>
                                </tr>
                            </thead>
                            <tbody>

                                <?php 
                                
                                    # GET Todays Sections that OFFERS $subject_title.

                                    $get_inserted_subjects = $con->prepare("SELECT t3.acronym, 
                                        t1.*, 
                                        t2.subject_id, t2.subject_code
                                    
                                    
                                        FROM course as t1

                                        INNER JOIN subject as t2 on t2.course_id = t1.course_id
                                        AND t2.subject_title=:subject_title
                                        
                                        LEFT JOIN program as t3 ON t3.program_id = t1.program_id
                                        WHERE t1.school_year_term=:school_year_term
                                        AND t1.active='yes'
                                        AND t1.course_level=:course_level
                                        AND t2.semester=:semester
                                        ");

                                    $get_inserted_subjects->bindValue(":subject_title", $subject_title);
                                    $get_inserted_subjects->bindValue(":school_year_term", $current_school_year_term);
                                    $get_inserted_subjects->bindValue(":course_level", $student_course_level);
                                    $get_inserted_subjects->bindValue(":semester", $current_school_year_period);
                                    // $get_inserted_subjects->bindValue(":enrollment_id", $student_enrollment_id);
                                    // $get_inserted_subjects->bindValue(":student_id", $student_id);
                                    $get_inserted_subjects->execute();
                                    
                                    // $inserted_subjects = $get_inserted_subjects->fetchAll(PDO::FETCH_ASSOC);

                                    if($get_inserted_subjects->rowCount() > 0){

                                        $_SESSION['student_id'] = $student_id;

                                        while($row = $get_inserted_subjects->fetch(PDO::FETCH_ASSOC)){

                                            $program_section = $row['program_section'];
                                            $course_id = $row['course_id'];
                                            $subject_id = $row['subject_id'];
                                            $subject_code = $row['subject_code'];
                                            $acronym = $row['acronym'];
                                            $capacity = $row['capacity'];
                                            $totalStudents = 0;

 

                                            $selected_section_url = "subject_shifting.php?section_id=$course_id&subject=$subject_title&id=$student_id";


                                            echo "
                                                <tr class='text-center'>
                                                    <td>$program_section</td>
                                                    <td>$acronym</td>
                                                    <td>$subject_code</td>
                                                    <td>$totalStudents / $capacity</td>
                                                    <td>
                                                        <form method='POST'>

                                                            <input type='hidden' name='course_id' value='$course_id'>
                                                            <input type='hidden' name='subject_id' value='$subject_id'>
                                                            
                                                            <button name='shift_subject_btn' type='submit' class='btn btn-sm btn-primary'>
                                                                <i class='fas fa-hand-pointer'></i>
                                                            </button>  
                                                        </form>
 

                                                    </td>
                                                </tr>
                                            ";
                                        }
                                    }

                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php

    }


 


?>