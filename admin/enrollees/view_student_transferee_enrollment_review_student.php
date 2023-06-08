<?php
    include('../registrar_enrollment_header.php');
    include('../../enrollment/classes/StudentEnroll.php');
    include('../../enrollment/classes/StudentSubject.php');
    include('../../enrollment/classes/Enrollment.php');
    include('../../enrollment/classes/Section.php');
    include('../../includes/classes/Student.php');
    include('../../admin/classes/Subject.php');

    ?>
        <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

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

            table{
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
            }

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
                width: 340px;
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
        
        </head> 
    <?php

 if(isset($_GET['inserted'])){

    if(isset($_GET['id']) 
        && isset($_GET['e_id'])
        && isset($_SESSION['selected_course_id'])){

        $selected_course_id = $_SESSION['selected_course_id'];
        $student_id = $_GET['id'];
        // $pending_enrollees_id = $_GET['p_id'];
        $enrollment_id = $_GET['e_id'];

        // echo $student_id;
        $section = new Section($con, $selected_course_id);
        $studentSubject = new StudentSubject($con);

        $subject = new Subject($con, $registrarLoggedIn);

        $section_name = $section->GetSectionName();

        $enroll = new StudentEnroll($con);

        $student_username = $enroll->GetStudentUsername($student_id);

        $student = new Student($con, $student_username);
        $enrollment = new Enrollment($con, $enroll);

        $student_unique_id = $student->GetStudentUniqueId();

        $school_year_obj = $enroll->GetActiveSchoolYearAndSemester();

        $current_school_year_id = $school_year_obj['school_year_id'];
        $current_school_year_term = $school_year_obj['term'];
        $current_school_year_period = $school_year_obj['period'];

        $enrollment_form_id = $enrollment->GetEnrollmentFormId($student_id,
            $selected_course_id, $current_school_year_id);

        $admission_status = $student->GetStudentAdmissionStatus();
                $student_status = $student->GetStudentStatusv2();

        $checkEnrollmentEnrolled = $enrollment->CheckEnrollmentEnrolled($student_id,
                $selected_course_id, $current_school_year_id);

        $cashierEvaluated = $enrollment->CheckEnrollmentCashierApproved($student_id,
                $selected_course_id, $current_school_year_id);

        $payment_status = "";

        if($checkEnrollmentEnrolled == true && $cashierEvaluated == true){
            $payment_status = "Enrolled";

        }else if($checkEnrollmentEnrolled == false && $cashierEvaluated == true){
            $payment_status = "Approved";
        }else{
            $payment_status = "Waiting";
        }

        $getEnrollmentNonEnrolledDate = $enrollment
            ->GetEnrollmentNonEnrolledDate($student_id, $selected_course_id, $current_school_year_id);

        $getEnrollmentEnrolledDate = $enrollment
            ->GetEnrollmentEnrolledDate($student_id, $selected_course_id, $current_school_year_id);

        $proccess_date = null;

        if($checkEnrollmentEnrolled == true){
            $proccess_date = $getEnrollmentEnrolledDate;
        }else{
            $proccess_date = $getEnrollmentNonEnrolledDate;
        }

        if(isset($_POST['change_status_os'])){

            $subject_id = $_POST['subject_id'];
            $student_id = $_POST['student_id'];
            $is_transferee = $_POST['is_transferee'];
            $student_subject_id = $_POST['student_subject_id'];
            $subject_title = $_POST['subject_title'];
            $course_id = $_POST['course_id'];

            // echo $subject_id;

            if($is_transferee == "yes"){

                $update = $con->prepare("UPDATE student_subject
                    SET is_transferee=:is_transferee
                    WHERE student_id=:student_id
                    AND subject_id=:subject_id
                    ");

                $update->bindValue(":is_transferee", "no");
                $update->bindValue(":student_id", $student_id);
                $update->bindValue(":subject_id", $subject_id);

                if($update->execute()){

                    // AdminUser::success("Subject Id $subject_id. is now set as Credit", "");

                    $remove_passed = $con->prepare("DELETE FROM student_subject_grade
                        WHERE student_id=:student_id
                        AND subject_id=:subject_id
                        AND student_subject_id=:student_subject_id
                        AND is_transferee=:is_transferee
                        ");

                    $remove_passed->bindValue(":student_id", $student_id);
                    $remove_passed->bindValue(":subject_id", $subject_id);
                    $remove_passed->bindValue(":student_subject_id", $student_subject_id);
                    $remove_passed->bindValue(":is_transferee", "yes");

                    if($remove_passed->execute()){
                       
                        AdminUser::success("Non-Credited subject #$subject_id has changed into Credited", "view_student_transferee_enrollment_review_student.php?inserted=true&id=$student_id&e_id=$enrollment_id");
                        // header("Location: view_student_transferee_enrollment_review_student.php?inserted=true&id=$student_id");
                        exit();
                    }
                }
                # Remove Passed Remarks on the student_subject_grade table
            }

            if($is_transferee == "no"){

                $update = $con->prepare("UPDATE student_subject
                    SET is_transferee=:is_transferee
                    WHERE student_id=:student_id
                    AND student_subject_id=:student_subject_id
                    AND subject_id=:subject_id
                    ");

                $update->bindValue(":is_transferee", "yes");
                $update->bindValue(":student_id", $student_id);
                $update->bindValue(":student_subject_id", $student_subject_id);
                $update->bindValue(":subject_id", $subject_id);

                // if(false){
                if($update->execute()){

                    // $student_subject_id = $con->lastInsertId();

                    $remarks = "Passed";

                    $is_transferee = "yes";

                    $add = $con->prepare("INSERT INTO student_subject_grade 
                        (student_id, student_subject_id, subject_id, course_id,
                        subject_title, remarks, is_transferee)
                        VALUES (:student_id, :student_subject_id, :subject_id, :course_id,
                        :subject_title, :remarks, :is_transferee)");

                    $add->bindParam(':student_id', $student_id);
                    $add->bindParam(':student_subject_id', $student_subject_id);
                    $add->bindParam(':subject_id', $subject_id);
                    $add->bindParam(':course_id', $course_id);
                    $add->bindParam(':subject_title', $subject_title);
                    $add->bindParam(':remarks', $remarks);
                    $add->bindParam(':is_transferee', $is_transferee);

                    if($add->execute()){
                      
                        AdminUser::success("Non-Credited subject #$subject_id has changed into Credited", "view_student_transferee_enrollment_review_student.php?inserted=true&id=$student_id&e_id=$enrollment_id");
                        exit();
                    }

                }


             
            }
            # Update
        }
        if(isset($_POST['remove_status_os'])){

            $subject_id = $_POST['subject_id'];
            $student_id = $_POST['student_id'];
            $is_transferee = $_POST['is_transferee'];
            $student_subject_id = $_POST['student_subject_id'];
            $subject_title = $_POST['subject_title'];
            $course_id = $_POST['course_id'];

            # Credited Subject
            if($is_transferee == "yes"){

                $delete_credited = $con->prepare("DELETE FROM student_subject_grade
                    WHERE student_subject_id=:student_subject_id
                    AND student_id=:student_id
                    -- AND school_year_id=:school_year_id
                    ");

                $delete_credited->bindValue(":student_subject_id", $student_subject_id);
                $delete_credited->bindValue(":student_id", $student_id);
                // $delete_credited->bindValue(":school_year_id", $current_school_year_id);
    
                // echo $student_subject_id;

                if($delete_credited->execute()){
                    $delete = $con->prepare("DELETE FROM student_subject
                        WHERE student_subject_id=:student_subject_id
                        AND student_id=:student_id
                        AND school_year_id=:school_year_id");

                    $delete->bindValue(":student_subject_id", $student_subject_id);
                    $delete->bindValue(":student_id", $student_id);
                    $delete->bindValue(":school_year_id", $current_school_year_id);
                    if($delete->execute()){

                        AdminUser::remove("Subject Id #$subject_id has been removed", "view_student_transferee_enrollment_review_student.php?inserted=true&id=$student_id&e_id=$enrollment_id");
                        exit();
                    }
                }
            }

            # Non credited Subject
            if($is_transferee == "no"){
                $delete = $con->prepare("DELETE FROM student_subject
                    WHERE student_subject_id=:student_subject_id
                    AND student_id=:student_id
                    AND school_year_id=:school_year_id");

                $delete->bindValue(":student_subject_id", $student_subject_id);
                $delete->bindValue(":student_id", $student_id);
                $delete->bindValue(":school_year_id", $current_school_year_id);
                if($delete->execute()){

                    AdminUser::remove("Subject Id #$subject_id has been removed",
                        "view_student_transferee_enrollment_review_student.php?inserted=true&id=$student_id&e_id=$enrollment_id");
                    exit();
                }
            }

        }

        if(isset($_POST['remove_added_btn_os'])){

            $student_subject_id = $_POST['student_subject_id'];
            $subject_id = $_POST['subject_id'];

            $delete = $con->prepare("DELETE FROM student_subject
                    WHERE student_subject_id=:student_subject_id
                    AND student_id=:student_id
                    AND school_year_id=:school_year_id");

            $delete->bindValue(":student_subject_id", $student_subject_id);
            $delete->bindValue(":student_id", $student_id);
            $delete->bindValue(":school_year_id", $current_school_year_id);

            if($delete->execute()){

                AdminUser::remove("Subject Id #$subject_id has been removed",
                    "view_student_transferee_enrollment_review_student.php?inserted=true&id=$student_id&e_id=$enrollment_id");
                exit();
            }
        }

        if($studentSubject->CheckIfStudentInsertedSubject($student_id,
            $enrollment_id, $current_school_year_id) == true){

            if(isset($_POST['mark_as_final_os'])){

                $enrollment = new Enrollment($con, null);
                // $url = "../admission/index.php";
                $url = "../student/transferee_insertion.php?enrolled_subjects=true&id=$student_id";
               
                // $enrollment_id = $_POST['enrollment_id'];
                $wasSuccess = $enrollment->MarkAsRegistrarEvaluatedByEnrollmentId($enrollment_id);

                if($wasSuccess == true){

                    AdminUser::success("Student has been given subjects", "$url");
                    exit();
                }
            }

                $my_course_subjects = [];
                $course_section_subjects = $enroll->GetStudentsStrandSubjectsPerLevelSemester($student_username);


            ?>
                <div class="row col-md-12">

                    <div class="content">

                        <div class="form-header">
                            <div class="header-content">
                                <h2>Enrollment form</h2>
                            </div>

                            <div class="student-table">
                                <table>
                                    <tr>
                                    <th>Form ID</th>
                                    <th>Admission type</th>
                                    <th>Student no</th>
                                    <th>Status</th>
                                    <th>Submitted on:</th>
                                    </tr>
                                    <tr>
                                    <td><?php echo $enrollment_form_id;?></td>
                                    <td>Transferee</td>
                                    <td><?php echo $student_unique_id;?></td>
                                    <td>Evaluation</td>

                                    <td><?php
                                        $date = new DateTime($proccess_date);
                                        $formattedDate = $date->format('m/d/Y H:i');

                                        echo $formattedDate;
                                    ?></td>
                                    
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="process-status">
                        <table class="selection">
                            <tr>
                                <th class="checkDetails" id="icon-1">
                                    <i  style="color: #FFFF;" class="bi bi-clipboard-check"></i>
                                </th>

                                <th style="color: #FFFF;" id="line-1">___________________________</th>
                                <th  class="findSection" id="icon-2">
                                    <i style="color: #FFFF;" class="bi bi-building"></i>
                                </th>

                                <th  style="color: #FFFF;"  id="line-2">___________________________</th>
                                <th class="subConfirm" id="icon-3">
                                    <i style="color: #FFFF;" class="bi bi-journal"></i>
                                </th>

                                <th  style="color: #FFFF;"  id="line-2">___________________________</th>
                                <th class="subConfirm" id="icon-3">
                                    <i style="color: #FFFF;" class="bi bi-journal"></i>
                                </th>

                            </tr>
                            <tr>
                                <td style="color: #FFFF;" class="checkDetails" id="process-1">Check details</td>
                                <td></td>
                                <td style="color: #FFFF;" class="findSection" id="process-2">Find section</td>
                                <td></td>
                                <td style="color: #FFFF;" class="subConfirm" id="process-3">Subject Confirmation</td>
                            
                                <td></td>
                                <td style="color: #FFFF;" class="subConfirm" id="process-3">Review</td>
                            </tr>
                        </table>
                    </div>


                    <hr>
                    <hr>


                    <h4>Student Selected Section: <?php echo $section_name; ?> Subjects</h4>

                    <div class="card-body">


                        


                        <div class="form-check">

                            <input class="form-check-input" 
                                    onclick="AddAdmissionStatus(this, <?php echo $student_id;?>)" 
                                    type="radio" name="studentType" id="regularRadio" 
                                    value="Regular"
                                    <?php if ($student_status === 'Regular') { echo 'checked'; } ?>>

                            <label class="form-check-label" for="regularRadio">
                                Regular
                            </label>

                        </div>

                        <div class="form-check">

                            <input class="form-check-input" 
                                onclick="AddAdmissionStatus(this, <?php echo $student_id;?>)" 
                                type="radio" name="studentType" id="irregularRadio" 
                                value="Irregular"
                                <?php if ($student_status === 'Irregular') { echo 'checked'; } ?>>


                            <label class="form-check-label" for="irregularRadio">
                                Irregular
                            </label>
                        </div>

                        <h3 class="text-center text-primary"></h3>
                        <table id="dash-table" 
                            class="table table-striped table-bordered table-hover table-responsive" 
                            style="font-size:14px" cellspacing="0">
                            <thead>
                                <tr class="text-center"> 
                                    <th rowspan="1">Id</th>
                                    <th rowspan="1">Code</th>  
                                    <th rowspan="2">Description</th>
                                    <th colspan="1">Unit</th> 
                                    <th colspan="1">Type</th> 
                                    <th colspan="1">Grade Level</th> 
                                    <th colspan="1">Semester</th> 
                                    <th colspan="1">Status</th> 
                                    <th colspan="1">Action</th> 
                                </tr>	
                            </thead> 
                            <tbody>
                                <?php

                                # For Pending Grade 11 1st Semester Only
                                $semester = "First";

                                $transfereeSubjects = $con->prepare("SELECT 
                                    t1.is_transferee, t1.is_final,
                                    t1.student_subject_id as t2_student_subject_id, 
                                    t3.student_subject_id as t3_student_subject_id,
                                    t2.* FROM student_subject as t1

                                    INNER JOIN subject as t2 ON t2.subject_id = t1.subject_id
                                    LEFT JOIN student_subject_grade as t3 ON t3.student_subject_id = t1.student_subject_id

                                    WHERE t1.student_id=:student_id
                                    AND t1.is_final=0
                                    AND t1.school_year_id=:school_year_id
                                    AND t2.course_id=:course_id
                                    ");

                                $transfereeSubjects->bindValue(":student_id", $student_id);
                                $transfereeSubjects->bindValue(":school_year_id", $current_school_year_id);
                                $transfereeSubjects->bindValue(":course_id", $selected_course_id);
                                $transfereeSubjects->execute();

                                    $totalUnits = 0;

                                    if($transfereeSubjects != null){
                                        $applyTabIsAvailable = true;

                                        foreach ($transfereeSubjects as $key => $row) {

                                            $unit = $row['unit'];
                                            $subject_id = $row['subject_id'];
                                            $subject_title = $row['subject_title'];
                                            $course_id = $row['course_id'];
                                            $subject_code = $row['subject_code'];
                                            $subject_type = $row['subject_type'];
                                            $semester = $row['semester'];
                                            $course_level = $row['course_level'];
                                            $is_transferee = $row['is_transferee'];

                                            // $is_final = $row['is_final'];

                                            if($course_id == $selected_course_id){
                                                array_push($my_course_subjects, $subject_id);
                                            }
                                            $totalUnits += $unit;

                                            $text = "";

                                            if($is_transferee == "yes"){
                                                $text = "
                                                    <button class='btn btn-sm btn'>Credited</button>
                                                ";
                                            }else{
                                                $text = "
                                                    <button class='btn btn-sm btn'>Non-Credited</button>
                                                ";             
                                            }

                                            $student_subject_id = $row['t2_student_subject_id'];
                                        
                                            echo "
                                                <tr class='text-center'>
                                                    <td>$subject_id</td>
                                                    <td>$subject_code</td>
                                                    <td>$subject_title</td>
                                                    <td>$unit</td>
                                                    <td>$subject_type</td>
                                                    <td>$course_level</td>
                                                    <td>$semester</td>
                                                    <td>$text</td>
                                                    <td>
                                                        <form method='POST'>
                                                            <input type='hidden' name='is_transferee' value='$is_transferee'>
                                                            <input type='hidden' name='student_id' value='$student_id'>
                                                            <input type='hidden' name='subject_id' value='$subject_id'>
                                                            <input type='hidden' name='student_subject_id' value='$student_subject_id'>
                                                            <input type='hidden' name='subject_title' value='$subject_title'>
                                                            <input type='hidden' name='course_id' value='$course_id'>
                                                            <button type='submit' name='change_status_os' class='btn btn-outline-success'>Change</button>
                                                            <button 
                                                                type='submit'
                                                                name='remove_status_os' class='btn btn-danger'
                                                                onclick=\"return confirm('Are you sure you want to remove?');\"
                                                                >Remove
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


                        <?php
                            $result = array_diff($course_section_subjects, $my_course_subjects);
                        
                            if (empty($result)) {
                               echo "
                                <h3 class='text-center text-info'>No Removed Subject .</h3>
                               ";

                            }else{

                                # Set Transferee Student as Irregular.

                                $doesHaveRemovedSubjects = "true";
                               

                                if(isset($_POST['remove_credited_btn'])){

                                    // $hidden_subject_id = $_POST['hidden_subject_id'];

                                    $subjectIds = implode(',', $my_course_subjects);

                                    $sql = $con->prepare("SELECT * FROM 
                                    
                                        subject as t1

                                        WHERE t1.subject_id NOT IN ($subjectIds)
                                        -- WHERE t1.student_id=:student_id
                                        AND t1.course_id=:course_id
                                        AND t1.course_level=:course_level
                                        AND t1.semester=:semester

                                        ");

                                    $sql->bindValue(":course_id", $selected_course_id);
                                    $sql->bindValue(":course_level", $course_level);
                                    $sql->bindValue(":semester", $current_school_year_period);
                                    $sql->execute();
 
                                    $add_student_subject = $con->prepare("INSERT INTO student_subject 
                                        (student_id, enrollment_id, subject_id, subject_program_id, course_level,
                                        school_year_id, is_final, is_transferee)
                                        VALUES (:student_id, :enrollment_id, :subject_id, :subject_program_id, :course_level,
                                        :school_year_id, :is_final, :is_transferee)");


                                    $add = $con->prepare("INSERT INTO student_subject_grade 
                                        (student_id, subject_id, student_subject_id, course_id,
                                        subject_title, remarks, is_transferee)
                                        VALUES (:student_id, :subject_id, :student_subject_id, :course_id,
                                        :subject_title, :remarks, :is_transferee)");
                                    
                                    $creditedSuccess = false;

                                    if($sql->rowCount() > 0){

                                        while($row = $sql->fetch(PDO::FETCH_ASSOC)){

                                            $subject_id = $row['subject_id'];

                                            echo $subject_id;
                                            echo "<br>";

                                            $subject_title = $subject->GetSubjectTitle($subject_id);

                                            $subject_program_id = $row['subject_program_id'];

                                            // echo $subject_id;

                                            $add_student_subject->bindValue(":student_id", $student_id);
                                            $add_student_subject->bindValue(":enrollment_id", $enrollment_id);
                                            $add_student_subject->bindValue(":subject_id", $subject_id);
                                            $add_student_subject->bindValue(":subject_program_id", $subject_program_id);
                                            $add_student_subject->bindValue(":course_level", $course_level);
                                            $add_student_subject->bindValue(":school_year_id", $current_school_year_id);
                                            $add_student_subject->bindValue(":is_final", 1);
                                            $add_student_subject->bindValue(":is_transferee", "yes");

                                            // if(false){
                                            if($add_student_subject->execute()){

                                                $student_subject_id = $con->lastInsertId();

                                                $remarks = "Passed";
                                                $is_transferee = "yes";


                                                $add->bindParam(':student_id', $student_id);
                                                $add->bindParam(':student_subject_id', $student_subject_id);
                                                $add->bindParam(':subject_id', $subject_id);

                                                $add->bindParam(':course_id', $selected_course_id);

                                                $add->bindParam(':subject_title', $subject_title);

                                                $add->bindParam(':remarks', $remarks);
                                                $add->bindParam(':is_transferee', $is_transferee);

                                                if($add->execute()){
                                                
                                                    $creditedSuccess = true;

                                                }

                                            }
                                        }

                                        if($creditedSuccess == true){

                                            // AdminUser::success("Credited Success", "");
                                        }
                                    }

                                }

                                ?>
                                    <h5 class="text-center">Removed Subjects</h5>
                                    <!-- REMOVE SUBJECTS -->
                                    <form method="POST">
                                        
                                        <table id="" class="table table-striped table-bordered table-hover "  style="font-size:13px" cellspacing="0"  > 
                                            <thead>
                                                <tr class="text-center"> 
                                                    <th rowspan="2">Id</th>
                                                    <th rowspan="2">Code</th>
                                                    <th rowspan="2">Description</th>
                                                    <th rowspan="2">Unit</th>
                                                    <th rowspan="2">Type</th>
                                                </tr>	
                                            </thead> 	
                                            <tbody>
                                                <?php
                                                    // print_r($my_course_subjects);

                                                    $subjectIds = implode(',', $my_course_subjects);

                                                    $sql = $con->prepare("SELECT * FROM 
                                                    
                                                        subject as t1

                                                        WHERE t1.subject_id NOT IN ($subjectIds)
                                                        -- WHERE t1.student_id=:student_id
                                                        AND t1.course_id=:course_id
                                                        AND t1.course_level=:course_level
                                                        AND t1.semester=:semester

                                                        ");

                                                    $sql->bindValue(":course_id", $selected_course_id);
                                                    $sql->bindValue(":course_level", $course_level);
                                                    $sql->bindValue(":semester", $current_school_year_period);
                                                    $sql->execute();
                                                
                                                    $totalUnits = 0;
                                                
                                                    if($sql->rowCount() > 0){
                                                        
                                                        while($row = $sql->fetch(PDO::FETCH_ASSOC)){

                                                            $subject_id = $row['subject_id'];

                                                            // echo $subject_id;
                                                            $subject_code = $row['subject_code'];
                                                            $subject_title = $row['subject_title'];
                                                            $unit = $row['unit'];
                                                            $subject_type = $row['subject_type'];
                                                            // $is_transferee = $row['is_transferee'];

                                                            $totalUnits += $unit;

                                                            $status = "Ongoing";
                                                            
                                                            echo "
                                                                <tr class='text-center'>
                                                                    <td>$subject_id</td>
                                                                    <td>$subject_code</td>
                                                                    <td>$subject_title</td>
                                                                    <td>$unit</td>
                                                                    <td>$subject_type</td>
                                                                </tr>
                                                            ";
                                                        }
                                                    }
                                                ?>
                                                <tr class="text-center">
                                                    <td colspan="3"  style="text-align: right;" >Remove Units</td>
                                                    <td><?php echo $totalUnits;?></td>
                                                </tr> 
                                            </tbody>
                                        </table>
                                        <button style="display: none;" type="submit" id="remove_credited_btn" name="remove_credited_btn" 
                                            class="btn btn-primary">Credited</button>
                                    </form>
                                <?php
                            }
                        ?>


                        <h3 class="mb-3 text-muted">Added Subjects</h3>

                        <?php 
                            if(count($subject->GetNewTransfereeAddedSubject($student_id,
                                $current_school_year_id, $selected_course_id)) > 0){
                                ?>
                                    <table class="table table-bordered table-hover "  style="font-size:15px" cellspacing="0"> 
                                        <thead>
                                            <tr class="text-center"">
                                                <th>Id</th>
                                                <th>Section</th>
                                                <th>Code</th>
                                                <th>Description</th>
                                                <th>Unit</th>
                                                <th>Pre-Requisite</th>
                                                <th>Type</th>
                                                <th></th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            <?php 

                                                $addedSubjects = $con->prepare("SELECT 
                                                    t1.is_transferee, t1.is_final,
                                                    t1.student_subject_id as t2_student_subject_id, 
                                                    t3.student_subject_id as t3_student_subject_id,

                                                    t4.program_section,
                                                    t2.* FROM student_subject as t1

                                                    INNER JOIN subject as t2 ON t2.subject_id = t1.subject_id
                                                    LEFT JOIN course as t4 ON t4.course_id = t2.course_id
                                                    LEFT JOIN student_subject_grade as t3 ON t3.student_subject_id = t1.student_subject_id

                                                    WHERE t1.student_id=:student_id
                                                    AND t1.is_final=0
                                                    AND t1.school_year_id=:school_year_id
                                                    AND t2.course_id!=:course_id

                                                    ");

                                                $addedSubjects->bindValue(":student_id", $student_id);
                                                $addedSubjects->bindValue(":school_year_id", $current_school_year_id);
                                                $addedSubjects->bindValue(":course_id", $selected_course_id);
                                                $addedSubjects->execute();
                                            
                                                if($addedSubjects->rowCount() > 0){

                                                    while($row = $addedSubjects->fetch(PDO::FETCH_ASSOC)){

                                                        $subject_id = $row['subject_id'];
                                                        $subject_code = $row['subject_code'];
                                                        $subject_title = $row['subject_title'];
                                                        $pre_requisite = $row['pre_requisite'];
                                                        $subject_type = $row['subject_type'];
                                                        $unit = $row['unit'];
                                                        $course_level = $row['course_level'];
                                                        $program_section = $row['program_section'];
                                                        $program_section = $row['program_section'];
                                                        $student_subject_id = $row['t2_student_subject_id'];

                                                        echo "
                                                            <tr class='text-center'>
                                                                <td>$subject_id</td>
                                                                <td>$program_section</td>
                                                                <td>$subject_code</td>
                                                                <td>$subject_title</td>
                                                                <td>$unit</td>
                                                                <td>$pre_requisite</td>
                                                                <td>$subject_type</td>
                                                                <td>
                                                                    <form method='POST'>

                                                                        <input type='hidden' name='student_subject_id' value='$student_subject_id'>
                                                                        <input type='hidden' name='subject_id' value='$subject_id'>
                                                                        <button 
                                                                            type='submit'
                                                                            name='remove_added_btn_os' class='btn btn-danger'
                                                                            onclick=\"return confirm('Are you sure you want to remove?');\"
                                                                            >Remove
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
                                <?php
                            }else{
                                echo "
                                    <h3 class='text-center text-info'>No Added Data Found.</h3>
                                ";
                            }
                        ?>

                        <!-- <button type="button" onclick="confirmUpdate(<?php echo $pending_enrollees_id; ?>,
                            <?php echo $student_id; ?>, <?php echo $enrollment_id; ?>)" 
                            name=""
                        class="btn btn-sm btn-success">Mark as final</button> -->

                        <div class="row col-md-12">
                            <div class="col-md-2">
                                <form method="POST">
                                    <button type="submit" 
                                    name="mark_as_final_os" class="btn btn-sm btn-success">Mark as Final</button>
                                </form>
                            </div>
                            <div class="col-md-2">
                                    <a href="../admission/transferee_process_enrollment.php?step3=true&st_id=<?php echo $student_id;?>&selected_course_id=<?php echo $selected_course_id?>">
                                        <button class="btn btn-sm btn-primary">Go back</button>
                                    </a>
                            </div>
                        </div>
                  


                    </div>
                </div>
            <?php
        }else{
            echo "
                <div class='row col-md-12'>
                    <h4 class='text-center'>No Inserted Subjects</h4>
                </div>
            ";
        }
        
    }
 }
?>

<script>

    function AddAdmissionStatus(radio, studentId){

        var transferee_student_status = radio.value;

        // console.log(studentId)
        Swal.fire({
            icon: 'question',
            title: `Mark as ${transferee_student_status}?`,
            showCancelButton: true,
            confirmButtonText: 'Yes',
            cancelButtonText: 'Cancel'
        }).then((result) => {

            if (result.isConfirmed) {

                $.ajax({
                url: '../ajax/pending/update_student_status.php',
                type: 'POST',
                data: {
                    transferee_student_status, studentId
                },
                success: function(response) {

                    console.log(response)
                    
                    if(response == "success"){

                        Swal.fire({
                            icon: 'success',
                            title: `Successfully Mark as ${transferee_student_status}`,
                            showConfirmButton: false,
                            timer: 2000, // Adjust the duration of the toast message in milliseconds (e.g., 3000 = 3 seconds)
                            toast: true,
                            position: 'top-end',
                            showClass: {
                            popup: 'swal2-noanimation',
                            backdrop: 'swal2-noanimation'
                            },
                            hideClass: {
                            popup: '',
                            backdrop: ''
                            }
                        });
                    }
                },
                error: function(xhr, status, error) {
                    // Handle any errors here
                    console.log(error);
                }
            });
            }
        });
    }

    // function confirmUpdate(pending_enrollees_id, student_id, enrollment_id) {
    //     Swal.fire({
    //         icon: 'question',
    //         title: 'Are you sure',
    //         showCancelButton: true,
    //         confirmButtonText: 'Yes',
    //         cancelButtonText: 'No'
    //     }).then((result) => {
    //         if (result.isConfirmed) {
    //             // console.log("nice")
    //             $.ajax({
    //                 url: '../ajax/pending/update_pending.php',
    //                 type: 'POST',
    //                 data: {
    //                    pending_enrollees_id, student_id, enrollment_id
    //                 },
    //                 success: function(response) {
    //                     if(response == "success"){
    //                         window.location.href = '../admission/index.php';
    //                     }

    //                     // console.log(response)
    //                 },
    //                 error: function(xhr, status, error) {
    //                     // Handle any errors here
    //                     console.log(error);
    //                 }
    //             });
    //         } else {
    //             // User clicked "No," perform alternative action or do nothing
    //         }
    //     });
    // }

</script>