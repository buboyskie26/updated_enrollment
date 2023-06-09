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

    if (isset($_SESSION['enrollment_form_id'])) {
        unset($_SESSION['enrollment_form_id']);
    }

    $enrollment = new Enrollment($con, null);
    $pendingEnrollment = $enrollment->PendingEnrollment();
    $waitingPaymentEnrollment = $enrollment->WaitingPaymentEnrollment($current_school_year_id);
    $waitingApprovalEnrollment = $enrollment->WaitingApprovalEnrollment($current_school_year_id);


    $pendingEnrollmentCount = count($pendingEnrollment);
    $waitingPaymentEnrollmentCount = count($waitingPaymentEnrollment);
    $waitingApprovalEnrollmentCount = count($waitingApprovalEnrollment);

    
?>

<div class="row col-md-12">

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
                <button class="btn btn  btn-outline-primary">Enrolled</button>

                </a>
            </div>
            <hr>
            <hr>
            <hr>

        </div>

        <h3 class="mb-2 text-center text-primary">Non-Evaluated</h3>

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

    


</div>
