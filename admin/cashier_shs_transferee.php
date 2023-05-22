<?php  
 
    // include('../includes/classes/Teacher.php');
    include('./classes/Department.php');
    include('./cashierHeader.php');
    include('../enrollment/classes/StudentEnroll.php');

 
    $enroll = new StudentEnroll($con);

    $school_year_obj = $enroll->GetActiveSchoolYearAndSemester();

    $current_school_year_id = $school_year_obj['school_year_id'];
    $current_school_year_term = $school_year_obj['term'];
    $current_school_year_period = $school_year_obj['period'];
?>


<div class="row col-md-12">
    <h4 class="text-center">Transferee Enrollees SHS (Cashier) (SY: <?php echo $current_school_year_term;?>-<?php echo $current_school_year_period;?> Semester)</h4>
    
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
                    $default_shs_course_level = 11;
                    $is_new_enrollee = 1;
                    $is_transferee = 1;
                    $regular_Status = "Regular";
                    $enrollment_status = "tentative";
                    $registrar_evaluated = "yes";
                    $cashier_evaluated = "no";
                    $new_enrollee = $con->prepare("SELECT 
                        t1.student_id, t2.firstname,
                        t2.lastname,t2.course_level,
                        t2.course_id, t2.student_id as t2_student_id,
                        t2.course_id, 
                        
                        t3.program_section
                        FROM enrollment as t1

                        INNER JOIN student as t2 ON t2.student_id = t1.student_id
                        LEFT  JOIN course as t3 ON t2.course_id = t3.course_id

                        WHERE t1.is_new_enrollee=:is_new_enrollee
                        AND t1.is_transferee=:is_transferee
                        AND t1.enrollment_status=:enrollment_status
                        AND t1.school_year_id=:school_year_id
                        AND t1.registrar_evaluated=:registrar_evaluated
                        AND t1.cashier_evaluated=:cashier_evaluated
                        ");

                    $new_enrollee->bindValue(":is_new_enrollee", $is_new_enrollee);
                    $new_enrollee->bindValue(":is_transferee", $is_transferee);
                    $new_enrollee->bindValue(":enrollment_status", $enrollment_status);
                    $new_enrollee->bindValue(":school_year_id", $current_school_year_id);
                    $new_enrollee->bindValue(":registrar_evaluated", $registrar_evaluated);
                    $new_enrollee->bindValue(":cashier_evaluated", $cashier_evaluated);
                    $new_enrollee->execute();
                
                    if($new_enrollee->rowCount() > 0){

                        while($row = $new_enrollee->fetch(PDO::FETCH_ASSOC)){

                            $enrollement_student_id = $row['student_id'];
                            $fullname = $row['firstname'] . " " . $row['lastname'];
                            $standing = $row['course_level'];
                            $course_id = $row['course_id'];
                            $student_id = $row['t2_student_id'];
                            $program_section = $row['program_section'];

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
                                        <button onclick='confirmValidation(" . $course_id . ", " . $enrollement_student_id . ")' name='confirm_validation_btn' class='btn btn-primary btn-sm'>Confirm</button>
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



<script>

   function confirmValidation(course_id, student_id) {
    $.ajax({
        url: './ajax/enrollee/cashier_confirm_new_enrollee.php', // replace with your PHP script URL
        type: 'POST',
        data: {
            // add any data you want to send to the server here
            course_id, student_id
        },
        success: function(response) {
            alert(response);
            location.reload();
        },
        error: function(xhr, status, error) {
        }
    });
}
</script>