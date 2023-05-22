<?php 

    include('../registrar_enrollment_header.php');
    include('../../enrollment/classes/StudentEnroll.php');
    include('../../enrollment/classes/OldEnrollees.php');

    $enrol = new StudentEnroll($con);
    $old_enroll = new OldEnrollees($con, $enrol);

    $school_year_obj = $enrol->GetActiveSchoolYearAndSemester();

    $current_school_year_id = $school_year_obj['school_year_id'];
    $current_school_year_term = $school_year_obj['term'];
    $current_school_year_period = $school_year_obj['period'];

?>

<div class="row col-md-12">
 
    <div class="row col-md-12">
        <h4 class="text-center mb-4">Transferee Enrollees SHS (Registrar) (SY: <?php echo $current_school_year_term;?>-<?php echo $current_school_year_period;?> Semester)</h4>
        <div class="table-responsive">			
            <table id="dash-table" class="table table-striped table-bordered table-hover table-responsive" style="font-size:12px" cellspacing="0">
                <thead>
                    <tr class="text-center">
                        <th>Id</th>
                        <th>Name</th>
                        <th>Standing</th>
                        <th>Course/Section</th>
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
                        // $registrar_evaluated = "no";

                        $new_enrollee = $con->prepare("SELECT 

                            t1.student_id, t1.cashier_evaluated,t1.registrar_evaluated,
                            t2.firstname,
                            t2.lastname,t2.course_level,
                            t2.course_id, t2.student_id as t2_student_id,
                            t2.course_id, t2.course_level,
                            
                            t3.program_section

                            FROM enrollment as t1

                            INNER JOIN student as t2 ON t2.student_id = t1.student_id
                            LEFT  JOIN course as t3 ON t2.course_id = t3.course_id

                            WHERE t1.is_new_enrollee=:is_new_enrollee
                            AND t1.is_transferee=:is_transferee
                            AND t1.enrollment_status=:enrollment_status
                            AND t1.school_year_id=:school_year_id
                            -- AND t1.registrar_evaluated=:registrar_evaluated
                            ");

                        $new_enrollee->bindValue(":is_new_enrollee", $is_new_enrollee);
                        $new_enrollee->bindValue(":is_transferee", $is_transferee);
                        $new_enrollee->bindValue(":enrollment_status", $enrollment_status);
                        $new_enrollee->bindValue(":school_year_id", $current_school_year_id);
                        // $new_enrollee->bindValue(":registrar_evaluated", $registrar_evaluated);
                        $new_enrollee->execute();
                    
                        if($new_enrollee->rowCount() > 0){
                            $transResult = "";
                            $createUrl = "";

                            while($row = $new_enrollee->fetch(PDO::FETCH_ASSOC)){

                                $enrollement_student_id = $row['student_id'];
                                $fullname = $row['firstname'] . " " . $row['lastname'];
                                $standing = $row['course_level'];
                                $course_id = $row['course_id'];
                                $student_id = $row['t2_student_id'];
                                $program_section = $row['program_section'];
                                $cashier_evaluated = $row['cashier_evaluated'];
                                $registrar_evaluated = $row['registrar_evaluated'];
                                $course_level = $row['course_level'];

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

                                $confirmButton  = "
                                     <button onclick='confirmValidation(" . $course_id . ", " . $enrollement_student_id . ")' name='confirm_validation_btn' class='btn btn-primary btn-sm'>Confirm</button>
                                ";

                                $evaluateBtn = "";
                                if($cashier_evaluated == "yes"
                                    && $registrar_evaluated == "yes"
                                    ){
                                    $evaluateBtn = "
                                        <a href='$transferee_insertion_url'>
                                            <button class='btn btn-success btn-sm'>
                                                Evaluate
                                            </button>
                                        </a>
                                    ";
                                }
                                
                                if($cashier_evaluated == "no"
                                    && $registrar_evaluated == "yes"
                                ){
                                    $evaluateBtn = "
                                        <button class='btn btn-secondary btn-sm'>
                                            Wait for Cashier
                                        </button>
                                    ";
                                }
                                echo "
                                    <tr class='text-center'>
                                        <td>$student_id</td>
                                        <td>
                                            <a style='color: grey;' href='$createUrl'>
                                                $fullname
                                             </a>
                                        </td>
                                        <td>$course_level </td>
                                        <td>$program_section</td>
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
    

        <div class="col-md-12">
            <h4 class="text-center">Pending Transferee New Enrollees SHS (Registrar) (SY: <?php echo $current_school_year_term;?>-<?php echo $current_school_year_period;?> Semester)</h4>
            
            <div class="table-responsive">			
                <table id="dash-table" class="table table-striped table-bordered table-hover table-responsive" style="font-size:12px" cellspacing="0">
                    <thead>
                        <tr class="text-center">
                            
                            <th>Name</th>
                            <th>Date Filed</th>
                            <th>Track</th>
                            <th class="text-center" width="15%" >Action</th>
                        </tr>	
                    </thead> 
                    <tbody>
                        <?php
                            $is_new_enrollee = 1;
                            $is_transferee = 0;
                            $enrollment_status = "tentative";
                            $registrar_evaluated = "no";

                            $student_status = "Transferee";
                            $new_pending_enrollee = $con->prepare("SELECT 
                                t1.*,

                                t2.program_name
                                FROM pending_enrollees AS t1

                                INNER JOIN program AS t2 on t2.program_id = t1.program_id
                                WHERE student_status=:student_status
                                -- AND student_status=:pending_student_status
                                ");

                            $new_pending_enrollee->bindValue(":student_status", $student_status);
                            // $new_pending_enrollee->bindValue(":student_status", $student_status);
                            $new_pending_enrollee->execute();
                        
                            if($new_pending_enrollee->rowCount() > 0){

                                while($row = $new_pending_enrollee->fetch(PDO::FETCH_ASSOC)){

                                    $fullname = $row['firstname'] . " ".$row['lastname'];

                                    $pending_enrollees_id = $row['pending_enrollees_id'];
                                    $firstname = $row['firstname'];
                                    $lastname = $row['lastname'];
                                    $middle_name = $row['middle_name'];
                                    $password = $row['password'];
                                    $program_id = $row['program_id'];
                                    $civil_status = $row['civil_status'];
                                    $nationality = $row['nationality'];
                                    $contact_number = $row['contact_number'];
                                    $birthday = $row['birthday'];
                                    $age = $row['age'];
                                    $guardian_name = $row['guardian_name'];
                                    $guardian_contact_number = $row['guardian_contact_number'];
                                    $sex = $row['sex'];
                                    $student_status = $row['student_status'];
                                    $birthday = $row['birthday'];

                                    $program_section = $row['program_name'];
                                    $date_creation = $row['date_creation'];
 
                                    echo "
                                        <tr class='text-center'>
                                          
                                            <td>
                                                <a href=''>
                                                    $fullname
                                                </a>
                                            </td>
                                            <td>$date_creation</td>
                                            <td>$program_section</td>
                                            <td>
                                                <a href='view_student_transferee_enrollment.php?id=$pending_enrollees_id'>
                                                    <button class='btn btn-primary btn-sm'>View</button>
                                                </a>
                                                <button onclick='confirmPendingValidation(\"" . $firstname . "\", \"" . $lastname . "\", \"" . $middle_name . "\", \"" . $password . "\", \"" . $program_id . "\", \"" . $civil_status . "\", \"" . $nationality . "\", \"" . $contact_number . "\", \"" . $birthday . "\", \"" . $age . "\", \"" . $guardian_name . "\", \"" . $guardian_contact_number . "\", \"" . $sex . "\", \"" . $student_status . "\", \"" . $program_section . "\", \"" . $pending_enrollees_id . "\",)' name='confirm_validation_btn' class='btn btn-success btn-sm'>Confirm</button>
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
</div>

<script>

    function confirmPendingValidation(firstname, lastname, middle_name, password,
        program_id, civil_status, nationality, contact_number, birthday, age,
        guardian_name, guardian_contact_number, sex, student_status,
        program_section,pending_enrollees_id){
        // Function code here

        // console.log('click');
        $.ajax({
            url: '../ajax/enrollee/registrar_confirm_trans_pending.php',
            type: 'POST',
            data: {
                firstname, lastname, middle_name,
                password, program_id, civil_status, nationality, 
                contact_number, birthday, age, guardian_name, 
                guardian_contact_number, sex, student_status, 
                program_section, pending_enrollees_id
            },
            success: function(response) {
                // handle the response from the server here
                // alert(response);
                console.log(response)
                // location.reload();

            },
            error: function(xhr, status, error) {
                // handle any errors here
            }
        });
    }

   function confirmValidation(course_id, enrollement_student_id) {
        $.ajax({
            url: '../ajax/enrollee/registrar_confirm_transferee_enrollee.php',
            type: 'POST',
            data: {
                course_id,
                student_id: enrollement_student_id
            },
            success: function(response) {
                alert(response);
                location.reload();
            },
            error: function(xhr, status, error) {
                console.log(error)
            }
        });
    }



</script>

