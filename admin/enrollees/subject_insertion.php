<script src="../assets/js/common.js"></script>

<?php 
    include('../registrar_enrollment_header.php');
    require_once('../../enrollment/classes/StudentEnroll.php');
    require_once('../../enrollment/classes/OldEnrollees.php');
    require_once('../../enrollment/classes/Pending.php');
    require_once('../../enrollment/classes/Enrollment.php');
    include('../../includes/classes/Student.php');

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

        $unique_form_id = $enrollment->GenerateEnrollmentFormId();

        $get_student_enrollment_form_id = $enrollment->GetEnrollmentFormId($student_id, $student_course_id, $school_year_id);

        $enrollment_id = $enrollment->GetEnrollmentId($student_id, $student_course_id, $school_year_id);

        // Student who click the apply for next semester as new semester had established
        if($old_student_status == "Regular" || $old_student_status == "Returnee" || $old_student_status == "Transferee"){

            if(isset($_POST['subject_load_btn']) 
                && isset($_POST['unique_enrollment_form_id']) 
                ){

                // $subject_ids = $_POST['subject_ids'];
                
                // $pre_subject_ids = $_POST['pre_subject_ids'];
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


                // foreach ($subject_ids as $key => $value) {
                
                //     $subject_id = $value;
                //     $query_sub = $con->prepare("SELECT pre_subject_id 
                //         FROM subject
                //         WHERE subject_id=:subject_id
                //         LIMIT 1");

                //     $query_sub->bindValue(":subject_id", $subject_id);
                //     $query_sub->execute();

                //     $pre_subject_id = $query_sub->rowCount() > 0 ? $query_sub->fetchColumn() : -1;

                //     $array_error = [];

                //     # Validation of <prerequisite
                //     // TODO: FIX THE BUG.
                //     $validSemesterScope = $studentEnroll->CheckPreRequisiteSubject($student_username, $subject_id);

                //     if($validSemesterScope == false){
                //         $errorMessage = "Not within the semester scope";
                //         echo $errorMessage;
                //         array_push($array_error, $errorMessage);
                //         // echo "<script>alert('$errorMessage');</script>";
                //     }

                //     if($pre_subject_id != 0){
                //         // echo $pre_subject_id;

                //         $failedSubjects = $studentEnroll->GetSHSStudentFailedSubject($student_username);
                //         $unavailable_array = [];

                //         foreach ($failedSubjects as $key => $val) {
                //             //
                //             if($val['subject_id'] == $pre_subject_id){
                //                 // Subject id that are unavailable.
                //                 array_push($unavailable_array, $subject_id);
                //             }
                //         }
                //         // print_r($unavailable_array);
                //         // Better performance validation.
                //         $unAvailableArray = array();

                //         foreach ($unavailable_array as $key => $subject_id_failed) {
                //             // subject_id_failed as a key and true is a value
                //             $unAvailableArray[$subject_id_failed] = true;
                //         }

                //         if(isset($unAvailableArray[$subject_id])){
                //             array_push($array_error, $subject_id);
                //             echo "subject_id $subject_id is not available to take";
                //             echo "<br>";
                //         }
                //     }
                //     # 2. If subject_id is already in the student_subject (doubled enrolled).
                //     # 3. Check if the subject is within only the scoped of 
                //     # what subject should be included in the subject loads.
                //     # 4. If the subject that registrar wanted to enroll must be marked first.
                //     $shsStudentSubject = $oldEnroll->GetSHSStudentEnrolledSubjects($student_username, $subject_id);
                
                //     $subjectLoadsIds = array();
                //     // Loop through the results and add the subject IDs to the array
                //     foreach ($shsStudentSubject as $key => $students_enrolled_subject) {
                //         // subject_id as a key and true is a value
                //         $subjectLoadsIds[$students_enrolled_subject['subject_id']] = true;
                //     }

                //     // Check if the subject ID exists in the array
                //     # 2. If subject_id is already in the student_subject (doubled enrolled).
                //     if (isset($subjectLoadsIds[$subject_id])) {
                //         echo $subject_id . " is already in the subject loads";
                //         array_push($array_error, $subject_id);
                //         echo "<br>";
                //     }
                    
                //     if(empty($array_error))
                //     {
                //         // $sql_insert = $con->prepare("INSERT INTO student_subject 
                //         //     (student_id, subject_id, school_year_id, course_level)
                //         //     VALUES(:student_id, :subject_id, :school_year_id, :course_level)");

                //         $sql_insert->bindValue(":student_id", $student_id);
                //         $sql_insert->bindValue(":subject_id", $subject_id);
                //         $sql_insert->bindValue(":school_year_id", $school_year_id);
                //         $sql_insert->bindValue(":course_level", $student_course_level);

                //         if($sql_insert->execute()){
                //         // if($sql_insert->execute()){

                //             $successInsertingSubjectLoad = true;
                //             array_push($array_success, $subject_id);
                //         }
                //         // If new enrollee Update into Old
                        
                //         // $newIntoOldSuccess = $oldEnroll->UpdateSHSStudentNewToOld($student_id);
                //     }
                // }
                // if($isSectionFull == true){
                //     echo "
                //         <script>
                //             alert('Section is full. It should validated at registering the student.!')
                //         </script>
                //     ";
                //     array_push($array_error, "Full section");
                // }
                
                // 
                if($successInsertingSubjectLoad == true 
                    ){
                    // echo "hitt";
                    // Update students status
                    // Grade 11 1nd sem -> Grade 11 2nd sem & Enroll Student
                    // Grade 12 1st sem -> Grade 12 2nd sem & Enroll Student
                    // Graduate What todo next?.

                    $wasSuccess = $oldEnroll->UpdateSHSStudentStatus($student_username);
                    
                }

                $isSuccess = false;

                // if(sizeof($array_success) > 0 && empty($array_error)){

                //     // Check registrar should include all available subjects
                //     // if not yet ALL included, no updating status will happen
                //     // TODO:
                //     if($wasSuccess){
                //         // echo "was Success ";
                        
                //     }else{
                //         echo "not success";
                //     }

                //     foreach ($array_success as $key => $subject_ids_inserted) {
                //         # subject_id that added in the student_subject db
                //         echo "You have successfully inserted subject_id $subject_ids_inserted to the database";
                //         $isSuccess = true;
                //     }

                //     if($isSuccess == true){
                //         $url = directoryPath . "old_enrollees.php";
                //         header("Location: $url");
                //         exit();
                //     }
                // }

                $new_regular_shs_subjects = $studentEnroll->GetStudentsStrandSubjects($student_username);

                // print_r($new_regular_shs_subjects);
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

                    # Enrolled Student.

                    $wasSuccess = $oldEnroll->EnrolledStudentInTheEnrollmentv2($school_year_id,
                        $student_id, $get_student_enrollment_form_id);

                    if($wasSuccess){

                        # Update student table
                        $newToOldSuccess = $oldEnroll->UpdateSHSStudentNewToOld($student_id);

                        if($newToOldSuccess){

                            # redirect to the receipt page.
                            if($subjectInitialized == true){

                                AdminUser::success("Successfully inserted the subjects, Student has been officially enrolled", "subject_insertion.php?inserted=success&id=$student_id");
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
                                # Check joining the query for the student_subject_grade student_subject_id
                                # if is student_subject_id, it it should delete all the student_subject_grade
                                # 
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
                            <button type="submit" name="subject_load_btn" class="btn btn-success btn-sm"
                            onclick="return confirm('Are you sure you want to insert & enroll??')"
                            >
                                Enroll Subject
                            </button>
                            <!-- <button type="submit" name="unload_subject_btn" class="btn btn-danger btn-sm">Unload Subject</button> -->
                        </form>
                    </div>
            <?php   
        }
    }


    if(isset($_GET['inserted'])){


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

                                                        // if($status === "checked"){
                                                        //     $subject_status = "CREDITED";
                                                        // }
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
                                            // Clear the subject_ids array from $_SESSION if needed
                                            // unset($_SESSION['subject_ids']);
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
                                        <button type="submit" class="btn btn-success"
                                            name="insert_enroll_transferee" 
                                            onclick="return confirm('Are you sure you to print?')">Print</button>

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
