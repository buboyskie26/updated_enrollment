<?php
    include('../registrar_enrollment_header.php');
    include('../../enrollment/classes/StudentEnroll.php');
    include('../../enrollment/classes/StudentSubject.php');
    include('../../enrollment/classes/Section.php');

 if(isset($_GET['inserted'])){

    if(
        isset($_GET['id']) 
        && isset($_GET['p_id'])
        && isset($_GET['e_id'])
        && isset($_SESSION['selected_course_id'])
    
    ){

        $selected_course_id = $_SESSION['selected_course_id'];
        $student_id = $_GET['id'];
        $pending_enrollees_id = $_GET['p_id'];
        $enrollment_id = $_GET['e_id'];

        // echo $student_id;
        $section = new Section($con, $selected_course_id);
        $studentSubject = new StudentSubject($con);

        $section_name = $section->GetSectionName();

        $enroll = new StudentEnroll($con);

        $school_year_obj = $enroll->GetActiveSchoolYearAndSemester();

        $current_school_year_id = $school_year_obj['school_year_id'];
        $current_school_year_term = $school_year_obj['term'];
        $current_school_year_period = $school_year_obj['period'];


        // if(isset($_POST['mark_as_final_btn'])){

        //     // AdminUser::confirm("Are you sure?", "view_student_transferee_enrollment_review.php?inserted=true&id=$student_id");

        //     $approved = "APPROVED";
        //     $update_pending = $con->prepare("UPDATE pending_enrollees
        //             SET student_status=:student_status
        //             WHERE pending_enrollees_id=:pending_enrollees_id
        //         ");

        //     $update_pending->bindParam(":student_status", $approved);
        //     $update_pending->bindParam(":pending_enrollees_id", $pending_enrollees_id);
        //     $update_pending->execute();

        // }

        if(isset($_POST['change_status'])){

            $subject_id = $_POST['subject_id'];
            $student_id = $_POST['student_id'];
            $is_transferee = $_POST['is_transferee'];
            $student_subject_id = $_POST['student_subject_id'];
            $subject_title = $_POST['subject_title'];
            $course_id = $_POST['course_id'];

            // echo $subject_id;

            if($is_transferee == "yes"){

                $update = $con->prepare("UPDATE student_subject
                    SET is_transferee=:is_transferee
                    WHERE student_id=:student_id
                    AND subject_id=:subject_id
                    ");

                $update->bindValue(":is_transferee", "no");
                $update->bindValue(":student_id", $student_id);
                $update->bindValue(":subject_id", $subject_id);

                if($update->execute()){

                    // AdminUser::success("Subject Id $subject_id. is now set as Credit", "");

                    $remove_passed = $con->prepare("DELETE FROM student_subject_grade
                        WHERE student_id=:student_id
                        AND subject_id=:subject_id
                        AND student_subject_id=:student_subject_id
                        AND is_transferee=:is_transferee
                        ");

                    $remove_passed->bindValue(":student_id", $student_id);
                    $remove_passed->bindValue(":subject_id", $subject_id);
                    $remove_passed->bindValue(":student_subject_id", $student_subject_id);
                    $remove_passed->bindValue(":is_transferee", "yes");

                    if($remove_passed->execute()){
                       
                        AdminUser::success("Non-Credited subject #$subject_id has changed into Credited", "view_student_transferee_enrollment_review.php?inserted=true&id=$student_id&p_id=$pending_enrollees_id&e_id=$enrollment_id");

                        // header("Location: view_student_transferee_enrollment_review.php?inserted=true&id=$student_id");
                        exit();
                    }
                }
                # Remove Passed Remarks on the student_subject_grade table
            }

            if($is_transferee == "no"){

                $update = $con->prepare("UPDATE student_subject
                    SET is_transferee=:is_transferee
                    WHERE student_id=:student_id
                    AND student_subject_id=:student_subject_id
                    AND subject_id=:subject_id
                    ");

                $update->bindValue(":is_transferee", "yes");
                $update->bindValue(":student_id", $student_id);
                $update->bindValue(":student_subject_id", $student_subject_id);
                $update->bindValue(":subject_id", $subject_id);

                // if(false){
                if($update->execute()){

                    // $student_subject_id = $con->lastInsertId();

                    $remarks = "Passed";

                    $is_transferee = "yes";

                    $add = $con->prepare("INSERT INTO student_subject_grade 
                        (student_id, student_subject_id, subject_id, course_id,
                        subject_title, remarks, is_transferee)
                        VALUES (:student_id, :student_subject_id, :subject_id, :course_id,
                        :subject_title, :remarks, :is_transferee)");

                    $add->bindParam(':student_id', $student_id);
                    $add->bindParam(':student_subject_id', $student_subject_id);
                    $add->bindParam(':subject_id', $subject_id);
                    $add->bindParam(':course_id', $course_id);
                    $add->bindParam(':subject_title', $subject_title);
                    $add->bindParam(':remarks', $remarks);
                    $add->bindParam(':is_transferee', $is_transferee);

                    if($add->execute()){
                      
                        AdminUser::success("Non-Credited subject #$subject_id has changed into Credited", "view_student_transferee_enrollment_review.php?inserted=true&id=$student_id&p_id=$pending_enrollees_id&e_id=$enrollment_id");
                        exit();
                    }

                }



             
            }
            # Update
        }
        if(isset($_POST['remove_status'])){

            $subject_id = $_POST['subject_id'];
            $student_id = $_POST['student_id'];
            $is_transferee = $_POST['is_transferee'];
            $student_subject_id = $_POST['student_subject_id'];
            $subject_title = $_POST['subject_title'];
            $course_id = $_POST['course_id'];

            # Credited Subject
            if($is_transferee == "yes"){

                $delete_credited = $con->prepare("DELETE FROM student_subject_grade
                    WHERE student_subject_id=:student_subject_id
                    AND student_id=:student_id
                    -- AND school_year_id=:school_year_id
                    ");

                $delete_credited->bindValue(":student_subject_id", $student_subject_id);
                $delete_credited->bindValue(":student_id", $student_id);
                // $delete_credited->bindValue(":school_year_id", $current_school_year_id);
    
                // echo $student_subject_id;

                if($delete_credited->execute()){
                    $delete = $con->prepare("DELETE FROM student_subject
                        WHERE student_subject_id=:student_subject_id
                        AND student_id=:student_id
                        AND school_year_id=:school_year_id");

                    $delete->bindValue(":student_subject_id", $student_subject_id);
                    $delete->bindValue(":student_id", $student_id);
                    $delete->bindValue(":school_year_id", $current_school_year_id);
                    if($delete->execute()){

                        AdminUser::remove("Subject Id #$subject_id has been removed", "view_student_transferee_enrollment_review.php?inserted=true&id=$student_id&p_id=$pending_enrollees_id&e_id=$enrollment_id");
                        exit();
                    }
                }



                
            }

            # Non credited Subject
            if($is_transferee == "no"){
                $delete = $con->prepare("DELETE FROM student_subject
                    WHERE student_subject_id=:student_subject_id
                    AND student_id=:student_id
                    AND school_year_id=:school_year_id");

                $delete->bindValue(":student_subject_id", $student_subject_id);
                $delete->bindValue(":student_id", $student_id);
                $delete->bindValue(":school_year_id", $current_school_year_id);
                if($delete->execute()){

                    AdminUser::remove("Subject Id #$subject_id has been removed", "view_student_transferee_enrollment_review.php?inserted=true&id=$student_id&p_id=$pending_enrollees_id&e_id=$enrollment_id");
                    exit();
                }
            }

        }

        if(isset($_POST['remove_added_btn'])){

            $student_subject_id = $_POST['student_subject_id'];
            $subject_id = $_POST['subject_id'];

            $delete = $con->prepare("DELETE FROM student_subject
                    WHERE student_subject_id=:student_subject_id
                    AND student_id=:student_id
                    AND school_year_id=:school_year_id");

            $delete->bindValue(":student_subject_id", $student_subject_id);
            $delete->bindValue(":student_id", $student_id);
            $delete->bindValue(":school_year_id", $current_school_year_id);

            if($delete->execute()){

                AdminUser::remove("Subject Id #$subject_id has been removed", "view_student_transferee_enrollment_review.php?inserted=true&id=$student_id&p_id=$pending_enrollees_id&e_id=$enrollment_id");
                exit();
            }
        }

        if($studentSubject->CheckIfStudentInsertedSubject($student_id,
            $enrollment_id, $current_school_year_id) == true){

            ?>
                <div class="row col-md-12">
                    <h4>Student Selected Section: <?php echo $section_name; ?> Subjects</h4>
                    <div class="card-body">
                        <h3 class="text-center text-primary"></h3>
                        <table id="dash-table" 
                            class="table table-striped table-bordered table-hover table-responsive" 
                            style="font-size:14px" cellspacing="0">
                            <thead>
                                <tr class="text-center"> 
                                    <th rowspan="1">Id</th>
                                    <th rowspan="1">Code</th>  
                                    <th rowspan="2">Description</th>
                                    <th colspan="1">Unit</th> 
                                    <th colspan="1">Type</th> 
                                    <th colspan="1">Grade Level</th> 
                                    <th colspan="1">Semester</th> 
                                    <th colspan="1">Status</th> 
                                    <th colspan="1">Action</th> 
                                </tr>	
                            </thead> 
                            <tbody>
                                <?php

                                # For Pending Grade 11 1st Semester Only
                                $semester = "First";

                                $transfereeSubjects = $con->prepare("SELECT 
                                    t1.is_transferee, t1.is_final,
                                    t1.student_subject_id as t2_student_subject_id, 
                                    t3.student_subject_id as t3_student_subject_id,
                                    t2.* FROM student_subject as t1

                                    INNER JOIN subject as t2 ON t2.subject_id = t1.subject_id
                                    LEFT JOIN student_subject_grade as t3 ON t3.student_subject_id = t1.student_subject_id

                                    WHERE t1.student_id=:student_id
                                    AND t1.is_final=0
                                    AND t1.school_year_id=:school_year_id
                                    AND t2.course_id=:course_id
                                    ");

                                $transfereeSubjects->bindValue(":student_id", $student_id);
                                $transfereeSubjects->bindValue(":school_year_id", $current_school_year_id);
                                $transfereeSubjects->bindValue(":course_id", $selected_course_id);
                                $transfereeSubjects->execute();

                                    $totalUnits = 0;

                                    if($transfereeSubjects != null){
                                        $applyTabIsAvailable = true;

                                        foreach ($transfereeSubjects as $key => $row) {

                                            $unit = $row['unit'];
                                            $subject_id = $row['subject_id'];
                                            $subject_title = $row['subject_title'];
                                            $course_id = $row['course_id'];
                                            $subject_code = $row['subject_code'];
                                            $subject_type = $row['subject_type'];
                                            $semester = $row['semester'];
                                            $course_level = $row['course_level'];
                                            $is_transferee = $row['is_transferee'];

                                            // $is_final = $row['is_final'];

                                            $totalUnits += $unit;

                                            $text = "";

                                            if($is_transferee == "yes"){
                                                $text = "
                                                    <button class='btn btn-sm btn'>Credited</button>
                                                ";
                                            }else{
                                                $text = "
                                                    <button class='btn btn-sm btn'>Non-Credited</button>
                                                ";             
                                            }

                                            $student_subject_id = $row['t2_student_subject_id'];
                                        
                                            echo "
                                                <tr class='text-center'>
                                                    <td>$subject_id</td>
                                                    <td>$subject_code</td>
                                                    <td>$subject_title</td>
                                                    <td>$unit</td>
                                                    <td>$subject_type</td>
                                                    <td>$course_level</td>
                                                    <td>$semester</td>
                                                    <td>$text</td>
                                                    <td>
                                                        <form method='POST'>
                                                            <input type='hidden' name='is_transferee' value='$is_transferee'>
                                                            <input type='hidden' name='student_id' value='$student_id'>
                                                            <input type='hidden' name='subject_id' value='$subject_id'>
                                                            <input type='hidden' name='student_subject_id' value='$student_subject_id'>
                                                            <input type='hidden' name='subject_title' value='$subject_title'>
                                                            <input type='hidden' name='course_id' value='$course_id'>
                                                            <button type='submit' name='change_status' class='btn btn-outline-success'>Change</button>
                                                            <button 
                                                                type='submit'
                                                                name='remove_status' class='btn btn-danger'
                                                                onclick=\"return confirm('Are you sure you want to remove?');\"
                                                                >Remove
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            ";
                                        }
                                    } 
                                ?>
                                
                            </tbody>
                        </table>


                        <h3 class="mb-3 text-muted">Added Subjects</h3>
                        <table class="table table-bordered table-hover "  style="font-size:15px" cellspacing="0"> 
                            <thead>
                                <tr class="text-center"">
                                    <th>Id</th>
                                    <th>Section</th>
                                    <th>Code</th>
                                    <th>Description</th>
                                    <th>Unit</th>
                                    <th>Pre-Requisite</th>
                                    <th>Type</th>
                                    <th></th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php 

                                    $addedSubjects = $con->prepare("SELECT 
                                        t1.is_transferee, t1.is_final,
                                        t1.student_subject_id as t2_student_subject_id, 
                                        t3.student_subject_id as t3_student_subject_id,

                                        t4.program_section,
                                        t2.* FROM student_subject as t1

                                        INNER JOIN subject as t2 ON t2.subject_id = t1.subject_id
                                        LEFT JOIN course as t4 ON t4.course_id = t2.course_id
                                        LEFT JOIN student_subject_grade as t3 ON t3.student_subject_id = t1.student_subject_id

                                        WHERE t1.student_id=:student_id
                                        AND t1.is_final=0
                                        AND t1.school_year_id=:school_year_id
                                        AND t2.course_id!=:course_id

                                        ");

                                    $addedSubjects->bindValue(":student_id", $student_id);
                                    $addedSubjects->bindValue(":school_year_id", $current_school_year_id);
                                    $addedSubjects->bindValue(":course_id", $selected_course_id);
                                    $addedSubjects->execute();
                                
                                    // $sql = $con->prepare("SELECT 
                                    //     t1.program_section,
                                    //     t2.* 
                                        
                                    //     FROM course as t1

                                    //     INNER JOIN subject as t2 ON t2.course_id = t1.course_id

                                    //     WHERE t1.course_id!=:course_id
                                    //     AND t1.active='yes'
                                    //     AND t1.course_level=:course_level
                                    //     AND t2.semester=:semester
                                    //     ");

                                    // $sql->bindValue(":course_id", $selected_course_id);
                                    // $sql->bindValue(":course_level", $student_course_level);
                                    // $sql->bindValue(":semester", $current_school_year_period);
                                    // $sql->execute();

                                    if($addedSubjects->rowCount() > 0){

                                        while($row = $addedSubjects->fetch(PDO::FETCH_ASSOC)){

                                            $subject_id = $row['subject_id'];
                                            $subject_code = $row['subject_code'];
                                            $subject_title = $row['subject_title'];
                                            $pre_requisite = $row['pre_requisite'];
                                            $subject_type = $row['subject_type'];
                                            $unit = $row['unit'];
                                            $course_level = $row['course_level'];
                                            $program_section = $row['program_section'];
                                            $program_section = $row['program_section'];
                                            $student_subject_id = $row['t2_student_subject_id'];

                                            echo "
                                                <tr class='text-center'>
                                                    <td>$subject_id</td>
                                                    <td>$program_section</td>
                                                    <td>$subject_code</td>
                                                    <td>$subject_title</td>
                                                    <td>$unit</td>
                                                    <td>$pre_requisite</td>
                                                    <td>$subject_type</td>
                                                    <td>
                                                        <form method='POST'>

                                                            <input type='hidden' name='student_subject_id' value='$student_subject_id'>
                                                            <input type='hidden' name='subject_id' value='$subject_id'>
                                                            <button 
                                                                type='submit'
                                                                name='remove_added_btn' class='btn btn-danger'
                                                                onclick=\"return confirm('Are you sure you want to remove?');\"
                                                                >Remove
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            ";
                                        }

                                    }
                                ?>

                            </tbody>

                            
                        </table>

                        <button type="button" onclick="confirmUpdate(<?php echo $pending_enrollees_id; ?>,
                            <?php echo $student_id; ?>, <?php echo $enrollment_id; ?>)" 
                            name=""
                        class="btn btn-sm btn-success">Mark as final</button>

                        <a href="../admission/transferee_process_enrollment.php?step3=true&id=<?php echo $pending_enrollees_id;?>&selected_course_id=<?php echo $selected_course_id?>">
                            <button class="btn btn-sm btn-primary">Go back</button>
                        </a>
                    </div>
                </div>
            <?php
        }else{
            echo "
                <div class='row col-md-12'>
                    <h4 class='text-center'>No Inserted Subjects</h4>
                </div>
            ";
        }
        
    }
 }
?>

<script>

    function confirmUpdate(pending_enrollees_id, student_id, enrollment_id) {
        Swal.fire({
            icon: 'question',
            title: 'Are you sure',
            showCancelButton: true,
            confirmButtonText: 'Yes',
            cancelButtonText: 'No'
        }).then((result) => {
            if (result.isConfirmed) {
                // console.log("nice")
                $.ajax({
                    url: '../ajax/pending/update_pending.php',
                    type: 'POST',
                    data: {
                       pending_enrollees_id, student_id, enrollment_id
                    },
                    success: function(response) {
                        if(response == "success"){
                            window.location.href = '../admission/index.php';
                        }

                        // console.log(response)
                    },
                    error: function(xhr, status, error) {
                        // Handle any errors here
                        console.log(error);
                    }
                });
            } else {
                // User clicked "No," perform alternative action or do nothing
            }
        });
    }
</script>