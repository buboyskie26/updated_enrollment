<script src="../assets/js/common.js"></script>

<?php 

    include('../registrar_enrollment_header.php');
    include('../../enrollment/classes/StudentEnroll.php');
    include('../../enrollment/classes/OldEnrollees.php');
    include('../../enrollment/classes/Section.php');
    include('../../enrollment/classes/Enrollment.php');
    include('../classes/Course.php');
    include('../classes/Subject.php');


    ?>
        <style>

            .process-status{
                display: flex;
                flex-direction: row;
                justify-content: center;
                font-style: normal;
                font-weight: 700;
                align-items: center;
                margin-top: 80px;
                padding: 0px 53px;
                gap: 1px;
                isolation: isolate;
                width: 100%;
                height: 74px;
                background: #1A0000;
            }

            .process-status .selection{
                margin-top: 5px;
            }

            .process-status .checkDetails{
                color: white;
            }

            .process-status #line-1, #line-2{
                color: #888888;
            }

            .findSection, .subConfirm{
                color: #888888;
            }

            .form-header h2{
                font-style: normal;
                font-weight: 700;
                font-size: 36px;
                line-height: 43px;
                display: flex;
                align-items: center;
                color: #BB4444;
            }   

            .header-content{
                display: flex;
                flex-direction: row;
                justify-content: space-between;
                margin-top: 50px;
                padding: 0px;
                gap: 10px;
                width: 90%;
                height: 43px;
                align-items: center;
            }

            .action{
                border: none;
                background: transparent;
                color: #E85959;
            }

            .action:hover{
                color: #9b3131;
            }

            .student-table{
                display: flex;
                flex-direction: row;
                align-items: flex-start;
                padding: 5px 0px;
                width: 100%;
                height: 58px;
            }

            .inner-student-table{
                table-layout: fixed;
                border-collapse: collapse;
                width: 100%;
                text-align: center;
            }

            /* table{
                table-layout: fixed;
                border-collapse: collapse;
                width: 100%;
                text-align: center;
            }

            tbody{
                font-style: normal;
                font-weight: 400;
                font-size: 17px;
                align-items: center;
            } */

            .choices{
                display: flex;
                flex-direction: row;
                justify-content: center;
                align-items: center;
                margin-top: 80px;
                padding: 20px 53px 0px;
                gap: 1px;
                width: 100%;
                height: 74px;
                background: #1A0000;
                flex: none;
                order: 2;
                align-self: stretch;
                flex-grow: 0;
            }
            .selection-btn{
                display: flex;
                flex-direction: row;
                justify-content: center;
                align-items: center;
                padding: 5px 20px;
                gap: 10px;
                width: 233px;
                height: 54px;
                background: #EFEFEF;
                border: none;
                font-style: normal;
                font-weight: 400;
                font-size: 20px;
            }

            .bg-content{
                display: flex;
                flex-direction: column;
                align-items: center;
                padding: 50px 0px;
                width: 100%;
                height: auto;
                background: #EFEFEF;
            }

            .form-details{
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: flex-start;
                padding: 32px 26px;
                gap: 19px;
                width: 85%;
                height: auto;
                background: #FFFFFF;
                box-shadow: 0px 5px 20px rgba(0, 0, 0, 0.25);
                border-radius: 10px;
                margin-top: 30px;
            }

            .form-details h3{
                display: flex;
                align-items: center;
                font-style: normal;
                font-weight: 700;
                font-size: 36px;
                line-height: 43px;
                color: #BB4444;
            }

            form{
                flex: none;
                order: 1;
                align-self: stretch;
                flex-grow: 0;
            }
            .back-menu{
                display: flex;
                flex: row;
                align-items: center;
                padding: 8px 40px;
                gap: 8px;
                width: 100%;
                height: 46px;
            }
            .admission-btn{
                border: none;
                background: none;
                color: #BB4444;
                font-style: normal;
                font-weight: 700;
                font-size: 16px;
            }
            .admission-btn:hover{
                color: #863131;
            }

        </style>
    <?php


    $enroll = new StudentEnroll($con);
    $old_enroll = new OldEnrollees($con, $enroll);
    $course = new Course($con, $enroll);

    if(isset($_GET['id'])){

        $student_id = $_GET['id'];

         
        $subject = new Subject($con, $student_id);

        $school_year_obj = $enroll->GetLatestSchoolYearAndSemester();

        $current_term = $school_year_obj[0];
        $current_semester = $school_year_obj[1];
        $school_year_id = $school_year_obj[2];

        $student_username = $enroll->GetStudentUsername($student_id);

        $userLoggedInId = $enroll->GetStudentId($student_username);
        $course_main_id = $enroll->GetStudentCourseId($student_username);

        $student_course = $course->GetStudentCourse($student_username);

        $student_obj = $enroll->GetStudentCourseLevelYearIdCourseId($student_username);

        $student_course_level = $student_obj['course_level'];
        // $student_course_level = $enroll->GetStudentCourseLevel($student_username);
        $section_name = $enroll->GetStudentCourseName($student_username);

        $student_course_id = $student_obj['course_id'];
        $student_status = $student_obj['student_status'];

        $GRADE_ELEVEN = 11;
        $GRADE_TWELVE = 12;
        $FIRST_SEMESTER = "First";
        $SECOND_SEMESTER = "Second";

        $get_student = $con->prepare("SELECT * FROM student
            WHERE student_id=:student_id
            AND student_status=:student_status
            LIMIT 1");
        
        $get_student->bindValue(":student_id", $student_id);
        $get_student->bindValue(":student_status", $student_status);
        $get_student->execute();

        

        if($get_student->rowCount() > 0){

            $row = $get_student->fetch(PDO::FETCH_ASSOC);


            // $program_id = $row['program_id'];
            $firstname = $row['firstname'];
            $middle_name = $row['middle_name'];
            $lastname = $row['lastname'];
            $birthday = $row['birthday'];
            $address = $row['address'];
            $sex = $row['sex'];
            $contact_number = $row['contact_number'];
            $date_creation = $row['date_creation'];
            $student_status = $row['student_status'];
            $email = $row['email'];
            // $pending_enrollees_id = $row['pending_enrollees_id'];
            // $password = $row['password'];
            $civil_status = $row['civil_status'];
            $nationality = $row['nationality'];
            $age = $row['age'];
            $guardian_name = $row['guardian_name'];
            $guardian_contact_number = $row['guardian_contact_number'];
            $lrn = $row['lrn'];
            $student_unique_id = $row['student_unique_id'];



            $student_program_id = $enroll->GetStudentProgramId($student_course_id);

            $section = new Section($con, $student_course_id);

            $section_acronym = $section->GetAcronymByProgramId($student_program_id);

            $section_acronym = $section->GetAcronymByProgramId($student_program_id);

            $enrollment = new Enrollment($con, $enroll);

            $checkEnrollmentEnrolled = $enrollment->CheckEnrollmentEnrolled($student_id,
                    $course_main_id, $school_year_id);

            $cashierEvaluated = $enrollment->CheckEnrollmentCashierApproved($student_id,
                    $course_main_id, $school_year_id);


            $registrarEvaluated = $enrollment->CheckEnrollmentRegistrarApproved($student_id,
                    $course_main_id, $school_year_id);

            $payment_status = "";

            if($checkEnrollmentEnrolled == true 
                && $cashierEvaluated == true && $registrarEvaluated == true){

                $payment_status = "Enrolled";

            }else if($checkEnrollmentEnrolled == false 
                && $cashierEvaluated == true && $registrarEvaluated == true){

                $payment_status = "Approved";
                
            }else if($checkEnrollmentEnrolled == false 
                && $cashierEvaluated == false && $registrarEvaluated == true){

                $payment_status = "Waiting Payment";
            }

            else if($checkEnrollmentEnrolled == false 
                && $registrarEvaluated == false
                && $cashierEvaluated == false){

                $payment_status = "Evaluation";

            }

            $getEnrollmentNonEnrolledDate = $enrollment
                ->GetEnrollmentNonEnrolledDate($student_id, $course_main_id,
                    $school_year_id);

            $getEnrollmentEnrolledDate = $enrollment
                ->GetEnrollmentEnrolledDate($student_id, $course_main_id,
                    $school_year_id);

            $proccess_date = null;

            if($checkEnrollmentEnrolled == true){
                $proccess_date = $getEnrollmentEnrolledDate;
            }else{
                $proccess_date = $getEnrollmentNonEnrolledDate;
            }
            
            // $checkStudentEnrolledYearlySemester = $subject->CheckStudentGradeLevelEnrolledSubjectSemesterv2(
            //     $student_id, $FIRST_SEMESTER, $GRADE_ELEVEN);
            
            

            if(isset($_GET['profile']) && $_GET['profile'] == "show"){
                ?>
                    <div class="row col-md-12">

                        <div class="content">

                            <div class="form-header">
                                <div class="header-content">
                                <h2><?php echo $lastname?>, <?php echo $firstname;?> <?php echo $middle_name;?>,</h2>
                                
                                </div>

                                <div class="student-table">
                                    <table class="inner-student-table">
                                        <tr>
                                            <th>Student No.</th>
                                            <th>Level</th>
                                            <th>Strand</th>
                                            <th>Status</th>
                                            <th>Added on:</th>
                                        </tr>

                                        <tr>
                                            <td><?php echo $student_unique_id; ?></td>
                                            <td><?php echo $student_course_level; ?></td>
                                            <td><?php echo $section_acronym; ?></td>
                                            <td><?php echo $payment_status; ?></td>
                                        

                                            <td><?php
                                                $date = new DateTime($proccess_date);
                                                $formattedDate = $date->format('m/d/Y H:i');

                                                echo $formattedDate;
                                            ?></td>
                                        
                                        </tr>
                                    </table>
                                </div>
                            </div>

                            <div class="choices">
                                <div class="student-details">

                                    <a href="shs_view_transferee_details.php?profile=show&id=<?php echo $student_id?>">
                                    
                                        <button style="background-color:palevioletred;"
                                            type="button"
                                            class="selection-btn"
                                            id="student-details">
                                            <i class="bi bi-clipboard-check"></i>Student Details
                                        </button>
                                    </a>

                                </div>
                                <div class="enrolled-subjects">
                                    <a href="shs_view_transferee_details.php?grade_record=show&id=<?php echo $student_id;?>">
                                        <button
                                            type="button"
                                            class="selection-btn"
                                            id="enrolled-subjects">
                                            <i class="bi bi-collection"></i>Grades Records
                                        </button>
                                    </a>
                                </div>


                                <div class="enrolled-subjects">
                                    <a href="shs_view_transferee_details.php?subject=show&id=<?php echo $student_id;?>">
                                        <button
                                            type="button"
                                            class="selection-btn"
                                            id="enrolled-subjects">
                                            <i class="bi bi-collection"></i>Enrolled subjects
                                        </button>
                                    </a>
                                </div>

                            </div>
                        </div>

                        <div class="card">
                            <div class="card-body">
                                <div class="row col-md-12">
                                    <div class="col-md-4 text-center">
                                        <a href="shs_view_transferee_details.php?profile=show&id=<?php echo $student_id;?>">
                                            <button class="btn btn-lg btn-primary">
                                                Student Information
                                            </button>
                                        </a>
                                    </div>

                                    <div class="col-md-4 text-center">
                                        <a href="shs_view_transferee_details.php?subject=show&id=<?php echo $student_id;?>">
                                            <button class="btn btn-lg btn-outline-primary">
                                                Enrolled Subjects
                                            </button>
                                        </a>
                                    </div>

                                    <div class="col-md-4 text-center">
                                        <a href="shs_view_transferee_details.php?credited_subject=show&id=<?php echo $student_id;?>">
                                            <button class="btn btn-lg btn-outline-primary">
                                                Credited Subjects
                                            </button>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr>
                        <hr>
                        <div class="card">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <h2 class="mb-3 text-center text-primary">Student Information</h2>
                                    <hr>
                                    <div class="col-md-4 mb-4">
                                        <label style="font-weight: bold;" class="text-muted">
                                            Academic Year: <?php echo $current_term; ?> 
                                            <span>(<?php echo $current_semester; ?>)</span> 
                                        </label>
                                    </div>
                                    <table class="table">
                                        <tr>
                                            <td><label>Id</label></td>
                                            <td>
                                                <input class="form-control input-md" 
                                                    readonly id="student_id" name="student_id" placeholder="Student Id" type="text" 
                                                    value='<?php echo $row['student_id']?>'>
                                            </td>

                                            <td ><label>Grade Level</label></td> 
                                            <td colspan="1">
                                                <?php 
                                                    if($student_course_level != 0){
                                                        echo '<input value="Grade ' . $student_course_level . '" readonly class="form-control input-md" type="text">';
                                                    }else{
                                                        echo '<input value="Not Set" readonly class="form-control input-md" type="text">';
                                                        
                                                    }
                                                ?>

                                            </td>

                                            <td ><label>LRN</label></td> 
                                            <td colspan="1">
                                                <input class="form-control input-md" 
                                                    name="lrn" placeholder="LRN:136-746-XXX" type="text" 
                                                    value='<?php echo $row['lrn']?>'>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><label>Firstname</label></td>
                                            <td>
                                                <input  value='<?php echo $row['firstname']?>' class="form-control input-md" id="FNAME" name="FNAME" placeholder="First Name" type="text">
                                            </td>
                                            <td><label>Lastname</label></td>
                                            <td colspan="2">
                                                <input value='<?php echo $row['lastname']?>'  class="form-control input-md" id="LNAME" name="LNAME" placeholder="Last Name" type="text">
                                            </td> 
                                            <td>
                                                <input value='<?php echo $row['middle_name']?>' class="form-control input-md" id="MI" name="MI" placeholder="MI"  maxlength="1" type="text">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><label>Address</label></td>
                                            <td colspan="5"  >
                                            <input  value='<?php echo $row['address']?>' class="form-control input-md" id="PADDRESS" name="PADDRESS" placeholder="Permanent Address" type="text" >
                                            </td> 
                                        </tr>
                                        <tr>
                                            <td ><label>Sex </label></td> 
                                            <td colspan="2">
                                                <label>
                                                    <input checked id="optionsRadios1" name="optionsRadios" type="radio" value="Female">Female 
                                                    <input id="optionsRadios2" name="optionsRadios" type="radio" value="Male"> Male
                                                </label>
                                            </td>
                                            <td ><label>Date of birth</label></td>
                                            <td colspan="2"> 
                                            <div class="input-group" >
                                            <div class="input-group-addon"> 
                                                <i class="fa fa-calendar"></i>
                                            </div>
                                            <input value='<?php echo $row['birthday']?>'  name="BIRTHDATE"  id="BIRTHDATE"  type="text" class="form-control input-md"   data-inputmask="'alias': 'mm/dd/yyyy'" data-mask >
                                            </div>             
                                            </td>
                                        </tr>

                                        <tr><td><label>Place of Birth</label></td>
                                            <td colspan="5">
                                            <input value='<?php echo $row['birthplace']?>'  class="form-control input-md" id="BIRTHPLACE" name="BIRTHPLACE" placeholder="Place of Birth" type="text" >
                                        </td>
                                        </tr>

                                        <tr>
                                            <td><label>Nationality</label></td>
                                            <td colspan="2"><input value='<?php echo $row['nationality']?>'   class="form-control input-md" id="NATIONALITY" name="NATIONALITY" placeholder="Nationality" type="text" >
                                                        </td>
                                            <td><label>Religion</label></td>
                                            <td colspan="2"><input  value='<?php echo $row['religion']?>'  class="form-control input-md" id="RELIGION" name="RELIGION" placeholder="Religion" type="text" >
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><label>Contact No.</label></td>
                                                <td value='<?php echo $row['contact_number']?>'  colspan="2"><input class="form-control input-md" id="CONTACT" name="CONTACT" placeholder="Contact Number" type="number" maxlength="11" >
                                                </td>
                                            
                                            <td><label>Civil Status</label></td>
                                        <td colspan="2">
                                            <select class="form-control input-sm" name="CIVILSTATUS">
                                                <option value="Single">Single</option>
                                                <option value="Married">Married</option> 
                                                <option value="Widow">Widow</option>
                                            </select>
                                        </td>
                                        </tr>

                                        
                                        <tr>
                                        <td>
                                            <label>Strand Section</label></td>
                                            <td colspan="2">
                                                <select class="form-control input-sm" name="course_id">
                                                    <?php

                                                        $selected_course_id = 0; 

                                                        $active_yes = "yes";
                                                        $get_course = $con->prepare("SELECT * FROM course
                                                                                    WHERE school_year_term = :school_year_term
                                                                                    AND active = :active
                                                                                    ORDER BY school_year_term ASC");

                                                        $get_course->bindValue(":school_year_term", $current_term);
                                                        $get_course->bindValue(":active", $active_yes);
                                                        $get_course->execute();

                                                        while ($get_row = $get_course->fetch(PDO::FETCH_ASSOC)) {

                                                            $selected = ($get_row['course_id'] == $student_course_id) ? 'selected' : '';

                                                            echo "<option value='{$get_row['course_id']}' {$selected}>{$get_row['program_section']}</option>";
                                                        }
                                                    ?>
                                                </select>

                                            </td>
                                        
                                            <td><label>Student Status</label></td>
                                            <td colspan="3">
                                                <!-- <select class="form-control input-sm" name="student_status">
                                                    <option value="Transferee">Transferee</option>
                                                    <option value="Regular">New Enrollee (Regular)</option> 
                                                </select> -->

                                                <select class="form-control input-sm" name="student_status">
                                                    <option value="Transferee" <?php if ($row['student_status'] == "Transferee") { echo "selected"; } ?>>Transferee</option>
                                                    <option value="Regular" <?php if ($row['student_status'] == "Regular") { echo "selected"; } ?>>New Enrollee (Regular)</option> 
                                                </select>
                                            </td>
                                        </tr>
                                        
                                        <tr>
                                            <td><label>Guardian</label></td>
                                            <td colspan="2">
                                                <input value='<?php echo $row['guardian_name']?>'  class="form-control input-md" id="GUARDIAN" name="GUARDIAN" placeholder="Parents/Guardian Name" type="text">
                                            </td>
                                            <td><label>Contact No.</label></td>
                                            <td colspan="2"><input value='<?php echo $row['guardian_contact_number']?>'  class="form-control input-md" id="GCONTACT" name="GCONTACT" placeholder="Contact Number" type="number" ></td>
                                        </tr>
                                    
                                    </table>
                                </div>
                            </div>
                        </div>

                    </div>
                <?php
            }

            if(isset($_GET['grade_record']) && $_GET['grade_record'] == "show"){
                 $studentEnroll = new StudentEnroll($con);

                $username = $studentEnroll->GetStudentUsername($_GET['id']);
                $userLoggedInId = $studentEnroll->GetStudentId($username);
                $course_main_id = $studentEnroll->GetStudentCourseId($username);
                $student_course_level = $studentEnroll->GetStudentCourseLevel($username);

                $school_year_obj = $studentEnroll->GetActiveSchoolYearAndSemester();

                $current_school_year_id = $school_year_obj['school_year_id'];
                $current_school_year_term = $school_year_obj['term'];
                $current_school_year_period = $school_year_obj['period'];


                $admission_status = $row['admission_status'];

                $proccess_date = "";

                $enrollment = new Enrollment($con, $enroll);
                $enrollment_form_id = $enrollment->GenerateEnrollmentFormId();

                $checkEnrollmentEnrolled = $enrollment->CheckEnrollmentEnrolled($student_id,
                        $course_main_id, $school_year_id);

                $cashierEvaluated = $enrollment->CheckEnrollmentCashierApproved($student_id,
                        $course_main_id, $school_year_id);


                $registrarEvaluated = $enrollment->CheckEnrollmentRegistrarApproved($student_id,
                        $course_main_id, $school_year_id);

                $payment_status = "";

                if($checkEnrollmentEnrolled == true 
                    && $cashierEvaluated == true && $registrarEvaluated == true){

                    $payment_status = "Enrolled";

                }else if($checkEnrollmentEnrolled == false 
                    && $cashierEvaluated == true && $registrarEvaluated == true){

                    $payment_status = "Approved";
                    
                }else if($checkEnrollmentEnrolled == false 
                    && $cashierEvaluated == false && $registrarEvaluated == true){

                    $payment_status = "Waiting Payment";
                }

                else if($checkEnrollmentEnrolled == false 
                    && $registrarEvaluated == false
                    && $cashierEvaluated == false){

                    $payment_status = "Evaluation";

                }

                $getEnrollmentNonEnrolledDate = $enrollment
                    ->GetEnrollmentNonEnrolledDate($student_id, $course_main_id,
                        $school_year_id);

                $getEnrollmentEnrolledDate = $enrollment
                    ->GetEnrollmentEnrolledDate($student_id, $course_main_id,
                        $school_year_id);

                $proccess_date = null;

                if($checkEnrollmentEnrolled == true){
                    $proccess_date = $getEnrollmentEnrolledDate;
                }else{
                    $proccess_date = $getEnrollmentNonEnrolledDate;
                }

            ?>
                <div class="row col-md-12">

                    <div class="content">

                        <div class="form-header">
                            <div class="header-content">
                            <h2><?php echo $lastname?>, <?php echo $firstname;?> <?php echo $middle_name;?>,</h2>
                             
                            </div>

                            <div class="student-table">
                                <table class="inner-student-table">
                                    <tr>
                                        <th>Student No.</th>
                                        <th>Level</th>
                                        <th>Strand</th>
                                        <th>Status</th>
                                        <th>Added on:</th>
                                    </tr>

                                    <tr>
                                        <td><?php echo $student_unique_id; ?></td>
                                        <td><?php echo $student_course_level; ?></td>
                                        <td><?php echo $section_acronym; ?></td>
                                        <td><?php echo $payment_status; ?></td>
                                     

                                        <td><?php
                                            $date = new DateTime($proccess_date);
                                            $formattedDate = $date->format('m/d/Y H:i');

                                            echo $formattedDate;
                                        ?></td>
                                    
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <div class="choices">
                            <div class="student-details">

                                <a href="shs_view_transferee_details.php?profile=show&id=<?php echo $student_id?>">
                                
                                    <button "
                                        type="button"
                                        class="selection-btn"
                                        id="student-details">
                                        <i class="bi bi-clipboard-check"></i>Student Details
                                    </button>
                                </a>

                            </div>
                            <div  class="enrolled-subjects">
                                <a href="shs_view_transferee_details.php?grade_record=show&id=<?php echo $student_id;?>">
                                    <button style="background-color:palevioletred;"
                                        type="button"
                                        class="selection-btn"
                                        id="enrolled-subjects">
                                        <i class="bi bi-collection"></i>Grades Records
                                    </button>
                                </a>
                            </div>


                            <div class="enrolled-subjects">
                                <a href="shs_view_transferee_details.php?subject=show&id=<?php echo $student_id?>">
                                    <button
                                        type="button"
                                        class="selection-btn"
                                        id="enrolled-subjects">
                                        <i class="bi bi-collection"></i>Enrolled subjects
                                    </button>
                                </a>
                            </div>

                        </div>
                    </div>

                    <hr>
                    <div class="card">
                        <div class="card-body">

                            <!-- GRADE 11 1st SEM -->
                            <?php 
                                // if($subject->CheckStudentGradeLevelEnrolledSubjectSemesterv2(
                                //     $student_id, $FIRST_SEMESTER, $GRADE_ELEVEN)){

                                 
                                if(count($subject->CheckStudentGradeLevelEnrolledSubjectSemesterv2(
                                    $student_id, $FIRST_SEMESTER, $GRADE_ELEVEN)) > 0){

                                    ?>
                                        <div class="row col-md-12 table-responsive" style="margin-top:5%;">

                                            <div class="card">
                                                <div class="card-header">

                                                    <?php 
                                                        // echo "wee";
                                                        $GRADE_TWELVE = 12;
                                                        $GRADE_ELEVEN = 11;
                                                        $FIRST_SEMESTER = "First";

                                                        // Section Based on the enrollment.
                                                        $enrollment_school_year = $studentEnroll->GetStudentSectionGradeLevelSemester($username, $userLoggedInId,
                                                            $GRADE_ELEVEN, $FIRST_SEMESTER);

                                                        if($enrollment_school_year !== null){
                                                            
                                                            $term = $enrollment_school_year['term'];
                                                            $period = $enrollment_school_year['period'];
                                                            $school_year_id = $enrollment_school_year['school_year_id'];
                                                            $enrollment_course_id = $enrollment_school_year['course_id'];
                                                            $enrollment_id = $enrollment_school_year['enrollment_id'];


                                                            $enrollment_section_name = $enroll->GetStudentCourseNameByCourseId($enrollment_course_id);

                                                            echo "
                                                                <h4 style='font-weight: 500;' class='text-primary text-center mb-3'>
                                                                    Grade 11 $enrollment_section_name $period Semester (SY $term)
                                                                </h4>
                                                            ";
                                                        }else{
                                                            echo "
                                                                <h3>Grade 11 First Semester</h3>	
                                                            ";
                                                        }
                                                    ?>	
                                                
                                                </div>
                                                <div class="card-body">
                                                    <div class="table-responsive" style="margin-top:2%;"> 
                                                                            
                                                        <table style="font-weight: 200;" class="table table-striped table-bordered table-hover "  style="font-size:12px" cellspacing="0"  > 
                                                            <thead>
                                                                <tr class="text-center"> 
                                                                    <th class="text-primary" rowspan="2">Subject</th>
                                                                    <th class="text-primary" rowspan="2">Type</th>
                                                                    <th class="text-primary" rowspan="2">Description</th>  
                                                                    <th class="text-primary" rowspan="2">Unit</th>
                                                                    <th class="text-primary">Semester</th>  
                                                                    <th class="text-primary">Course Level</th>  
                                                                    <th class="text-primary">Remarks</th>  
                                                                </tr>	
                                                            </thead> 	 
                                                            <tbody>
                                                                <?php 

                                                                    // $listOfSubjects = $studentEnroll
                                                                    //     ->GetSHSTransfereeEnrolledSubjectSemester($student_username,
                                                                    //         $student_id, $GRADE_ELEVEN, $FIRST_SEMESTER);

                                                                    $listOfSubjects = $studentEnroll
                                                                        ->GetStudentTransCurriculumBasedOnSemesterSubject($username,
                                                                            $userLoggedInId, $GRADE_ELEVEN, $FIRST_SEMESTER, $enrollment_id);

                                                                    if($listOfSubjects !== null){

                                                                        foreach ($listOfSubjects as $key => $value) {

                                                                            $subject_id = $value['subject_id'];
                                                                            $course_level = $value['course_level'];

                                                                            $remarks_url = "";

                                                                            $query_student_subject = $con->prepare("SELECT 
                                                                        
                                                                                t1.subject_id, t1.student_subject_id as t1_student_subject_id,
                                                                                
                                                                                t2.student_subject_id as t2_student_subject_id,
                                                                                t2.remarks,

                                                                                t1.is_transferee

                                                                                FROM student_subject as t1

                                                                                LEFT JOIN student_subject_grade as t2 ON t2.student_subject_id = t1.student_subject_id

                                                                                WHERE t1.subject_id=:subject_id
                                                                                AND t1.student_id=:student_id
                                                                                LIMIT 1");

                                                                            $query_student_subject->bindValue(":subject_id", $subject_id);
                                                                            $query_student_subject->bindValue(":student_id", $student_id);
                                                                            $query_student_subject->execute();

                                                                            $t1_student_subject_id = null;

                                                                            if($query_student_subject->rowCount() > 0){

                                                                                // echo "notx";
                                                                                
                                                                                $row = $query_student_subject->fetch(PDO::FETCH_ASSOC);

                                                                                $student_subject_subject_id = $row['subject_id'];
                                                                                $t1_student_subject_id = $row['t1_student_subject_id'];
                                                                                $t2_student_subject_id = $row['t2_student_subject_id'];
                                                                                $remarks = $row['remarks'];
                                                                                $is_transferee = $row['is_transferee'];



                                                                                
                                                                                // echo $t1_student_subject_id . " ";
                                                                                // echo $t2_student_subject_id . " ";

                                                                                if($t1_student_subject_id == $t2_student_subject_id && $is_transferee == "no"){
                                                                                    $remarks_url = $remarks;

                                                                                }else if($t1_student_subject_id == $t2_student_subject_id && $is_transferee == "yes"){
                                                                                    $remarks_url = "Credited";
                                                                                }
                                                                                
                                                                                else if($student_subject_subject_id == $subject_id
                                                                                    && $t1_student_subject_id != $t2_student_subject_id){

                                                                                    // $_SESSION['remarks'] = "shs_view_transferee_details"; #QWE

                                                                                    $remarks_url = "
                                                                                        <a href='shs_transferee_remarks.php?student_id=$student_id&s_id=$subject_id&page=shs_view_transferee_details'>
                                                                                            <button type='button' class='btn btn-sm btn-primary'>Remark</button>
                                                                                        </a>
                                                                                    ";
                                                                                }

                                                                            }
                                                                            
                                                                            echo '<tr class="text-center">'; 
                                                                                    echo '<td>'.$value['subject_code'].'</td>';
                                                                                    echo '<td>'.$value['subject_type'].'</td>';
                                                                                    echo '<td>'.$value['subject_title'].'</td>';
                                                                                    echo '<td>'.$value['unit'].'</td>';
                                                                                    // echo '<td>'.$schedule_day.'</td>';
                                                                                    // echo '<td>'.$schedule_time.'</td>';
                                                                                    // echo '<td>'.$room.'</td>';
                                                                                    // echo '<td>'.$section.'</td>';
                                                                                    echo '<td>'.$value['semester'].'</td>';
                                                                                    echo '<td>'.$course_level.'</td>';
                                                                                    echo '<td>'.$remarks_url.'</td>';
                                                                            echo '</tr>';
                                                                            }     
                                                                    }
                                                                    else{
                                                                        echo "Student did not have regular enrolled subject for their Grade 11 First Semester";
                                                                    }
                                                                    
                                                                ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>  
                                    <?php
                                }else{
                                    ?>
                                    <h3 class="text-center">Grade 11 First semester</h3>
                                    <table style="font-weight: 200;" class="table table-striped table-bordered table-hover "  style="font-size:12px" cellspacing="0"  > 
                                        <thead>
                                            <tr class="text-center"> 
                                                <th class="text-success" rowspan="2">Subject</th>
                                                <th class="text-success" rowspan="2">Type</th>
                                                <th class="text-success" rowspan="2">Description</th>  
                                                <th class="text-success" rowspan="2">Unit</th>
                                                <th class="text-muted" colspan="4">Schedule</th> 
                                            </tr>	
                                            <tr> 
                                                <!-- <th>Day</th> 
                                                <th>Time</th>
                                                <th>Room</th> 
                                                <th>Section</th>   -->
                                                <th class="text-success">Semester</th>  
                                                <th class="text-success">Course Level</th>  
                                                <th class="text-success">Remarks</th>  
                                            </tr>
                                        </thead> 	 
                                        <tbody>
                                            <?php 

                                                // $listOfSubjects = $studentEnroll->GetStudentSubjectListWithGrades($username);
                                                $listOfSubjects = $studentEnroll
                                                    ->GetStudentCurriculumBasedOnSemesterSubject($username,
                                                $userLoggedInId, $GRADE_ELEVEN, $FIRST_SEMESTER);

                                                if($listOfSubjects !== null){

                                                    foreach ($listOfSubjects as $key => $value) {
                                                        
                                                    $subject_id = $value['subject_id'];
                                                        $course_level = $value['course_level'];

                                                        $remarks_url = "";

                                                        $query_student_subject = $con->prepare("SELECT 
                                                    
                                                            t1.subject_id, t1.student_subject_id as t1_student_subject_id,
                                                            
                                                            t2.student_subject_id as t2_student_subject_id,
                                                            t2.remarks

                                                            FROM student_subject as t1

                                                            LEFT JOIN student_subject_grade as t2 ON t2.student_subject_id = t1.student_subject_id

                                                            WHERE t1.subject_id=:subject_id
                                                            AND t1.student_id=:student_id
                                                            LIMIT 1");

                                                        $query_student_subject->bindValue(":subject_id", $subject_id);
                                                        $query_student_subject->bindValue(":student_id", $student_id);
                                                        $query_student_subject->execute();


                                                        $t1_student_subject_id = null;

                                                            // echo $subject_id . " 1 ";

                                                        if($query_student_subject->rowCount() > 0){

                                                            $row = $query_student_subject->fetch(PDO::FETCH_ASSOC);

                                                            $student_subject_subject_id = $row['subject_id'];
                                                            $t1_student_subject_id = $row['t1_student_subject_id'];
                                                            $t2_student_subject_id = $row['t2_student_subject_id'];
                                                            $remarks = $row['remarks'];

                                                            // echo $t1_student_subject_id . " ";
                                                            // echo $t2_student_subject_id . " ";

                                                            if($t1_student_subject_id == $t2_student_subject_id){

                                                                $remarks_url = $remarks;

                                                            }else if($student_subject_subject_id == $subject_id
                                                                && $t1_student_subject_id != $t2_student_subject_id){

                                                                $remarks_url = "
                                                                    <a href='shs_transferee_remarks.php?student_id=$student_id&s_id=$subject_id&page=view_details'>
                                                                        <button type='button' class='btn btn-sm btn-primary'>Remark</button>
                                                                    </a>
                                                                ";
                                                            }
                                                        }
                                                    
                                                        echo '<tr class="text-center">'; 
                                                                echo '<td>'.$value['subject_code'].'</td>';
                                                                echo '<td>'.$value['subject_type'].'</td>';
                                                                echo '<td>'.$value['subject_title'].'</td>';
                                                                echo '<td>'.$value['unit'].'</td>';
                                                                // echo '<td>'.$schedule_day.'</td>';
                                                                // echo '<td>'.$schedule_time.'</td>';
                                                                // echo '<td>'.$room.'</td>';
                                                                // echo '<td>'.$section.'</td>';
                                                                echo '<td>'.$value['semester'].'</td>';
                                                                echo '<td>'.$value['course_level'].'</td>';
                                                                echo '<td>'.$remarks_url.'</td>';
                                                        echo '</tr>';
                                                    }     
                                                }
                                                else{
                                                    echo "No Datax was found for Grade 11 1st Semester.";
                                                }
                                                
                                            ?>
                                        </tbody>
                                    </table>
                                    <?php
                                }
                            ?>

                            <!-- GRADE 11 2nd SEMESTER -->
                            <div class="row col-md-12 table-responsive"
                                    style="margin-top:5%;">
                                <div class="table-responsive" style="margin-top:2%;"> 

                                    <?php 

                                        $GRADE_TWELVE = 12;
                                        $GRADE_ELEVEN = 11;
                                        $FIRST_SEMESTER = "First";
                                        // Section Based on the enrollment.
                                        $enrollment_school_year = $studentEnroll->GetStudentSectionGradeElevenSchoolYear($username, $userLoggedInId, 11, "Second");

                                        if($enrollment_school_year !== null){
                                            $term = $enrollment_school_year['term'];
                                            $period = $enrollment_school_year['period'];
                                            $school_year_id = $enrollment_school_year['school_year_id'];
                                            $enrollment_course_id = $enrollment_school_year['course_id'];

                                            $section = new Section($con, $enrollment_course_id);
                                            $enrollment_course_level = $section->GetSectionGradeLevel();

                                            $enrollment_section_name = $enroll->GetStudentCourseNameByCourseId($enrollment_course_id);
                                            
                                            echo "
                                                <h4 style='font-weight: 500;' class='text-primary text-center mb-3'>
                                                    Grade 11 $enrollment_section_name $period Semester (SY $term)
                                                </h4>

                                                <form action='grade_subject_generate.php' method='POST'>

                                                    <input type='hidden' value='$student_username' name='student_username'>
                                                    <input type='hidden' value='$student_id' name='student_id'>
                                                    <input type='hidden' value='$enrollment_course_id' name='enrollment_course_id'>
                                                    <input type='hidden' value='$enrollment_course_level' name='student_course_level'>
                                                    <input type='hidden' value='$period' name='current_school_year_period'>

                                                    <button name='grade_subject_generate' type='submit'
                                                        class='btn btn-outline-primary btn-sm'>
                                                        Generate PDF
                                                    </button>
                                                </form>
                                            ";

                                        }else{
                                            echo "
                                                <h3>Grade 11 First Semester</h3>	
                                            ";
                                        }
                                    ?>	
                                    <table style="font-weight: 200;" class="table table-striped table-bordered table-hover "  style="font-size:12px" cellspacing="0"  > 
                                        <thead>
                                            <tr class="text-center"> 
                                                <th class="text-success" rowspan="2">Code</th>
                                                <th class="text-success" rowspan="2">Type</th>
                                                <th class="text-success" rowspan="2">Description</th>  
                                                <th class="text-success" rowspan="2">Unit</th>
                                                <th class="text-success" rowspan="2">Prelim</th>
                                                <th class="text-success" rowspan="2">Midterm</th>
                                                <th class="text-success" rowspan="2">Pre-Final</th>
                                                <th class="text-success" rowspan="2">Final</th>
                                                <th class="text-success" rowspan="2">Average</th>
                                                <th class="text-success" rowspan="2">Remark</th>
                                            </tr>	
                                        </thead> 	

                                        <tbody>
                                            <?php 

                                                // $listOfSubjects = $studentEnroll->GetStudentSubjectListWithGrades($username);
                                                $listOfSubjects = $studentEnroll
                                                    ->GetStudentCurriculumBasedOnSemesterSubject($username,
                                                $userLoggedInId, $GRADE_ELEVEN, $SECOND_SEMESTER);


                                                if($listOfSubjects !== null){

                                                    foreach ($listOfSubjects as $key => $value) {
                                                        
                                                        $subject_id = $value['subject_id'];
                                                        $course_level = $value['course_level'];

                                                        $first = $value['first'];
                                                        $second = $value['second'];
                                                        $third = $value['third'];
                                                        $fourth = $value['fourth'];
                                                        $grade_remarks = $value['grade_remarks'];

                                                        $remarks_url = "";

                                                        $query_student_subject = $con->prepare("SELECT 
                                                    
                                                            t1.subject_id, t1.student_subject_id as t1_student_subject_id,
                                                            
                                                            t2.student_subject_id as t2_student_subject_id,
                                                            t2.remarks

                                                            FROM student_subject as t1

                                                            LEFT JOIN student_subject_grade as t2 ON t2.student_subject_id = t1.student_subject_id

                                                            WHERE t1.subject_id=:subject_id
                                                            AND t1.student_id=:student_id
                                                            LIMIT 1");


                                                        $query_student_subject->bindValue(":subject_id", $subject_id);
                                                        $query_student_subject->bindValue(":student_id", $student_id);
                                                        $query_student_subject->execute();

                                                        $t1_student_subject_id = null;

                                                        if($query_student_subject->rowCount() > 0){

                                                            $row = $query_student_subject->fetch(PDO::FETCH_ASSOC);

                                                            $student_subject_subject_id = $row['subject_id'];
                                                            $t1_student_subject_id = $row['t1_student_subject_id'];
                                                            $t2_student_subject_id = $row['t2_student_subject_id'];
                                                            $remarks = $row['remarks'];

                                                            // echo $t1_student_subject_id . " ";
                                                            // echo $t2_student_subject_id . " ";

                                                            if($t1_student_subject_id == $t2_student_subject_id){

                                                                $remarks_url = $remarks;

                                                            }else if($student_subject_subject_id == $subject_id
                                                                && $t1_student_subject_id != $t2_student_subject_id){

                                                                $remarks_url = "
                                                                    <a href='shs_transferee_remarks.php?student_id=$student_id&s_id=$subject_id&page=view_details'>
                                                                        <button type='button' class='btn btn-sm btn-primary'>Remark</button>
                                                                    </a>
                                                                ";
                                                            }
                                                        }
                                                    
                                                        echo '<tr class="text-center">'; 
                                                                echo '<td>'.$value['subject_code'].'</td>';
                                                                echo '<td>'.$value['subject_type'].'</td>';
                                                                echo '<td>'.$value['subject_title'].'</td>';
                                                                echo '<td>'.$value['unit'].'</td>';
                                                             
                                                                echo '<td>'.$first.'</td>';
                                                                echo '<td>'.$second.'</td>';
                                                                echo '<td>'.$third.'</td>';
                                                                echo '<td>'.$fourth.'</td>';
                                                                echo '<td></td>';
                                                                echo '<td>'.$grade_remarks.'</td>';

                                                        echo '</tr>';
                                                    }     
                                                }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>



                        </div>
                    </div>

                </div>
            <?php
            }
            


            if(isset($_GET['subject']) 
                && $_GET['subject'] == "show" && isset($_GET['id'])){

                $studentEnroll = new StudentEnroll($con);

                $username = $studentEnroll->GetStudentUsername($_GET['id']);
                $userLoggedInId = $studentEnroll->GetStudentId($username);
                $course_main_id = $studentEnroll->GetStudentCourseId($username);
                $student_course_level = $studentEnroll->GetStudentCourseLevel($username);

                $school_year_obj = $studentEnroll->GetActiveSchoolYearAndSemester();

                $current_school_year_id = $school_year_obj['school_year_id'];
                $current_school_year_term = $school_year_obj['term'];
                $current_school_year_period = $school_year_obj['period'];


                $checkEnrollmentEnrolled = $enrollment->CheckEnrollmentEnrolled($student_id,
                        $course_main_id, $school_year_id);

                $cashierEvaluated = $enrollment->CheckEnrollmentCashierApproved($student_id,
                        $course_main_id, $school_year_id);


                $registrarEvaluated = $enrollment->CheckEnrollmentRegistrarApproved($student_id,
                        $course_main_id, $school_year_id);

                $payment_status = "";

                if($checkEnrollmentEnrolled == true 
                    && $cashierEvaluated == true && $registrarEvaluated == true){

                    $payment_status = "Enrolled";

                }else if($checkEnrollmentEnrolled == false 
                    && $cashierEvaluated == true && $registrarEvaluated == true){

                    $payment_status = "Approved";
                    
                }else if($checkEnrollmentEnrolled == false 
                    && $cashierEvaluated == false && $registrarEvaluated == true){

                    $payment_status = "Waiting Payment";
                }

                else if($checkEnrollmentEnrolled == false 
                    && $registrarEvaluated == false
                    && $cashierEvaluated == false){

                    $payment_status = "Evaluation";

                }

                $getEnrollmentNonEnrolledDate = $enrollment
                    ->GetEnrollmentNonEnrolledDate($student_id, $course_main_id,
                        $school_year_id);

                $getEnrollmentEnrolledDate = $enrollment
                    ->GetEnrollmentEnrolledDate($student_id, $course_main_id,
                        $school_year_id);

                $proccess_date = null;

                if($checkEnrollmentEnrolled == true){
                    $proccess_date = $getEnrollmentEnrolledDate;
                }else{
                    $proccess_date = $getEnrollmentNonEnrolledDate;
                }


                ?>
                <div class="row col-md-12">

                    <div class="content">

                        <div class="form-header">
                            <div class="header-content">
                            <h2><?php echo $lastname?>, <?php echo $firstname;?> <?php echo $middle_name;?>,</h2>
                             
                            </div>

                            <div class="student-table">
                                <table class="inner-student-table">
                                    <tr>
                                        <th>Student No.</th>
                                        <th>Level</th>
                                        <th>Strand</th>
                                        <th>Status</th>
                                        <th>Added on:</th>
                                    </tr>

                                    <tr>
                                        <td><?php echo $student_unique_id; ?></td>
                                        <td><?php echo $student_course_level; ?></td>
                                        <td><?php echo $section_acronym; ?></td>
                                        <td><?php echo $payment_status; ?></td>
                                     

                                        <td><?php
                                            $date = new DateTime($proccess_date);
                                            $formattedDate = $date->format('m/d/Y H:i');

                                            echo $formattedDate;
                                        ?></td>
                                    
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <div class="choices">
                            <div class="student-details">

                                <a href="shs_view_transferee_details.php?profile=show&id=<?php echo $student_id?>">
                                
                                    <button 
                                        type="button"
                                        class="selection-btn"
                                        id="student-details">
                                        <i class="bi bi-clipboard-check"></i>Student Details
                                    </button>
                                </a>

                            </div>
                            <div class="enrolled-subjects">
                                <a href="shs_view_transferee_details.php?grade_record=show&id=<?php echo $student_id;?>">
                                    <button
                                        type="button"
                                        class="selection-btn"
                                        id="enrolled-subjects">
                                        <i class="bi bi-collection"></i>Grades Records
                                    </button>
                                </a>
                            </div>


                            <div class="enrolled-subjects">
                                <a href="shs_view_transferee_details.php?subject=show&id=<?php echo $student_id;?>">
                                    <button style="background-color:palevioletred;"
                                        type="button" 
                                        class="selection-btn"
                                        id="enrolled-subjects">
                                        <i class="bi bi-collection"></i>Enrolled subjects
                                    </button>
                                </a>
                            </div>

                        </div>
                    </div>


                    <!--  2nd -->
                    <div class="card">
                        <div class="card-body">
                            <div class="row col-md-12">
                                <div class="col-md-4 text-center">
                                    <a href="shs_view_transferee_details.php?profile=show&id=<?php echo $student_id;?>">
                                        <button class="btn btn-lg btn-outline-primary">
                                            Student Information
                                        </button>
                                    </a>
                                </div>

                                <div class="col-md-4 text-center">
                                    <a href="shs_view_transferee_details.php?subject=show&id=<?php echo $student_id;?>">
                                        <button class="btn btn-lg btn-primary">
                                            Enrolled Subjects
                                        </button>
                                    </a>
                                </div>

                                <div class="col-md-4 text-center">
                                    <a href="shs_view_transferee_details.php?credited_subject=show&id=<?php echo $student_id;?>">
                                        <button class="btn btn-lg btn-outline-primary">
                                            Credited Subjects
                                        </button>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr>
                    <!-- GRADE 11 1st SEM -->
                    <?php 
                        if(count($subject->CheckStudentGradeLevelEnrolledSubjectSemesterv2(
                            $student_id, $FIRST_SEMESTER, $GRADE_ELEVEN)) > 0){
                            ?>
                                <div class="row col-md-12 table-responsive" style="margin-top:5%;">

                                    <div class="card">
                                        <div class="card-header">

                                            <?php 
                                                // echo "wee";
                                                $GRADE_TWELVE = 12;
                                                $GRADE_ELEVEN = 11;
                                                $FIRST_SEMESTER = "First";

                                                // Section Based on the enrollment.
                                                $enrollment_school_year = $studentEnroll->GetStudentSectionGradeLevelSemester($username, $userLoggedInId,
                                                    $GRADE_ELEVEN, $FIRST_SEMESTER);

                                                if($enrollment_school_year !== null){
                                                    
                                                    $term = $enrollment_school_year['term'];
                                                    $period = $enrollment_school_year['period'];
                                                    $school_year_id = $enrollment_school_year['school_year_id'];
                                                    $enrollment_course_id = $enrollment_school_year['course_id'];
                                                    $enrollment_id = $enrollment_school_year['enrollment_id'];


                                                    $enrollment_section_name = $enroll->GetStudentCourseNameByCourseId($enrollment_course_id);

                                                    echo "
                                                        <h4 style='font-weight: 500;' class='text-primary text-center mb-3'>
                                                            Grade 11 $enrollment_section_name $period Semester (SY $term)
                                                        </h4>
                                                    ";
                                                }else{
                                                    echo "
                                                        <h3>Grade 11 First Semester</h3>	
                                                    ";
                                                }
                                            ?>	
                                        
                                        </div>
                                        <div class="card-body">
                                            <div class="table-responsive" style="margin-top:2%;"> 
                                                                    
                                                <table style="font-weight: 200;" class="table table-striped table-bordered table-hover "  style="font-size:12px" cellspacing="0"  > 
                                                    <thead>
                                                        <tr class="text-center"> 
                                                            <th class="text-primary" rowspan="2">Subject</th>
                                                            <th class="text-primary" rowspan="2">Type</th>
                                                            <th class="text-primary" rowspan="2">Description</th>  
                                                            <th class="text-primary" rowspan="2">Unit</th>
                                                            <th class="text-primary">Semester</th>  
                                                            <th class="text-primary">Course Level</th>  
                                                            <th class="text-primary">Remarks</th>  
                                                        </tr>	
                                                    </thead> 	 
                                                    <tbody>
                                                        <?php 

                                                            // $listOfSubjects = $studentEnroll
                                                            //     ->GetSHSTransfereeEnrolledSubjectSemester($student_username,
                                                            //         $student_id, $GRADE_ELEVEN, $FIRST_SEMESTER);

                                                            $listOfSubjects = $studentEnroll
                                                                ->GetStudentTransCurriculumBasedOnSemesterSubject($username,
                                                                    $userLoggedInId, $GRADE_ELEVEN, $FIRST_SEMESTER, $enrollment_id);

                                                            if($listOfSubjects !== null){

                                                                foreach ($listOfSubjects as $key => $value) {

                                                                    $subject_id = $value['subject_id'];
                                                                    $course_level = $value['course_level'];

                                                                    $remarks_url = "";

                                                                    $query_student_subject = $con->prepare("SELECT 
                                                                
                                                                        t1.subject_id, t1.student_subject_id as t1_student_subject_id,
                                                                        
                                                                        t2.student_subject_id as t2_student_subject_id,
                                                                        t2.remarks,

                                                                        t1.is_transferee

                                                                        FROM student_subject as t1

                                                                        LEFT JOIN student_subject_grade as t2 ON t2.student_subject_id = t1.student_subject_id

                                                                        WHERE t1.subject_id=:subject_id
                                                                        AND t1.student_id=:student_id
                                                                        LIMIT 1");

                                                                    $query_student_subject->bindValue(":subject_id", $subject_id);
                                                                    $query_student_subject->bindValue(":student_id", $student_id);
                                                                    $query_student_subject->execute();

                                                                    $t1_student_subject_id = null;

                                                                    if($query_student_subject->rowCount() > 0){

                                                                        // echo "notx";
                                                                        
                                                                        $row = $query_student_subject->fetch(PDO::FETCH_ASSOC);

                                                                        $student_subject_subject_id = $row['subject_id'];
                                                                        $t1_student_subject_id = $row['t1_student_subject_id'];
                                                                        $t2_student_subject_id = $row['t2_student_subject_id'];
                                                                        $remarks = $row['remarks'];
                                                                        $is_transferee = $row['is_transferee'];



                                                                        
                                                                        // echo $t1_student_subject_id . " ";
                                                                        // echo $t2_student_subject_id . " ";

                                                                        if($t1_student_subject_id == $t2_student_subject_id && $is_transferee == "no"){
                                                                            $remarks_url = $remarks;

                                                                        }else if($t1_student_subject_id == $t2_student_subject_id && $is_transferee == "yes"){
                                                                            $remarks_url = "Credited";
                                                                        }
                                                                        
                                                                        else if($student_subject_subject_id == $subject_id
                                                                            && $t1_student_subject_id != $t2_student_subject_id
                                                                            && $payment_status == "Enrolled"
                                                                            
                                                                            ){

                                                                            // $_SESSION['remarks'] = "shs_view_transferee_details"; #QWE

                                                                            $remarks_url = "
                                                                                <a href='shs_transferee_remarks.php?student_id=$student_id&s_id=$subject_id&page=shs_view_transferee_details'>
                                                                                    <button type='button' class='btn btn-sm btn-primary'>Remark</button>
                                                                                </a>
                                                                            ";
                                                                        }

                                                                    }
                                                                    
                                                                    echo '<tr class="text-center">'; 
                                                                            echo '<td>'.$value['subject_code'].'</td>';
                                                                            echo '<td>'.$value['subject_type'].'</td>';
                                                                            echo '<td>'.$value['subject_title'].'</td>';
                                                                            echo '<td>'.$value['unit'].'</td>';
                                                                            // echo '<td>'.$schedule_day.'</td>';
                                                                            // echo '<td>'.$schedule_time.'</td>';
                                                                            // echo '<td>'.$room.'</td>';
                                                                            // echo '<td>'.$section.'</td>';
                                                                            echo '<td>'.$value['semester'].'</td>';
                                                                            echo '<td>'.$course_level.'</td>';
                                                                            echo '<td>'.$remarks_url.'</td>';
                                                                    echo '</tr>';
                                                                    }     
                                                            }
                                                            else{
                                                                echo "Student did not have regular enrolled subject for their Grade 11 First Semester";
                                                            }
                                                            
                                                        ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>  
                            <?php
                        }else{
                            ?>
                            <h3 class="text-center">Grade 11 First semester</h3>
                            <table style="font-weight: 200;" class="table table-striped table-bordered table-hover "  style="font-size:12px" cellspacing="0"  > 
                                <thead>
                                    <tr class="text-center"> 
                                        <th class="text-success" rowspan="2">Subject</th>
                                        <th class="text-success" rowspan="2">Type</th>
                                        <th class="text-success" rowspan="2">Description</th>  
                                        <th class="text-success" rowspan="2">Unit</th>
                                        <th class="text-muted" colspan="4">Schedule</th> 
                                    </tr>	
                                    <tr> 
                                        <!-- <th>Day</th> 
                                        <th>Time</th>
                                        <th>Room</th> 
                                        <th>Section</th>   -->
                                        <th class="text-success">Semester</th>  
                                        <th class="text-success">Course Level</th>  
                                        <th class="text-success">Remarks</th>  
                                    </tr>
                                </thead> 	 
                                <tbody>
                                    <?php 

                                        // $listOfSubjects = $studentEnroll->GetStudentSubjectListWithGrades($username);
                                        $listOfSubjects = $studentEnroll
                                            ->GetStudentCurriculumBasedOnSemesterSubject($username,
                                        $userLoggedInId, $GRADE_ELEVEN, $FIRST_SEMESTER);

                                        if($listOfSubjects !== null){

                                            foreach ($listOfSubjects as $key => $value) {
                                                 
                                                $subject_id = $value['subject_id'];
                                                $course_level = $value['course_level'];

                                                $remarks_url = "";
                                            
                                                echo '<tr class="text-center">'; 
                                                        echo '<td>'.$value['subject_code'].'</td>';
                                                        echo '<td>'.$value['subject_type'].'</td>';
                                                        echo '<td>'.$value['subject_title'].'</td>';
                                                        echo '<td>'.$value['unit'].'</td>';
                                                        // echo '<td>'.$schedule_day.'</td>';
                                                        // echo '<td>'.$schedule_time.'</td>';
                                                        // echo '<td>'.$room.'</td>';
                                                        // echo '<td>'.$section.'</td>';
                                                        echo '<td>'.$value['semester'].'</td>';
                                                        echo '<td>'.$value['course_level'].'</td>';
                                                        echo '<td></td>';
                                                echo '</tr>';
                                            }     
                                        }
                                        else{
                                            echo "No Datax was found for Grade 11 1st Semester.";
                                        }
                                        
                                    ?>
                                </tbody>
                            </table>
                            <?php
                        }
                    ?>

                    <!-- GRADE 11 2nd SEM -->
                    <?php 
                    
                        if($subject->CheckStudentGradeLevelEnrolledSubjectSemesterv2(
                            $student_id, $SECOND_SEMESTER, $GRADE_ELEVEN)){
                            
                            ?>
                                <div class="row col-md-12 table-responsive" style="margin-top:5%;">

                                    <div class="card">
                                        <div class="card-header">

                                            <?php 
                                                // echo "wee";
                                                $GRADE_TWELVE = 12;
                                                $GRADE_ELEVEN = 11;
                                                $FIRST_SEMESTER = "First";

                                                // Section Based on the enrollment.
                                                $enrollment_school_year = $studentEnroll->GetStudentSectionGradeLevelSemester($username, $userLoggedInId,
                                                    $GRADE_ELEVEN, $SECOND_SEMESTER);

                                                if($enrollment_school_year !== null){
                                                    
                                                    $term = $enrollment_school_year['term'];
                                                    $period = $enrollment_school_year['period'];
                                                    $school_year_id = $enrollment_school_year['school_year_id'];
                                                    $enrollment_course_id = $enrollment_school_year['course_id'];
                                                    $enrollment_id = $enrollment_school_year['enrollment_id'];


                                                    $enrollment_section_name = $enroll->GetStudentCourseNameByCourseId($enrollment_course_id);

                                                    echo "
                                                        <h4 style='font-weight: 500;' class='text-primary text-center mb-3'>
                                                            Grade 11 $enrollment_section_name $period Semester (SY $term)
                                                        </h4>
                                                    ";
                                                }else{
                                                    echo "
                                                        <h3>Grade 11 Second Semester</h3>	
                                                    ";
                                                }
                                            ?>	

                                            <?php 
                                                # ONLY FOR GRADE 11 2nd BECAUSE OF MOVING UP.
                                                $isFinished = $old_enroll->CheckIfGradeLevelSemesterSubjectWereAllPassed(
                                                    $student_id, $GRADE_ELEVEN, $SECOND_SEMESTER);
                                                
                                                $moveUpBtn = "moveUpAction(\"$student_username\", $student_id)";
                                                

                                                if($isFinished == true
                                                    && $student_course_level != $GRADE_TWELVE){

                                                    // echo "qwe";
                                                    
                                                    $wasSuccess = $old_enroll->StudentMoveUpToGrade12($student_username);

                                                    if($wasSuccess){

                                                        AdminUser::success("$student_username has been Move Up to Grade 12"
                                                            , "view_details.php?subject=show&id=$student_id");
                                                        exit();

                                                    }
                                                }

                                                if($isFinished == true && $student_course_level != $GRADE_TWELVE){
                                                    # Enable this for the button.
                                                    // echo "
                                                    //     <button type='button' onclick='$moveUpBtn' class='btn btn-success'>
                                                    //         Move Up
                                                    //     </button>				
                                                    // ";
                                                }
                                            ?>
                                                        
                                        </div>
                                        <div class="card-body">
                                            <div class="table-responsive" style="margin-top:2%;"> 
                                                                    
                                                <table style="font-weight: 200;" class="table table-striped table-bordered table-hover "  style="font-size:12px" cellspacing="0"  > 
                                                    <thead>
                                                        <tr class="text-center"> 
                                                            <th class="text-primary" rowspan="2">Subject</th>
                                                            <th class="text-primary" rowspan="2">Type</th>
                                                            <th class="text-primary" rowspan="2">Description</th>  
                                                            <th class="text-primary" rowspan="2">Unit</th>
                                                            <th class="text-primary">Semester</th>  
                                                            <th class="text-primary">Course Level</th>  
                                                            <th class="text-primary">Remarks</th>  
                                                        </tr>	
                                                    </thead> 	 
                                                    <tbody>
                                                        <?php 

                                                            // $listOfSubjects = $studentEnroll
                                                            //     ->GetSHSTransfereeEnrolledSubjectSemester($student_username,
                                                            //         $student_id, $GRADE_ELEVEN, $FIRST_SEMESTER);

                                                            $listOfSubjects = $studentEnroll
                                                                ->GetStudentTransCurriculumBasedOnSemesterSubject($username,
                                                                    $userLoggedInId, $GRADE_ELEVEN, $SECOND_SEMESTER, $enrollment_id);

                                                            if($listOfSubjects !== null){

                                                                foreach ($listOfSubjects as $key => $value) {

                                                                    $subject_id = $value['subject_id'];
                                                                    $course_level = $value['course_level'];

                                                                    $remarks_url = "";

                                                                    $query_student_subject = $con->prepare("SELECT 
                                                                
                                                                        t1.subject_id, t1.student_subject_id as t1_student_subject_id,
                                                                        
                                                                        t2.student_subject_id as t2_student_subject_id,
                                                                        t2.remarks,

                                                                        t1.is_transferee

                                                                        FROM student_subject as t1

                                                                        LEFT JOIN student_subject_grade as t2 ON t2.student_subject_id = t1.student_subject_id

                                                                        WHERE t1.subject_id=:subject_id
                                                                        AND t1.student_id=:student_id
                                                                        LIMIT 1");

                                                                    $query_student_subject->bindValue(":subject_id", $subject_id);
                                                                    $query_student_subject->bindValue(":student_id", $student_id);
                                                                    $query_student_subject->execute();

                                                                    $t1_student_subject_id = null;

                                                                    if($query_student_subject->rowCount() > 0){

                                                                        // echo "notx";
                                                                        
                                                                        $row = $query_student_subject->fetch(PDO::FETCH_ASSOC);

                                                                        $student_subject_subject_id = $row['subject_id'];
                                                                        $t1_student_subject_id = $row['t1_student_subject_id'];
                                                                        $t2_student_subject_id = $row['t2_student_subject_id'];
                                                                        $remarks = $row['remarks'];
                                                                        $is_transferee = $row['is_transferee'];

                                                                        // echo $t1_student_subject_id . " ";
                                                                        // echo $t2_student_subject_id . " ";

                                                                        if($t1_student_subject_id == $t2_student_subject_id && $is_transferee == "no"){
                                                                            $remarks_url = $remarks;

                                                                        }else if($t1_student_subject_id == $t2_student_subject_id && $is_transferee == "yes"){
                                                                            $remarks_url = "Credited";
                                                                        }
                                                                        
                                                                        
                                                                        else if($student_subject_subject_id == $subject_id
                                                                            && $t1_student_subject_id != $t2_student_subject_id
                                                                            && $payment_status == "Enrolled"
                                                                            
                                                                            ){

                                                                            // $_SESSION['remarks'] = "shs_view_transferee_details"; #QWE

                                                                            $remarks_url = "
                                                                                <a href='shs_transferee_remarks.php?student_id=$student_id&s_id=$subject_id&page=shs_view_transferee_details'>
                                                                                    <button type='button' class='btn btn-sm btn-primary'>Remark</button>
                                                                                </a>
                                                                            ";
                                                                        }

                                                                    }
                                                                    
                                                                    echo '<tr class="text-center">'; 
                                                                            echo '<td>'.$value['subject_code'].'</td>';
                                                                            echo '<td>'.$value['subject_type'].'</td>';
                                                                            echo '<td>'.$value['subject_title'].'</td>';
                                                                            echo '<td>'.$value['unit'].'</td>';
                                                                            // echo '<td>'.$schedule_day.'</td>';
                                                                            // echo '<td>'.$schedule_time.'</td>';
                                                                            // echo '<td>'.$room.'</td>';
                                                                            // echo '<td>'.$section.'</td>';
                                                                            echo '<td>'.$value['semester'].'</td>';
                                                                            echo '<td>'.$course_level.'</td>';
                                                                            echo '<td>'.$remarks_url.'</td>';
                                                                    echo '</tr>';
                                                                    }     
                                                            }
                                                            else{
                                                                echo "Student did not have regular enrolled subject for their Grade 11 First Semester";
                                                            }
                                                            
                                                        ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>  
                            <?php
                        }else{
                            ?>
                            <h3 class="text-center">Grade 11 Second semester</h3>
                            <table style="font-weight: 200;" class="table table-striped table-bordered table-hover "  style="font-size:12px" cellspacing="0"  > 
                                <thead>
                                    <tr class="text-center"> 
                                        <th class="text-success" rowspan="2">Subject</th>
                                        <th class="text-success" rowspan="2">Type</th>
                                        <th class="text-success" rowspan="2">Description</th>  
                                        <th class="text-success" rowspan="2">Unit</th>
                                        <th class="text-muted" colspan="4">Schedule</th> 
                                    </tr>	
                                    <tr> 
                                        <!-- <th>Day</th> 
                                        <th>Time</th>
                                        <th>Room</th> 
                                        <th>Section</th>   -->
                                        <th class="text-success">Semester</th>  
                                        <th class="text-success">Course Level</th>  
                                        <th class="text-success">Remarks</th>  
                                    </tr>
                                </thead> 	 
                                <tbody>
                                    <?php 

                                        // $listOfSubjects = $studentEnroll->GetStudentSubjectListWithGrades($username);
                                        $listOfSubjects = $studentEnroll
                                            ->GetStudentCurriculumBasedOnSemesterSubject($username,
                                        $userLoggedInId, $GRADE_ELEVEN, $SECOND_SEMESTER);

                                        if($listOfSubjects !== null){

                                            foreach ($listOfSubjects as $key => $value) {
                                                 
                                                $subject_id = $value['subject_id'];
                                                $course_level = $value['course_level'];

                                                $remarks_url = "";
                                                 
                                                echo '<tr class="text-center">'; 
                                                        echo '<td>'.$value['subject_code'].'</td>';
                                                        echo '<td>'.$value['subject_type'].'</td>';
                                                        echo '<td>'.$value['subject_title'].'</td>';
                                                        echo '<td>'.$value['unit'].'</td>';
                                                        // echo '<td>'.$schedule_day.'</td>';
                                                        // echo '<td>'.$schedule_time.'</td>';
                                                        // echo '<td>'.$room.'</td>';
                                                        // echo '<td>'.$section.'</td>';
                                                        echo '<td>'.$value['semester'].'</td>';
                                                        echo '<td>'.$value['course_level'].'</td>';
                                                        echo '<td></td>';
                                                echo '</tr>';
                                            }     
                                        }
                                        else{
                                            echo "No Datax was found for Grade 11 1st Semester.";
                                        }
                                        
                                    ?>
                                </tbody>
                            </table>
                            <?php
                        }
                    ?>

                    <!-- GRADE 12 1st SEM -->
                    <?php 
                    
                        if($subject->CheckStudentGradeLevelEnrolledSubjectSemesterv2(
                            $student_id, $FIRST_SEMESTER, $GRADE_TWELVE)){

                            ?>
                                <div class="row col-md-12 table-responsive" style="margin-top:5%;">

                                    <div class="card">
                                        <div class="card-header">

                                            <?php 
                                                // echo "wee";
                                                $GRADE_TWELVE = 12;
                                                $GRADE_ELEVEN = 11;
                                                $FIRST_SEMESTER = "First";

                                                // Section Based on the enrollment.
                                                $enrollment_school_year = $studentEnroll->GetStudentSectionGradeLevelSemester($username, $userLoggedInId,
                                                    $GRADE_TWELVE, $FIRST_SEMESTER);

                                                if($enrollment_school_year !== null){
                                                    
                                                    $term = $enrollment_school_year['term'];
                                                    $period = $enrollment_school_year['period'];
                                                    $school_year_id = $enrollment_school_year['school_year_id'];
                                                    $enrollment_course_id = $enrollment_school_year['course_id'];
                                                    $enrollment_id = $enrollment_school_year['enrollment_id'];


                                                    $enrollment_section_name = $enroll->GetStudentCourseNameByCourseId($enrollment_course_id);

                                                    echo "
                                                        <h4 style='font-weight: 500;' class='text-primary text-center mb-3'>
                                                            Grade 12 $enrollment_section_name $period Semester (SY $term)
                                                        </h4>
                                                    ";
                                                }else{
                                                    echo "
                                                        <h3>Grade 12 First Semester</h3>	
                                                    ";
                                                }
                                            ?>	
                                        
                                        </div>
                                        <div class="card-body">
                                            <div class="table-responsive" style="margin-top:2%;"> 
                                                                    
                                                <table style="font-weight: 200;" class="table table-striped table-bordered table-hover "  style="font-size:12px" cellspacing="0"  > 
                                                    <thead>
                                                        <tr class="text-center"> 
                                                            <th class="text-primary" rowspan="2">Subject</th>
                                                            <th class="text-primary" rowspan="2">Type</th>
                                                            <th class="text-primary" rowspan="2">Description</th>  
                                                            <th class="text-primary" rowspan="2">Unit</th>
                                                            <th class="text-primary">Semester</th>  
                                                            <th class="text-primary">Course Level</th>  
                                                            <th class="text-primary">Remarks</th>  
                                                        </tr>	
                                                    </thead> 	 
                                                    <tbody>
                                                        <?php 

                                                            // $listOfSubjects = $studentEnroll
                                                            //     ->GetSHSTransfereeEnrolledSubjectSemester($student_username,
                                                            //         $student_id, $GRADE_ELEVEN, $FIRST_SEMESTER);

                                                            $listOfSubjects = $studentEnroll
                                                                ->GetStudentTransCurriculumBasedOnSemesterSubject($username,
                                                                    $userLoggedInId, $GRADE_TWELVE, $FIRST_SEMESTER, $enrollment_id);

                                                            if($listOfSubjects !== null){

                                                                foreach ($listOfSubjects as $key => $value) {

                                                                    $subject_id = $value['subject_id'];
                                                                    $course_level = $value['course_level'];

                                                                    $remarks_url = "";

                                                                    $query_student_subject = $con->prepare("SELECT 
                                                                
                                                                        t1.subject_id, t1.student_subject_id as t1_student_subject_id,
                                                                        
                                                                        t2.student_subject_id as t2_student_subject_id,
                                                                        t2.remarks

                                                                        FROM student_subject as t1

                                                                        LEFT JOIN student_subject_grade as t2 ON t2.student_subject_id = t1.student_subject_id

                                                                        WHERE t1.subject_id=:subject_id
                                                                        AND t1.student_id=:student_id
                                                                        LIMIT 1");

                                                                    $query_student_subject->bindValue(":subject_id", $subject_id);
                                                                    $query_student_subject->bindValue(":student_id", $student_id);
                                                                    $query_student_subject->execute();

                                                                    $t1_student_subject_id = null;

                                                                    if($query_student_subject->rowCount() > 0){

                                                                        // echo "notx";
                                                                        
                                                                        $row = $query_student_subject->fetch(PDO::FETCH_ASSOC);

                                                                        $student_subject_subject_id = $row['subject_id'];
                                                                        $t1_student_subject_id = $row['t1_student_subject_id'];
                                                                        $t2_student_subject_id = $row['t2_student_subject_id'];
                                                                        $remarks = $row['remarks'];

                                                                        // echo $t1_student_subject_id . " ";
                                                                        // echo $t2_student_subject_id . " ";

                                                                        if($t1_student_subject_id == $t2_student_subject_id){
                                                                            $remarks_url = $remarks;

                                                                        }else if($student_subject_subject_id == $subject_id
                                                                            && $t1_student_subject_id != $t2_student_subject_id){

                                                                            // $_SESSION['remarks'] = "shs_view_transferee_details"; #QWE

                                                                            $remarks_url = "
                                                                                <a href='shs_transferee_remarks.php?student_id=$student_id&s_id=$subject_id&page=shs_view_transferee_details'>
                                                                                    <button type='button' class='btn btn-sm btn-primary'>Remark</button>
                                                                                </a>
                                                                            ";
                                                                        }

                                                                    }
                                                                    
                                                                    echo '<tr class="text-center">'; 
                                                                            echo '<td>'.$value['subject_code'].'</td>';
                                                                            echo '<td>'.$value['subject_type'].'</td>';
                                                                            echo '<td>'.$value['subject_title'].'</td>';
                                                                            echo '<td>'.$value['unit'].'</td>';
                                                                            // echo '<td>'.$schedule_day.'</td>';
                                                                            // echo '<td>'.$schedule_time.'</td>';
                                                                            // echo '<td>'.$room.'</td>';
                                                                            // echo '<td>'.$section.'</td>';
                                                                            echo '<td>'.$value['semester'].'</td>';
                                                                            echo '<td>'.$course_level.'</td>';
                                                                            echo '<td>'.$remarks_url.'</td>';
                                                                    echo '</tr>';
                                                                    }     
                                                            }
                                                            else{
                                                                echo "Student did not have regular enrolled subject for their Grade 11 First Semester";
                                                            }
                                                            
                                                        ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>  
                            <?php
                        }else{
                            ?>
                            <h3 class="text-center">Grade 12 First semester</h3>
                            <table style="font-weight: 200;" class="table table-striped table-bordered table-hover "  style="font-size:12px" cellspacing="0"  > 
                                <thead>
                                    <tr class="text-center"> 
                                        <th class="text-success" rowspan="2">Subject</th>
                                        <th class="text-success" rowspan="2">Type</th>
                                        <th class="text-success" rowspan="2">Description</th>  
                                        <th class="text-success" rowspan="2">Unit</th>
                                        <th class="text-muted" colspan="4">Schedule</th> 
                                    </tr>	
                                    <tr> 
                                        <!-- <th>Day</th> 
                                        <th>Time</th>
                                        <th>Room</th> 
                                        <th>Section</th>   -->
                                        <th class="text-success">Semester</th>  
                                        <th class="text-success">Course Level</th>  
                                        <th class="text-success">Remarks</th>  
                                    </tr>
                                </thead> 	 
                                <tbody>
                                    <?php 

                                        // $listOfSubjects = $studentEnroll->GetStudentSubjectListWithGrades($username);
                                        $listOfSubjects = $studentEnroll
                                            ->GetStudentCurriculumBasedOnSemesterSubject($username,
                                        $userLoggedInId, $GRADE_TWELVE, $FIRST_SEMESTER);

                                        if($listOfSubjects !== null){

                                            foreach ($listOfSubjects as $key => $value) {
                                                 
                                            $subject_id = $value['subject_id'];
                                                $course_level = $value['course_level'];

                                                $remarks_url = "";

                                                 
                                            
                                                echo '<tr class="text-center">'; 
                                                        echo '<td>'.$value['subject_code'].'</td>';
                                                        echo '<td>'.$value['subject_type'].'</td>';
                                                        echo '<td>'.$value['subject_title'].'</td>';
                                                        echo '<td>'.$value['unit'].'</td>';
                                                        // echo '<td>'.$schedule_day.'</td>';
                                                        // echo '<td>'.$schedule_time.'</td>';
                                                        // echo '<td>'.$room.'</td>';
                                                        // echo '<td>'.$section.'</td>';
                                                        echo '<td>'.$value['semester'].'</td>';
                                                        echo '<td>'.$value['course_level'].'</td>';
                                                        echo '<td></td>';
                                                echo '</tr>';
                                            }     
                                        }
                                        else{
                                            echo "No Datax was found for Grade 11 1st Semester.";
                                        }
                                        
                                    ?>
                                </tbody>
                            </table>
                            <?php
                        }
                    ?>

                    <!-- GRADE 12 2nd SEM -->
                    <?php 
                    
                        if($subject->CheckStudentGradeLevelEnrolledSubjectSemesterv2(
                            $student_id, $SECOND_SEMESTER, $GRADE_TWELVE)){
                            
                            ?>
                                <div class="row col-md-12 table-responsive" style="margin-top:5%;">

                                    <div class="card">
                                        <div class="card-header">

                                            <?php 
                                                // echo "wee";
                                                $GRADE_TWELVE = 12;
                                                $GRADE_ELEVEN = 11;
                                                $FIRST_SEMESTER = "First";

                                                // Section Based on the enrollment.
                                                $enrollment_school_year = $studentEnroll->GetStudentSectionGradeLevelSemester($username, $userLoggedInId,
                                                    $GRADE_TWELVE, $SECOND_SEMESTER);

                                                if($enrollment_school_year !== null){
                                                    
                                                    $term = $enrollment_school_year['term'];
                                                    $period = $enrollment_school_year['period'];
                                                    $school_year_id = $enrollment_school_year['school_year_id'];
                                                    $enrollment_course_id = $enrollment_school_year['course_id'];
                                                    $enrollment_id = $enrollment_school_year['enrollment_id'];


                                                    $enrollment_section_name = $enroll->GetStudentCourseNameByCourseId($enrollment_course_id);

                                                    echo "
                                                        <h4 style='font-weight: 500;' class='text-primary text-center mb-3'>
                                                            Grade 12 $enrollment_section_name $period Semester (SY $term)
                                                        </h4>
                                                    ";
                                                }else{
                                                    echo "
                                                        <h3>Grade 12 Second Semester</h3>	
                                                    ";
                                                }
                                            ?>	
                                        
                                        </div>
                                        <div class="card-body">
                                            <div class="table-responsive" style="margin-top:2%;"> 
                                                                    
                                                <table style="font-weight: 200;" class="table table-striped table-bordered table-hover "  style="font-size:12px" cellspacing="0"  > 
                                                    <thead>
                                                        <tr class="text-center"> 
                                                            <th class="text-primary" rowspan="2">Subject</th>
                                                            <th class="text-primary" rowspan="2">Type</th>
                                                            <th class="text-primary" rowspan="2">Description</th>  
                                                            <th class="text-primary" rowspan="2">Unit</th>
                                                            <th class="text-primary">Semester</th>  
                                                            <th class="text-primary">Course Level</th>  
                                                            <th class="text-primary">Remarks</th>  
                                                        </tr>	
                                                    </thead> 	 
                                                    <tbody>
                                                        <?php 

                                                            // $listOfSubjects = $studentEnroll
                                                            //     ->GetSHSTransfereeEnrolledSubjectSemester($student_username,
                                                            //         $student_id, $GRADE_ELEVEN, $FIRST_SEMESTER);

                                                            $listOfSubjects = $studentEnroll
                                                                ->GetStudentTransCurriculumBasedOnSemesterSubject($username,
                                                                    $userLoggedInId, $GRADE_TWELVE, $SECOND_SEMESTER, $enrollment_id);

                                                            if($listOfSubjects !== null){

                                                                foreach ($listOfSubjects as $key => $value) {

                                                                    $subject_id = $value['subject_id'];
                                                                    $course_level = $value['course_level'];

                                                                    $remarks_url = "";

                                                                    $query_student_subject = $con->prepare("SELECT 
                                                                
                                                                        t1.subject_id, t1.student_subject_id as t1_student_subject_id,
                                                                        
                                                                        t2.student_subject_id as t2_student_subject_id,
                                                                        t2.remarks

                                                                        FROM student_subject as t1

                                                                        LEFT JOIN student_subject_grade as t2 ON t2.student_subject_id = t1.student_subject_id

                                                                        WHERE t1.subject_id=:subject_id
                                                                        AND t1.student_id=:student_id
                                                                        LIMIT 1");

                                                                    $query_student_subject->bindValue(":subject_id", $subject_id);
                                                                    $query_student_subject->bindValue(":student_id", $student_id);
                                                                    $query_student_subject->execute();

                                                                    $t1_student_subject_id = null;

                                                                    if($query_student_subject->rowCount() > 0){

                                                                        // echo "notx";
                                                                        
                                                                        $row = $query_student_subject->fetch(PDO::FETCH_ASSOC);

                                                                        $student_subject_subject_id = $row['subject_id'];
                                                                        $t1_student_subject_id = $row['t1_student_subject_id'];
                                                                        $t2_student_subject_id = $row['t2_student_subject_id'];
                                                                        $remarks = $row['remarks'];

                                                                        // echo $t1_student_subject_id . " ";
                                                                        // echo $t2_student_subject_id . " ";

                                                                        if($t1_student_subject_id == $t2_student_subject_id){
                                                                            $remarks_url = $remarks;

                                                                        }else if($student_subject_subject_id == $subject_id
                                                                            && $t1_student_subject_id != $t2_student_subject_id){

                                                                            // $_SESSION['remarks'] = "shs_view_transferee_details"; #QWE

                                                                            $remarks_url = "
                                                                                <a href='shs_transferee_remarks.php?student_id=$student_id&s_id=$subject_id&page=shs_view_transferee_details'>
                                                                                    <button type='button' class='btn btn-sm btn-primary'>Remark</button>
                                                                                </a>
                                                                            ";
                                                                        }

                                                                    }
                                                                    
                                                                    echo '<tr class="text-center">'; 
                                                                            echo '<td>'.$value['subject_code'].'</td>';
                                                                            echo '<td>'.$value['subject_type'].'</td>';
                                                                            echo '<td>'.$value['subject_title'].'</td>';
                                                                            echo '<td>'.$value['unit'].'</td>';
                                                                            // echo '<td>'.$schedule_day.'</td>';
                                                                            // echo '<td>'.$schedule_time.'</td>';
                                                                            // echo '<td>'.$room.'</td>';
                                                                            // echo '<td>'.$section.'</td>';
                                                                            echo '<td>'.$value['semester'].'</td>';
                                                                            echo '<td>'.$course_level.'</td>';
                                                                            echo '<td>'.$remarks_url.'</td>';
                                                                    echo '</tr>';
                                                                    }     
                                                            }
                                                            else{
                                                                echo "Student did not have regular enrolled subject for their Grade 11 First Semester";
                                                            }
                                                            
                                                        ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>  
                            <?php
                        }else{
                            ?>
                            <h3 class="text-center">Grade 12 Second semester</h3>
                            <table style="font-weight: 200;" class="table table-striped table-bordered table-hover "  style="font-size:12px" cellspacing="0"  > 
                                <thead>
                                    <tr class="text-center"> 
                                        <th class="text-success" rowspan="2">Subject</th>
                                        <th class="text-success" rowspan="2">Type</th>
                                        <th class="text-success" rowspan="2">Description</th>  
                                        <th class="text-success" rowspan="2">Unit</th>
                                        <th class="text-muted" colspan="4">Schedule</th> 
                                    </tr>	
                                    <tr> 
                                        <!-- <th>Day</th> 
                                        <th>Time</th>
                                        <th>Room</th> 
                                        <th>Section</th>   -->
                                        <th class="text-success">Semester</th>  
                                        <th class="text-success">Course Level</th>  
                                        <th class="text-success">Remarks</th>  
                                    </tr>
                                </thead> 	 
                                <tbody>
                                    <?php 

                                        // $listOfSubjects = $studentEnroll->GetStudentSubjectListWithGrades($username);
                                        $listOfSubjects = $studentEnroll
                                            ->GetStudentCurriculumBasedOnSemesterSubject($username,
                                        $userLoggedInId, $GRADE_TWELVE, $SECOND_SEMESTER);

                                        if($listOfSubjects !== null){

                                            foreach ($listOfSubjects as $key => $value) {
                                                 
                                                $subject_id = $value['subject_id'];
                                                $course_level = $value['course_level'];

                                                $remarks_url = "";
                                            
                                                echo '<tr class="text-center">'; 
                                                        echo '<td>'.$value['subject_code'].'</td>';
                                                        echo '<td>'.$value['subject_type'].'</td>';
                                                        echo '<td>'.$value['subject_title'].'</td>';
                                                        echo '<td>'.$value['unit'].'</td>';
                                                        // echo '<td>'.$schedule_day.'</td>';
                                                        // echo '<td>'.$schedule_time.'</td>';
                                                        // echo '<td>'.$room.'</td>';
                                                        // echo '<td>'.$section.'</td>';
                                                        echo '<td>'.$value['semester'].'</td>';
                                                        echo '<td>'.$value['course_level'].'</td>';
                                                        echo '<td></td>';
                                                echo '</tr>';
                                            }     
                                        }
                                        else{
                                            echo "No Datax was found for Grade 11 1st Semester.";
                                        }
                                        
                                    ?>
                                </tbody>
                            </table>
                            <?php
                        }
                    ?>
                </div>

                <?php
            }
 
            if(isset($_GET['credited_subject']) 
                && $_GET['credited_subject'] == "show" && isset($_GET['id'])){

                ?>
                <div class="row col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <h4><?php echo $lastname?>, <?php echo $firstname;?> <?php echo $middle_name;?>,</h4>
                            <hr>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="container">
                                        <div class="form-group">
                                            <label for="">Student No.</label>
                                            <p for=""><?php echo $student_unique_id;?></p>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="container">
                                        <div class="form-group">
                                            <label for="">Level</label>
                                            <p for=""><?php echo $student_course_level;?></p>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="container">
                                        <div class="form-group">
                                            <label for="">Section</label>
                                            <p for=""><?php echo $section_name;?></p>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="container">
                                        <div class="form-group">
                                            <label for="">Status</label>
                                            <p for="">Enrolled</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr>
                    <hr>

                    <!--  2nd -->
                    <div class="card">
                        <div class="card-body">
                            <div class="row col-md-12">
                                <div class="col-md-4 text-center">
                                    <a href="shs_view_transferee_details.php?profile=show&id=<?php echo $student_id;?>">
                                        <button class="btn btn-lg btn-outline-primary">
                                            Student Information
                                        </button>
                                    </a>
                                </div>

                                <div class="col-md-4 text-center">
                                    <a href="shs_view_transferee_details.php?subject=show&id=<?php echo $student_id;?>">
                                        <button class="btn btn-lg btn-outline-primary">
                                            Enrolled Subjects
                                        </button>
                                    </a>
                                </div>

                                <div class="col-md-4 text-center">
                                    <a href="shs_view_transferee_details.php?credited_subject=show&id=<?php echo $student_id;?>">
                                        <button class="btn btn-lg btn-primary">
                                            Credited Subjects
                                        </button>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="row col-md-12 table-responsive"
                        style="margin-top:5%;">
                        <div class="table-responsive" style="margin-top:2%;"> 
                            <form action="customer/controller.php?action=delete" method="POST"> 
                                    <?php 
                                    
                                        $GRADE_TWELVE = 12;
                                        $GRADE_ELEVEN = 11;
                                        $FIRST_SEMESTER = "First";

                                        // Section Based on the enrollment.
                                        $enrollment_school_year = null;

                                        if($enrollment_school_year !== null){

                                            echo "
                                                <h4 style='font-weight: 500;' class='text-primary text-center mb-3'>
                                                    Grade 11 1st Semester
                                                </h4>
                                            ";
                                        }
                                    
                                    ?> 
                                    <div class="card">
                                        <div class="card-header mt-2">
                                            <h4 class="text-primary text-center">Credited Subjects</h4>
                                        </div>
                                        <div class="card-body">
                                            <table style="font-weight: 200;" class="table table-striped table-bordered table-hover "  style="font-size:12px" cellspacing="0"  > 
                                                <thead>
                                                    <tr class="text-center"> 
                                                        <th class="text-success" rowspan="2">Subject</th>
                                                        <th class="text-success" rowspan="2">Type</th>
                                                        <th class="text-success" rowspan="2">Description</th>  
                                                        <th class="text-success" rowspan="2">Unit</th>
                                                        <th class="text-muted" colspan="4">Schedule</th> 
                                                    </tr>	
                                                    <tr class="text-center"> 
                                                        <!-- <th>Day</th> 
                                                        <th>Time</th>
                                                        <th>Room</th> 
                                                        <th>Section</th>   -->
                                                        <th class="text-success">Semester</th>  
                                                        <th class="text-success">Course Level</th>  
                                                        <th class="text-success">Program Section</th>  
                                                        <th class="text-success">Remarks</th>  
                                                    </tr>
                                                </thead> 	 
                                                <tbody>
                                                    <?php 
                                                        // $listOfSubjects = $studentEnroll->GetStudentSubjectListWithGrades($username);
                                                        $listOfSubjects = $con->prepare("SELECT 
                                                        
                                                            t2.*,
                                                            t3.*
                                                            
                                                            FROM student_subject as t1

                                                            INNER JOIN subject as t2 ON t2.subject_id = t1.subject_id
                                                            INNER JOIN course as t3 ON t3.course_id = t2.course_id
                                                            INNER JOIN student_subject_grade as t4 ON t4.student_subject_id = t1.student_subject_id
                                                        
                                                            WHERE t1.student_id=:student_id
                                                            AND t4.is_transferee=:is_transferee
                                                            -- AND t2.course_level=:course_level
                                                            -- AND t2.semester=:semester
                                                            ");
                                                        
                                                        $listOfSubjects->bindValue(":student_id", $student_id);
                                                        $listOfSubjects->bindValue(":is_transferee", "yes");
                                                        // $listOfSubjects->bindValue(":semester", $FIRST_SEMESTER);
                                                        $listOfSubjects->execute();

                                                        if($listOfSubjects !== null){

                                                            foreach ($listOfSubjects as $key => $value) {

                                                                $subject_id = $value['subject_id'];
                                                                $course_level = $value['course_level'];
                                                                $program_section = $value['program_section'];


                                                                $remarksOutput = "Credited";
                                                            
                                                                echo '<tr class="text-center">'; 
                                                                        echo '<td>'.$value['subject_code'].'</td>';
                                                                        echo '<td>'.$value['subject_type'].'</td>';
                                                                        echo '<td>'.$value['subject_title'].'</td>';
                                                                        echo '<td>'.$value['unit'].'</td>';
                                                                        // echo '<td>'.$schedule_day.'</td>';
                                                                        // echo '<td>'.$schedule_time.'</td>';
                                                                        // echo '<td>'.$room.'</td>';
                                                                        // echo '<td>'.$section.'</td>';
                                                                        echo '<td>'.$value['semester'].'</td>';
                                                                        echo '<td>'.$course_level.'</td>';
                                                                        echo '<td>'.$program_section.'</td>';
                                                                        
                                                                        echo '<td>'.$remarksOutput.'</td>';
                                                                echo '</tr>';
                                                            }     
                                                        }
                                                        else{
                                                            echo "No Datax was found for Grade 11 1st Semester.";
                                                        }
                                                        
                                                    ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>		

                            </form>
                        </div>
                    </div>
                    
                </div>
                <?php
            }

        }


    }
?>