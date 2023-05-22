<?php  
 
    include('../../enrollment/classes/StudentEnroll.php');

    include('../registrar_enrollment_header.php');

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
?>

<div class="row col-md-12">

     <h3 class="mb-2 text-center text-primary">Non-Evaluated</h3>
     <table id="courseTable" class="table table-striped table-bordered table-hover "  style="font-size:13px" cellspacing="0"  > 
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
                ");
                $sql->execute();

                if($sql->rowCount() > 0){

                    // echo "we";
                    while($row = $sql->fetch(PDO::FETCH_ASSOC)){

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


    <h3 class="mb-2 text-center text-success">Evaluated</h3>
        <div class="table-responsive">			
            <table id="dash-table" class="table table-striped table-bordered table-hover table-responsive" style="font-size:12px" cellspacing="0">
                <thead>
                    <tr class="text-center">
                        <th>Id</th>
                        <th>Name</th>
                        <th>Standing</th>
                        <th>Course/Section</th>
                        <th>Type</th>
                        <th style="width: 150px;;" class="text-center">Action</th>
                    </tr>	
                </thead> 
                <tbody>
                    <?php

                        // Generate a random alphanumeric string as the enrollment form ID

                        $default_shs_course_level = 11;
                        $is_new_enrollee = 1;
                        $is_transferee = 1;
                        $regular_Status = "Regular";
                        $enrollment_status = "tentative";
                        $registrar_evaluated = "yes";

                        $registrar_side = $con->prepare("SELECT 

                            t1.student_id, t1.cashier_evaluated,t1.registrar_evaluated,
                            t1.is_transferee,

                            t2.firstname,t2.username,
                            t2.lastname,t2.course_level,
                            t2.course_id, t2.student_id as t2_student_id,
                            t2.course_id, t2.course_level,t2.student_status,
                            t2.is_tertiary, t2.new_enrollee,
                            
                            t3.program_section

                            FROM enrollment as t1

                            INNER JOIN student as t2 ON t2.student_id = t1.student_id
                            LEFT JOIN course as t3 ON t2.course_id = t3.course_id

                            WHERE (t1.is_new_enrollee=:is_new_enrollee
                            OR t1.is_new_enrollee=:is_new_enrollee2)
                            -- AND t1.is_transferee=:is_transferee

                             AND (t1.is_transferee = :is_transferee OR t1.is_transferee = :is_transferee2)
                             
                            AND t1.enrollment_status=:enrollment_status
                            AND t1.school_year_id=:school_year_id
                            AND t1.registrar_evaluated=:registrar_evaluated
                            AND t1.cashier_evaluated=:cashier_evaluated
                            ");

                        $registrar_side->bindValue(":is_new_enrollee", $is_new_enrollee);
                        $registrar_side->bindValue(":is_new_enrollee2", 0);
                        $registrar_side->bindValue(":is_transferee", $is_transferee);
                        $registrar_side->bindValue(":is_transferee2", "0");
                        $registrar_side->bindValue(":enrollment_status", $enrollment_status);
                        $registrar_side->bindValue(":school_year_id", $current_school_year_id);
                        $registrar_side->bindValue(":registrar_evaluated", $registrar_evaluated);
                        $registrar_side->bindValue(":cashier_evaluated", "yes");
                        $registrar_side->execute();
                    
                        if($registrar_side->rowCount() > 0){
                            $transResult = "";
                            $createUrl = "";

                            while($row = $registrar_side->fetch(PDO::FETCH_ASSOC)){

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

                                $transferee_insertion_url = "http://localhost/dcbt/admin/student/transferee_insertion.php?id=$student_id";

                                // $regular_insertion_url = "http://localhost/dcbt/admin/student/transferee_insertion.php?id=$student_id";

                                $regular_insertion_url = "../enrollees/subject_insertion.php?username=$username&id=$student_id";

                                $confirmButton  = "
                                     <button onclick='confirmValidation(" . $course_id . ", " . $enrollement_student_id . ")' name='confirm_validation_btn' class='btn btn-primary btn-sm'>Confirm</button>
                                ";

                                $evaluateBtn = "";

                                // # SHS
                                // if($cashier_evaluated == "yes"
                                //     && $registrar_evaluated == "yes"
                                //     && $student_status == "Transferee"
                                //     ){
                                //     $evaluateBtn = "
                                //         <a href='$transferee_insertion_url'>
                                //             <button class='btn btn-outline-success btn-sm'>
                                //                 Evaluate
                                //             </button>
                                //         </a>
                                //     ";
                                // }else if($cashier_evaluated == "yes"
                                //     && $registrar_evaluated == "yes"
                                //     && $student_status == "Regular"
                                //     ){
                                //     $evaluateBtn = "
                                //         <a href='$regular_insertion_url'>
                                //             <button class='btn btn-success btn-sm'>
                                //                 Evaluate
                                //             </button>
                                //         </a>
                                //     ";
                                // }
                                
                                // if($cashier_evaluated == "no"
                                //     && $registrar_evaluated == "yes"
                                // ){
                                //     $evaluateBtn = "
                                //         <button class='btn btn-secondary btn-sm'>
                                //             Wait for Cashier
                                //         </button>
                                //     ";
                                // }

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
                                    }

                                    # if Transferee
                                    if($student_status == "Transferee"){

                                            // if($new_enrollee == 0 || $new_enrollee == 1){
                                        if($new_enrollee == 1 && $is_tertiary == 0 && $is_transferee == 1){
                                            $student_type_status = "New Transferee (SHS)";

                                            $evaluateBtn = "
                                                <a href='$transferee_insertion_url'>
                                                    <button class='btn btn-outline-success btn-sm'>
                                                        Evaluate
                                                    </button>
                                                </a>
                                            ";

                                        }else if($new_enrollee == 0 && $is_tertiary == 0 && $is_transferee == 0){

                                            $student_type_status = "On Going Transferee (SHS)";

                                            // $evaluateBtn = "
                                            //     <a href='cashier_process_enrollment.php?id=$student_id'>

                                            //         <button class='btn btn-outline-primary btn-sm'>
                                            //             Evaluate
                                            //         </button>
                                            //     </a>
                                            // ";

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
                        }
                    ?>
                </tbody>
            </table>
        </div>

</div>
