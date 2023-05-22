<?php  
 
    include('../../enrollment/classes/StudentEnroll.php');

    include('../cashier_enrollment_header.php');

    if(!AdminUser::IsCashierAuthenticated()){

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

    <h3 class="mb-2 text-center text-success">List of Evaluated</h3>
        <div class="table-responsive">			
            <table id="dash-table" class="table table-striped table-bordered table-hover table-responsive" style="font-size:12px" cellspacing="0">
                <thead>
                    <tr class="text-center">
                        <th>Id</th>
                        <th>Name</th>
                        <th>Standing</th>
                        <th>Course/Section</th>
                        <th>Type</th>
                        <th class="text-center" width="15%" >Action</th>
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

                        $query_cashier_side = $con->prepare("SELECT 

                            t1.student_id, t1.cashier_evaluated,t1.registrar_evaluated,
                            t1.is_transferee,

                            t2.firstname,t2.username,t2.is_tertiary,
                            t2.lastname, t2.course_level, t2.new_enrollee,
                            t2.course_id, t2.student_id as t2_student_id,
                            t2.course_id, t2.course_level,t2.student_status,
                            
                            t3.program_section

                            FROM enrollment as t1

                            INNER JOIN student as t2 ON t2.student_id = t1.student_id
                            LEFT JOIN course as t3 ON t2.course_id = t3.course_id

                            WHERE (t1.is_new_enrollee = :is_new_enrollee 
                            OR t1.is_new_enrollee = :is_new_enrollee2)

                            AND (t1.is_transferee = :is_transferee OR t1.is_transferee = :is_transferee2)
                            AND t1.enrollment_status=:enrollment_status
                            AND t1.school_year_id=:school_year_id
                            AND t1.registrar_evaluated=:registrar_evaluated
                            AND t1.cashier_evaluated=:cashier_evaluated
                            ");

                        $query_cashier_side->bindValue(":is_new_enrollee", $is_new_enrollee);
                        $query_cashier_side->bindValue(":is_new_enrollee2", 0);
                        $query_cashier_side->bindValue(":is_transferee", $is_transferee);
                        $query_cashier_side->bindValue(":is_transferee2", "0");
                        $query_cashier_side->bindValue(":enrollment_status", $enrollment_status);
                        $query_cashier_side->bindValue(":school_year_id", $current_school_year_id);
                        $query_cashier_side->bindValue(":registrar_evaluated", $registrar_evaluated);
                        $query_cashier_side->bindValue(":cashier_evaluated", "no");
                        $query_cashier_side->execute();
                    
                        if($query_cashier_side->rowCount() > 0){
                            $transResult = "";
                            $createUrl = "";

                            while($row = $query_cashier_side->fetch(PDO::FETCH_ASSOC)){

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

                                $student_type_status = "";
                                
                                if($cashier_evaluated == "no" && 
                                    $registrar_evaluated == "yes"){

                                    # If Regular
                                    if($student_status == "Regular"){
                                        $evaluateBtn = "

                                            <a href='cashier_process_enrollment.php?id=$student_id'>
                                                <button class='btn btn-primary btn-sm'>
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
                                                <a href='cashier_transferee_process_enrollment.php?id=$student_id'>

                                                    <button class='btn btn-outline-primary btn-sm'>
                                                        Evaluate
                                                    </button>
                                                </a>
                                            ";
                                        }else if($new_enrollee == 0 && $is_tertiary == 0
                                                && $is_transferee == 0){
                                            $student_type_status = "On Going Transferee (SHS)";

                                            $evaluateBtn = "
                                                <a href='cashier_process_enrollment.php?id=$student_id'>

                                                    <button class='btn btn-outline-primary btn-sm'>
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


