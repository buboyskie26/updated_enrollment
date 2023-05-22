<?php 

    include('../cashierHeader.php');
    include('../../enrollment/classes/StudentEnroll.php');
    include('../../enrollment/classes/Schedule.php');
    include('../../enrollment/classes/Section.php');
    include('../../enrollment/classes/Pending.php');
    // include('../classes/AdminUser.php');

    if(AdminUser::IsCashierAuthenticated()){

        if(isset($_GET['id'])){

            $pending_enrollees_id = $_GET['id'];

            $username = "";

            $enroll = new StudentEnroll($con);

            $pending = new Pending($con);
            $section = new Section($con, null);

            $school_year_obj = $enroll->GetActiveSchoolYearAndSemester();

            $current_school_year_id = $school_year_obj['school_year_id'];
            $current_school_year_term = $school_year_obj['term'];
            $current_school_year_period = $school_year_obj['period'];

            $sql = $con->prepare("SELECT t1.*, t2.* FROM pending_enrollees as t1

                LEFT JOIN program as t2 ON t1.program_id = t2.program_id

                WHERE t1.pending_enrollees_id=:pending_enrollees_id");

            $sql->bindValue(":pending_enrollees_id", $pending_enrollees_id);
            $sql->execute();

    
            if($sql->rowCount() > 0){
                $row = $sql->fetch(PDO::FETCH_ASSOC);

                $acronym = $row['acronym'];
                $date_creation = $row['date_creation'];
                // $firstname = $row['firstname'];
                // $lastname = $row['lastname'];
                // $fullname = $firstname . " " . $lastname;
                $email = $row['email'];
                // $contact_number = $row['contact_number'];
                $address = $row['address'];


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
                $lrn = $row['lrn'];

                $get_available_section = $con->prepare("SELECT 
                    course_id, capacity, program_section

                    FROM course

                    WHERE program_id=:program_id
                    AND active=:active
                    AND course_level=:course_level
                    AND is_full=:is_full
                    AND school_year_term=:school_year_term
                    ORDER BY course_id DESC
                    LIMIT 1");

                // $program_id = 0;
                $get_available_section->bindValue(":program_id", $program_id);
                $get_available_section->bindValue(":course_level", 11);
                $get_available_section->bindValue(":active", "yes");
                $get_available_section->bindValue(":is_full", "no");
                $get_available_section->bindValue(":school_year_term", $current_school_year_term);
                $get_available_section->execute();

                $section_output = "";
                
                if($get_available_section->rowCount() > 0){

                    $available_section = $get_available_section->fetch(PDO::FETCH_ASSOC);

                    $default_course_id = $available_section['course_id'];
                    $capacity = $available_section['capacity'];
                    $program_section = $available_section['program_section'];

                    // echo $default_course_id;
                    // echo "<br>";

                    $enrolledStudents = $enroll->CheckNumberOfStudentInSection(
                        $default_course_id, "First");

                        // echo $enrolledStudents;

                    if($enrolledStudents >= $capacity){
                      
                        $section_output = $section->AutoCreateAnotherSection($program_section);
                      
                    }else{
                        $section_output = $program_section;
                    }
                }

            ?>
                <div class="row col-md-12">

                    <div class="col-lg-10 offset-md-1">
                        <h4 class="text-center text-muted mb-3">SHS Pre-Enrollment</h4>
                        <div class="card mb-4">
                            <div class="card-body">
                            <div class="row">
                                <div class="col-sm-3">
                                <p class="mb-0">Course</p>
                                </div>
                                <div class="col-sm-9">
                                <p class="text-muted mb-0"><?php echo $acronym;?></p>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-sm-3">
                                    <p class="mb-0">Year Level</p>
                                </div>
                                <div class="col-sm-9">
                                    <p class="text-muted mb-0">Grade 11</p>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-sm-3">
                                    <p class="mb-0">Semester</p>
                                </div>
                                <div class="col-sm-9">
                                    <p class="text-muted mb-0"><?php echo $current_school_year_period; ?></p>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-sm-3">
                                    <p class="mb-0">School Year</p>
                                </div>
                                <div class="col-sm-9">
                                    <p class="text-muted mb-0"><?php echo $current_school_year_term; ?></p>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-sm-3">
                                    <p class="mb-0">Date</p>
                                </div>
                                <div class="col-sm-9">
                                    <p class="text-muted mb-0"><?php echo $date_creation; ?></p>
                                </div>
                            </div>
                            <hr>
                                <div class="row">
                                    <div class="col-sm-3">
                                        <p class="mb-0">Status</p>
                                    </div>
                                    <div class="col-sm-9">
                                        <p class="text-muted mb-0">Pending</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-10 offset-md-1">
                        <h4 class="text-center text-muted mb-3">Personal Info</h4>
                        <div class="card mb-4">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-sm-3">
                                    <p class="mb-0">Full Name</p>
                                    </div>
                                    <div class="col-sm-9">
                                    <p class="text-muted mb-0"><?php echo $fullname?></p>
                                    </div>
                                </div>
                                <hr>
                                <div class="row">
                                    <div class="col-sm-3">
                                        <p class="mb-0">Email</p>
                                    </div>
                                    <div class="col-sm-9">
                                        <p class="text-muted mb-0"><?php echo $email?></p>
                                    </div>
                                </div>
                                <hr>
                                <div class="row">
                                    <div class="col-sm-3">
                                        <p class="mb-0">Phone</p>
                                    </div>
                                    <div class="col-sm-9">
                                        <p class="text-muted mb-0"><?php echo $contact_number?></p>
                                    </div>
                                </div>
                                <hr>
                                <div class="row">
                                    <div class="col-sm-3">
                                        <p class="mb-0">Address</p>
                                    </div>
                                    <div class="col-sm-9">
                                        <p class="text-muted mb-0"><?php echo $address?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-10 offset-md-1">
                        <h4 class="text-center text-muted mb-3">Subject</h4>
                        <div style="margin-left: 6px;" class="row">
                            <div class="col-md-4">
                                <h5 class="text-success mb-0">Course Destination:</h5>
                            </div>
                            <div style="padding: 0px; font-weight:500;" class="col-md-8">
                                <p class="mb-0 text-primary"><?php echo $section_output;?></p>
                            </div>
                        </div>
                        
                        <div class="card mb-4">
                            <div class="card-body">
                                <table id="dash-table" class="table table-striped table-bordered table-hover table-responsive" style="font-size:12px" cellspacing="0">
                                    <thead>
                                    <tr class="text-center"> 
                                        <th rowspan="1">Id</th>
                                        <th rowspan="1">Subject Code</th>  
                                        <th rowspan="2">Name</th>
                                        <th colspan="1">Unit</th> 
                                        </tr>	
                                    </thead> 
                                    <tbody>
                                        <?php

                                        # For Pending Grade 11 1st Semester Only
                                        $semester = "First";
                                        $recommendedSubject = $con->prepare("SELECT * FROM subject_program
                                            WHERE program_id=:program_id
                                            AND course_level=11
                                            AND semester=:semester
                                            ");

                                        $recommendedSubject->bindValue(":program_id", $program_id);
                                        $recommendedSubject->bindValue(":semester", $semester);
                                        $recommendedSubject->execute();
                                        $totalUnits = 0;

                                            if($recommendedSubject != null){
                                                $applyTabIsAvailable = true;

                                                foreach ($recommendedSubject as $key => $row) {

                                                    $unit = $row['unit'];

                                                    $totalUnits += $unit;

                                                    // $totalUnits = 0;
                                                    echo '<tr class="text-center">'; 
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
                            </div>
                        </div>
                    </div>

                    <div class="text-center">
                        <button onclick='confirmPendingValidation("<?php echo $firstname ?>", "<?php echo $lastname ?>", "<?php echo $middle_name ?>", "<?php echo $password ?>", "<?php echo $program_id ?>", "<?php echo $civil_status ?>", "<?php echo $nationality ?>", "<?php echo $contact_number ?>", "<?php echo $birthday ?>", "<?php echo $age ?>", "<?php echo $guardian_name ?>", "<?php echo $guardian_contact_number ?>", "<?php echo $sex ?>", "<?php echo $student_status ?>", "<?php echo $program_section ?>", "<?php echo $pending_enrollees_id ?>", "<?php echo $address; ?>", "<?php echo $lrn; ?>")' name='confirm_validation_btn' class='btn btn-success btn-sm'>Confirm</button>

                        <button class="btn btn-danger">Reject</button>
                    </div>

                </div>

                <script>
                // alert('qwe');

                    function confirmPendingValidation(firstname, lastname, middle_name, password,
                        program_id, civil_status, nationality, contact_number, birthday, age,
                        guardian_name, guardian_contact_number, sex, student_status,
                        program_section,pending_enrollees_id, address, lrn){

                            // alert('confirm');
                        // console.log('click');
                        $.ajax({
                            url: '../ajax/enrollee/registrar_confirm_pending.php',
                            type: 'POST',
                            data: {
                                firstname, lastname, middle_name,
                                password, program_id, civil_status, nationality, 
                                contact_number, birthday, age, guardian_name, 
                                guardian_contact_number, sex, student_status, 
                                program_section, pending_enrollees_id, address, lrn
                            },
                            success: function(response) {

                                // handle the response from the server here
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

    }else if(AdminUser::IsCashierAuthenticated()){


        if(isset($_GET['id'])){

            $pending_enrollees_id = $_GET['id'];

            $username = "";

            $enroll = new StudentEnroll($con);

            $pending = new Pending($con);
            $section = new Section($con, null);

            $school_year_obj = $enroll->GetActiveSchoolYearAndSemester();

            $current_school_year_id = $school_year_obj['school_year_id'];
            $current_school_year_term = $school_year_obj['term'];
            $current_school_year_period = $school_year_obj['period'];

            $sql = $con->prepare("SELECT t1.*, t2.* FROM pending_enrollees as t1

                LEFT JOIN program as t2 ON t1.program_id = t2.program_id

                WHERE t1.pending_enrollees_id=:pending_enrollees_id");

            $sql->bindValue(":pending_enrollees_id", $pending_enrollees_id);
            $sql->execute();

            
    
            if($sql->rowCount() > 0){
                $row = $sql->fetch(PDO::FETCH_ASSOC);

                $acronym = $row['acronym'];
                $date_creation = $row['date_creation'];
                // $firstname = $row['firstname'];
                // $lastname = $row['lastname'];
                // $fullname = $firstname . " " . $lastname;
                $email = $row['email'];
                // $contact_number = $row['contact_number'];
                $address = $row['address'];


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
                $lrn = $row['lrn'];

                $get_available_section = $con->prepare("SELECT course_id, capacity, program_section

                    FROM course

                    WHERE program_id=:program_id
                    AND active=:active
                    AND course_level=:course_level
                    AND is_full=:is_full
                    AND school_year_term=:school_year_term
                    ORDER BY course_id DESC
                    LIMIT 1");

                // $program_id = 0;
                $get_available_section->bindValue(":program_id", $program_id);
                $get_available_section->bindValue(":course_level", 11);
                $get_available_section->bindValue(":active", "yes");
                $get_available_section->bindValue(":is_full", "no");
                $get_available_section->bindValue(":school_year_term", $current_school_year_term);
                $get_available_section->execute();
                
                if($get_available_section->rowCount() > 0){

                    $available_section = $get_available_section->fetch(PDO::FETCH_ASSOC);

                    $default_course_id = $available_section['course_id'];
                    $capacity = $available_section['capacity'];
                    $program_section = $available_section['program_section'];

                    // echo $default_course_id;
                    // echo "<br>";

                    $enrolledStudents = $enroll->CheckNumberOfStudentInSection(
                        $default_course_id, "First");

                        // echo $enrolledStudents;

                    if($enrolledStudents >= $capacity){

                        // echo "eee";
                        // echo $capacity;
                        // echo "$program_section is now full";
                        // echo "<br>";
                        $program_section = $section->AutoCreateAnotherSection($program_section);
                        // echo $nextSection . " is preferred";
                        // echo $program_section;

                    }

                }

            ?>
                <div class="row col-md-12">

                    <div class="col-lg-10 offset-md-1">
                        <h4 class="text-center text-muted mb-3">Pre-Enrollment</h4>
                        <div class="card mb-4">
                            <div class="card-body">
                            <div class="row">
                                <div class="col-sm-3">
                                <p class="mb-0">Course</p>
                                </div>
                                <div class="col-sm-9">
                                <p class="text-muted mb-0"><?php echo $acronym;?></p>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-sm-3">
                                    <p class="mb-0">Year Level</p>
                                </div>
                                <div class="col-sm-9">
                                    <p class="text-muted mb-0">Grade 11</p>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-sm-3">
                                    <p class="mb-0">Semester</p>
                                </div>
                                <div class="col-sm-9">
                                    <p class="text-muted mb-0"><?php echo $current_school_year_period; ?></p>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-sm-3">
                                    <p class="mb-0">School Year</p>
                                </div>
                                <div class="col-sm-9">
                                    <p class="text-muted mb-0"><?php echo $current_school_year_term; ?></p>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-sm-3">
                                    <p class="mb-0">Date</p>
                                </div>
                                <div class="col-sm-9">
                                    <p class="text-muted mb-0"><?php echo $date_creation; ?></p>
                                </div>
                            </div>
                            <hr>
                                <div class="row">
                                    <div class="col-sm-3">
                                        <p class="mb-0">Status</p>
                                    </div>
                                    <div class="col-sm-9">
                                        <p class="text-muted mb-0">Pending</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-10 offset-md-1">
                        <h4 class="text-center text-muted mb-3">Personal Info</h4>
                        <div class="card mb-4">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-sm-3">
                                    <p class="mb-0">Full Name</p>
                                    </div>
                                    <div class="col-sm-9">
                                    <p class="text-muted mb-0"><?php echo $fullname?></p>
                                    </div>
                                </div>
                                <hr>
                                <div class="row">
                                    <div class="col-sm-3">
                                        <p class="mb-0">Email</p>
                                    </div>
                                    <div class="col-sm-9">
                                        <p class="text-muted mb-0"><?php echo $email?></p>
                                    </div>
                                </div>
                                <hr>
                                <div class="row">
                                    <div class="col-sm-3">
                                        <p class="mb-0">Phone</p>
                                    </div>
                                    <div class="col-sm-9">
                                        <p class="text-muted mb-0"><?php echo $contact_number?></p>
                                    </div>
                                </div>
                                <hr>
                                <div class="row">
                                    <div class="col-sm-3">
                                        <p class="mb-0">Address</p>
                                    </div>
                                    <div class="col-sm-9">
                                        <p class="text-muted mb-0"><?php echo $address?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-10 offset-md-1">
                        <h4 class="text-center text-muted mb-3">Subject</h4>
                        <div style="margin-left: 6px;" class="row">
                            <div class="col-md-4">
                                <h5 class="text-success mb-0">Course Destination:</h5>
                            </div>
                            <div style="padding: 0px; font-weight:500;" class="col-md-8">
                                <p class="mb-0 text-primary"><?php echo $program_section;?></p>
                            </div>
                        </div>
                        
                        <div class="card mb-4">
                            <div class="card-body">
                                <table id="dash-table" class="table table-striped table-bordered table-hover table-responsive" style="font-size:12px" cellspacing="0">
                                    <thead>
                                    <tr class="text-center"> 
                                        <th rowspan="1">Id</th>
                                        <th rowspan="1">Subject Code</th>  
                                        <th rowspan="2">Name</th>
                                        <th colspan="1">Unit</th> 
                                        </tr>	
                                    </thead> 
                                    <tbody>
                                        <?php

                                        # For Pending Grade 11 1st Semester Only
                                        $semester = "First";
                                        $recommendedSubject = $con->prepare("SELECT * FROM subject_program
                                            WHERE program_id=:program_id
                                            AND course_level=11
                                            AND semester=:semester
                                            ");

                                        $recommendedSubject->bindValue(":program_id", $program_id);
                                        $recommendedSubject->bindValue(":semester", $semester);
                                        $recommendedSubject->execute();
                                        $totalUnits = 0;

                                            if($recommendedSubject != null){
                                                $applyTabIsAvailable = true;

                                                foreach ($recommendedSubject as $key => $row) {

                                                    $unit = $row['unit'];

                                                    $totalUnits += $unit;

                                                    // $totalUnits = 0;
                                                    echo '<tr class="text-center">'; 
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
                            </div>
                        </div>
                    </div>

                    <div class="text-center">
                        <button onclick='confirmPendingValidationCashier("<?php echo $firstname ?>", "<?php echo $lastname ?>", "<?php echo $middle_name ?>", "<?php echo $password ?>", "<?php echo $program_id ?>", "<?php echo $civil_status ?>", "<?php echo $nationality ?>", "<?php echo $contact_number ?>", "<?php echo $birthday ?>", "<?php echo $age ?>", "<?php echo $guardian_name ?>", "<?php echo $guardian_contact_number ?>", "<?php echo $sex ?>", "<?php echo $student_status ?>", "<?php echo $program_section ?>", "<?php echo $pending_enrollees_id ?>", "<?php echo $address; ?>", "<?php echo $lrn; ?>")' name='confirm_validation_btn' class='btn btn-success btn-sm'>Confirm</button>

                        <button class="btn btn-danger">Reject</button>
                    </div>

                </div>

                <script>
                // alert('qwe');
                    function confirmPendingValidationCashier(firstname, lastname, middle_name, password,
                        program_id, civil_status, nationality, contact_number, birthday, age,
                        guardian_name, guardian_contact_number, sex, student_status,
                        program_section,pending_enrollees_id, address, lrn){

                            alert('confirm');
                        // console.log('click');
                        // $.ajax({
                        //     url: '../ajax/enrollee/registrar_confirm_pending.php',
                        //     type: 'POST',
                        //     data: {
                        //         firstname, lastname, middle_name,
                        //         password, program_id, civil_status, nationality, 
                        //         contact_number, birthday, age, guardian_name, 
                        //         guardian_contact_number, sex, student_status, 
                        //         program_section, pending_enrollees_id, address, lrn
                        //     },
                        //     success: function(response) {

                        //         // handle the response from the server here
                        //         console.log(response);
                        //         // alert(response);
                        //         // window.location.href = 'old_enrollees.php';
                        //         // location.reload();

                        //     },
                        //     error: function(xhr, status, error) {
                        //         // handle any errors here
                        //     }
                        // });
                    }
                </script>
            <?php
            }
        }
    }

    else{

        header("Location: /dcbt/registrarLogin.php");
        exit();
    }


?>

