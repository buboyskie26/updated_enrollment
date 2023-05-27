<?php
 
    include('../registrar_enrollment_header.php');
    include('../../enrollment/classes/StudentEnroll.php');
    include('../../enrollment/classes/Section.php');
    include('../../enrollment/classes/Enrollment.php');
    include('../classes/Course.php');

    if(!AdminUser::IsRegistrarAuthenticated()){
        header("Location: /dcbt/adminLogin.php");
        exit();
    }


    if(isset($_GET['id'])){

        $pending_enrollees_id = $_GET['id'];

        unset($_SESSION['pending_enrollees_id']);
        unset($_SESSION['process_enrollment']);

        $studentEnroll = new StudentEnroll($con);
        $enrollment = new Enrollment($con, $studentEnroll);
        
        $enrollment_form_id = $enrollment->GenerateEnrollmentFormId();

        if (!isset($_SESSION['enrollment_form_id'])) {
            $enrollment_form_id = $enrollment->GenerateEnrollmentFormId();
            $_SESSION['enrollment_form_id'] = $enrollment_form_id;

        } else {
            $enrollment_form_id = $_SESSION['enrollment_form_id'];
        }

        $school_year_obj = $studentEnroll->GetActiveSchoolYearAndSemester();

        $current_school_year_id = $school_year_obj['school_year_id'];
        $current_school_year_term = $school_year_obj['term'];
        $current_school_year_period = $school_year_obj['period'];

        $sql = $con->prepare("SELECT * FROM pending_enrollees
                WHERE pending_enrollees_id=:pending_enrollees_id
            ");

        $sql->bindValue(":pending_enrollees_id", $pending_enrollees_id);
        $sql->execute();

        $row = null;

        $course_id = 0;

        if($sql->rowCount() > 0){

            $row = $sql->fetch(PDO::FETCH_ASSOC);

            $program_id = $row['program_id'];

            $firstname = $row['firstname'];

            $middle_name = $row['middle_name'];
            $lastname = $row['lastname'];
            $birthday = $row['birthday'];
            $address = $row['address'];
            $sex = $row['sex'];
            $contact_number = $row['contact_number'];
            $date_creation = $row['date_creation'];
            $student_status = $row['student_status'];
            $email = $row['email'];
            $pending_enrollees_id = $row['pending_enrollees_id'];
            $password = $row['password'];
            $civil_status = $row['civil_status'];
            $nationality = $row['nationality'];
            $age = $row['age'];
            $guardian_name = $row['guardian_name'];
            $guardian_contact_number = $row['guardian_contact_number'];
            $lrn = $row['lrn'];
            $birthplace = $row['birthplace'];
            $religion = $row['religion'];
            $email = $row['email'];

            $program = $con->prepare("SELECT acronym FROM program
                WHERE program_id=:program_id
                LIMIT 1
            ");
            $program->bindValue(":program_id", $program_id);
            $program->execute();

            $program_acronym = $program->fetchColumn();

            $student_fullname = $firstname . " " . $lastname;

            // $program_section = $row['program_name'];
            // $date_creation = $row['date_creation'];

            if(isset($_GET['id']) && isset($_GET['step1'])){
                ?>
                    <div class="row col-md-12">

                        <!-- Enrollment Form -->
                        <div class="container">
                            <h3 class="text-center text-primary">Enrollment Form</h3>
                            <div class="card ">
                                <div class="card-body">
                                    <div class="row col-md-12">
                                        <div class="mb-4 col-md-3">
                                            <label for="">Form Id</label>
                                            <input readonly style="width: 100%;" type="text" 
                                                value='<?php echo $enrollment_form_id;?>' class="form-control">
                                        </div>
                                    
                                        <div class="mb-4 col-md-3">
                                            <label for="">Admission Type</label>
                                            <input readonly style="width: 100%;" type="text" 
                                                value='New' class="form-control">
                                        </div>

                                        <div class="mb-4 col-md-3">
                                            <label for="">Status</label>
                                            <input readonly style="width: 100%;" type="text" 
                                                value='Evaluation' class="form-control">
                                        </div>

                                        <div class="mb-4 col-md-3">
                                            <label for="">Date Submitted</label>
                                            <input readonly style="width: 100%;" type="text" 
                                                value='<?php echo $date_creation?>' class="form-control">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Navbar Step 1 -->

                        <h4 class="text-center">STEP 1 ~ Check Details</h4>

                        <!-- Student  Details -->
                        <div class="container">
                            <h3 class="text-center text-primary">Student Details</h3>

                            <div class="card mb-4">
                                <div class="card-body">
                                    <div class="row col-md-12">
                                        <div class="mb-4 col-md-4">
                                            <label for="">First Name</label>
                                            <input readonly style="width: 100%;" type="text" 
                                                value='<?php echo $firstname;?>' class="form-control">
                                        </div>
                                        
                                        <div class="mb-4 col-md-4">
                                            <label for="">Middle Name</label>
                                            <input readonly style="width: 100%;" type="text" 
                                                value='<?php echo $middle_name?>' class="form-control">
                                        </div>

                                        <div class="mb-4 col-md-4">
                                            <label for="">Last Name</label>
                                            <input readonly style="width: 100%;" type="text" 
                                                value='<?php echo $lastname?>' class="form-control">
                                        </div>
                                    </div>
                                </div>
                            </div>



                            <a href="process_enrollment.php?step2=true&id=<?php echo $pending_enrollees_id?>">
                                <button class="btn-sm btn-primary">Proceed To Step 2</button>

                            </a>
                        </div>

                    </div>
                <?php
            }

            if(isset($_GET['id']) && isset($_GET['step2'])){

                if(isset($_POST['pending_choose_section'])
                    && isset($_POST['selected_course_id'])
                    ){
                    $selected_course_id_value = $_POST['selected_course_id'];

                    echo $selected_course_id_value;
                    $section_url = "process_enrollment.php?step3=true&id=$pending_enrollees_id&selected_course_id=$selected_course_id_value";

                    header("Location: $section_url");
                    // exit();
                }
                ?>
                <div class="row col-md-12">

                    <!-- Enrollment Form -->
                    <div class="container">

                        <div class="card">
                        <h3 class="mt-2 text-center text-primary">Enrollment Form</h3>

                            <div class="card-body">
                            <div class="row col-md-12">
                                <div class="mb-4 col-md-3">
                                    <label for="">Form Id</label>
                                    <input readonly style="width: 100%;" type="text" 
                                        value='<?php echo $enrollment_form_id;?>' class="form-control">
                                </div>
                                <div class="mb-4 col-md-3">
                                    <label for="">Name</label>
                                    <input readonly style="width: 100%;" type="text" 
                                        value='<?php echo $student_fullname;?>' class="form-control">
                                </div>
                                <div class="mb-4 col-md-3">
                                    <label for="">Admission Type</label>
                                    <input readonly style="width: 100%;" type="text" 
                                        value='New' class="form-control">
                                    </div>
                                <div class="mb-4 col-md-3">
                                    <label for="">Date Submitted</label>
                                    <input readonly style="width: 100%;" type="text" 
                                        value='<?php echo $date_creation?>' class="form-control">
                                </div>
                        </div> 
                            </div>
                        </div>


                    </div>

                    <h4 class="text-center">STEP 2 ~ Check Details</h4>

                    <!-- Enrollmetn Details -->
                    <div class="container">
                        <h3 class="text-center text-primary">Enrollment Details</h3>
                        <div class="row mb-3">
                            <div class=" mb-3 col-md-4">
                                <label for="">Term</label>
                                <input readonly style="width: 100%;" type="text" 
                                    value='<?php echo $current_school_year_term;?>' class="form-control">
                            </div>
                            <div class="mb-4 col-md-4">
                                <label for="">Track</label>
                                <input readonly style="width: 100%;" type="text" value='Academic' class="form-control">
                            </div>
                            
                            <div class="mb-4 col-md-4">
                                <label for="">Strand</label>
                                <input readonly style="width: 100%;" type="text"
                                    value='<?php echo $program_acronym;?>' class="form-control">
                            </div>
                        </div>

                    </div>

                    <!-- Available Section -->
                    <div class="container mt-4 mb-2">

                        <h3 class="text-center text-success">Available Section</h3>
                        <!-- <a href="../section/create.php">
                            <button class="mb-2 btn btn-sm btn-success" onclick="<?php 
                                $_SESSION['pending_enrollees_id'] = $pending_enrollees_id; 
                                $_SESSION['process_enrollment'] = 'non_transferee';
                                ?>">
                                Create Section
                            </button>

                        </a> -->

                        <form method="POST">
                            <table id="availableTransfereeSectionTable" class="table table-striped table-bordered table-hover "  style="font-size:13px" cellspacing="0"  > 
                                <thead>
                                    <tr class="text-center"> 
                                        <th rowspan="2">Section Id</th>
                                        <th rowspan="2">Section Name</th>
                                        <th rowspan="2">Student</th>
                                        <th rowspan="2">Capacity</th>
                                        <th rowspan="2">Term</th>
                                        <th rowspan="2"></th>
                                    </tr>	
                                </thead> 	
                                <tbody>
                                    <?php
                                        $course_level = 11;
                                        $active = "yes";
                                        # Only Available now.
                                        $sql = $con->prepare("SELECT * FROM course
                                        WHERE program_id=:program_id
                                        AND active=:active
                                        AND course_level=:course_level
                                        ");
                                        $sql->bindValue(":program_id", $program_id);
                                        $sql->bindValue(":active", $active);
                                        $sql->bindValue(":course_level", $course_level);

                                        $sql->execute();
                                    
                                        if($sql->rowCount() > 0){

                                            while($get_course = $sql->fetch(PDO::FETCH_ASSOC)){

                                                $course_id = $get_course['course_id'];
                                                $program_section = $get_course['program_section'];
                                                $capacity = $get_course['capacity'];
                                                $school_year_term = $get_course['school_year_term'];
                                                $section = new Section($con, $course_id);

                                                $section_obj = $section->GetSectionObj($course_id);

                                                $totalStudent = $section->GetTotalNumberOfStudentInSection($course_id, $current_school_year_id);
                                                $capacity = $section_obj['capacity'];

                                                $program_id = $section_obj['program_id'];
                                                $course_level = $section_obj['course_level'];

                                                $removeSection = "removeSection($course_id, \"$program_section\")";

                                                // echo $totalStudent;
                                                // echo $program_id;


                                                $new_program_section = $section->AutoCreateAnotherSection($program_section);

                                                // echo $program_section;
                                                // echo "<br>";
                                                // echo $new_program_section;

                                                echo "
                                                <tr class='text-center'>
                                                    <td>$course_id</td>
                                                    <td>$program_section</td>
                                                    <td>$totalStudent</td>
                                                    <td>$capacity</td>
                                                    <td>$school_year_term</td>
                                                    <td>
                                                        <input name='selected_course_id' class='radio' value='$course_id' type='radio'>
                                                    </td>
                                                    <td>
                                                        <i onclick='$removeSection' style='cursor: pointer; color: orange; " . ($totalStudent != 0 ? "display: none;" : "") . "' class='fas fa-times'></i>
                                                    </td>
                                                </tr>
                                            ";
                                            }
                                            
                                        }
                                    ?>
                                </tbody>
                            </table>

                            <button type="submit" name="pending_choose_section"
                                class="btn btn-primary">Proceed to Step 3</button>

                        </form>
                    </div> 
                </div>

                <script>
                    function removeSection(course_id, program_section){
                        Swal.fire({
                                icon: 'question',
                                title: `I agreed to removed ${program_section}.`,
                                showCancelButton: true,
                                confirmButtonText: 'Yes',
                                cancelButtonText: 'No'
                            }).then((result) => {
                            if (result.isConfirmed) {
                                // console.log("nice")
                                $.ajax({
                                    url: '../ajax/section/remove_section.php',
                                    type: 'POST',
                                    data: {
                                        course_id
                                    },
                                    success: function(response) {

                                        $('#availableTransfereeSectionTable').load(
                                            location.href + ' #availableTransfereeSectionTable'
                                        );

                                    },
                                    error: function(xhr, status, error) {
                                        // handle any errors here
                                    }
                                });
                            } else {
                                // User clicked "No," perform alternative action or do nothing
                            }
                        });
                    }
                </script>
                <?php
            }

            if(isset($_GET['id']) 
                && isset($_GET['step3'])
                && isset($_GET['selected_course_id'])
                ){

                $selected_course_id = $_GET['selected_course_id'];

                $section = new Section($con, $selected_course_id);

                $section_name = $section->GetSectionName();
                $section_course_level = $section->GetSectionGradeLevel();
                
                // echo $section_course_level;
                
                ?>
                    <div class="row col-md-12">
                        <div class="container mt-4 mb-2">
                            <h4 class="mb-3 text-center text-success"><?php echo $section_name;?> Subjects </h4>
                            <h5 class="mb-3 text-center text-muted">S.Y <?php echo $current_school_year_term;?> <?php echo $current_school_year_period;?> Semester</h5>

                            <table id="courseTable" class="table table-striped table-bordered table-hover "  style="font-size:13px" cellspacing="0"  > 
                                <thead>
                                    <tr class="text-center"> 
                                        <th rowspan="2">Id</th>
                                        <th rowspan="2">Code</th>
                                        <th rowspan="2">Description</th>
                                        <th rowspan="2">Unit</th>
                                        <th rowspan="2">Type</th>
                                        <th colspan="4">Schedule</th> 
                                    </tr>	
                                    <tr class="text-center">
                                        <th>Day</th>
                                        <th>Time</th>
                                        <th>Room</th> 
                                        <th>Instructor</th> 
                                    </tr>	
                                </thead> 	
                                <tbody>
                                    <?php
                                        $course_level = 11;
                                        $active = "yes";

                                        # Only Available now.

                                        $sql = $con->prepare("SELECT 
                                            t1.*,
                                            t3.room, t3.schedule_time, t3.schedule_day ,
                                            t4.firstname,
                                            t4.lastname
                                            
                                            FROM subject as t1

                                            INNER JOIN course as t2 ON t2.course_id = t1.course_id
                                            LEFT JOIN subject_schedule as t3 ON t3.subject_id = t1.subject_id
                                            LEFT JOIN teacher as t4 ON t4.teacher_id = t3.teacher_id

                                            WHERE t1.course_id=:course_id
                                            AND t1.semester=:semester
                                            ");

                                        $sql->bindValue(":course_id", $selected_course_id);
                                        $sql->bindValue(":semester", $current_school_year_period);
                                        // $sql->bindValue(":course_level", $course_level);

                                        $sql->execute();
                                    
                                        if($sql->rowCount() > 0){

                                            while($row = $sql->fetch(PDO::FETCH_ASSOC)){

                                                $subject_id = $row['subject_id'];
                                                $subject_code = $row['subject_code'];
                                                $subject_title = $row['subject_title'];
                                                $unit = $row['unit'];
                                                $subject_type = $row['subject_type'];

                                                $room = $row['room'];
                                                $schedule_time = $row['schedule_time'];
                                                $schedule_day = $row['schedule_day'];
                                                
                                                $teacher_firstname = $row['firstname'];
                                                $teacher_lastname = $row['lastname'];

                                                // echo $subject_id;
                                                echo "
                                                <tr class='text-center'>
                                                    <td>$subject_id</td>
                                                    <td>$subject_code</td>
                                                    <td>$subject_title</td>
                                                    <td>$unit</td>
                                                    <td>$subject_type</td>
                                                    <td>$schedule_day</td>
                                                    <td>$schedule_time</td>
                                                    <td>$room</td>
                                                    <td>$teacher_firstname $teacher_lastname</td>
                                                </tr>
                                            ";
                                            }
                                            
                                        }
                                    ?>
                                </tbody>
                            </table>
                            <!-- <button type="button" name="pending_choose_section" class="btn btn-primary">Confirm</button> -->
                            <button onclick='confirmPendingValidation("<?php echo $firstname ?>", "<?php echo $lastname ?>", "<?php echo $middle_name ?>", "<?php echo $password ?>", "<?php echo $program_id ?>", "<?php echo $civil_status ?>", "<?php echo $nationality ?>", "<?php echo $contact_number ?>", "<?php echo $birthday ?>", "<?php echo $age ?>", "<?php echo $guardian_name ?>", "<?php echo $guardian_contact_number ?>", "<?php echo $sex ?>", "<?php echo $student_status ?>", "<?php echo $pending_enrollees_id ?>", "<?php echo $address; ?>", "<?php echo $lrn; ?>", "<?php echo $selected_course_id; ?>", "<?php echo $enrollment_form_id; ?>", "<?php echo $religion; ?>", "<?php echo $birthplace; ?>", "<?php echo $email; ?>")' name='confirm_validation_btn' class='btn btn-success btn-sm'>Confirm</button>
                            
                        </div> 
                    </div>

                    <script>
                        function confirmPendingValidation(firstname, lastname, middle_name, password,
                            program_id, civil_status, nationality, contact_number, birthday, age,
                            guardian_name, guardian_contact_number, sex, student_status,
                            pending_enrollees_id, address, lrn,
                            selected_course_id, enrollment_form_id,
                            religion, birthplace, email){

                            selected_course_id = parseInt(selected_course_id);
                            program_id = parseInt(program_id);
                            age = parseInt(age);
                            pending_enrollees_id = parseInt(pending_enrollees_id);

                            $.ajax({
                                url: '../ajax/enrollee/pending_steps_confirmation.php',
                                type: 'POST',
                                data: {
                                    firstname, lastname, middle_name,
                                    password, program_id, civil_status, nationality, 
                                    contact_number, birthday, age, guardian_name, 
                                    guardian_contact_number, sex, student_status, 
                                    pending_enrollees_id, address, lrn, selected_course_id,
                                    enrollment_form_id, religion, birthplace, email
                                },
                                success: function(response) {
    
                                    // console.log(response);
                                    alert(response);
                                    window.location.href = 'index.php';
                                    // location.reload();
                                },
                                error: function(xhr, status, error) {
                                    // handle any errors here
                                }
                            });

                        }
                    </script>
                <?php
            }
        }



    }

?>

