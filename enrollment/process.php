<?php 

    require_once('../includes/studentHeader.php');
    require_once('./classes/StudentEnroll.php');
    require_once('./classes/Section.php');
    require_once('./classes/Pending.php');
    require_once('./classes/SectionTertiary.php');
    require_once('../includes/classes/Student.php');

    if(isset($_SESSION['username'])
        && isset($_SESSION['status']) 
        && $_SESSION['status'] == 'pending'
        && $_SESSION['status'] != 'enrolled'
        ){

        $username = $_SESSION['username'];

        // echo $username;
        $enroll = new StudentEnroll($con);
        $pending = new Pending($con);
        $school_year_obj = $enroll->GetActiveSchoolYearAndSemester();
        // $course_id = $enroll->GetStudentCourseId($username);

        // $section = new Section($con, $course_id);

        $school_year_id = $school_year_obj['school_year_id'];
        $current_semester = $school_year_obj['period'];

        // $student_year_id = $enroll->GetStudentCurrentYearId($username);

        // $sql = $con->prepare("SELECT * FROM student
        //     WHERE username=:username
        //     -- WHERE firstname=:firstname
        //     LIMIT 1");

        // $sql->bindValue(":username", $username);
        // $sql->execute();
                
        // $sectionName = $section->GetSectionName();

        $sql = $con->prepare("SELECT * FROM pending_enrollees
            WHERE firstname=:firstname");
        
        $sql->bindValue(":firstname", $username);
        $sql->execute();


        if($sql->rowCount() > 0){

            $row = $sql->fetch(PDO::FETCH_ASSOC);

            // $pending_enrollees_id = $row['pending_enrollees_id'];
            // $program_id = $row['program_id'];
            // $type = $row['type'];
            // $student_status = $row['student_status'];

            # STEP 1
            $pending_enrollees_id = empty($row['pending_enrollees_id']) ? null : $row['pending_enrollees_id'];
            $program_id = empty($row['program_id']) ? 0 : $row['program_id'];
            $type = empty($row['type']) ? '' : $row['type'];
            $student_status = empty($row['student_status']) ? '' : $row['student_status'];

            // STEP 2
            $lrn = empty($row['lrn']) ? '' : $row['lrn'];
            $firstname = empty($row['firstname']) ? '' : $row['firstname'];
            $middle_name = empty($row['middle_name']) ? '' : $row['middle_name'];
            $lastname = empty($row['lastname']) ? '' : $row['lastname'];
            $civil_status = empty($row['civil_status']) ? '' : $row['civil_status'];
            $nationality = empty($row['nationality']) ? '' : $row['nationality'];
            $sex = empty($row['sex']) ? '' : $row['sex'];
            $birthday = empty($row['birthday']) ? '' : $row['birthday'];
            $religion = empty($row['religion']) ? '' : $row['religion'];
            $address = empty($row['address']) ? '' : $row['address'];
            $contact_number = empty($row['contact_number']) ? '' : $row['contact_number'];
            $email = empty($row['email']) ? '' : $row['email'];
            $birthplace = empty($row['birthplace']) ? '' : $row['birthplace'];


            $get_parent = $con->prepare("SELECT * FROM parent
                WHERE pending_enrollees_id=:pending_enrollees_id");
        
            $get_parent->bindValue(":pending_enrollees_id", $pending_enrollees_id);
            $get_parent->execute();

            $parent_firstname = "";
            $parent_lastname = "";
            $parent_middle_name = "";
            $parent_contact_number = "";

            if($get_parent->rowCount() > 0){
                $rowParnet = $get_parent->fetch(PDO::FETCH_ASSOC);

                $parent_firstname = $rowParnet['firstname'];
                $parent_lastname = $rowParnet['lastname'];
                $parent_middle_name = $rowParnet['middle_name'];
                $parent_contact_number = $rowParnet['contact_number'];

            }

            if(isset($_GET['new_student']) && $_GET['new_student'] == "true"){

                if(isset($_GET['step']) && $_GET['step'] == 1){

                    if(isset($_POST['new_step1_btn'])){

                        $admission_type = $_POST['admission_type'];
                        $student_type = $_POST['student_type'];
                        $program_id = $_POST['STRAND'];


                        # Check if all form are inputed.


                        $wasSuccess = $pending->UpdatePendingNewStep1($admission_type,
                                $student_type, $program_id, $pending_enrollees_id);
                        if($wasSuccess){
                    
                            $step1Completed = $pending->CheckFormStep1Complete($pending_enrollees_id);

                            if($step1Completed==true){
                                header("Location: process.php?new_student=true&step=2");
                                exit();
                            }else{
                                AdminUser::error("All inputs are required.", "process.php?new_student=true&step=1");
                                exit();
                            }
                            
                        }
                        

                    }

                    ?>
                        <div class="row col-md-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="card-header">
                                        <h4 class="text-center">STEP 1 ~ Prefered Course</h4>
                                        <div class="container mb-4">
                                            <form method="POST">
                                                <div class="row">
                                                    <span>Admission Type</span>
                                                    <div class="col-md-6">
                                                        <label for="">New Student</label>
                                                        <input type="radio" name="admission_type"
                                                            value="Regular"<?php echo ($student_status == "Regular") ? ' checked' : ''; ?>>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label for="">Transferee</label>
                                                        <input type="radio" name="admission_type"
                                                            value="Transferee"<?php echo ($student_status == "Transferee") ? ' checked' : ''; ?>>
                                                    </div>
                                                </div>

                                                <div class="row mt-4">
                                                    <span>Student Type</span>
                                                    <div class="col-md-6">
                                                        <label for="">College</label>
                                                        <input type="radio" name="student_type"
                                                            value="Tertiary" <?php echo ($type == "Tertiary") ? ' checked' : ''; ?>>
                                                        
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label for="">Senior High</label>
                                                        <input type="radio" name="student_type"
                                                            value="SHS" <?php echo ($type == "SHS") ? ' checked' : ''; ?>>
                                                    </div>
                                                </div>

                                                <div class="row mt-4">
                                                    <span>Strand</span>
                                                    <?php echo $pending->CreateRegisterStrand($program_id);?>
                                                </div>

                                                <button type="submit" name="new_step1_btn" class="btn btn-primary">Proceed</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php
                }

                if(isset($_GET['step']) && $_GET['step'] == 2){

                    if(isset($_POST['new_step2_btn'])){

                        // $firstname = isset($_POST['firstname']) ? $_POST['firstname'] : '';
                        // $middle_name = isset($_POST['middle_name']) ? $_POST['middle_name'] : '';
                        // $lastName = isset($_POST['lastname']) ? $_POST['lastname'] : '';
                        // $civil_status = isset($_POST['civil_status']) ? $_POST['civil_status'] : '';
                        // $nationality = isset($_POST['nationality']) ? $_POST['nationality'] : '';
                        // $sex = isset($_POST['sex']) ? $_POST['sex'] : '';

                        // $birthday = isset($_POST['birthday']) ? $_POST['birthday'] : '2000-01-01';
                        // $age = $pending->CalculateAge($birthday);

                        // $birthplace = isset($_POST['birthplace']) ? $_POST['birthplace'] : '';
                        // $religion = isset($_POST['religion']) ? $_POST['religion'] : '';
                        // $address = isset($_POST['address']) ? $_POST['address'] : '';
                        // $contact_number = isset($_POST['contact_number']) ? $_POST['contact_number'] : '';
                        // $email = isset($_POST['email']) ? $_POST['email'] : '';
                        // $lrn = isset($_POST['lrn']) ? $_POST['lrn'] : '';


                        // $parent_firstname = isset($_POST['parent_firstname']) ? $_POST['parent_firstname'] : '';
                        // $parent_middle_name = isset($_POST['parent_middle_name']) ? $_POST['parent_middle_name'] : '';
                        // $parent_lastname = isset($_POST['parent_lastname']) ? $_POST['parent_lastname'] : '';
                        // $parent_contact_number = isset($_POST['parent_contact_number']) ? $_POST['parent_contact_number'] : '';


                        // $wasSuccess = $pending->UpdatePendingNewStep2($pending_enrollees_id, $firstname, $middle_name,
                        //     $lastName, $civil_status, $nationality, $sex, $birthday,
                        //     $birthplace, $religion, $address, $contact_number, $email, $age, $lrn);

                        // $guardian_form_input = $pending->PendingParentInput($pending_enrollees_id, 
                        //     $parent_firstname, $parent_middle_name,
                        //     $parent_lastname, $parent_contact_number);

                        // if($wasSuccess && $guardian_form_input){

                        //     // echo "succes";
                        //     header("Location: process.php?new_student=true&step=3");
                        //     exit();
                        // }

                        $firstname = $_POST['firstname'];
                        $middle_name = $_POST['middle_name'];
                        $lastName = $_POST['lastname'];
                        $civil_status = $_POST['civil_status'];
                        $nationality = $_POST['nationality'];
                        $sex = $_POST['sex'];
                        $birthday = $_POST['birthday'];
                        $birthplace = $_POST['birthplace'];
                        $religion = $_POST['religion'];
                        $address = $_POST['address'];
                        $contact_number = $_POST['contact_number'];
                        $email = $_POST['email'];
                        $lrn = $_POST['lrn'];

                        $age = $pending->CalculateAge($birthday);

                        # Guardian.

                        $parent_firstname = isset($_POST['parent_firstname']) ? $_POST['parent_firstname'] : '';
                        $parent_middle_name = isset($_POST['parent_middle_name']) ? $_POST['parent_middle_name'] : '';
                        $parent_lastname = isset($_POST['parent_lastname']) ? $_POST['parent_lastname'] : '';
                        $parent_contact_number = isset($_POST['parent_contact_number']) ? $_POST['parent_contact_number'] : '';

                        $wasSuccess = $pending->UpdatePendingNewStep2($pending_enrollees_id, $firstname, $middle_name,
                            $lastName, $civil_status, $nationality, $sex, $birthday,
                            $birthplace, $religion, $address, $contact_number, $email, $age, $lrn);

                        $guardian_form_input = $pending->PendingParentInput($pending_enrollees_id, 
                            $parent_firstname, $parent_middle_name,
                            $parent_lastname, $parent_contact_number);

                        if($wasSuccess && $guardian_form_input){

                            $wasCompleted = $pending->CheckAllStepsComplete($pending_enrollees_id);
                            if($wasCompleted == true){
                                AdminUser::success("All forms are successfully inserted",
                                    "process.php?new_student=true&step=3");
                                exit();

                            }else{
                                AdminUser::error("All fields must be filled-up", "");
                            }
                            // header("Location: process.php?new_student=true&step=3");
                            // exit();
                        }
                
                    }
                    ?>
                        <div class="row col-md-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="card-header">

                                        <h4 class="text-center">STEP 2 ~ Personal Info<h4>

                                        <div class="container mb-4">

                                            <form method="POST">

                                            <div class="form-group">
                                                <label for="lrn">LRN</label>
                                                <!-- <input type="text" id="firstname" name="firstname" class="form-control"> -->
                                                <input type="text" name="lrn" class="form-control" 
                                                    value="<?php echo ($lrn != "") ? $lrn : ''; ?>">

                                                <div class="form-group">
                                                    <label for="firstname">First Name</label>
                                                    <!-- <input type="text" id="firstname" name="firstname" class="form-control"> -->
                                                    <input type="firstname" name="firstname" class="form-control" 
                                                        value="<?php echo ($firstname != "") ? $firstname : ''; ?>">
                                                </div>

                                                <div class="form-group">
                                                    <label for="middlename">Middle Name</label>
                                                    <input type="middle_name" name="middle_name" class="form-control" 
                                                        value="<?php echo ($middle_name != "") ? $middle_name : ''; ?>">
                                                </div>

                                                <div class="form-group">
                                                    <label for="lastname">Last Name</label>
                                                    <input type="lastname" name="lastname" class="form-control" 
                                                        value="<?php echo ($lastname != "") ? $lastname : ''; ?>">
                                                </div>

                                                <div class="form-group">
                                                    <label for="status">Status</label>
                                                    <select id="status" name="civil_status" class="form-control">
                                                        <option value="Single"<?php echo ($civil_status == "Single") ? " selected" : ""; ?>>Single</option>
                                                        <option value="Married"<?php echo ($civil_status == "Married") ? " selected" : ""; ?>>Married</option>
                                                        <option value="Divorced"<?php echo ($civil_status == "Divorced") ? " selected" : ""; ?>>Divorced</option>
                                                        <option value="Widowed"<?php echo ($civil_status == "Widowed") ? " selected" : ""; ?>>Widowed</option>
                                                    </select>
                                                </div>

                                                <div class="form-group">
                                                    <label for="citizenship">Citizenship</label>
                                                    <input type="nationality" name="nationality" class="form-control" 
                                                        value="<?php echo ($nationality != "") ? $nationality : ''; ?>">
                                                </div>

                                                <div class="form-group">
                                                    <label for="sex">Gender</label>
                                                    <div>
                                                        <!-- <input type="radio" id="male" name="sex" value="Male"> -->
                                                        <input type="radio" name="sex"
                                                            value="Male" <?php echo ($sex == "Male") ? ' checked' : ''; ?>>
                                                        <label for="male">Male</label>
                                                    </div>
                                                    <div>
                                                        <input type="radio" name="sex"
                                                            value="Female" <?php echo ($sex == "Female") ? ' checked' : ''; ?>>
                                                        <label for="female">Female</label>
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <label for="birthday">Birth Date</label>

                                                    <!-- <input type="date" id="birthday" name="birthday" class="form-control"> -->
                                                    <input type="date" id="birthday" name="birthday" class="form-control" value="<?php echo ($birthday != "") ? $birthday : ""; ?>">
                                                </div>

                                                <div class="form-group">
                                                    <label for="birthplace">Birth Place</label>
                                                    <input type="text" id="birthplace" name="birthplace" class="form-control" value="<?php echo ($birthplace != "") ? $birthplace : ""; ?>">

                                                </div>

                                                <div class="form-group">
                                                    <label for="religion">Religion</label>
                                                    <input type="text" id="religion" name="religion" class="form-control" value="<?php echo ($religion != "") ? $religion : ""; ?>">

                                                </div>

                                                <div class="form-group">
                                                    <label for="address">Address</label>
                                                    
                                                    <input type="text" id="address" name="address" class="form-control" value="<?php echo ($address != "") ? $address : ""; ?>">
                                                
                                                </div>

                                                <div class="form-group">
                                                    <label for="contact_number">Phone Number</label>
                                                
                                                    <input type="tel" id="contact_number" name="contact_number" class="form-control" value="<?php echo ($contact_number != "") ? $contact_number : ""; ?>">
                                                </div>

                                                <div class="form-group">
                                                    <label for="email">Email</label>
                                                    <!-- <input type="email" id="email" name="email" class="form-control"> -->
                                                    <input type="email" name="email" class="form-control" 
                                                        value="<?php echo ($email != "") ? $email : ''; ?>">
                                                </div>


                                                <h4 class="mb-4 mt-4 text-muted">Parent Info</h4>

                                                <div class="form-group">
                                                    <label for="firstname">First Name</label>
                                                    <!-- <input type="text" id="firstname" name="firstname" class="form-control"> -->
                                                    <input type="text" name="parent_firstname" class="form-control" 
                                                        value="<?php echo ($parent_firstname != "") ? $parent_firstname : ''; ?>">
                                                </div>

                                                <div class="form-group">
                                                    <label for="middlename">Middle Name</label>
                                                    <input type="text" name="parent_middle_name" class="form-control" 
                                                        value="<?php echo ($parent_middle_name != "") ? $parent_middle_name : ''; ?>">
                                                </div>

                                                <div class="form-group">
                                                    <label for="lastname">Last Name</label>
                                                    <input type="text" name="parent_lastname" class="form-control" 
                                                        value="<?php echo ($parent_lastname != "") ? $parent_lastname : ''; ?>">
                                                </div>

                                                
                                                <div class="form-group">
                                                    <label for="phone">Phone Number</label>
                                                    <input type="tel" name="parent_contact_number" class="form-control" 
                                                        value="<?php echo ($parent_contact_number != "") ? $parent_contact_number : ''; ?>">
                                                </div>

                                                <button name="new_step2_btn" type="submit" class="btn btn-success">Confirm</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    <?php
                }

                if(isset($_GET['step']) && $_GET['step'] == 3){

                    if(isset($_POST['new_step3_btn'])){

                        $firstname = $_POST['firstname'];
                        $middle_name = $_POST['middle_name'];
                        $lastName = $_POST['lastname'];
                        $civil_status = $_POST['civil_status'];
                        $nationality = $_POST['nationality'];
                        $sex = $_POST['sex'];
                        $birthday = $_POST['birthday'];
                        $birthplace = $_POST['birthplace'];
                        $religion = $_POST['religion'];
                        $address = $_POST['address'];
                        $contact_number = $_POST['contact_number'];
                        $email = $_POST['email'];
                        $lrn = $_POST['lrn'];

                        # Check if All Necessary inputs were met.

                        $wasCompleted = $pending->CheckAllStepsComplete($pending_enrollees_id);

                        if($wasCompleted == true){
                            $wasSuccess = $pending->UpdatePendingNewStep3($pending_enrollees_id, $firstname, $middle_name,
                                $lastName, $civil_status, $nationality, $sex, $birthday,
                                $birthplace, $religion, $address, $contact_number, $email, $lrn);
                            
                            if($wasSuccess){

                                AdminUser::success("All forms are successfully inserted",
                                    "profile.php?fill_up_state=finished");
                                exit();
                            }
                        }else{
                            AdminUser::error("All fields must be filled-up", "");
                            // exit();
                        }
                    }
                    ?>
                        <div class="row col-md-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="card-header">

                                        <h4 class="text-center">STEP 3 ~ Validating Details<h4>

                                        <div class="container mb-4">

                                            <form method="POST">

                                            <div class="form-group">
                                                <label for="lrn">LRN</label>
                                                <!-- <input type="text" id="firstname" name="firstname" class="form-control"> -->
                                                <input type="text" name="lrn" class="form-control" 
                                                    value="<?php echo ($lrn != "") ? $lrn : ''; ?>">

                                                <div class="form-group">
                                                    <label for="firstname">First Name</label>
                                                    <!-- <input type="text" id="firstname" name="firstname" class="form-control"> -->
                                                    <input type="firstname" name="firstname" class="form-control" 
                                                        value="<?php echo ($firstname != "") ? $firstname : ''; ?>">
                                                </div>

                                                <div class="form-group">
                                                    <label for="middlename">Middle Name</label>
                                                    <input type="middle_name" name="middle_name" class="form-control" 
                                                        value="<?php echo ($middle_name != "") ? $middle_name : ''; ?>">
                                                </div>

                                                <div class="form-group">
                                                    <label for="lastname">Last Name</label>
                                                    <input type="lastname" name="lastname" class="form-control" 
                                                        value="<?php echo ($lastname != "") ? $lastname : ''; ?>">
                                                </div>

                                                <div class="form-group">
                                                    <label for="status">Status</label>
                                                    <select id="status" name="civil_status" class="form-control">
                                                        <option value="Single"<?php echo ($civil_status == "Single") ? " selected" : ""; ?>>Single</option>
                                                        <option value="Married"<?php echo ($civil_status == "Married") ? " selected" : ""; ?>>Married</option>
                                                        <option value="Divorced"<?php echo ($civil_status == "Divorced") ? " selected" : ""; ?>>Divorced</option>
                                                        <option value="Widowed"<?php echo ($civil_status == "Widowed") ? " selected" : ""; ?>>Widowed</option>
                                                    </select>
                                                </div>

                                                <div class="form-group">
                                                    <label for="citizenship">Citizenship</label>
                                                    <input type="nationality" name="nationality" class="form-control" 
                                                        value="<?php echo ($nationality != "") ? $nationality : ''; ?>">
                                                </div>

                                                <div class="form-group">
                                                    <label for="sex">Gender</label>
                                                    <div>
                                                        <!-- <input type="radio" id="male" name="sex" value="Male"> -->
                                                        <input type="radio" name="sex"
                                                            value="Male" <?php echo ($sex == "Male") ? ' checked' : ''; ?>>
                                                        <label for="male">Male</label>
                                                    </div>
                                                    <div>
                                                        <input type="radio" name="sex"
                                                            value="Female" <?php echo ($sex == "Female") ? ' checked' : ''; ?>>
                                                        <label for="female">Female</label>
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <label for="birthday">Birth Date</label>

                                                    <!-- <input type="date" id="birthday" name="birthday" class="form-control"> -->
                                                    <input type="date" id="birthday" name="birthday" class="form-control" value="<?php echo ($birthday != "") ? $birthday : ""; ?>">
                                                </div>

                                                <div class="form-group">
                                                    <label for="birthplace">Birth Place</label>
                                                    <input type="text" id="birthplace" name="birthplace" class="form-control" value="<?php echo ($birthplace != "") ? $birthplace : ""; ?>">

                                                </div>

                                                <div class="form-group">
                                                    <label for="religion">Religion</label>
                                                    <input type="text" id="religion" name="religion" class="form-control" value="<?php echo ($religion != "") ? $religion : ""; ?>">

                                                </div>

                                                <div class="form-group">
                                                    <label for="address">Address</label>
                                                    
                                                    <input type="text" id="address" name="address" class="form-control" value="<?php echo ($address != "") ? $address : ""; ?>">
                                                
                                                </div>

                                                <div class="form-group">
                                                    <label for="contact_number">Phone Number</label>
                                                
                                                    <input type="tel" id="contact_number" name="contact_number" class="form-control" value="<?php echo ($contact_number != "") ? $contact_number : ""; ?>">
                                                </div>

                                                <div class="form-group">
                                                    <label for="email">Email</label>
                                                    <!-- <input type="email" id="email" name="email" class="form-control"> -->
                                                    <input type="email" name="email" class="form-control" 
                                                        value="<?php echo ($email != "") ? $email : ''; ?>">
                                                </div>

                                                <button name="new_step3_btn" type="submit" class="btn btn-success">Confirm</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php
                }
            }



        }


    } 
?>


<?php  include('../includes/footer.php');?>
