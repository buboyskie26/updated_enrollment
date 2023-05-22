<?php 

    include('../registrar_enrollment_header.php');
    include('../../enrollment/classes/StudentEnroll.php');
    include('../../enrollment/classes/Schedule.php');
    include('../../enrollment/classes/Section.php');
    include('../../enrollment/classes/Pending.php');
    include('../../enrollment/classes/Enrollment.php');
    // include('../classes/AdminUser.php');
    // include('../classes/AdminUser.php');

    if(!AdminUser::IsRegistrarAuthenticated()){
        header("Location: /dcbt/registrar_login.php");
        exit();
    }

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


            $student_program_section = "";
            $student_course_id = 0;
            $student_id = null;
            $student_course_level = null;

            $enrollment_id = null;

            $student = $con->prepare("SELECT username, student_id, course_level 
                
                FROM student
                WHERE firstname=:firstname
                AND lastname=:lastname
                AND middle_name=:middle_name
                
                ");
            $student->bindValue(":firstname", $firstname);
            $student->bindValue(":lastname", $lastname);
            $student->bindValue(":middle_name", $middle_name);
            $student->execute();

            $enrollment = new Enrollment($con, $enroll);

            if($student->rowCount() > 0){

                $row_student = $student->fetch(PDO::FETCH_ASSOC);

                $student_username = $row_student['username'];
                $student_id = $row_student['student_id'];
                $student_course_level = $row_student['course_level'];

                $student_course_id = $enroll->GetStudentCourseId($student_username);
                $student_program_section = $enroll->GetStudentProgramSection($student_course_id);

                $enrollment_id = $enrollment->GetEnrollmentId($student_id, $student_course_id, $current_school_year_id);

                // echo $enrollment_id;
            } 
            
            $get_available_section = $con->prepare("SELECT course_id, capacity, program_section

                FROM course

                WHERE program_id=:program_id
                AND active=:active
                AND course_level=:course_level
                AND is_full=:is_full
                AND school_year_term=:school_year_term
                -- ORDER BY course_id DESC
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

                $enrolledStudents = $enroll->CheckNumberOfStudentInSection(
                    $default_course_id, "First");

                
                if($enrolledStudents >= $capacity){
                    // echo "$program_section is now full";
                    // echo "<br>";

                    // $program_section = $section->AutoCreateAnotherSection($program_section);

                    // echo $nextSection . " is preferred";
                }

            }

            if(isset($_POST['shs_irreg_subject_load_btn'])
                && isset($_POST['select_transferee_subject'])){


                    $select_transferee_subject = $_POST['select_transferee_subject'];

                    $insert = $con->prepare("INSERT INTO student_subject
                        (student_id, subject_id, course_level, school_year_id, is_final)
                        VALUES (:student_id, :subject_id, :course_level, :school_year_id, :is_final)");
                            
                    $successInserted = false;
                    foreach ($select_transferee_subject as $key => $value) {
                        # code...

                        $subject_id = $value;

                        // echo $subject_id;
                        // echo "<br>";

                        # Check subject if it was inserted.

                        # Insert subject
                        if($student_id != null && $student_course_level != null){

                            $insert->bindValue(":student_id", $student_id);
                            $insert->bindValue(":subject_id", $subject_id);
                            $insert->bindValue(":course_level", $student_course_level);
                            $insert->bindValue(":school_year_id", $current_school_year_id);
                            $insert->bindValue(":is_final", 0);

                            if($insert->execute())
                                $successInserted = true;
                        }

                        # Update Enrolled student.


                        # Update student table.

                    }

                    if($successInserted == true){

                        // Swal Alert
                        AdminUser::success("Inserted, Wait for Cashier", "");
                        exit();

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
                    <h4 class="text-center text-primary mb-3">Enrollment Subjects of <?php echo $current_school_year_period;?> Semester</h4>
                    <div style="margin-left: 6px;" class="row">
                        <div class="col-md-4 mb-4">
                            <h5 class="text-success mb-0">Course Destination:</h5>
                        </div>
                        <div style="padding: 0px; font-weight:500;" class="col-md-8">

                            <?php
                                # Edit the student section
                                if($student_id != null){
                                    ?>
                                    <select name="edit_course_id" id="edit_course_id" class="form-control">
                                        <option value=""selected>Select Section</option>
                                        <?php

                                            $sql = $con->prepare("SELECT * FROM course
                                            
                                                WHERE school_year_term=:school_year_term
                                                AND program_id=:program_id
                                                ");
                                            $sql->bindValue(":school_year_term", $current_school_year_term);
                                            $sql->bindValue(":program_id", $program_id);
                                            $sql->execute();
                                            if($sql->rowCount() > 0){

                                                // echo "weqwe";
                                                
                                                while($row = $sql->fetch(PDO::FETCH_ASSOC)){

                                                    $status = "";


                                                    $enrolledStudents = $enroll->CheckNumberOfStudentInSection(
                                                        $row['course_id'], "First");

                                                    echo $enrolledStudents;
                                                    echo "<br>";
                                                
                                                    if($enrolledStudents >= $row['capacity']){
                                                        $status = "(Full)";
                                                        $program_section = $section->AutoCreateAnotherSection($program_section);
                                                    }

                                                    if($row['is_full'] == "yes"){
                                                        $status = "(Full)";
                                                    }

                                                    $course_id = $student_course_id;
                                                    $selected = ($row['course_id'] == $course_id) ? "selected" : "";

                                                    echo "
                                                        <option $selected value='".$row['course_id']."'>".$row['program_section']." $status</option>
                                                    ";
                                                }
                                            }
                                        ?>
                                        
                                        <input type="hidden" id="student_id" value="<?php echo $student_id; ?>" >
                                        <input type="hidden" id="student_course_level" value="<?php echo $student_course_level; ?>" >
                                    </select>

                                    <?php
                                }
                                
                                if($student_id == null ){
                                    ?>
                                    <select name="course_id" id="course_id" class="form-control">
                                        <option value=""selected>Select Section</option>
                                        <?php

                                            $sql = $con->prepare("SELECT * FROM course
                                            
                                                WHERE school_year_term=:school_year_term
                                                AND program_id=:program_id
                                                ");
                                            $sql->bindValue(":school_year_term", $current_school_year_term);
                                            $sql->bindValue(":program_id", $program_id);
                                            $sql->execute();
                                            if($sql->rowCount() > 0){

                                                // echo "weqwe";
                                                
                                                while($row = $sql->fetch(PDO::FETCH_ASSOC)){

                                                    $status = "";

                                                    $enrolledStudents = $enroll->CheckNumberOfStudentInSection(
                                                        $row['course_id'], "First");

                                                    echo $enrolledStudents;
                                                    echo "<br>";
                                                
                                                    if($enrolledStudents >= $row['capacity']){
                                                        $status = "(Full)";
                                                        $program_section = $section->AutoCreateAnotherSection($program_section);
                                                    }

                                                    if($row['is_full'] == "yes"){
                                                        $status = "(Full)";
                                                    }

                                                    $course_id = $student_course_id;
                                                    $selected = ($row['course_id'] == $course_id) ? "selected" : "";
                                                    echo "
                                                        <option $selected value='".$row['course_id']."'>".$row['program_section']." $status</option>
                                                    ";
                                                }
                                            }
                                        ?>
                                        <input type="hidden" id="firstname" value="<?php echo $firstname; ?>" >
                                        <input type="hidden" id="lastname" value="<?php echo $lastname; ?>" >
                                        <input type="hidden" id="middle_name" value="<?php echo $middle_name; ?>" >
                                        <input type="hidden" id="password" value="<?php echo $password; ?>" >
                                        <input type="hidden" id="program_id" value="<?php echo $program_id; ?>" >
                                        <input type="hidden" id="civil_status" value="<?php echo $civil_status; ?>" >
                                        <input type="hidden" id="nationality" value="<?php echo $nationality; ?>" >
                                        <input type="hidden" id="contact_number" value="<?php echo $contact_number; ?>" >
                                        <input type="hidden" id="age" value="<?php echo $age; ?>" >
                                        <input type="hidden" id="guardian_name" value="<?php echo $guardian_name; ?>" >
                                        <input type="hidden" id="sex" value="<?php echo $sex; ?>" >
                                        <input type="hidden" id="guardian_contact_number" value="<?php echo $guardian_contact_number; ?>" >
                                        <input type="hidden" id="student_status" value="<?php echo $student_status; ?>" >
                                        <input type="hidden" id="program_section" value="<?php echo $student_program_section; ?>" >
                                        <input type="hidden" id="pending_enrollees_id" value="<?php echo $pending_enrollees_id; ?>" >
                                        <input type="hidden" id="address" value="<?php echo $address; ?>" >
                                        <input type="hidden" id="lrn" value="<?php echo $lrn; ?>" >
                                        <input type="hidden" id="birthday" value="<?php echo $birthday; ?>" >
                                    </select>

                                    <?php
                                }
                            ?>
                        </div>

                    </div>

                    <!--  Non Searchable -->
                    <div style="display: none;" class="card mb-4">
                        <div class="card-body">

                            <form method="POST">
                                <table id="dash-table" 
                                    class="table table-striped table-bordered table-hover table-responsive" 
                                    style="font-size:14px" cellspacing="0">
                                    <thead>
                                        <tr class="text-center"> 
                                            <th></th>
                                            <th rowspan="1">Id</th>
                                            <th rowspan="1">Code</th>  
                                            <th rowspan="2">Description</th>
                                            <th colspan="1">Unit</th> 
                                            <th colspan="1">Type</th> 
                                            <th colspan="1">Grade Level</th> 
                                            <th colspan="1">Semester</th> 
                                            <th colspan="1">Action</th> 
                                        </tr>	
                                    </thead> 
                                    <tbody>
                                        <?php

                                        # For Pending Grade 11 1st Semester Only
                                        $semester = "First";
                                     
                                        
                                        # All of his subject program subject not less than his current level and semester.
                                        # All other minor subjects of SHS.
                                        $transfereeSubjects = $con->prepare("SELECT * FROM subject as t1

                                            INNER JOIN course as t2 ON t2.course_id = t1.course_id
                                            WHERE t2.active='yes'
                                            -- AND semester=:semester
                                            ORDER BY t1.course_level ASC,
                                            t1.subject_title
                                            ");

                                        // $transfereeSubjects->bindValue(":course_level", $student_status);
                                        // $transfereeSubjects->bindValue(":semester", $current_school_year_period);
                                        $transfereeSubjects->execute();

                                        $totalUnits = 0;

                                            if($transfereeSubjects != null){
                                                $applyTabIsAvailable = true;

                                                foreach ($transfereeSubjects as $key => $row) {

                                                    $unit = $row['unit'];
                                                    $subject_id = $row['subject_id'];
                                                    $course_id = $row['course_id'];
                                                    $subject_title = $row['subject_title'];
                                                    $subject_code = $row['subject_code'];
                                                    $subject_type = $row['subject_type'];
                                                    $semester = $row['semester'];
                                                    $course_level = $row['course_level'];

                                                    $totalUnits += $unit;

                                                    // $totalUnits = 0;

                                                    
                                                    $add_click = "add_transferee($student_id, $subject_id,
                                                        $course_level, $current_school_year_id)";

                                                    $add_credit_click = "add_credit_transferee($student_id, $subject_id,
                                                        $course_level, $current_school_year_id,
                                                        $course_id, \"$subject_title\")";
                                                
                                                    echo "
                                                        <tr class='text-center'>
                                                            <td>
                                                                <input value='$subject_id' name='select_transferee_subject[]' type='checkbox'>
                                                            </td>
                                                            <td>$subject_id</td>
                                                            <td>$subject_code</td>
                                                            <td>$subject_title</td>
                                                            <td>$unit</td>
                                                            <td>$subject_type</td>
                                                            <td>Grade $course_level</td>
                                                            <td>$semester</td>
                                                            <td>
                                                                <button type='button' onclick='$add_click' class='btn btn-outline-primary'>Add</button>
                                                                <button type='button' onclick='$add_credit_click' class='btn btn-outline-success'>Credit</button>
                                                            </td>
                                                        </tr>
                                                    ";
                                                }
                                            }else{
                                                echo "Not yet set new school_year. Enrollment school_year_id DESC is the same with current_school_year_id";
                                            }
                                        ?>
                                       
                                    </tbody>
                                </table>

                                <?php
                                
                                    if($student_id != null){
                                        ?>
                                            <!-- <button type="submit" name="shs_irreg_subject_load_btn"
                                                id="shs_irreg_subject_load_btn"
                                                class="btn btn-success btn">Insert & Enroll
                                            </button> -->

                                            <a href="view_student_transferee_enrollment_review.php?inserted=true&id=<?php echo $student_id?>&p_id=<?php echo $pending_enrollees_id?>&e_id=<?php echo $enrollment_id;?>">
                                                <button type="button" 
                                                    class="btn btn-primary btn">Review Insertion
                                                </button>
                                            </a>

                                        <?php
                                    }else{
                                        ?>
                                            <button type="submit" disabled
                                                class="btn btn-outline-success btn">Select Section First
                                            </button>
                                        <?php 
                                    }
                                ?>

                            </form>
                        </div>
                    </div>


                    <table id="transfereeTable" class="table table-bordered table-hover "  style="font-size:15px" cellspacing="0"> 
                        <thead>
                            <tr>
                                <th>Id</th>
                                <th>Code</th>
                                <th>Description</th>
                                <th>Unit</th>
                                <th>Type</th>
                                <th>Semester</th>
                                <th>Non-Credit</th>
                                <th>Credit</th>
                            </tr>
                        </thead>
                    </table>
                        <?php
                            if($student_id != null){
                                ?>
                                    <!-- <button type="submit" name="shs_irreg_subject_load_btn"
                                        id="shs_irreg_subject_load_btn"
                                        class="btn btn-success btn">Insert & Enroll
                                    </button> -->

                                    <a href="view_student_transferee_enrollment_review.php?inserted=true&id=<?php echo $student_id?>&p_id=<?php echo $pending_enrollees_id?>&e_id=<?php echo $enrollment_id;?>">
                                        <button type="button" 
                                            class="btn btn-primary btn">Review Insertion
                                        </button>
                                    </a>

                                <?php
                            }else{
                                ?>
                                    <button type="submit" disabled
                                        class="btn btn-outline-success btn">Select Section First
                                    </button>
                                <?php 
                            }
                        ?>
                </div>
            </div>
        <?php
        }

    }
?>

<script>

    $(document).ready(function(){

        // var id = '<?php echo $_GET['id']; ?>';  
        var id = '<?php echo $student_id; ?>';  
    
        var table = $('#transfereeTable').DataTable({
            'processing': true,
            'serverSide': true,
            'serverMethod': 'POST',
            'ajax': {
                'url':'transfeeEnrollmentDataList.php?id=' + id
            },
            'columns': [
                { data: 'subject_id' },
                { data: 'subject_code' },
                { data: 'subject_title' },
                { data: 'unit' },
                { data: 'semester' },
                { data: 'subject_type' },
                { data: 'actions2' },
                { data: 'actions3' }
            ]
        });
    });
    // alert('qwe');

    function add_transferee(student_id, subject_id, course_level, school_year_id){

        // console.log('add_transferee');

        $.ajax({
            url: '../ajax/enrollee/add_non_credit_subject.php',
            type: 'POST',
            data: {
                student_id, subject_id,
                course_level, school_year_id
            },
            success: function(response) {
                // console.log(response);
                alert(response);
                // window.location.href = 'transfee_enrollees.php';
            },
            error: function(xhr, status, error) {
                // handle any errors here
            }
        });

    }

    function add_credit_transferee(student_id, subject_id,
        course_level, school_year_id, course_id, subject_title){

        $.ajax({
            url: '../ajax/enrollee/add_credit_subject.php',
            type: 'POST',
            data: {
                student_id, subject_id,
                course_level, school_year_id,
                course_id, subject_title
            },
            success: function(response) {

                // console.log(response);
                alert(response);
                // window.location.href = 'transfee_enrollees.php';
            },
            error: function(xhr, status, error) {
                // handle any errors here
            }
        });
    }
        
    $(document).ready(function() {

        $('#edit_course_id').change(function() {

            var edit_course_id =  $("#edit_course_id").val();
            var student_id =  $("#student_id").val();
            var student_course_level = parseInt($("#student_course_level").val());

            // console.log(student_course_level);

            var confirmation = confirm("Do you want change student current section??");

            if (confirmation) {
                $.ajax({
                url: '../ajax/enrollee/registrar_confirm_trans_pending.php',
                type: 'POST',
                data: {
                    edit_course_id,
                    student_id,
                    student_course_level
                },
                success: function(response) {

                    if(response == "success"){
                        
                        // alert(response);
                        location.reload();
                    }
              
                    // alert(response);
                    // window.location.href = 'transfee_enrollees.php';
                },
                error: function(xhr, status, error) {
                    // handle any errors here
                }
                });     
            }
            //
        });

        $('#course_id').change(function() {
            // Retrieve the selected course_id value
            var courseId =  $("#course_id").val();

            // console.log(courseId)
            var firstname = $("#firstname").val();
            var lastname = $("#lastname").val();
            var middle_name = $("#middle_name").val();
            var password = $("#password").val();
            var program_id = $("#program_id").val();
            var civil_status = $("#civil_status").val();
            var nationality = $("#nationality").val();
            var contact_number = $("#contact_number").val();
            
            var age = $("#age").val();
            var guardian_name = $("#guardian_name").val();
            var guardian_contact_number = $("#guardian_contact_number").val();
            var sex = $("#sex").val();
            var guardian_contact_number = $("#guardian_contact_number").val();
            var student_status = $("#student_status").val();
            var program_section = $("#program_section").val();
            var pending_enrollees_id = $("#pending_enrollees_id").val();
            var address = $("#address").val();
            var lrn = $("#lrn").val();
            var birthday = $("#birthday").val();

            //     confirmPendingValidationSelect(firstname, lastname, middle_name, password,
            // program_id, civil_status, nationality, contact_number, birthday, age,
            // guardian_name, guardian_contact_number, sex, student_status,
            // program_section,pending_enrollees_id, address, lrn, courseId)
 
            var confirmation = confirm("Do you want to enroll & insert the student?");

            if (confirmation) {
                $.ajax({
                url: '../ajax/enrollee/registrar_confirm_trans_pending.php',
                type: 'POST',
                data: {
                    courseId,
                    firstname,
                    lastname,
                    middle_name,
                    password,
                    program_id,
                    civil_status,
                    nationality,
                    contact_number,
                    age,
                    guardian_name,
                    guardian_contact_number,
                    sex,
                    student_status,
                    program_section,
                    pending_enrollees_id,
                    address,
                    lrn,
                    birthday
                },
                success: function(response) {

                    console.log(response);
                    alert(response);
                    location.reload();
                    // window.location.href = 'transfee_enrollees.php';
                },
                error: function(xhr, status, error) {
                    // handle any errors here
                }
                });     
            }


        });
  });

    function confirmPendingValidationSelect(firstname, lastname, middle_name, password,
        program_id, civil_status, nationality, contact_number, birthday, age,
        guardian_name, guardian_contact_number, sex, student_status,
        program_section,pending_enrollees_id, address, lrn, courseId){

        $.ajax({
            url: '../ajax/enrollee/registrar_confirm_trans_pending.php',
            type: 'POST',
            data: {
                firstname, lastname, middle_name,
                password, program_id, civil_status, nationality, 
                contact_number, birthday, age, guardian_name, 
                guardian_contact_number, sex, student_status, 
                program_section, pending_enrollees_id, address,
                lrn, course_id
            },
            success: function(response) {

                console.log(response);
                // alert(response);
                // window.location.href = 'transfee_enrollees.php';
            },
            error: function(xhr, status, error) {
                // handle any errors here
            }
        });
    }
    // function confirmPendingValidation(firstname, lastname, middle_name, password,
    //     program_id, civil_status, nationality, contact_number, birthday, age,
    //     guardian_name, guardian_contact_number, sex, student_status,
    //     program_section,pending_enrollees_id, address, lrn){

    //         // alert('confirm');
    //     // console.log('click');
    //     var course_id = $("#course_id").val();

    //     // console.log(course_id)

    //     $.ajax({
    //         url: '../ajax/enrollee/registrar_confirm_trans_pending.php',
    //         type: 'POST',
    //         data: {
    //             firstname, lastname, middle_name,
    //             password, program_id, civil_status, nationality, 
    //             contact_number, birthday, age, guardian_name, 
    //             guardian_contact_number, sex, student_status, 
    //             program_section, pending_enrollees_id, address, lrn, course_id
    //         },
    //         success: function(response) {

    //             // handle the response from the server here
    //             // console.log(response);
    //             alert(response);
    //             // alert(response);
    //             window.location.href = 'transfee_enrollees.php';
    //             // location.reload();

    //         },
    //         error: function(xhr, status, error) {
    //             // handle any errors here
    //         }
    //     });
    // }


</script>