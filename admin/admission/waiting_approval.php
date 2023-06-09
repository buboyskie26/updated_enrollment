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
    
    $waitingApprovalEnrollment = $enrollment->WaitingApprovalEnrollment($current_school_year_id);


    $enrollment = new Enrollment($con, null);
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
                <button class="btn btn  btn-outline-primary">Waiting Payment <span class="text-white">
                    (<?php echo $waitingPaymentEnrollmentCount;?>)</span></button>

                </a>
            </div>
            <div class="col-md-3">
                <a href="waiting_approval.php">
                    <button class="btn btn  btn-primary">Waiting Approval <span class="text-white">(<?php echo $waitingApprovalEnrollmentCount;?>)</span></button>
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
        
        if(count($waitingApprovalEnrollment) > 0){
        ?>
            <div class="row col-md-12">
                <h3 class="mb-2 text-center text-success">Waiting Enrollment Approval</h3>
                <div class="table-responsive">			
                    <table id="dash-table" class="table table-striped table-bordered table-hover table-responsive" style="font-size:12px" cellspacing="0">
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

                                foreach ($waitingApprovalEnrollment as $key => $row) {

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
                                    $student_status = $row['student_status'];
                                    $new_enrollee = $row['new_enrollee'];
                                    $is_tertiary = $row['is_tertiary'];
                                    $is_transferee = $row['is_transferee'];
                                    
                                    $admission_status = $row['admission_status'];
                                    $student_statusv2 = $row['student_statusv2'];


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

                                    // $regular_insertion_url = "../enrollees/subject_insertion.php?username=$username&id=$student_id";
                                    $regular_insertion_url = "../enrollees/subject_insertion.php?enrolled_subjects=true&id=$student_id";

                                    
                                    $confirmButton  = "
                                            <button onclick='confirmValidation(" . $course_id . ", " . $enrollement_student_id . ")' name='confirm_validation_btn' class='btn btn-primary btn-sm'>Confirm</button>
                                    ";

                                    $evaluateBtn = "";

                                    $student_type_status = "";
                                    
                                    if($cashier_evaluated == "yes"
                                        && $registrar_evaluated == "yes"){

                                        if($student_status == "Regular"){
                                            $evaluateBtn = "
                                                <a href='$regular_insertion_url'>
                                                    <button class='btn btn-success btn-sm'>
                                                        Evaluate
                                                    </button>
                                                </a>
                                            ";

                                            if($new_enrollee == 1 && $is_tertiary == 0){
                                                $student_type_status = "New Regular (SHS)";

                                            }else if($new_enrollee == 0 && $is_tertiary == 0){
                                                $student_type_status = "On Going (SHS)";

                                            }
                                            else if($new_enrollee == 0 && $is_tertiary == 1){
                                                $student_type_status = "On Going (Tertiary)";

                                            }
                                        }

                                        # if Transferee
                                        if($student_status == "Transferee"){

                                                // if($new_enrollee == 0 || $new_enrollee == 1){
                                            // if($new_enrollee == 1 && $is_tertiary == 0 && $is_transferee == 1){

                                            //     $student_type_status = "New Transferee (SHS)";

                                            //     $evaluateBtn = "
                                            //         <a href='$transferee_insertion_url'>
                                            //             <button class='btn btn-outline-success btn-sm'>
                                            //                 Evaluate
                                            //             </button>
                                            //         </a>
                                            //     ";

                                            // }
                                            if($admission_status == "Standard" 
                                                && $student_statusv2 == "Regular"){

                                                $student_type_status = "Standard Regular";

                                                $evaluateBtn = "
                                                    <a href='$transferee_insertion_url'>
                                                        <button class='btn btn-outline-success btn-sm'>
                                                            Evaluate
                                                        </button>
                                                    </a>
                                                ";

                                            }
                                            else if($admission_status == "Transferee" 
                                                && $student_statusv2 == "Regular"){

                                                // $student_type_status = "On Going Transferee (SHS)";
                                                
                                                $student_type_status = "O.S $admission_status (SHS $student_statusv2)";

                                               
                                                # PREVIOUS URL
                                                $trans_url = "transferee_process_enrollment.php?step3=true&st_id=$student_id&selected_course_id=$course_id";
                                                $regular_insertion_url = "../enrollees/subject_insertion.php?username=$username&id=$student_id";

                                                $evaluateBtn = "
                                                    <a href='$regular_insertion_url'>
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
        <?php
    }else{
        echo "
            <h3 class='text-info text-center'>No Waiting Approval found.</h3>
        ";
    }

        ?>

    </div>
