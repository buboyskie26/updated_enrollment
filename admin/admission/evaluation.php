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

    $enrollment = new Enrollment($con, null);
    $pendingEnrollment = $enrollment->PendingEnrollment();
    $ongoingEnrollment = $enrollment->OngoingEnrollment();

    echo "<div class='col-md-12 row'>";

        if(count($pendingEnrollment) > 0){
            ?>
                <div class="row col-md-12">

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
                                        $acronym = $row['acronym'];
                                        // $pending_enrollees_id = $row['pending_enrollees_id'];
                                        $student_unique_id = "N/A";

                                        $type = "Ongoing Transferee (SHS)";
                                        $url = "";
                                        $status = "Evaluation";
                                        $button_output = "";

                                        // $process_url = "process_enrollment.php?step1=true&id=$pending_enrollees_id";
                                        $process_url = "";
                                        $trans_url = "transferee_process_enrollment.php?step3=true&st_id=$student_id&selected_course_id=$student_course_id";
                                        

                                        $evaluateBtn = "
                                            <a href='$trans_url'>
                                                <button class='btn btn-outline-success btn-sm'>
                                                    Evaluate
                                                </button>
                                            </a>
                                        ";
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
                    <h3 class='text-info text-center'>No Data found For Ongoing Enrollees.</h3>
                </div>
            ";
        }
    echo "</div>";

?>