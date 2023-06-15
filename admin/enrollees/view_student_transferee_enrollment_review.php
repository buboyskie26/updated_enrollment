<?php
    include('../registrar_enrollment_header.php');
    include('../../enrollment/classes/StudentEnroll.php');
    include('../../enrollment/classes/Enrollment.php');
    include('../../enrollment/classes/StudentSubject.php');
    include('../../enrollment/classes/Section.php');
    include('../../admin/classes/Subject.php');
    require_once("../../includes/classes/Student.php");

    ?>
        <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css"
        rel="stylesheet"
        integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ"
        crossorigin="anonymous"
        />
        <link  rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css"
        />
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

    $enroll = new StudentEnroll($con);

    $school_year_obj = $enroll->GetActiveSchoolYearAndSemester();

    $current_school_year_id = $school_year_obj['school_year_id'];
    $current_school_year_term = $school_year_obj['term'];
    $current_school_year_period = $school_year_obj['period'];

 if(isset($_GET['inserted'])){

    if(
        isset($_GET['id']) 
        && isset($_GET['p_id'])
        && isset($_GET['e_id'])
        && isset($_SESSION['selected_course_id'])
    
    ){

        $selected_course_id = $_SESSION['selected_course_id'];

        // echo $selected_course_id;
        $student_id = $_GET['id'];
        $pending_enrollees_id = $_GET['p_id'];
        $enrollment_id = $_GET['e_id'];

        // echo $student_id; qwe

        $sql = $con->prepare("SELECT * FROM pending_enrollees
                WHERE pending_enrollees_id=:pending_enrollees_id
            ");

        $sql->bindValue(":pending_enrollees_id", $pending_enrollees_id);
        $sql->execute();

        $date_creation = date("Y-m-d H:i:s");

        $row = null;
        if($sql->rowCount() > 0){
            $row = $sql->fetch(PDO::FETCH_ASSOC);

            $date_creation = $row['date_creation'];

        }

        $section = new Section($con, $selected_course_id);

        $studentSubject = new StudentSubject($con);

        $student_username = $enroll->GetStudentUsername($student_id);
        
        $student_course_level = $enroll->GetStudentCourseLevel($student_username);

        $subject = new Subject($con, $registrarLoggedIn, null);


        $section_name = $section->GetSectionName();


        $student_course_id = $enroll->GetStudentCourseIdById($student_id);
        $student_username = $enroll->GetStudentUsername($student_id);

        $student = new Student($con, $student_username);
        $enrollment = new Enrollment($con, null);

        
        $enrollment_form_id = $enrollment->GetEnrollmentFormId($student_id,
            $student_course_id, $current_school_year_id);


        if(isset($_POST['change_status'])){

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
                       
                        AdminUser::success("Non-Credited subject #$subject_id has changed into Credited", "view_student_transferee_enrollment_review.php?inserted=true&id=$student_id&p_id=$pending_enrollees_id&e_id=$enrollment_id");

                        // header("Location: view_student_transferee_enrollment_review.php?inserted=true&id=$student_id");
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
                      
                        AdminUser::success("Non-Credited subject #$subject_id has changed into Credited", "view_student_transferee_enrollment_review.php?inserted=true&id=$student_id&p_id=$pending_enrollees_id&e_id=$enrollment_id");
                        exit();
                    }

                }



             
            }
            # Update
        }
        if(isset($_POST['remove_status'])){

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
                        AdminUser::remove("Subject Id #$subject_id has been removed", "view_student_transferee_enrollment_review.php?inserted=true&id=$student_id&p_id=$pending_enrollees_id&e_id=$enrollment_id");
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

                    AdminUser::remove("Subject Id #$subject_id has been removed", "view_student_transferee_enrollment_review.php?inserted=true&id=$student_id&p_id=$pending_enrollees_id&e_id=$enrollment_id");
                    exit();
                }
            }

        }

        if(isset($_POST['remove_added_btn'])){

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

                AdminUser::remove("Subject Id #$subject_id has been removed", "view_student_transferee_enrollment_review.php?inserted=true&id=$student_id&p_id=$pending_enrollees_id&e_id=$enrollment_id");
                exit();
            }
        }

        if($studentSubject->CheckIfStudentInsertedSubject($student_id,
            $enrollment_id, $current_school_year_id) == true){

            $my_course_subjects = [];

            $course_section_subjects = $enroll->GetStudentsStrandSubjectsPerLevelSemester($student_username);

            $doesHaveRemovedSubjects = "Regular";

            $student_status = $student->GetStudentStatusv2();

            $student_unique_id = $student->GetStudentUniqueId();

            // print_r($course_section_subjects);

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
                                        $date = new DateTime($date_creation);
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
                    <div class="container mt-4 mb-2">
                        <h4 class="mb-3 text-start text-muted">Credited(s) subjects</h4>
                        <form method="POST">

                                <table id="credit_section_table" class="table table-bordered table-hover "  style="font-size:15px" cellspacing="0"> 
                                    <thead class="bg-dark">
                                        <tr class="text-center"">
                                            <th>Code</th>
                                            <th>Description</th>
                                            <th>Unit</th>
                                            <th>Pre-Requisite</th>
                                            <th>Semester</th>
                                            <th>Level</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <?php 

                                            $student_program_id = $enroll->GetStudentProgramId($selected_course_id);

                                        
                                            # Depends on the PROGRAM CURRICULUM.
                                            
                                            $sql = $con->prepare("SELECT DISTINCT 
                                                t1.subject_title, 
                                                t1.subject_code,
                                                t1.unit,
                                                t1.semester,
                                                t1.course_level,
                                                t1.pre_req_subject_title,
                                                t1.subject_type

                                            
                                                FROM subject_program as t1
                                            -- 
                                                INNER JOIN student_subject_grade as t2 ON t2.subject_title = t1.subject_title

                                                WHERE t2. student_id=:student_id
                                                AND  t2.is_transferee='yes'
                                                AND t2.remarks='Passed'
                                                ");

                                            $sql->bindValue(":student_id", $student_id);
                                          
                                            $sql->execute();

                        

                                            if($sql->rowCount() > 0){

                                                while($row = $sql->fetch(PDO::FETCH_ASSOC)){

                                                    // $subject_id = $row['subject_id'];

                                                    $subject_code = $row['subject_code'];
                                                    $subject_title = $row['subject_title'];
                                                    // $pre_requisite = $row['pre_requisite'];
                                                    $pre_requisite = $row['pre_req_subject_title'];
                                                    $subject_type = $row['subject_type'];
                                                    $unit = $row['unit'];
                                                    $semester = $row['semester'];
                                                    $course_level = $row['course_level'];


                                                    // $subject_title = $row['subject_title'];

                                                    $student_student_subject_id = 0;
                                                     

                                                    echo "
                                                        <tr class='text-center'>
                                                            <td>$subject_code</td>
                                                            <td>$subject_title</td>
                                                            <td>$unit</td>
                                                            <td>$pre_requisite</td>
                                                            <td>$semester</td>
                                                            <td>$course_level</td>
                                                        </tr>
                                                    ";
                                                }

                                            }
                                        ?>
                                    </tbody>

                                    <script>

                                        function creditSubject(
                                            // subject_id, 
                                            student_id,
                                            subject_title, enrollment_id){


                                            console.log(subject_title)

                                            Swal.fire({
                                                icon: 'question',
                                                title: `Credit ${subject_title}`,
                                                showCancelButton: true,
                                                confirmButtonText: 'Yes',
                                                cancelButtonText: 'Cancel'

                                            }).then((result) => {

                                                if (result.isConfirmed) {

                                                    $.ajax({
                                                        url: '../ajax/subject/creditSubject.php',
                                                        type: 'POST',
                                                        data: {
                                                            // subject_id, 
                                                            student_id,
                                                            subject_title, enrollment_id
                                                        },
                                                        success: function(response) {

                                                            console.log(response)

                                                            if(response == "success_credit"){

                                                                Swal.fire({
                                                                    icon: 'success',
                                                                    title: `Successfully Credited`,
                                                                    showConfirmButton: false,
                                                                    timer: 800, // Adjust the duration of the toast message in milliseconds (e.g., 3000 = 3 seconds)
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
                                                                }).then((result) => {
                                                                    // location.reload();
                                                                    
                                                                    $('#selected_table').load(
                                                                        location.href + ' #selected_table'
                                                                    );

                                                                    $('#credit_section_table').load(
                                                                        location.href + ' #credit_section_table'
                                                                    );
                                                                });
                                                            }

                                                            if(response == "subject_exists"){
                                                                Swal.fire({
                                                                    icon: 'error',
                                                                    title: `Already Exists`,
                                                                    showConfirmButton: false,
                                                                    timer: 1200, // Adjust the duration of the toast message in milliseconds (e.g., 3000 = 3 seconds)
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
                                                        }
                                                    });
                                                }
                                            });
                                        }

                                        function unCreditSubject(
                                            // subject_id, 
                                            student_id,
                                            subject_title, enrollment_id){


                                            // console.log(subject_id)

                                            Swal.fire({
                                                icon: 'question',
                                                title: `Undo Credit ${subject_title}`,
                                                showCancelButton: true,
                                                confirmButtonText: 'Yes',
                                                cancelButtonText: 'Cancel'

                                            }).then((result) => {

                                                if (result.isConfirmed) {

                                                    $.ajax({
                                                        url: '../ajax/subject/unCreditSubject.php',
                                                        type: 'POST',
                                                        data: {
                                                            // subject_id,
                                                            student_id,
                                                            subject_title, enrollment_id
                                                        },

                                                        success: function(response) {
                                                            console.log(response);

                                                            if(response == "success_undo_credit"){
                                                            
                                                                Swal.fire({
                                                                icon: 'success',
                                                                title: `Successfully Undo the Credit Subject`,
                                                                showConfirmButton: false,
                                                                timer: 800, // Adjust the duration of the toast message in milliseconds (e.g., 3000 = 3 seconds)
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
                                                            }).then((result) => {
                                                                // location.reload();

                                                                // var tableContent = $(response).find('#credit_section_table').html();
                                                                // $('#credit_section_table').html(tableContent);

                                                                $('#selected_table').load(
                                                                    location.href + ' #selected_table'
                                                                );

                                                                $('#credit_section_table').load(
                                                                    location.href + ' #credit_section_table'
                                                                );
                                                            });

                                                            }
                                                        }
                                                    });
                                                }
                                            });
                                        }
                                    </script>
                                </table>


                                <!-- <button type="button" class="btn btn-sm btn-outline-primary" onclick="showDiv()">Add Subject</button> -->
                                <div  class="credited_subjects" 
                                    id="subjectDiv" style="display: none;">
                                    <h3 class="mb-3 text-primary text-center">Select Credit Subjects</h3>
                                    <table id="creditedSubjectsTable" class="table table-bordered table-hover "  style="font-size:15px" cellspacing="0"> 
                                        <thead>
                                            <tr class="text-center"">
                                                <th>Id</th>
                                                <th>Section</th>
                                                <th>Code</th>
                                                <th>Description</th>
                                                <th>Unit</th>
                                                <th>Pre-Requisite</th>
                                                <th>Type</th>
                                                <th>
                                                    Action
                                                </th>
                                            </tr>
                                        </thead>

                                        <!-- <tbody>
                                            <?php 
                                            
                                                $sql = $con->prepare("SELECT 
                                                    t1.program_section,
                                                    t2.* 
                                                    
                                                    FROM course as t1

                                                    INNER JOIN subject as t2 ON t2.course_id = t1.course_id

                                                    WHERE t1.course_id !=:course_id
                                                    AND t1.active='yes'
                                                    AND t1.course_level=:course_level
                                                    AND t2.semester=:semester
                                                    ");

                                                $sql->bindValue(":course_id", $selected_course_id);
                                                $sql->bindValue(":course_level", $student_course_level);
                                                $sql->bindValue(":semester", $current_school_year_period);
                                                $sql->execute();

                                                if($sql->rowCount() > 0){

                                                    while($row = $sql->fetch(PDO::FETCH_ASSOC)){

                                                        $subject_id = $row['subject_id'];
                                                        $subject_code = $row['subject_code'];
                                                        $subject_title = $row['subject_title'];
                                                        $pre_requisite = $row['pre_requisite'];
                                                        $subject_type = $row['subject_type'];
                                                        $unit = $row['unit'];
                                                        $course_level = $row['course_level'];
                                                        $program_section = $row['program_section'];
                                                        
                                                        $student_student_subject_id = 0;

                                                        $get_student_subject = $studentSubject->
                                                            GetNonFinalizedStudentSubject($student_id, $subject_id,
                                                            $enrollment_id, $current_school_year_id);

                                                        
                                                        if(count($get_student_subject) > 0){
                                                            $student_student_subject_id = $get_student_subject['subject_id'];
                                                            // echo $student_student_subject_id;
                                                        }



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
                                                                    <input name='normal_selected_subject[]' 
                                                                        value='" . $subject_id . "'
                                                                        type='checkbox'" . ($student_student_subject_id == $subject_id ? " checked" : "") . ">
                                                                </td>
                                                            </tr>
                                                        ";
                                                    }

                                                }
                                            ?>
                                        </tbody> -->
                                    </table>
                                </div>
                        </form>
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
                                    AND t1.is_transferee='no'
                                    ");

                                $transfereeSubjects->bindValue(":student_id", $student_id);
                                $transfereeSubjects->bindValue(":school_year_id", $current_school_year_id);
                                $transfereeSubjects->bindValue(":course_id", $selected_course_id);
                                $transfereeSubjects->execute();

                                    $totalUnits = 0;

                                    if($transfereeSubjects != null){
                                        $applyTabIsAvailable = true;

                                        foreach ($transfereeSubjects as $key => $row) {

                                            $subject_course_id = $row['course_id'];

                                            $unit = $row['unit'];
                                            $subject_id = $row['subject_id'];

                                            if($subject_course_id == $student_course_id){
                                                array_push($my_course_subjects, $subject_id);
                                            }

                                            $subject_title = $row['subject_title'];
                                            $course_id = $row['course_id'];
                                            $subject_code = $row['subject_code'];
                                            $subject_type = $row['subject_type'];
                                            $semester = $row['semester'];
                                            $course_level = $row['course_level'];
                                            $is_transferee = $row['is_transferee'];

                                            // $is_final = $row['is_final'];

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
                                        
                                            // <button type='submit' name='change_status' class='btn btn-outline-success'>Change</button>
                                            echo "
                                                <tr class='text-center'>
                                                    <td>$subject_id</td>
                                                    <td>$subject_code</td>
                                                    <td>$subject_title</td>
                                                    <td>$unit</td>
                                                    <td>$subject_type</td>
                                                    <td>$course_level</td>
                                                    <td>$semester</td>
                                                    <td>
                                                        <form method='POST'>
                                                            <input type='hidden' name='is_transferee' value='$is_transferee'>
                                                            <input type='hidden' name='student_id' value='$student_id'>
                                                            <input type='hidden' name='subject_id' value='$subject_id'>
                                                            <input type='hidden' name='student_subject_id' value='$student_subject_id'>
                                                            <input type='hidden' name='subject_title' value='$subject_title'>
                                                            <input type='hidden' name='course_id' value='$course_id'>
                                                            
                                                            <button 
                                                                type='submit'
                                                                name='remove_status' class='btn btn-danger'
                                                                onclick=\"return confirm('Are you sure you want to remove?');\"
                                                                >
                                                                <i class='fas fa-times-circle'></i>
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

                                $doesHaveRemovedSubjects = "Irregular";

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

                                    $sql->bindValue(":course_id", $student_course_id);
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

                                                    if($subjectIds == null){
                                                        $subjectIds = 0;
                                                    }

                                                    $sql = $con->prepare("SELECT * FROM 
                                                    
                                                        subject as t1

                                                        WHERE t1.subject_id NOT IN ($subjectIds)
                                                        -- WHERE t1.student_id=:student_id
                                                        AND t1.course_id=:course_id
                                                        AND t1.course_level=:course_level
                                                        AND t1.semester=:semester

                                                        ");

                                                    $sql->bindValue(":course_id", $student_course_id);
                                                    $sql->bindValue(":course_level", $student_course_level);
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

                                                    // $addedSubjects = $subject->GetTransfereeAddedSubject($student_id,
                                                    //     $current_school_year_id, $selected_course_id);

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
                                                                                name='remove_added_btn' class='btn btn-danger'
                                                                                onclick=\"return confirm('Are you sure you want to remove?');\"
                                                                                ><i class='fas fa-times-circle'></i>
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

                        <button type="button" onclick="confirmUpdate(<?php echo $pending_enrollees_id; ?>,
                            <?php echo $student_id; ?>, <?php echo $enrollment_id; ?>, '<?php echo $doesHaveRemovedSubjects; ?>')" 
                            name=""
                            class="btn btn btn-success">
                            Mark as Final
                        </button>
                        <a href="../admission/transferee_process_enrollment.php?step3=true&id=<?php echo $pending_enrollees_id;?>&selected_course_id=<?php echo $selected_course_id?>">
                            <button class="btn btn-sm btn-primary">Go back</button>
                        </a>
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

 
 if(isset($_GET['final'])
    && $_GET['final'] == "true" 
    && isset($_GET['e_id'])
 ){


    $section = new Section($con, null);

    $enrollment_id = $_GET['e_id'];

    $enrollmentSection = $section->GetEnrollmentSectionName($enrollment_id);
    $enrollmentCourseId = $section->GetEnrollmentSectionId($enrollment_id);
    $enrollmentStudentId = $section->GetEnrollmentStudentId($enrollment_id);
    $subject = new Subject($con, $registrarLoggedIn, null);

    ?>
        <div class="col-md-12 row">
    

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
                                    <td>N/A</td>
                                    <td>Evaluation</td>

                                    <td><?php
                                        $date = new DateTime($date_creation);
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
                            </tr>
                            <tr>
                                <td style="color: #FFFF;" class="checkDetails" id="process-1">Check details</td>
                                <td></td>
                                <td style="color: #FFFF;" class="findSection" id="process-2">Find section</td>
                                <td></td>
                                <td style="color: #FFFF;" class="subConfirm" id="process-3">Subject Confirmation</td>
                                
                            </tr>
                        </table>
                    </div>

            <div class="card">
                <div class="card-header"><?php echo $enrollmentSection;?> Subjects</div>
                <div class="card-body">
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

                            $transfereeSubjects->bindValue(":student_id", $enrollmentStudentId);
                            $transfereeSubjects->bindValue(":school_year_id", $current_school_year_id);
                            $transfereeSubjects->bindValue(":course_id", $enrollmentCourseId);
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
                                            </tr>
                                        ";
                                    }
                                } 
                            ?>
                            
                        </tbody>
                    </table>
                                    
                        


                        <h3 class="mb-3 text-muted">Added Subjects</h3>
                        <?php 
                            if(count($subject->GetNewTransfereeAddedSubject($enrollmentStudentId,
                                $current_school_year_id, $enrollmentCourseId)) > 0){
                                    
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

                                                    // $addedSubjects = $subject->GetTransfereeAddedSubject($student_id,
                                                    //     $current_school_year_id, $selected_course_id);

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

                                                    $addedSubjects->bindValue(":student_id", $enrollmentStudentId);
                                                    $addedSubjects->bindValue(":school_year_id", $current_school_year_id);
                                                    $addedSubjects->bindValue(":course_id", $enrollmentCourseId);
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
                                                                                name='remove_added_btn' class='btn btn-danger'
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
                </div>
            </div>
        </div>
    <?php
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

    function confirmUpdate(pending_enrollees_id, student_id,
        enrollment_id, doesHaveRemovedSubjects) {

            // console.log(doesHaveRemovedSubjects);


        
        Swal.fire({
            icon: 'question',
            title: 'Are you sure',
            showCancelButton: true,
            confirmButtonText: 'Yes',
            cancelButtonText: 'No'
        }).then((result) => {

            if (result.isConfirmed) {

                // $('#remove_credited_btn').click();
                $.ajax({
                    url: '../ajax/pending/update_pending.php',
                    type: 'POST',
                    data: {
                       pending_enrollees_id, student_id, enrollment_id,
                       doesHaveRemovedSubjects
                    },
                    success: function(response) {


                        if(response == "success"){

                            // window.location.href = '../admission/index.php';
                            window.location.href = '../student/transferee_insertion.php?enrolled_subjects=true&id=' + student_id;
                            // window.location.href = 'view_student_transferee_enrollment_review.php?final=true&e_id=' + enrollment_id;
                        }

                        // console.log(response)
                    },
                    error: function(xhr, status, error) {
                        // Handle any errors here
                        console.log(error);
                    }
                });
            } else {
                // User clicked "No," perform alternative action or do nothing
            }
        });
    }
</script>