<?php 

    include('../registrar_enrollment_header.php');
    include('../../enrollment/classes/StudentEnroll.php');
    include('../classes/Course.php');

    $course = new Course($con, null);
     
    $enroll = new StudentEnroll($con);

    $school_year_obj = $enroll->GetActiveSchoolYearAndSemester();

    $current_school_year_id = $school_year_obj['school_year_id'];
    $current_school_year_term = $school_year_obj['term'];
    $current_school_year_period = $school_year_obj['period'];
?>

<script src="../assets/js/common.js"></script>

    <div class="row col-md-12">

        <div class="col-md-12">
            <h5 class="mb-3 text-primary text-center">New Enrollees SHS (Registrar) (SY: <?php echo $current_school_year_term;?>-<?php echo $current_school_year_period;?> Semester)</h5>
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
                            $is_new_enrollee = 1;
                            $is_transferee = 0;
                            $enrollment_status = "tentative";
                            // $registrar_evaluated = "no";
                            $registrar_evaluated = "yes";

                            $new_enrollee = $con->prepare("SELECT 
                                t1.student_id, t1.cashier_evaluated, t2.firstname,
                                t2.lastname,t2.course_level,
                                t2.course_id, t2.student_id as t2_student_id,
                                t2.course_id, t2.username,
                                
                                t3.program_section
                                FROM enrollment as t1

                                INNER JOIN student as t2 ON t2.student_id = t1.student_id
                                LEFT  JOIN course as t3 ON t2.course_id = t3.course_id

                                WHERE t1.is_new_enrollee=:is_new_enrollee
                                AND t1.is_transferee=:is_transferee

                                AND t1.enrollment_status=:enrollment_status
                                AND t1.school_year_id=:school_year_id
                                AND t1.registrar_evaluated=:registrar_evaluated
                                ");

                            $new_enrollee->bindValue(":is_new_enrollee", $is_new_enrollee);
                            $new_enrollee->bindValue(":is_transferee", $is_transferee);
                            $new_enrollee->bindValue(":enrollment_status", $enrollment_status);
                            $new_enrollee->bindValue(":school_year_id", $current_school_year_id);
                            $new_enrollee->bindValue(":registrar_evaluated", $registrar_evaluated);
                            $new_enrollee->execute();
                        
                            if($new_enrollee->rowCount() > 0){

                                while($row = $new_enrollee->fetch(PDO::FETCH_ASSOC)){

                                    $enrollement_student_id = $row['student_id'];

                                    $student_username = $row['username'];

                                    $fullname = $row['firstname'] . " " . $row['lastname'];
                                    $cashier_evaluated = $row['cashier_evaluated'];
                                    $standing = $row['course_level'];
                                    $course_id = $row['course_id'];
                                    $student_id = $row['t2_student_id'];
                                    $program_section = $row['program_section'];
                                    $cashier_evaluated = $row['cashier_evaluated'];

                                    $createUrl = base_url . "/subject_insertion.php?username=$student_username&id=$student_id";

                                    $btn = "
                                        <button class='btn btn-sm btn-dark'>Wait for Cashier</button>
                                    ";
                                    
                                    if($cashier_evaluated == "yes"){
                                        $btn = "
                                            <a href='$createUrl'>
                                                <button class='btn btn-success btn-sm'>
                                                    Enroll Now!
                                                </button>
                                            </a>
                                        ";
                                    }

                                    // store only.
                                    $reserve_btn = "
                                         <button onclick='confirmValidation(" . $course_id . ", " . $enrollement_student_id . ")' name='confirm_validation_btn' class='btn btn-primary btn-sm'>Confirm</button>
                                    ";
                                    echo "
                                        <tr class='text-center'>
                                            <td>$student_id</td>
                                            <td>
                                                <a href=''>
                                                    $fullname
                                                </a>
                                            </td>
                                            <td>Grade </td>
                                            <td>$program_section</td>
                                            <td>
                                                $btn
                                            </td>
                                        </tr>
                                    ";
                                }
                            }
                        ?>
                    </tbody>
                </table>
            </div>
            
            <hr>
            <hr>

            <h5 class="mb-3 text-success text-center">New Enrollees Tertiary (Registrar) (SY: <?php echo $current_school_year_term;?>-<?php echo $current_school_year_period;?> Semester)</h5>
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
                            $is_new_enrollee = 1;
                            $is_transferee = 0;
                            $enrollment_status = "tentative";
                            // $registrar_evaluated = "no";
                            $registrar_evaluated = "yes";

                            $new_enrollee = $con->prepare("SELECT 
                                t1.student_id, t1.cashier_evaluated, t2.firstname,
                                t2.lastname,t2.course_level,
                                t2.course_id, t2.student_id as t2_student_id,
                                t2.course_id, t2.username
                                
                                -- t3.program_section
                                FROM enrollment_tertiary as t1

                                INNER JOIN student as t2 ON t2.student_id = t1.student_id

                                WHERE t1.is_new_enrollee=:is_new_enrollee
                                AND t1.is_transferee=:is_transferee

                                AND t1.enrollment_status=:enrollment_status
                                AND t1.school_year_id=:school_year_id
                                AND t1.registrar_evaluated=:registrar_evaluated
                                ");

                            $new_enrollee->bindValue(":is_new_enrollee", $is_new_enrollee);
                            $new_enrollee->bindValue(":is_transferee", $is_transferee);
                            $new_enrollee->bindValue(":enrollment_status", $enrollment_status);
                            $new_enrollee->bindValue(":school_year_id", $current_school_year_id);
                            $new_enrollee->bindValue(":registrar_evaluated", $registrar_evaluated);
                            $new_enrollee->execute();
                        
                            if($new_enrollee->rowCount() > 0){

                                while($row = $new_enrollee->fetch(PDO::FETCH_ASSOC)){

                                    $enrollement_student_id = $row['student_id'];

                                    $student_username = $row['username'];

                                    $fullname = $row['firstname'] . " " . $row['lastname'];
                                    $cashier_evaluated = $row['cashier_evaluated'];
                                    $standing = $row['course_level'];
                                    $course_id = $row['course_id'];
                                    $student_id = $row['t2_student_id'];
                                    $program_section = "";
                                    $cashier_evaluated = $row['cashier_evaluated'];

                                    $createUrl = directoryPath . "tertiary_subject_insertion.php?id=$student_id";

                                    $btn = "
                                        <button class='btn btn-sm btn-dark'>Wait for Cashier</button>
                                    ";
                                    
                                    if($cashier_evaluated == "yes"){
                                        $btn = "
                                            <a href='$createUrl'>
                                                <button class='btn btn-success btn-sm'>
                                                    Enroll Now!
                                                </button>
                                            </a>
                                        ";
                                    }

                                    echo "
                                        <tr class='text-center'>
                                            <td>$student_id</td>
                                            <td>
                                                $fullname
                                            </td>
                                            <td>Grade </td>
                                            <td>$program_section</td>
                                            <td>
                                                $btn
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




        <hr>
        <hr>
        <div class="col-md-12">
            <h5 class="text-center">Pending Regular New Enrollees SHS (Registrar) (SY: <?php echo $current_school_year_term;?>-<?php echo $current_school_year_period;?> Semester)</h5>
            
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

                            $student_status = "Regular";
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
                                    $type = $row['type'];

                                    // echo $type;
 

                                    $actionBtn = "";

                                    if($type == "SHS"){
                                        $actionBtn = "
                                            <a href='view_student_new_enrollment.php?id=$pending_enrollees_id'>
                                                <button class='btn btn-primary btn-sm'>View</button>
                                            </a>
                                            ";
                                    }else if($type == "Tertiary"){
                                       $actionBtn = "
                                            <a href='view_student_new_enrollment_tertiary.php?id=$pending_enrollees_id'>
                                                <button class='btn btn-primary btn-sm'>View</button>
                                            </a>
                                        "; 
                                    }

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
                                                $actionBtn
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

    // console.log('click');
    $.ajax({
        url: '../ajax/enrollee/registrar_confirm_pending.php',
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
            // console.log(response);
            alert(response);
            location.reload();
        },
        error: function(xhr, status, error) {
            // handle any errors here
        }
    });
}
</script>
 