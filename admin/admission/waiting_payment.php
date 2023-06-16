<?php  
 
    include('../../enrollment/classes/StudentEnroll.php');
    include('../../enrollment/classes/Enrollment.php');

    include('../registrar_enrollment_header.php');

    // require_once __DIR__ . '/../../vendor/autoload.php';
    // use Dompdf\Dompdf;
    // use Dompdf\Options;

    if(!AdminUser::IsRegistrarAuthenticated()){

        header("Location: /dcbt/adminLogin.php");
        exit();
    }

    $enroll = new StudentEnroll($con);

    $school_year_obj = $enroll->GetActiveSchoolYearAndSemester();

    $current_school_year_id = $school_year_obj['school_year_id'];
    $current_school_year_term = $school_year_obj['term'];
    $current_school_year_period = $school_year_obj['period'];

    $enrollment = new Enrollment($con, $enroll);
    
    $waitingPaymentEnrollment = $enrollment->WaitingPaymentEnrollment($current_school_year_id);


    $pendingEnrollment = $enrollment->PendingEnrollment();
    $waitingPaymentEnrollment = $enrollment->WaitingPaymentEnrollment($current_school_year_id);
    $waitingApprovalEnrollment = $enrollment->WaitingApprovalEnrollment($current_school_year_id);
    $enrolledStudentsEnrollment = $enrollment->EnrolledStudentsWithinSYSemester($current_school_year_id);


    $pendingEnrollmentCount = count($pendingEnrollment);
    $waitingPaymentEnrollmentCount = count($waitingPaymentEnrollment);
    $waitingApprovalEnrollmentCount = count($waitingApprovalEnrollment);
    $enrolledStudentsEnrollmentCount = count($enrolledStudentsEnrollment);
    
?>

    <div class="row col-md-12">

        <div class="row col-md-12">
            <div class="col-md-3">
                <a href="evaluation.php">
                    <button class="btn btn btn-outline-primary">Evaluation 
                        <span class="text-white">(<?php echo $pendingEnrollmentCount;?>)</span></button>
                    
                </a>
            </div>
            <div class="col-md-3">
                <a href="waiting_payment.php">
                <button class="btn btn  btn-primary">Waiting Payment <span class="text-white">
                    (<?php echo $waitingPaymentEnrollmentCount;?>)</span></button>

                </a>
            </div>
            <div class="col-md-3">
                <a href="waiting_approval.php">
                    <button class="btn btn  btn-outline-primary">Waiting Approval <span class="text-white">(<?php echo $waitingApprovalEnrollmentCount;?>)</span></button>
                </a>
            </div>
            <div class="col-md-3">
                <a href="enrolled.php">
                   <button class="btn btn  btn-outline-primary">Enrolled <span class="text-white">(<?php echo $enrolledStudentsEnrollmentCount;?>)</span></button>
                </a>
            </div>
            <hr>
            <hr>
            <hr>

        </div>

        <?php 
            if(count($waitingPaymentEnrollment) > 0){
                ?>
                    <div class="card">
                        <div class="card-header">
                            <h4 class="text-muted text-center">Waiting Payment List</h4>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">			
                                <table id="dash-table" 
                                    class="table table-striped table-bordered table-hover table-responsive"
                                    style="font-size:14px" cellspacing="0">
                                    <thead>
                                        <tr class="text-center">
                                            <th>Id</th>
                                            <th>Name</th>
                                            <th>Standing</th>
                                            <th>Program-Section</th>
                                            <th>Type</th>
                                            <th style="width: 150px;;" class="text-center">Action</th>
                                        </tr>	
                                    </thead> 
                                    <tbody>
                                        <?php
                                            // Generate a random alphanumeric string as the enrollment form ID

                                            $waitingPaymentEnrollment = $enrollment->WaitingPaymentEnrollment($current_school_year_id);
                                        
                                            $transResult = "";
                                            $createUrl = "";

                                            foreach ($waitingPaymentEnrollment as $key => $row) {

                                                    $enrollement_student_id = $row['student_id'];
                                                    $fullname = $row['firstname'] . " " . $row['lastname'];
                                                    $standing = $row['course_level'];
                                                    $course_id = $row['course_id'];
                                                    $username = $row['username'];
                                                    $student_id = $row['t2_student_id'];
                                                    $program_section = $row['program_section'];
                                                    $cashier_evaluated = $row['cashier_evaluated'];
                                                    $registrar_evaluated = $row['registrar_evaluated'];
                                                    $course_level = $row['course_level'];

                                                    $student_status = $row['student_statusv2'];
                                                    $admission_status = $row['admission_status'];

                                                    

                                                    $new_enrollee = $row['new_enrollee'];
                                                    $is_tertiary = $row['is_tertiary'];
                                                    // $is_transferee = $row['is_transferee'];

                                                    // $program_section_default = "";
                                                    if($program_section === ""){
                                                        $program_section = "NO SECTION";
                                                    }

                                                    // $course_level_default = "";
                                                    if($course_level == ""){
                                                        $course_level = "NO SECTION";
                                                    }else{
                                                        $course_level = "Grade $course_level";
                                                    }
                                                
                                                    $createUrl = "http://localhost/dcbt/admin/student/edit.php?id=$student_id";

                                                    // $transferee_insertion_url = "http://localhost/dcbt/admin/student/transferee_insertion.php?id=$student_id";
                                                    $transferee_insertion_url = "../student/transferee_insertion.php?enrolled_subjects=true&id=$student_id";

                                                    // $regular_insertion_url = "http://localhost/dcbt/admin/student/transferee_insertion.php?id=$student_id";

                                                    $regular_insertion_url = "../enrollees/subject_insertion.php?username=$username&id=$student_id";

                                                    $confirmButton  = "
                                                        <button onclick='confirmValidation(" . $course_id . ", " . $enrollement_student_id . ")' name='confirm_validation_btn' class='btn btn-primary btn-sm'>Confirm</button>
                                                    ";

                                                    $evaluateBtn = "";
                    
                                                    $student_type_status = "";

                                                    if($cashier_evaluated == "no"
                                                        && $registrar_evaluated == "yes"){

                                                        if($admission_status == "Standard"){

                                                            $evaluateBtn = "
                                                                <a href='$regular_insertion_url'>
                                                                    <button class='btn btn-outline-success btn-sm'>
                                                                        Check
                                                                    </button>
                                                                </a>
                                                            ";

                                                            if($new_enrollee == 1 && $is_tertiary == 0){
                                                                $student_type_status = "New Regular (SHS)";

                                                            }else if($new_enrollee == 0 && $is_tertiary == 0){
                                                                $student_type_status = "On Going Standard (SHS)";
                                                            }
                                                            else if($new_enrollee == 0 && $is_tertiary == 1){
                                                                $student_type_status = "On Going Standard (Tertiary)";
                                                            }
                                                        }

                                                        # if Transferee
                                                        if($admission_status == "Transferee"){

                                                            // echo $admission_status;

                                                                // if($new_enrollee == 0 || $new_enrollee == 1){
                                                            if($new_enrollee == 1 && $is_tertiary == 0 
                                                                ){

                                                                $student_type_status = "New Transferee (SHS)";

                                                                $evaluateBtn = "
                                                                    <a href='$transferee_insertion_url'>
                                                                        <button class='btn btn-outline-success btn-sm'>
                                                                            Evaluate
                                                                        </button>
                                                                    </a>
                                                                ";

                                                            }else if($new_enrollee == 0 && $is_tertiary == 0 
                                                                ){

                                                                $student_type_status = "On Going Transferee (SHS)";

                                                                // $evaluateBtn = "
                                                                //     <a href='cashier_process_enrollment.php?id=$student_id'>

                                                                //         <button class='btn btn-outline-primary btn-sm'>
                                                                //             Evaluate
                                                                //         </button>
                                                                //     </a>
                                                                // ";

                                                                // $asd = $course_id;

                                                                // $trans_url = "transferee_process_enrollment.php?step3=true&id=$student_id&selected_course_id=$course_id";

                                                                # PREVIOUS URL
                                                                $trans_url = "transferee_process_enrollment.php?step3=true&st_id=$student_id&selected_course_id=$course_id";

                                                                $evaluateBtn = "
                                                                    <a href='$transferee_insertion_url'>
                                                                        <button class='btn btn-outline-success btn-sm'>
                                                                            Evaluate
                                                                        </button>
                                                                    </a>
                                                                ";
                                                            }
                                                            else if($new_enrollee == 0 && $is_tertiary == 1
                                                                ){
                                                                $student_type_status = "On Going Transferee (Tertiary)";

                                                                $evaluateBtn = "
                                                                    <a href='$transferee_insertion_url'>
                                                                        <button class='btn btn-outline-success btn-sm'>
                                                                            Evaluate
                                                                        </button>
                                                                    </a>
                                                                ";
                                                            }
                                                        }
                                                    }


                                                    echo "
                                                        <tr class='text-center'>
                                                            <td>$student_id</td>
                                                            <td>$fullname</td>
                                                            <td>$course_level </td>
                                                            <td>$program_section</td>
                                                            <td>$student_type_status</td>
                                                            
                                                            <td>
                                                                $evaluateBtn
                                                            </td>
                                                        </tr>
                                                    ";
                                            }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php
            }else{
                echo "
                    <h3 class='text-info text-center'>No Waiting Payment found.</h3>
                ";
            }
        ?>
    </div>
