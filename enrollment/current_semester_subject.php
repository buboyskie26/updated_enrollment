<!-- <script src="../assets/js/common.js"></script> -->
<script src="../admin/assets/js/common.js"></script>

<?php

    require_once('./classes/StudentEnroll.php');
    require_once('./classes/OldEnrollees.php');
    require_once('./classes/Enrollment.php');
    require_once('./classes/SectionTertiary.php');
    // require_once('../includes/config.php');
    require_once('../includes/studentEnrollHeader.php');
    require_once('../includes/studentHeader.php');
    require_once('../admin/classes/AdminUser.php');

    if(isset($_SESSION['username'])
        && isset($_SESSION['status']) 
        && $_SESSION['status'] == 'enrolled'
        && $_SESSION['status'] != 'pending'){

            $applyTabIsAvailable = false;

            $username = $_SESSION['username'];

            $enroll = new StudentEnroll($con);
            $enrollment = new Enrollment($con, $enroll);

            $enrollment_form_id = $enrollment->GenerateEnrollmentFormId();

            if (!isset($_SESSION['enrollment_form_id'])) {

                $enrollment_form_id = $enrollment->GenerateEnrollmentFormId();
                $_SESSION['enrollment_form_id'] = $enrollment_form_id;

            } else {
                $enrollment_form_id = $_SESSION['enrollment_form_id'];
            }

            $old_enroll = new OldEnrollees($con, $enroll);

            $student_obj = $enroll->GetStudentCourseLevelYearIdCourseId($username);
            $student_id = $student_obj['student_id'];

            $student_status = $student_obj['student_status'];
            $student_course_level = $student_obj['course_level'];
            $is_tertiary = $student_obj['is_tertiary'];

            $course_id = $enroll->GetStudentCourseId($username);
            
            $student_course_tertiary_id = $enroll->GetStudentCourseTertiaryId($username);

            $course_tertiary = new SectionTertiary($con, $student_course_tertiary_id);


            $school_year_obj = $enroll->GetActiveSchoolYearAndSemester();

            $active_school_year_id = $school_year_obj['school_year_id'];
            $year = $school_year_obj['term'];
            $semester = $school_year_obj['period'];

            $current_semester = $school_year_obj['period'];
            $current_term = $school_year_obj['term'];

            $student_current_course_term = $course_tertiary->GetCourseTertiaryTerm();

            
            $totalUnits = 0;

            $GRADE_TWELVE = 12;
            $GRADE_ELEVEN = 11;

            $FIRST_SEMESTER = "First";
            $SECOND_SEMESTER = "Second";

            if($is_tertiary == 0){

                $checkAlignedSectionGr12 = $old_enroll->CheckGrade12AlignedSections($student_id);

                if(isset($_POST['apply_next_semester'])){

                    // echo "wee";
                    $tentative_status = "tentative";
                    $registrar_evaluated = "yes";
                    $registrar_not_evaluated = "no";



                    // echo $generate_form_id;

                    $enroll_tentative = $con->prepare("INSERT INTO enrollment 
                        (student_id, course_id, school_year_id, enrollment_status,
                            registrar_evaluated, enrollment_form_id)
                        VALUES (:student_id,:course_id, :school_year_id, :enrollment_status,
                            :registrar_evaluated, :enrollment_form_id)");

                    if($student_course_level == $GRADE_ELEVEN && $semester == $SECOND_SEMESTER){

                        # Check if student_id and school_year_id is the same
                        // Each student can only be assigned to a unique school year.


                        
                        $enroll_tentative->bindValue(":student_id", $student_id);
                        # Course id will changed based
                        # Grade 11 Section 101 
                        # Grade 12 Section 101 will change depend on the capacity of the room.
                        # and student volume inside.
                        $enroll_tentative->bindValue(":course_id", $course_id);
                        $enroll_tentative->bindValue(":school_year_id", $active_school_year_id);
                        $enroll_tentative->bindValue(":enrollment_status", $tentative_status);
                        $enroll_tentative->bindValue(":registrar_evaluated", $registrar_evaluated);
                        $enroll_tentative->bindValue(":enrollment_form_id", $enrollment_form_id);
                        $enroll_tentative->execute();
                    } 
                    
                    else if($student_course_level == $GRADE_TWELVE 
                        && $semester === $FIRST_SEMESTER
                        && $checkAlignedSectionGr12 == false
                        ){

                        $get_student_course = $con->prepare("SELECT course_id FROM course
                            WHERE previous_course_id=:previous_course_id
                            AND active=:active");

                        $get_student_course->bindValue(":previous_course_id", $course_id);
                        $get_student_course->bindValue(":active", "yes");
                        $get_student_course->execute();
                        
                        if($get_student_course->rowCount() > 0){

                            $moveUpCourseId = $get_student_course->fetchColumn();

                            // echo $moveUpCourseId;
                            $enroll_tentative->bindValue(":student_id", $student_id);
                            # Course id will changed based
                            # Grade 11 Section 101 
                            # Grade 12 Section 101 will change depend on the capacity of the room.
                            # and student volume inside.
                            $enroll_tentative->bindValue(":course_id", $moveUpCourseId);
                            $enroll_tentative->bindValue(":school_year_id", $active_school_year_id);
                            $enroll_tentative->bindValue(":enrollment_status", $tentative_status);
                            $enroll_tentative->bindValue(":registrar_evaluated", $registrar_evaluated);
                            $enroll_tentative->bindValue(":enrollment_form_id", $enrollment_form_id);
                            // $enroll_tentative->execute();

                            if($enroll_tentative->execute()){
                                // echo "Able to apply (Process & Move Up)";
                                // Update Move_Up in Student Course_Id
                                // echo $moveUpCourseId;
                                $update_student_course = $con->prepare("UPDATE student
                                    SET course_id=:move_up_course_id
                                    WHERE course_id=:course_id
                                    AND student_id=:student_id
                                    ");
                                
                                $update_student_course->bindValue(":move_up_course_id", $moveUpCourseId);
                                $update_student_course->bindValue(":course_id", $course_id);
                                $update_student_course->bindValue(":student_id", $student_id);
                                $update_student_course->execute();

                                if($update_student_course->execute()){
                                    
                                    // echo "success registrar evaluated this ongoing enrollee";
                                    # MOVE UP
                                    echo "Applied. You`re moved up in course_id $moveUpCourseId";
                                    echo "<br>";
                                    echo "Successfully Applied for $current_semester";
                                    echo "<br>";
                                    // AdminUser::success("Successfully Applied for $current_semester", "current_semester_subject.php");
                                    // exit();
                                }
                            }
                        }
                    }

                    else if($student_course_level == $GRADE_TWELVE && $semester === $SECOND_SEMESTER){

                        $enroll_tentative->bindValue(":student_id", $student_id);
                        # Course id will changed based
                        # Grade 11 Section 101 
                        # Grade 12 Section 101 will change depend on the capacity of the room.
                        # and student volume inside.
                        $enroll_tentative->bindValue(":course_id", $course_id);
                        $enroll_tentative->bindValue(":school_year_id", $active_school_year_id);
                        $enroll_tentative->bindValue(":enrollment_status", $tentative_status);
                        $enroll_tentative->bindValue(":registrar_evaluated", $registrar_evaluated);
                        $enroll_tentative->bindValue(":enrollment_form_id", $enrollment_form_id);

                        $enroll_tentative->execute();

                    }
                }

                if($student_status == "Regular" || $student_status == "Transferee" ){
                    ?>
                        <div class="table-responsive"> 
                            
                            <h5>List of Regular Subjects for upcoming <?php echo $year;?>  <?php echo $semester;?> Semester (Old student)</h5>
                            <table  class="table table-striped table-bordered table-hover "  style="font-size:12px" cellspacing="0"  > 
                                <thead>
                                    <tr> 
                                        <th rowspan="1">Id</th>
                                        <th rowspan="1">Subject Code</th>  
                                        <th rowspan="2">Name</th>
                                        <th colspan="1">Unit</th> 
                                    </tr>	
                                </thead> 


                                    <tbody>
                                        <?php

                                        // echo directoryPath;
                                        // $recommendedSubject = $studentEnroll->GetRecommendedOldSHSStudentForUpcomingSemester($username);
                                        $recommendedSubject = $enroll->GetRecommendedOldSHSStudentForUpcomingSemesterProgramBased($username);

                                            if($recommendedSubject != null){
                                                $applyTabIsAvailable = true;

                                                foreach ($recommendedSubject as $key => $row) {

                                                    $totalUnits += $row['unit'];
                                                    // $totalUnits = 0;
                                                    echo '<tr>'; 
                                                        echo '<td>'.$row['subject_program_id'].'</td>';
                                                        echo '<td>'.$row['subject_code'].'</td>';
                                                        echo '<td>'.$row['subject_title'].'</td>';
                                                        echo '<td>'.$row['unit'].'</td>';
                                                    echo '</tr>'; 
                                                }
                                            }else{
                                                echo "Not yet set new school_year. Enrollment school_year_id DESC is the same with current_school_year_id";
                                            }
                                        ?>
                                        <tr>
                                            <td colspan="3"  style="text-align: right;" >Total Units</td>
                                            <td><?php echo $totalUnits;?></td>
                                        </tr> 
                                    </tbody>
                            </table>
                    
                            <?php
                                // In the backend, it doesnt have a validation yet.
                                // if($enroll->CheckStudentYearIdAndCurrentYearId($username) == false){
                                if($applyTabIsAvailable == true){

                                    $checkIfEligibleToApply = $old_enroll->CheckIfStudentApplicableToApplyNextSemester($username, $active_school_year_id);
                                    $checkIfStudentAlreadyApplied = $old_enroll->checkIfStudentAlreadyApplied($username, $active_school_year_id);
                                    
                                    if($checkIfEligibleToApply == true & $checkIfStudentAlreadyApplied == false){
                                        // echo "eligibnle";
                                        ?>
                                            <form method="POST">
                                                <button name='apply_next_semester'
                                                class="btn btn-success">Apply for Next Sem</button>
                                            </form>
                                        <?php
                                    }else{
                                        // echo "not eligible.";
                                    }
                                }
                            ?>
                    <?php
                }
            }

            if($is_tertiary == 1){


                if(isset($_POST['apply_next_semester'])
                    && $student_status == "Regular"){

                    # Tertiary Student Requirement for moving-up.

                    # If current section is not-activated and that section_id
                    # is in the prev_course_tertiary_id of other row, then
                    # that row should be the aligned section of that regular student.

                    # REGULAR STUDENT ONLY.
                    if($current_semester == "First"
                        && $student_status == "Regular" 
                        && $student_current_course_term != $current_term){
                        
                        # Aligned the section.

                        $sectionMoveUpObj = $course_tertiary->GetMoveUpTertiarySection($student_course_tertiary_id);

                        $myPrevCourseSectionId = $sectionMoveUpObj['course_tertiary_id'];
                        $myPrevCourseSectionYearLevel = $sectionMoveUpObj['course_level'];
    
                        # Update Student Course_Tertiary_Id

                        $update = $con->prepare("UPDATE student
                            SET course_tertiary_id=:course_tertiary_id,
                                course_level=:course_level
                            WHERE student_id=:student_id");

                        $update->bindValue(":course_tertiary_id", $myPrevCourseSectionId);
                        $update->bindValue(":course_level", $myPrevCourseSectionYearLevel);
                        $update->bindValue(":student_id", $student_id);


                        $tentative_status = "tentative";

                        $registrar_evaluated = "yes";
                        $registrar_not_evaluated = "no";

                        # Apply

                        $enroll_tentative = $con->prepare("INSERT INTO enrollment_tertiary 
                            (student_id, course_tertiary_id, school_year_id, enrollment_status, registrar_evaluated)
                            VALUES (:student_id,:course_tertiary_id, :school_year_id, :enrollment_status, :registrar_evaluated)");
                    
                        $enroll_tentative->bindValue(":student_id", $student_id);
                        # Course id will changed based
                        # Grade 11 Section 101 
                        # Grade 12 Section 101 will change depend on the capacity of the room.
                        # and student volume inside.
                        $enroll_tentative->bindValue(":course_tertiary_id", $myPrevCourseSectionId);
                        $enroll_tentative->bindValue(":school_year_id", $active_school_year_id);
                        $enroll_tentative->bindValue(":enrollment_status", $tentative_status);
                        $enroll_tentative->bindValue(":registrar_evaluated", $registrar_evaluated);

                        if($enroll_tentative->execute() && $update->execute()){
                            echo "Student has been move up to $myPrevCourseSectionId, becomes $myPrevCourseSectionYearLevel Year Level and has been applied for enrollment this S.Y $current_term $current_semester semester";

                        }else{
                            echo "something went wrong on enrollment and moving up";
                        }

                    }

                    if($current_semester == "Second"
                        && $student_status == "Regular" 
                        && $student_current_course_term == $current_term){

                        # Apply Only.
                        $tentative_status = "tentative";

                        $registrar_evaluated = "yes";
                        $registrar_not_evaluated = "no";

                        # Apply

                        // $enroll_tentative = $con->prepare("INSERT INTO enrollment_tertiary 
                        //     (student_id, course_tertiary_id, school_year_id, enrollment_status, registrar_evaluated)
                        //     VALUES (:student_id,:course_tertiary_id, :school_year_id, :enrollment_status, :registrar_evaluated)");

                        $enroll_tentative = $con->prepare("INSERT IGNORE INTO enrollment_tertiary 
                            (student_id, course_tertiary_id, school_year_id, enrollment_status, registrar_evaluated)
                            VALUES (:student_id,:course_tertiary_id, :school_year_id, :enrollment_status, :registrar_evaluated)");

                    
                        $enroll_tentative->bindValue(":student_id", $student_id);
                            
                        $enroll_tentative->bindValue(":course_tertiary_id", $student_course_tertiary_id);
                        $enroll_tentative->bindValue(":school_year_id", $active_school_year_id);
                        $enroll_tentative->bindValue(":enrollment_status", $tentative_status);
                        $enroll_tentative->bindValue(":registrar_evaluated", $registrar_evaluated);
                        
                        $result = $enroll_tentative->execute();

                        if($result && $enroll_tentative->rowCount() > 0){
                            echo "Student has been applied for enrollment this S.Y $current_term $current_semester semester";
                        } else {
                            echo "You have issued already. Please wait to confirm by the registrar.";
                        }
                    }
                }


                if(isset($_POST['apply_next_semester'])
                    && $student_status == "Irregular"){

                        $tentative_status = "tentative";

                        $registrar_evaluated = "no";
                        $registrar_not_evaluated = "no";

                        # Apply

                        // $enroll_tentative = $con->prepare("INSERT INTO enrollment_tertiary 
                        //     (student_id, course_tertiary_id, school_year_id, enrollment_status, registrar_evaluated)
                        //     VALUES (:student_id,:course_tertiary_id, :school_year_id, :enrollment_status, :registrar_evaluated)");

                        $enroll_tentative = $con->prepare("INSERT IGNORE INTO enrollment_tertiary 
                            (student_id, course_tertiary_id, school_year_id, enrollment_status, registrar_evaluated)
                            VALUES (:student_id,:course_tertiary_id, :school_year_id, :enrollment_status, :registrar_evaluated)");

                    
                        $enroll_tentative->bindValue(":student_id", $student_id);

                        $irreg_student_course_tertiary_id = 0;
                            
                        $enroll_tentative->bindValue(":course_tertiary_id", $irreg_student_course_tertiary_id);
                        $enroll_tentative->bindValue(":school_year_id", $active_school_year_id);
                        $enroll_tentative->bindValue(":enrollment_status", $tentative_status);
                        $enroll_tentative->bindValue(":registrar_evaluated", $registrar_evaluated);
                        
                        $result = $enroll_tentative->execute();

                        if($result && $enroll_tentative->rowCount() > 0){
                            echo "Irreg Student has been applied for enrollment this S.Y $current_term $current_semester semester";
                        }
                }

                // Check if tertiary appicable to apply for next sem.

                # Requirements.
                # As long as enrolled in prev sem and the S.Y has changed
                # Student is eligible to apply.
                # 1. Should have a remark grades of passed
                # 2. Payment should be cleared in the previous semester.

                ?>
                    <div class="row col-md-12 table-responsive"> 
                        <h3 style="font-weight:500;" class="text-primary text-center">Registration History</h3>
                        <hr>
                        <table  class="table table-striped table-bordered table-hover "  style="font-size:14px" cellspacing="0"  > 
                            <thead>
                                <tr class="text-center text-success"> 
                                    <th rowspan="1">Section</th>
                                    <th rowspan="1">Enrollment Date</th>  
                                    <th rowspan="2">S.Y</th>
                                    <th colspan="1">Semester</th> 
                                    <th colspan="1">Subject Count</th> 
                                </tr>	
                            </thead> 

                                <tbody>
                                    <?php

                                        $sql = $con->prepare("SELECT 

                                            t1.* , t2.*, t3.*
                                            
                                            FROM enrollment_tertiary as t1

                                            LEFT JOIN course_tertiary as t2 ON t2.course_tertiary_id = t1.course_tertiary_id
                                            LEFT JOIN school_year as t3 ON t3.school_year_id = t1.school_year_id
                                            WHERE t1.student_id=:student_id
                                            AND t1.enrollment_status=:enrollment_status");

                                        $sql->bindValue(":student_id", $student_id);
                                        $sql->bindValue(":enrollment_status", "enrolled");

                                        $sql->execute();

                                        if($sql->rowCount() > 0){

                                            while($row = $sql->fetch(PDO::FETCH_ASSOC)){
                                                $course_tertiary_id = $row['course_tertiary_id'];
                                                $school_year_id = $row['school_year_id'];
                                                $term = $row['term'];
                                                $program_section = $row['program_section'];
                                                $enrollment_date = $row['enrollment_date'];

                                                $subject_count  = $old_enroll->GetSubjectCount($course_tertiary_id, $school_year_id);

                                                $period = $row['period'];
                                                echo '<tr class="text-center">'; 

                                                    echo '<td>
                                                        <a style="text-decoration:none;" href="enrolled_schedule.php?id='.$course_tertiary_id.'&yid='.$school_year_id.'">
                                                            '.$program_section.'
                                                        </a>
                                                    </td>';
                                                    echo '<td>'.$enrollment_date.'</td>';
                                                    echo '<td>'.$term.'</td>';
                                                    echo '<td>'.$period.'</td>';
                                                    echo '<td>'.$subject_count.'</td>';
                                                echo '</tr>';
                                            }
                                        }

                                    ?>
                                </tbody>
                        </table>
                        <hr>
                        <hr>
                        <!-- <form method="POST">
                            <button name='apply_next_semester'
                            class="btn btn-success">Apply for Next Sem (Tertiary)</button>
                        </form> -->
                    </div>

                <?php
            }

    }

    if(isset($_SESSION['username'])
        && isset($_SESSION['status']) 
        && $_SESSION['status'] == 'pending'
        && $_SESSION['status'] != 'enrolled'
        ){

            $username = $_SESSION['username'];

            // $year_semester_obj = $enroll->GetActiveSchoolYearAndSemester();

            // $active_school_year_id = $year_semester_obj['school_year_id'];
            // $year = $year_semester_obj['term'];
            // $semester = $year_semester_obj['period'];

        ?>

        <div class="table-responsive row col-md-12"> 

            <h3 class="text-success text-center">Registration</h3>
            <div>
                <?php
                    // Add necessary check
                    $check_filled_up = $con->prepare("SELECT * FROM pending_enrollees
                        WHERE firstname !=:firstname
                        AND lastname !=:lastname
                        AND middle_name !=:middle_name
                        AND email !=:email
                        AND token !=:token
                        AND activated !=:activated
                        AND contact_number !=:contact_number
                        AND address !=:address
                        AND program_id =:program_id
                        AND firstname =:user_firstname
                        ");
                    $check_filled_up->bindValue(":firstname", "");
                    $check_filled_up->bindValue(":lastname", "");
                    $check_filled_up->bindValue(":middle_name", "");
                    $check_filled_up->bindValue(":email", "");
                    $check_filled_up->bindValue(":token", "");
                    $check_filled_up->bindValue(":activated", 0);
                    $check_filled_up->bindValue(":contact_number", "");
                    $check_filled_up->bindValue(":address", "");
                    $check_filled_up->bindValue(":program_id", 0);
                    $check_filled_up->bindValue(":user_firstname", $username);
                $check_filled_up->execute();
                    if($check_filled_up->rowCount() == 0){
                        // Disabled.
                        echo "
                                <button class='btn btn-success btn-sm'  disabled style='pointer-events: none;'>
                                    Register Here!
                                </button>
                        ";
                    }else{
                        echo "
                        <a href='pre_registration_pending.php'>
                                <button class='btn btn-success btn-sm'>
                                    Register Here!
                                </button>
                            </a>";
                    }
                ?>
                <!-- <a href="pre_registration_pending.php">
                    <button class="btn btn-success btn-sm">
                        Apply
                    </button>
                </a> -->
            </div>
            <table  class="table table-striped table-bordered table-hover "  style="font-size:12px" cellspacing="0"  > 
                <thead>
                    <tr class="text-center"> 
                        <th rowspan="1">Student Id</th>
                        <th rowspan="2">Name</th>
                        <th rowspan="2">Course</th>
                        <th colspan="1">Status</th> 
                        <th colspan="1"></th> 
                    </tr>	
                </thead> 
                <tbody>
                    <?php 
                    
                    
                        $sql = $con->prepare("SELECT t1.student_status,t1.lastname, t1.firstname, t2.program_name FROM pending_enrollees as t1

                            LEFT JOIN  program as t2 ON t1.program_id = t2.program_id
                            WHERE t1.firstname=:firstname
                            AND t1.program_id !=:program_id
                            ");
                        $sql->bindValue(":firstname", $username);
                        $sql->bindValue(":program_id", 0);
                        $sql->execute();
                        if($sql->rowCount() > 0){

                            while($row = $sql->fetch(PDO::FETCH_ASSOC)){
                                
                                $status = "Approved";
                                if($row['student_status'] != "APPROVED"){
                                    $status = "Pending";
                                }
                                echo "
                                    <tr class='text-center'>
                                        <td></td>
                                        <td>".$row['firstname']." ".$row['lastname']."</td>
                                        <td>".$row['program_name']." </td>
                                        <td>$status</td>
                                        <td>
                                            <a href=''>
                                               <button class='btn btn-primary btn-sm'>
                                                Edit
                                               </button>
                                            </a>
                                        </td>
                                    </tr>
                                
                                ";
                            }
                        }
                    ?>
                </tbody>
            </table>
        <?php

    }
?>