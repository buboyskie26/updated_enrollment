<script src="../assets/js/common.js"></script>

<?php 
    include('../registrar_enrollment_header.php');
    require_once('../../enrollment/classes/StudentEnroll.php');
    require_once('../../enrollment/classes/OldEnrollees.php');
    require_once('../../enrollment/classes/Pending.php');
    require_once('../../enrollment/classes/Enrollment.php');
    require_once('../../enrollment/classes/Section.php');
    require_once('../../includes/classes/Student.php');

    // require '../../vendor/autoload.php';
    require_once __DIR__ . '/../../vendor/autoload.php';
    use Dompdf\Dompdf;
    use Dompdf\Options;

    // if (class_exists('Dompdf\Dompdf')) {
    //     echo "autoload.php is working correctly.";
    // } else {
    //     echo "autoload.php is not working.";
    // }

    require_once('../classes/Course.php');

    $studentEnroll = new StudentEnroll($con);
    $oldEnroll = new OldEnrollees($con, $studentEnroll);
    $pending = new Pending($con);

    $course = new Course($con, $studentEnroll);

    if(!isset($_SESSION['registrarLoggedIn'])){
        header("Location: " . base_url . "/index.php");
        exit();
    }
    else if(isset($_SESSION['registrarLoggedIn']) 
        && isset($_GET['username'])
         && isset($_GET['id'])
         && !isset($_GET['inserted'])

        ){

        unset($_SESSION['regular_subject_ids_v2']);

        $student_username = $_GET['username'];
        $old_student_status = $oldEnroll->GetOldStudentStatus($student_username);
        $studentNewEnrolee = $oldEnroll->DoesStudentNewEnrollee($student_username);
            
        $base_url = 'http://localhost/elms/admin';

        $student_id = $_GET['id'];


        $student_fullname = $studentEnroll->GetStudentFullName($student_id);
        $student_firstname = $studentEnroll->GetStudentFirstname($student_id);
        $username = $studentEnroll->GetStudentUsername($student_id);

        $student = new Student($con, $username);
        $student_unique_id = $student->GetStudentUniqueId();

        $pending_form_submission = $pending->GetSubmittedOn($student_firstname);

        $confirmStudent = "enrolledStudent(\"$student_username\")";

        // $username2 = "(\"$username\")";
        $recommendedSubject = $studentEnroll->GetSHSNewStudentSubjectProgramBased($student_username);
        $studentCourseYear = $studentEnroll->GetStudentCourseName($student_username);
        $school_year_obj = $studentEnroll->GetActiveSchoolYearAndSemester();

        $student_course_level = $studentEnroll->GetStudentCourseLevel($student_username);

        $student_course_id = $studentEnroll->GetStudentCourseId($student_username);
        $student_program_id = $studentEnroll->GetStudentProgramId($student_course_id);

        // echo $student_id;
        $student_program_section = $studentEnroll->GetStudentProgramSection($student_course_id);

        $grade_level = $student_course_level >=  11  ? "Grade" : "";

        $current_school_year_term = $school_year_obj['term'];
        $current_school_year_semester = $school_year_obj['period'];
        $school_year_id = $school_year_obj['school_year_id'];
    
        $forget_establish_time_url = $base_url . '/schedule/create.php';

        $unscheduledSubjectLeft = false;

        $display = "block";
        
      
        $enrollment = new Enrollment($con, $studentEnroll);
        $section = new Section($con, $student_course_id);


        $isSectionFull = $section->CheckSectionIsFull($student_course_id);


        $unique_form_id = $enrollment->GenerateEnrollmentFormId();

        $get_student_enrollment_form_id = $enrollment->GetEnrollmentFormId($student_id, $student_course_id, $school_year_id);

        $enrollment_id = $enrollment->GetEnrollmentId($student_id,
            $student_course_id, $school_year_id);

        // echo "enrollment_id: $enrollment_id";

        $student_section_obj = $section->GetSectionObj($student_course_id);

        $student_curren_course_program_id = $student_section_obj['program_id'];
        $student_current_course_level = $student_section_obj['course_level'];
        $student_current_capacity = $student_section_obj['capacity'];


        $updatedTotalStudent = $section->GetTotalNumberOfStudentInSection($student_course_id,
            $school_year_id);

        // echo $updatedTotalStudent . " school ear";

        if(isset($_POST['section_full_btn'])){

            # Get Aaailable section next to the recently full section.
            $get_prev_section = $con->prepare("SELECT course_id FROM course
                WHERE program_id=:program_id
                AND active=:active
                AND course_level=:course_level
                AND school_year_term=:school_year_term
                ORDER BY course_id DESC
                LIMIT 1");
            
            $get_prev_section->bindValue(":program_id", $student_program_id);
            $get_prev_section->bindValue(":active", "yes");
            $get_prev_section->bindValue(":course_level", $student_course_level);
            $get_prev_section->bindValue(":school_year_term", $current_school_year_term);
            $get_prev_section->execute();

            if($get_prev_section->rowCount() > 0){

                $available_course_id = $get_prev_section->fetchColumn();

                $available_section_obj = $section->GetSectionObj($available_course_id);

                $available_section_name = $available_section_obj['program_section'];

                $update_enrollment_success = $enrollment->UpdateSHSStudentEnrollmentCourseId(
                    $enrollment_id, $available_course_id);
                
                $update_student_course_id = $student->UpdateStudentCourseId(
                    $student_course_id, $student_id, $available_course_id);

                if($update_enrollment_success == true && $update_student_course_id){

                    AdminUser::success("Student section moved to: $available_section_name",
                        "subject_insertion.php?username=$student_username&id=$student_id");
                    exit();
                }

            }                

            # Update enrollment student course_id

        }

        if($old_student_status == "Regular" || $old_student_status == "Returnee" || $old_student_status == "Transferee"){

            if(isset($_POST['subject_load_btn']) 
                && isset($_POST['unique_enrollment_form_id']) 
                ){
                
                $array_success = [];

                $subject_program_id = 0;

                $unique_enrollment_form_id = $_POST['unique_enrollment_form_id'];

                $successInsertingSubjectLoad = false;

                // Update student + 1 to the total_student
                $active = "yes";
                $is_full = "no";
                $total_student = 1;

                $sql_insert = $con->prepare("INSERT INTO student_subject 
                    (student_id, subject_id, school_year_id,
                        course_level, enrollment_id, subject_program_id)
                    VALUES(:student_id, :subject_id, :school_year_id,
                        :course_level, :enrollment_id, :subject_program_id)");

                if($successInsertingSubjectLoad == true){
                    $wasSuccess = $oldEnroll->UpdateSHSStudentStatus($student_username);
                }

                $isSuccess = false;

                $new_regular_shs_subjects = $studentEnroll->GetStudentsStrandSubjects($student_username);

                // print_r($new_regular_shs_subjects);
                
                $is_inserted_all = false;

                $subjectInitialized = false;

                foreach ($new_regular_shs_subjects as $key => $value) {
                    # code...

                    $subject_id = $value['subject_id'];
                    $subject_program_id = $value['subject_program_id'];

                    if($subject_id != 0){
                        $_SESSION['regular_subject_ids_v2'][] = array(
                            'subject_id' => $subject_id
                        );
                        $subjectInitialized = true;
                    }

                    // echo "redirect to receipt page";
                    # Check if subjects already enrolled.

                    # Insert all subjects
                    $sql_insert->bindValue(":student_id", $student_id);
                    $sql_insert->bindValue(":subject_id", $subject_id);
                    $sql_insert->bindValue(":school_year_id", $school_year_id);
                    $sql_insert->bindValue(":course_level", $student_course_level);
                    $sql_insert->bindValue(":enrollment_id", $enrollment_id);
                    $sql_insert->bindValue(":subject_program_id", $subject_program_id);

                    if($sql_insert->execute()){

                        $is_inserted_all = true;
                    }
                }
           
                if($is_inserted_all == true){

                    $isSubjectCreated = false;

                    # Enrolled Student.

                    $wasSuccess = $oldEnroll->EnrolledStudentInTheEnrollmentv2($school_year_id,
                        $student_id, $get_student_enrollment_form_id);

                    if($wasSuccess){

                        $section_obj = $section->GetSectionObj($student_course_id);

                        $sectionTotalStudent = $section->GetTotalNumberOfStudentInSection($student_course_id,
                            $school_year_id);

                        $capacity = $section_obj['capacity'];
                        $program_section = $section_obj['program_section'];
                        $course_program_id = $section_obj['program_id'];
                        $course_level = $section_obj['course_level'];
                        # Check if section is full

                        if($sectionTotalStudent >= $capacity 
                            && $current_school_year_semester == "First"){

                            # yes mark as full
                            # Update Previous Section into Is FULL.
                            $update_isfull = $section->SetSectionIsFull($student_course_id);
                            
                            $new_program_section = $section->AutoCreateAnotherSection($program_section);

                            $createNewSection = $section->CreateNewSection($new_program_section, 
                                $course_program_id, $course_level,
                                $current_school_year_term);

                            # Create Subject In that section
                            
                            if($createNewSection == true && $update_isfull == true){

                                $createNewSection_Id = $con->lastInsertId();

                                $get_subject_program = $con->prepare("SELECT * 
                                
                                    FROM subject_program

                                    WHERE program_id=:program_id
                                    AND course_level=:course_level
                                    ");

                                $get_subject_program->bindValue(":program_id", $course_program_id);
                                $get_subject_program->bindValue(":course_level", $course_level);
                                $get_subject_program->execute();

                                if($get_subject_program->rowCount() > 0){

                                    $insert_section_subject = $con->prepare("INSERT INTO subject
                                        (subject_title, description, subject_program_id, unit, semester, program_id, course_level, course_id, subject_type, subject_code, pre_requisite)
                                        VALUES(:subject_title, :description, :subject_program_id, :unit, :semester, :program_id, :course_level, :course_id, :subject_type, :subject_code, :pre_requisite)");

                                    while($row = $get_subject_program->fetch(PDO::FETCH_ASSOC)){

                                        $program_program_id = $row['subject_program_id'];
                                        $program_course_level = $row['course_level'];
                                        $program_semester = $row['semester'];
                                        $program_subject_type = $row['subject_type'];
                                        $program_subject_title = $row['subject_title'];
                                        $program_subject_description = $row['description'];
                                        $program_subject_unit = $row['unit'];
                                        $program_subject_pre_requisite = $row['pre_req_subject_title'];

                                        $program_subject_code = $row['subject_code'] ."-". $new_program_section; 
                                        // $program_subject_code = $row['subject_code'];

                                        $insert_section_subject->bindValue(":subject_title", $program_subject_title);
                                        $insert_section_subject->bindValue(":description", $program_subject_description);
                                        $insert_section_subject->bindValue(":subject_program_id", $program_program_id);
                                        $insert_section_subject->bindValue(":unit", $program_subject_unit);
                                        $insert_section_subject->bindValue(":semester", $program_semester);
                                        $insert_section_subject->bindValue(":program_id", $course_program_id);
                                        $insert_section_subject->bindValue(":course_level", $program_course_level);
                                        $insert_section_subject->bindValue(":course_id", $createNewSection_Id);
                                        $insert_section_subject->bindValue(":subject_type", $program_subject_type);
                                        $insert_section_subject->bindValue(":subject_code", $program_subject_code);
                                        $insert_section_subject->bindValue(":pre_requisite", $program_subject_pre_requisite);

                                        // $insert_section_subject->execute();
                                        if($insert_section_subject->execute()){
                                            $isSubjectCreated = true;
                                            // echo "New Section $new_program_section is created and student has confirmed.";
                                        }
                                    }
                                    // if($isSubjectCreated == true){
                                    //     // echo "Successfully populated subjects in course_id $course_id";
                                    // }
                                }
                            }

                            
                        }
                   

                        # Update student table
                        $newToOldSuccess = $oldEnroll->UpdateSHSStudentNewToOld($student_id);

                        if($newToOldSuccess && $isSubjectCreated == false){

                            # redirect to the receipt page.
                            if($subjectInitialized == true){
                                // echo "truee";
                                AdminUser::success("Successfully inserted the subjects, Student has been officially enrolled", "subject_insertion.php?enrolled=success&id=$student_id");
                                // header("Location: ");
                                exit();
                            }
                        }

                        if($newToOldSuccess && $isSubjectCreated == true){

                            # redirect to the receipt page.
                            if($subjectInitialized == true){
                                AdminUser::success("Successfully inserted the subjects, Student has been officially enrolled & Section is $program_section now full",
                                    "subject_insertion.php?enrolled=success&id=$student_id");
                                // header("Location: ");
                                exit();
                            }
                        }

                    }
                }
            }
            
            ?>
                <div class="row col-md-12">

                    <div class="card">
                        <div class="card-header">
                            <h4 class="text-center text-primary">Enrollment Form</h4>
                            <hr>
                        </div>


                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label class="mb-2" for="">Enrollment ID</label>
                                        <input readonly value="<?php echo $get_student_enrollment_form_id;?>" type="text" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label class="mb-2" for="">Student Type</label>
                                        <input readonly value="New" type="text" class="form-control">
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label class="mb-2" for="">Student no.</label>
                                        <input readonly value="<?php echo $student_unique_id;?>" type="text" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="mb-2" for="">Status Evaluation.</label>
                                        <input readonly value="Evaluation" type="text" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label class="mb-2" for="">Submitted on.</label>
                                        <input readonly value="<?php echo $pending_form_submission;?>" type="text" class="form-control">
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="table-responsive" style="margin-top:5%;"> 
                        <form action="" method="POST">
                            <h4 style="font-weight: bold;" class="mb-3 mt-4 text-primary text-center"><?php echo $student_program_section; ?> Subjects Curriculum</h4>
                            <span>
                                Section Capacity:
                                <?php 
                                    echo $updatedTotalStudent;
                                ?> / <?php echo $student_current_capacity;?>
                            </span>
                            <?php

                                if($isSectionFull == true 
                                // && $current_school_year_semester != "Second"
                                ){
                                    echo "
                                        <form method='POST'>
                                            <button type='submit' name='section_full_btn' class='btn btn-primary btn-sm'>
                                                Move to Available Section
                                            </button>
                                        </form>
                                    ";
                                }
                            ?>

                            <table class="table table-striped table-bordered table-hover "  style="font-size:13px" cellspacing="0"  > 
                                <thead>
                                    <tr class="text-center"> 
                                        <th rowspan="2">ID</th>
                                        <th rowspan="2">Code</th>
                                        <th rowspan="2">Description</th>  
                                        <th rowspan="2">Unit</th>
                                        <th colspan="4">Schedule</th> 
                                    </tr>
                                    <tr class="text-center">
                                        <th>Day</th>
                                        <th>Time</th>
                                        <th>Room</th> 
                                    </tr>	
                                </thead> 
                                    <tbody>
                                        <?php

                                            // echo $old_student_status;
                                            $listOfSubjects = $studentEnroll->GetStudentsStrandSubjects($student_username);
                                            $failed_subject_id = null;
                                            $unavaible_subject_id = null;
                                            // poll
                                            foreach ($listOfSubjects as $key => $value) {

                                                $subject_id = $value['subject_id'];
                                                $schedule_day = $value['schedule_day'] == "" ? '-' : $value['schedule_day'];
                                                $time_from = $value['time_from'] == "" ? '-' : $value['time_from'];
                                                $time_to = $value['time_to'] == "" ? '-' : $value['time_to'];
                                                $room = $value['room'] == "" ? '-' : $value['room'];

                                                $pre_subject_id = $value['pre_subject_id'] != 0 ? $value['pre_subject_id'] : "";
                                                $failed_remark = "Failed";

                                                // echo $pre_subject_id . " ";
                                                // MUST BE REMOVED.
                                                $query_failed = $con->prepare("SELECT subject_id 

                                                    FROM student_subject_grade

                                                    -- AND subject_id=:subject_id
                                                    WHERE remarks=:remarks
                                                    AND student_id=:student_id

                                                    LIMIT 1");
                                                
                                                $query_failed->bindValue(":remarks", $failed_remark);
                                                $query_failed->bindValue(":student_id", $student_id);
                                                // $query_failed->bindValue(":subject_id", $subject_id);
                                                $query_failed->execute();

                                                if($query_failed->rowCount() > 0){
                                                    // echo "got " . $subject_id;
                                                    $failed_subject_id = $query_failed->fetchColumn();
                                                }
                                            
                                                echo '<tr class="text-center">'; 
                                                        // echo 
                                                        // '<td  class="text-center">
                                                        //     <input name="subject_ids[]" class="checkbox"  value="'.$subject_id.'" type="checkbox">
                                                        // </td>';
                                                        echo '<td>'.$value['subject_id'].'</td>';
                                                        echo '<td>'.$value['subject_code'].'</td>';
                                                        echo '<td>'.$value['subject_title'].'</td>';
                                                        echo '<td>'.$value['unit'].'</td>';
                                                        echo '<td>'.$schedule_day.'</td>';
                                                        echo '<td>'.$time_from.' - '.$time_to.'</td>';
                                                        echo '<td>'.$room.'</td>';
                                                echo '</tr>';

                                            }
                                        ?>
                                    </tbody> 
                            </table>

                            <?php 
                                # Check if Graduate, Tentative
                                $doesFinished = $oldEnroll->DoesStudentFinishedAllSubjectLoads($student_username);
                                // if($doesFinished == true){
                                //     echo "
                                //         <p>* SHS Candidate for Graduation</p>
                                //     ";
                                // }else{
                                //     echo "Have more Subjects to finished";
                                // }
                            ?>

                            <input type="hidden" name="unique_enrollment_form_id" value="<?php echo $unique_form_id;?>">

                            <?php 

                                $checkIfCashierEvaluated = $enrollment->CheckEnrollmentCashierApproved($student_id,
                                    $student_course_id, $school_year_id);
                                    
                                if($isSectionFull == true){

                                    echo "
                                        <button disabled class='btn btn-outline-success'>Enroll Subject</button>
                                    ";
                                }else if($isSectionFull == false && $checkIfCashierEvaluated == true){
                                    ?>
                                        <button type="submit" name="subject_load_btn" 
                                            class="btn btn-success btn-sm"
                                            onclick="return confirm('Are you sure you want to insert & enroll??')"
                                        >
                                            Approve Enrollment
                                        </button>
                                    <?php
                                }
                                else if($isSectionFull == false && $checkIfCashierEvaluated == false){
                                    ?>
                                        <button type="button" class="btn btn-primary btn-sm">
                                            Waiting
                                        </button>
                                    <?php
                                }
                            ?>
                            <!-- <button type="submit" name="unload_subject_btn" class="btn btn-danger btn-sm">Unload Subject</button> -->
                        </form>
                    </div>
            <?php   
        }
    }

    if(isset($_GET['enrolled'])){
        if(isset($_GET['id'])){


            $student_id = $_GET['id'];
            $student_fullname = $studentEnroll->GetStudentFullName($student_id);
            $school_year_obj = $studentEnroll->GetActiveSchoolYearAndSemester();
            $school_year_id = $school_year_obj['school_year_id'];
            $current_school_year_term = $school_year_obj['term'];
            $current_school_year_period = $school_year_obj['period'];

                // echo $student_id;
            $username = $studentEnroll->GetStudentUsername($student_id);

            $student = new Student($con, $username);
            $student_address = $student->GetStudentAddress();
            
            $student_contact = $student->GetGuardianNameContact();

            $student_course_level = $studentEnroll->GetStudentCourseLevel($username);
            $student_course_id = $studentEnroll->GetStudentCourseId($username);
            // echo $student_id;
            $student_course_level = $studentEnroll->GetStudentCourseLevel($username);
            $student_course_id = $studentEnroll->GetStudentCourseId($username);
            $student_program_section = $studentEnroll->GetStudentProgramSection($student_course_id);

            ?>
            <div class="row col-md-12">
                <div class="container">
                    <h4 class="text-center text-primary">Student Information</h4>
                    <hr>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="mb-2" for="">Name</label>
                                <input readonly value="<?php echo $student_fullname; ?>" type="text" class="form-control">
                            </div>
                            <div class="form-group">
                                <label class="mb-2" for="">Address</label>
                                <input readonly value="<?php echo $student_address; ?>" type="text" class="form-control">
                            </div>
                            <div class="form-group">
                                <label class="mb-2" for="">Contact Number.</label>
                                <input readonly value="<?php echo $student_contact; ?>" type="text" class="form-control">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="mb-2" for="">Program & Section</label>
                                <input readonly value="<?php echo $student_program_section ?>" type="text" class="form-control">
                            </div>
                            <div class="form-group">
                                <label class="mb-2" for="">Semester</label>
                                <input readonly value="<?php echo $current_school_year_period ?>" type="text" class="form-control">
                            </div>
                            <div class="form-group">
                                <label class="mb-2" for="">Academic Year</label>
                                <input readonly value="<?php echo $current_school_year_term ?>" type="text" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>
                <hr>
                <hr>

                <div class="container">
                    <h4 class="text-center text-success">List of Enrolled Subject</h4>
                    <h5 class="text-center text-muted">S.Y <?php echo $current_school_year_term; ?> <?php echo $current_school_year_period?> Semester</h5>
                    <div class="table-responsive" style="margin-top:5%;"> 

                        <form method="POST">

                            <table class="table table-striped table-bordered table-hover "  style="font-size:13px" cellspacing="0"  > 
                                <thead>
                                    <tr class="text-center"> 
                                        <th rowspan="2">ID</th>
                                        <th rowspan="2">Code</th>
                                        <th rowspan="2">Description</th>  
                                        <th rowspan="2">Unit</th>
                                    </tr>	
                                </thead>

                                <tbody>

                                    <?php 

                                        $totalUnits = 0;

                                        if (isset($_SESSION['regular_subject_ids_v2']) && is_array($_SESSION['regular_subject_ids_v2'])) {
                                            foreach ($_SESSION['regular_subject_ids_v2'] as $subject) {

                                                $subject_id = isset($subject['subject_id']) ? $subject['subject_id'] : 0;

                                                if ($subject_id != 0) {

                                                    $subject_id =  $subject['subject_id'];
                                                    // $status =  $subject['status'];

                                                    $sql = $con->prepare("SELECT * FROM subject
                                                        WHERE subject_id=:subject_id");
                                                    
                                                    $sql->bindValue(":subject_id", $subject_id);
                                                    $sql->execute();

                                                    if($sql->rowCount() > 0){

                                                        $row = $sql->fetch(PDO::FETCH_ASSOC);

                                                        $subject_id = $row['subject_id'];
                                                        $subject_title = $row['subject_title'];
                                                        $subject_code = $row['subject_code'];
                                                        $unit = $row['unit'];

                                                        $subject_status = "";
                                                        $totalUnits += $unit;

                                                        echo "
                                                            <tr class='text-center'>

                                                                <td>$subject_id</td>
                                                                <td>$subject_code</td>
                                                                <td>$subject_title</td>
                                                                <td>$unit</td>
                                                            </tr>
                                                        ";
                                                    }
                                                }
                                            }
                                        }   
                                    ?>
                                </tbody>
                            
                                <?php
                                    if($totalUnits != 0){
                                        echo "
                                        <tr>
                                            <td colspan='3'style='text-align: right;' >Total Units</td>
                                            <td style='font-weight:bold;'>$totalUnits</td>
                                        </tr> 
                                        ";
                                        
                                    }
                                ?>
                            
                            </table>
                            <?php
                                if($totalUnits != 0){
                                    ?>
                                    <form action="generate_pdf.php" method="POST">

                                        <button type="submit" class="btn btn-success"
                                            name="generate_pdf">Print
                                        </button>

                                    </form>
                                        <a href='../admission/index.php'>
                                            <button type='button' class='btn btn-outline-primary btn-sm'>Go back</button>
                                        </a>
                                    <?php
                                    
                                }
                                
                            ?>
                        </form>
                    </div>
                </div>

            </div>

            <?php
        }
    }
?>





<script>
        // window.addEventListener('load', function() {
        //     document.getElementById('select-all-checkbox').click();
        // });

        
        // document.getElementById('select-all-checkbox').addEventListener('click', function() {
        // var checkboxes = document.getElementsByClassName('checkbox');

        // for (var i = 0; i < checkboxes.length; i++) {
        //     checkboxes[i].checked = this.checked;
        // }
        // });


</script>
