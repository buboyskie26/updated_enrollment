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

    $enrollment = new Enrollment($con, null);
    $pendingEnrollment = $enrollment->PendingEnrollment();
    $ongoingEnrollment = $enrollment->OngoingEnrollment();

    $pendingEnrollment = $enrollment->PendingEnrollment();
    $waitingPaymentEnrollment = $enrollment->WaitingPaymentEnrollment($current_school_year_id);
    $waitingApprovalEnrollment = $enrollment->WaitingApprovalEnrollment($current_school_year_id);
    $enrolledStudentsEnrollment = $enrollment->EnrolledStudentsWithinSYSemester($current_school_year_id);

    $pendingEnrollmentCount = count($pendingEnrollment);
    $waitingPaymentEnrollmentCount = count($waitingPaymentEnrollment);
    $waitingApprovalEnrollmentCount = count($waitingApprovalEnrollment);
    $enrolledStudentsEnrollmentCount = count($enrolledStudentsEnrollment);

    if(isset( $_SESSION['enrollment_form_id'])){
        unset($_SESSION['enrollment_form_id']);
    }
?>

    <div class="row col-md-12">

        <div class="row">
            <div class="col-md-3">
                <a href="evaluation.php">
                    <button class="btn btn btn-primary">Evaluation 
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
                    <button class="btn btn  btn-outline-primary">Waiting Approval <span class="text-white">(<?php echo $waitingApprovalEnrollmentCount;?>)</span></button>
                </a>
            </div>
            
            <div class="col-md-3">
                <a href="enrolled.php">
                <button class="btn btn  btn-outline-primary">Enrolled <span class="text-white">(<?php echo $enrolledStudentsEnrollmentCount;?>)</span></button>
                </a>
            </div>

            <hr>

        </div>
    
        <?php
            if(count($pendingEnrollment) > 0){
                ?>
                    <div class="row col-md-12">
                        
                        <h3 class="mb-2 text-center text-primary">Pending Enrollees</h3>

                        <table id="admission_evaluation" 
                            class="table table-striped table-bordered table-hover "
                            style="font-size:13px" cellspacing="0"  > 
                            <thead>
                                <tr class="text-center"> 
                                    <th rowspan="2">Name</th>
                                    <th rowspan="2">Type</th>
                                    <th rowspan="2">Strand</th>
                                    <th rowspan="2">Date Submitted</th>
                                    <th rowspan="2">Status</th>
                                    <th rowspan="2">Action</th>
                                </tr>	
                            </thead> 	

                            <tbody>
                                <?php 
                                    $sql = $con->prepare("SELECT t1.*, t2.acronym 
                                        FROM pending_enrollees as t1

                                        LEFT JOIN program as t2 ON t2.program_id = t1.program_id
                                        WHERE t1.student_status !='APPROVED'
                                        AND t1.is_finished = 1

                                        ");

                                    $sql->execute();


                                    if(count($pendingEnrollment) > 0){
                                        foreach ($pendingEnrollment as $key => $row) {

                                            $fullname = $row['firstname'] . " " . $row['lastname'];
                                            $date_creation = $row['date_creation'];
                                            $acronym = $row['acronym'];
                                            $pending_enrollees_id = $row['pending_enrollees_id'];
                                            $student_unique_id = "N/A";

                                            $type = "";
                                            $url = "";
                                            $status = "Evaluation";
                                            $button_output = "";
                                            $process_url = "process_enrollment.php?step1=true&id=$pending_enrollees_id";

                                            if($row['student_status'] == "Regular"){
                                                $type = "New Regular";
                                                // $url = "../enrollees/view_student_new_enrollment.php?id=$pending_enrollees_id";
                                                $button_output = "
                                                    <a href='$process_url'>
                                                        <button class='btn btn-primary btn-sm'>View</button>
                                                    </a>
                                                ";
                                                
                                            }else  if($row['student_status'] == "Transferee"){
                                                $type = "New Transferee";
                                                // $url = "../enrollees/view_student_transferee_enrollment.php?id=$pending_enrollees_id";
                                                $url_trans = "transferee_process_enrollment.php?step1=true&id=$pending_enrollees_id";

                                                $button_output = "
                                                    <a href='$url_trans'>
                                                        <button class='btn btn-outline-primary btn-sm'>View</button>
                                                    </a>
                                                ";
                                            }

                                            echo "
                                                <tr class='text-center'>
                                                    <td>$fullname</td>
                                                    <td>$type</td>
                                                    <td>$acronym</td>
                                                    <td>$date_creation</td>
                                                    <td>$status</td>
                                                    <td>
                                                        $button_output
                                                    </td>
                                                </tr>
                                            ";
                                        }
                                    }
                                ?>
                            </tbody>
                        </table>

                    </div>
                <?php
            }else{
                echo "
                    <div class='col-md-12 row'>
                        <h3 class='text-info text-center'>No Data found For Pending Enrollees.</h3>
                        <hr>
                        <hr>
                    </div>
                ";
            }

            if(count($ongoingEnrollment) > 0){
                ?>
                    <div class="row col-md-12">

                        <h3 class="mb-2 text-center text-primary">Ongoing Student</h3>

                        <table id="admission_evaluation" 
                            class="table table-striped table-bordered table-hover "
                            style="font-size:13px" cellspacing="0"  > 
                            <thead>
                                <tr class="text-center"> 
                                    <th rowspan="2">Name</th>
                                    <th rowspan="2">Type</th>
                                    <th rowspan="2">Strand</th>
                                    <th rowspan="2">Date Submitted</th>
                                    <th rowspan="2">Status</th>
                                    <th rowspan="2">Action</th>
                                </tr>	
                            </thead> 	

                            <tbody>
                                <?php 
                                    $sql = $con->prepare("SELECT t1.*, t2.acronym 
                                        FROM pending_enrollees as t1

                                        LEFT JOIN program as t2 ON t2.program_id = t1.program_id
                                        WHERE t1.student_status !='APPROVED'
                                        AND t1.is_finished = 1

                                        ");

                                    $sql->execute();
                                    if(count($ongoingEnrollment) > 0){
                                        foreach ($ongoingEnrollment as $key => $row) {

                                            $fullname = $row['firstname'] . " " . $row['lastname'];
                                            $enrollment_date = $row['enrollment_date'];
                                            $student_id = $row['student_id'];
                                            $student_course_id = $row['course_id'];

                                            $admission_status = $row['admission_status'];
                                            $student_statusv2 = $row['student_statusv2'];

                                            $username = $row['username'];

                                            $acronym = $row['acronym'];
                                            // $pending_enrollees_id = $row['pending_enrollees_id'];
                                            $student_unique_id = "N/A";

                                            $type = "O.S $admission_status (SHS $student_statusv2)";
                                            $url = "";
                                            $status = "Evaluation";
                                            $button_output = "";

                                            // $process_url = "process_enrollment.php?step1=true&id=$pending_enrollees_id";
                                            $process_url = "";
                                            $trans_url = "transferee_process_enrollment.php?step3=true&st_id=$student_id&selected_course_id=$student_course_id";

                                            $regular_insertion_url = "../enrollees/subject_insertion.php?username=$username&id=$student_id";

                                            $evaluateBtn = "";

                                            if($admission_status == "Transferee" 
                                                && $student_statusv2 == "Regular"){

                                                $evaluateBtn = "
                                                    <a href='$regular_insertion_url'>
                                                        <button class='btn btn-success btn-sm'>
                                                            Evaluate
                                                        </button>
                                                    </a>
                                                ";
                                            }else{
                                                $evaluateBtn = "
                                                    <a href='$trans_url'>
                                                        <button class='btn btn-outline-success btn-sm'>
                                                            Evaluate
                                                        </button>
                                                    </a>
                                                ";
                                            }

                                            // $evaluateBtn = "
                                            //         <a href='$trans_url'>
                                            //             <button class='btn btn-outline-success btn-sm'>
                                            //                 Evaluate
                                            //             </button>
                                            //         </a>
                                            //     ";



                                            echo "
                                                <tr class='text-center'>
                                                    <td>$fullname</td>
                                                    <td>$type</td>
                                                    <td>$acronym</td>
                                                    <td>$enrollment_date</td>
                                                    <td>$status</td>
                                                    <td>
                                                        $evaluateBtn
                                                    </td>
                                                </tr>
                                            ";
                                            }
                                    }
                                ?>
                            </tbody>
                        </table>

                    </div>
                <?php
            }else{
                echo "
                    <div class='col-md-12 row'>
                        <h3 class='text-info text-center'>No Ongoing Enrollees to be evaluated.</h3>
                    </div>
                ";
            }
        ?>

    </div>
