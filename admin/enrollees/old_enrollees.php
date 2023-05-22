<?php 

    include('../registrar_enrollment_header.php');
    include('../../enrollment/classes/StudentEnroll.php');
    include('../../enrollment/classes/OldEnrollees.php');


    $enrol = new StudentEnroll($con);
    $old_enroll = new OldEnrollees($con, $enrol);

    $school_year_id = $enrol->GetCurrentYearId();

    $school_year_obj = $enrol->GetActiveSchoolYearAndSemester();

    $current_school_year_id = $school_year_obj['school_year_id'];
    $current_school_year_term = $school_year_obj['term'];
    $current_school_year_period = $school_year_obj['period'];
?>
 

<div class="row col-md-12">

    <div class="row col-md-12">
        <h4 class="text-center">Ongoing Enrollees SHS (Registrar) (SY: <?php echo $current_school_year_term;?>-<?php echo $current_school_year_period;?> Semester)</h4>
        <div class="table-responsive">	

            <table id="dash-table" class="table table-striped table-bordered table-hover table-responsive" style="font-size:12px" cellspacing="0">
                <thead>
                    <tr class="text-center">
                        <th>Id</th>
                        <th>Name</th>
                        <th>Standing</th>
                        <th>Course-Section</th>
                        <th>Status</th>
                        <th class="text-center" width="15%" >Action</th>
                    </tr>	
                </thead> 
                <tbody>
                    <?php
                        $default_shs_course_level = 11;
                        $regular_Status = "Regular";

                        $is_new_enrollee = 0;
                        $is_transferee = 0;
                        $enrollment_status = "tentative";
                        // $registrar_evaluated = "yes";

                        $new_enrollee = $con->prepare("SELECT 
                            t1.student_id, t1.cashier_evaluated, t1.registrar_evaluated,
                            
                            t2.firstname,
                            t2.lastname,t2.course_level,
                            t2.student_id as t2_student_id,
                            t2.course_id, t2.course_level, t2.username,
                            t2.student_status,
                            
                            t3.program_section
                            FROM enrollment as t1

                            INNER JOIN student as t2 ON t2.student_id = t1.student_id
                            LEFT JOIN course as t3 ON t2.course_id = t3.course_id

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
                    
                        $createUrl = "";
                        $GRADE_ELEVEN = 11;
                        
                        if($new_enrollee->rowCount() > 0){

                            while($row = $new_enrollee->fetch(PDO::FETCH_ASSOC)){

                                // $previous_course_id = $row['student_course_id'];
                                $student_username = $row['username'];
                                $student_status = $row['student_status'];
                                $enrollement_student_id = $row['student_id'];
                                $fullname = $row['firstname'] . " " . $row['lastname'];
                                $standing = $row['course_level'];
                                $course_id = $row['course_id'];
                                $course_level = $row['course_level'];
                                $student_id = $row['t2_student_id'];
                                $program_section = $row['program_section'];
                                $cashier_evaluated = $row['cashier_evaluated'];
                                $registrar_evaluated = $row['registrar_evaluated'];

                                $doesSectionUpdated = $old_enroll->CheckStudentUpdatedSection($student_username);

                                $courseStatus = "";

                                $linkToPopulateSectionWithSubjects = "";

                                $createUrl = base_url . "/subject_insertion.php?username=$student_username&id=$student_id";

                                if($doesSectionUpdated == true){
                                        $courseStatus = " (Not Updated)";
                                        $asd = "http://localhost/dcbt/admin/enrollees/strand_section.php";
                                        $linkToPopulateSectionWithSubjects = $asd;
                                }

                                if($courseStatus != ""){
                                    $enrollBtn = "Update & Enroll";
                                }

                                $moveUpCourseIdBtn = "";

                             
                                $enrollBtn = "
                                    <button class='btn btn-sm btn-secondary'>Wait for Cashier</button>
                                ";

                                if($cashier_evaluated == "yes" ){
                                    $enrollBtn = "
                                        <a href='$createUrl'>
                                            <button class='btn btn-success btn-sm'>
                                                Enroll Now!
                                            </button>
                                        </a>
                                    ";
                                }

                                // Check if student fresh grade 12 aligned section to grade 12 sections.
                                $checkAlignedSectionGr12 = $old_enroll->CheckGrade12AlignedSections($student_id);
                                
                                if($checkAlignedSectionGr12 == false
                                    && $cashier_evaluated == "no"
                                    && $course_level ==  12
                                ){
                                    $enrollBtn = "
                                        <button onclick='confirmValidationv2(" . $course_id . ", " . $enrollement_student_id . ")' name='confirm' class='btn btn-primary btn-sm'>
                                            Update Section
                                        </button>
                                    ";
                                }

                                if($checkAlignedSectionGr12 == false
                                    && $cashier_evaluated == "no" 
                                    && $course_level == $GRADE_ELEVEN
                                ){
                                    $enrollBtn = "
                                        <button onclick='confirmValidationv2(" . $course_id . ", " . $enrollement_student_id . ")' name='confirm_validation_btn' class='btn btn-primary btn-sm'>
                                            Confirm
                                        </button>
                                    ";
                                }


                               if($cashier_evaluated == "no" 
                                    && $registrar_evaluated == "yes"
                                    && $course_level == $GRADE_ELEVEN
                                ){
                                    $enrollBtn = "
                                        <button class='btn btn-sm btn-secondary'>Wait for Cashier</button>

                                    ";
                                }



                                if($course_level > $GRADE_ELEVEN 
                                    && $cashier_evaluated == "yes"
                                    && $checkAlignedSectionGr12 == false
                                    ){
                                    $moveUpCourseIdBtn = "
                                    <button onclick='confirmValidationv2(" . $course_id . ", " . $enrollement_student_id . ")' name='confirm_validation_btn' class='btn btn-primary btn-sm'>
                                        Confirm
                                    </button>
                                    ";
                                }

                                
                               
                                echo "
                                    <tr class='text-center'>
                                        <td>$student_id</td>
                                        <td>
                                            <a href=''>
                                                $fullname
                                            </a>
                                        </td>
                                        <td>Grade $course_level</td>
                                    
                                        <td>
                                            <a href='$linkToPopulateSectionWithSubjects'>
                                                $program_section $courseStatus
                                            </a>
                                        </td>
                                        <td>$student_status</td>

                                        <td>
                                            $enrollBtn
                                            $moveUpCourseIdBtn
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

        <h4 class="text-primary text-center">Ongoing Enrollees (SY: <?php echo $current_school_year_term;?>-<?php echo $current_school_year_period;?> Semester)</h4>
        <div class="table-responsive">	

            <table id="dash-table" class="table table-striped table-bordered table-hover table-responsive" style="font-size:12px" cellspacing="0">
                <thead>
                    <tr class="text-center">
                        <th>Id</th>
                        <th>Name</th>
                        <th>Standing</th>
                        <th>Course-Section</th>
                        <th>Status</th>
                        <th class="text-center" width="15%" >Action</th>
                    </tr>	
                </thead> 
                <tbody>
                    <?php
                        $default_shs_course_level = 11;
                        $regular_Status = "Regular";

                        $is_new_enrollee = 0;
                        $is_transferee = 0;
                        $enrollment_status = "tentative";
                        // $registrar_evaluated = "yes";

                        $new_enrollee = $con->prepare("SELECT 
                            t1.student_id, t1.cashier_evaluated, t1.registrar_evaluated,
                            
                            t2.firstname,
                            t2.lastname, t2.course_level,
                            t2.student_id as t2_student_id,
                            t2.course_tertiary_id, t2.course_level, t2.username,
                            t2.student_status,
                            
                            t3.program_section
                            FROM enrollment_tertiary as t1

                            INNER JOIN student as t2 ON t2.student_id = t1.student_id
                            LEFT JOIN course_tertiary as t3 ON t2.course_tertiary_id = t3.course_tertiary_id

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
                    
                        $createUrl = "";
                        $GRADE_ELEVEN = 11;
                        
                        if($new_enrollee->rowCount() > 0){

                            while($row = $new_enrollee->fetch(PDO::FETCH_ASSOC)){

                                // $previous_course_id = $row['student_course_id'];
                                $student_username = $row['username'];
                                $student_status = $row['student_status'];
                                $enrollement_student_id = $row['student_id'];
                                $fullname = $row['firstname'] . " " . $row['lastname'];
                                $standing = $row['course_level'];
                                $course_tertiary_id = $row['course_tertiary_id'];
                                $course_level = $row['course_level'];
                                $student_id = $row['t2_student_id'];
                                $program_section = $row['program_section'];
                                $cashier_evaluated = $row['cashier_evaluated'];
                                $registrar_evaluated = $row['registrar_evaluated'];

                                $doesSectionUpdated = $old_enroll->CheckStudentUpdatedSection($student_username);

                                $courseStatus = "";

                                $linkToPopulateSectionWithSubjects = "";

                                $createUrl = base_url . "/tertiary_subject_insertion.php?username=$student_username&id=$student_id";

                                if($doesSectionUpdated == true){
                                        $courseStatus = " (Not Updated)";
                                        $asd = "http://localhost/dcbt/admin/enrollees/strand_section.php";
                                        $linkToPopulateSectionWithSubjects = $asd;
                                }

                                if($courseStatus != ""){
                                    $enrollBtn = "Update & Enroll";
                                }

                                $moveUpCourseIdBtn = "";

                             
                                $enrollBtn = "
                                     
                                ";

                                if($cashier_evaluated == "yes" 
                                    && $student_status != "Irregular"

                                ){
                                    $enrollBtn = "
                                        <a href='$createUrl'>
                                            <button class='btn btn-success btn-sm'>
                                                Enroll Now!
                                            </button>
                                        </a>
                                    ";
                                }

                                // Check if student fresh grade 12 aligned section to grade 12 sections.
                                $checkAlignedSectionGr12 = $old_enroll->CheckGrade12AlignedSections($student_id);
                                
                                if($checkAlignedSectionGr12 == false
                                    && $cashier_evaluated == "no"
                                    && $course_level ==  12
                                    && $student_status != "Irregular"

                                ){
                                    $enrollBtn = "
                                        <button onclick='confirmValidationv2(" . $course_tertiary_id . ", " . $enrollement_student_id . ")' name='confirm' class='btn btn-primary btn-sm'>
                                            Update Section
                                        </button>
                                    ";
                                }

                                if($checkAlignedSectionGr12 == false
                                    && $cashier_evaluated == "no" 
                                    && $course_level == $GRADE_ELEVEN
                                    && $student_status != "Irregular"

                                ){
                                    $enrollBtn = "
                                        <button onclick='confirmValidationv2(" . $course_tertiary_id . ", " . $enrollement_student_id . ")' name='confirm_validation_btn' class='btn btn-primary btn-sm'>
                                            Confirm
                                        </button>
                                    ";
                                }


                               if($cashier_evaluated == "no" 
                                    && $registrar_evaluated == "yes"
                                    && $course_level == $GRADE_ELEVEN
                                    && $student_status != "Irregular"

                                ){
                                    $enrollBtn = "
                                        <button class='btn btn-sm btn-secondary'>Wait for Cashier</button>
                                    ";
                                }



                                if($course_level > $GRADE_ELEVEN 
                                    && $cashier_evaluated == "yes"
                                    && $checkAlignedSectionGr12 == false
                                    && $student_status != "Irregular"
                                    ){
                                    $moveUpCourseIdBtn = "
                                        <button onclick='confirmValidationv2(" . $course_tertiary_id . ", " . $enrollement_student_id . ")' name='confirm_validation_btn' class='btn btn-primary btn-sm'>
                                            Confirm
                                        </button>
                                    ";
                                }

                                $evaluate_irregular = "";

                                if($student_status == "Irregular"){

                                    $evaluate_irregular = "
                                        <a href='irregular_evaluation_insertion.php?id=$student_id'>
                                            <button class='btn btn-dark btn-sm'>Evaluate</button>
                                        </a>
                                    ";
                                }

                                if($cashier_evaluated == "no" 
                                    && $registrar_evaluated == "yes"
                                    && $student_status == "Regular"

                                ){
                                    $enrollBtn = "
                                        <button class='btn btn-sm btn-secondary'>Wait for Cashier</button>
                                    ";
                                }

                                echo "
                                    <tr class='text-center'>
                                        <td>$student_id</td>
                                        <td>
                                            <a href=''>
                                                $fullname
                                            </a>
                                        </td>
                                        <td>Year $course_level</td>
                                        <td>
                                            <a href='$linkToPopulateSectionWithSubjects'>
                                                $program_section $courseStatus
                                            </a>
                                        </td>
                                        <td>$student_status</td>

                                        <td>
                                            $enrollBtn
                                            $moveUpCourseIdBtn
                                            $evaluate_irregular
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

   function confirmValidationv2(course_id, student_id) {

    // console.log(student_id)
    $.ajax({
        url: '../ajax/enrollee/registrar_confirm_os_enrollee.php', // replace with your PHP script URL
        type: 'POST',
        data: {
            // add any data you want to send to the server here
            course_id,
            student_id
        },
        success: function(response) {
            // console.log(response);
            alert(response)
            location.reload();
        },
        error: function(xhr, status, error) {
        }
    });
    }
</script>


<!-- 

 -->
 
 



