<?php 

    include('../registrar_enrollment_header.php');
    include('../../enrollment/classes/StudentEnroll.php');
    include('../../enrollment/classes/OldEnrollees.php');
    include('../classes/Course.php');

    $enroll = new StudentEnroll($con);
    $old_enroll = new OldEnrollees($con, $enroll);
    $course = new Course($con, $enroll);


	$school_year_obj = $enroll->GetLatestSchoolYearAndSemester();

	$current_term = $school_year_obj[0];
	$current_semester = $school_year_obj[1];
	$school_year_id = $school_year_obj[2];
	$strandSelection = $enroll->CreateRegisterStrand();

    $generateStudentUniqueId = $enroll->GenerateUniqueStudentNumber();

    $course_dropdown = $course->GetCourseAvailableSelectionForCurrentSY();
    
    // Ieven if its full, if registrar selects that student placed into that section
    // it should be adjustable.
    if($_GET['id']){
        
        $student_id = $_GET['id'];
        // $student_status = "Transferee";

        $student_username = $enroll->GetStudentUsername($student_id);

        $student_course = $course->GetStudentCourse($student_username);

        $student_obj = $enroll->GetStudentCourseLevelYearIdCourseId($student_username);


        $student_course_level = $student_obj['course_level'];
        // $student_course_level = $enroll->GetStudentCourseLevel($student_username);

        $student_course_id = $student_obj['course_id'];
        $student_status = $student_obj['student_status'];
        // $student_status = $enroll->GetStudentStatus($student_username);

        // $student_course_level_dropdown = $course->GetStudentCourseLevel($student_username);

        if(isset($_POST['student_admission_edit_btn'])){

            $firstname = $_POST['FNAME'] ?? '';
            $lastname = $_POST['LNAME'] ?? '';
            $mi = $_POST['MI'] ?? '';
            $address = $_POST['PADDRESS'] ?? '';
            $optionsRadios = $_POST['optionsRadios'] ?? '';
            $birthdate = $_POST['BIRTHDATE'] ?? '';
            $birthplace = $_POST['BIRTHPLACE'] ?? '';
            $nationality = $_POST['NATIONALITY'] ?? '';
            $religion = $_POST['RELIGION'] ?? '';
            $contact = $_POST['CONTACT'] ?? '';

            $course_id = intval($_POST['course_id']) ?? 0;

            $civilstatus = $_POST['CIVILSTATUS'] ?? '';
            $user_name = $_POST['USER_NAME'] ?? '';

            // $student_password = $_POST['PASS'];

            $guardian = $_POST['GUARDIAN'] ?? '';
            $gcontact = $_POST['GCONTACT'] ?? '';
            $lrn = $_POST['lrn'] ?? 0;
            $student_status = $_POST['student_status'] ?? '';

            // $student_course_level_dropdown = $_POST['student_course_level'];

            // If REGISTRAR updates the student section (course_id)
            // If that room is full, the capacity should adjust accordingly
            
            if($current_semester == "First" && $student_course_level == 11){
                
            }
            // If Transferee Student is Grade 11 2nd sem and above-> Transferee

            if($course_id != 0){

                $get_course = $con->prepare("SELECT course_level FROM course
                    WHERE course_id=:course_id");

                $get_course->bindValue(":course_id", $course_id);
                $get_course->execute();

                if($get_course->rowCount() > 0){

                    $course_course_level = $get_course->fetchColumn();
                    $old_enrollee = 0;
                    $registrar_evaluted = "yes";
                    $update_student = $con->prepare("UPDATE student
                        SET course_id=:course_id,
                            course_level=:course_level,
                            new_enrollee=:new_enrollee

                        WHERE student_id=:student_id
                        AND student_status=:student_status");
                    
                    $update_student->bindValue(":course_id", $course_id, PDO::PARAM_INT);
                    $update_student->bindValue(":course_level", $course_course_level);
                    $update_student->bindValue(":new_enrollee", $old_enrollee);
                    $update_student->bindValue(":student_id", $student_id);
                    $update_student->bindValue(":student_status", $student_status);

                    if($update_student->execute() && $student_status !== "Drop"){

                        $enrollment_tentative = "tentative"; 

                        $get_student_update = $con->prepare("SELECT enrollment_id FROM enrollment

                            WHERE student_id=:student_id
                            AND school_year_id=:school_year_id
                            AND enrollment_status=:enrollment_status
                            ");
                        
                        $get_student_update->bindValue(":student_id", $student_id);
                        $get_student_update->bindValue(":school_year_id", $school_year_id);
                        $get_student_update->bindValue(":enrollment_status", $enrollment_tentative);
                        $get_student_update->execute();

                        if($get_student_update->rowCount() > 0 && $student_status == "Transferee"){
                            //
                            $enrollment_id = $get_student_update->fetchColumn();

                            // echo $enrollment_id;
                            $enrollment_status = "enrolled";
                            $enrolled_transferee = $con->prepare("UPDATE enrollment
                                SET 
                                -- enrollment_status=:enrollment_status,
                                    course_id=:course_id
                                    -- registrar_evaluted=:registrar_evaluted

                                WHERE enrollment_id=:enrollment_id");

                            // $enrolled_transferee->bindValue(":enrollment_status", $enrollment_status);
                            $enrolled_transferee->bindValue(":course_id", $course_id);
                            $enrolled_transferee->bindValue(":enrollment_id", $enrollment_id);
                            // $enrolled_transferee->bindValue(":registrar_evaluted", $registrar_evaluted);

                            if($enrolled_transferee->execute()){
                                echo "Transferee student is now given a section and but not yet enrolled.";
                            }
                        }
                    }else{
                        echo "update->execute() went wrong";
                    }
                }
            }

            if($student_status == "Drop"){

                // Update 1.student_status => Returnee
                // Updating course_id, course_level
                // Updating course_id in the enrollment

                $update_status = "Returnee";

                $update_drop_student = $con->prepare("UPDATE student
                    SET course_id=:course_id,
                        course_level=:course_level,
                        student_status=:update_status
                    WHERE student_id=:student_id
                    AND student_status=:student_status");
                
                $update_drop_student->bindValue(":course_id", $course_id);
                $update_drop_student->bindValue(":course_level", 0);
                $update_drop_student->bindValue(":update_status", $update_status);
                $update_drop_student->bindValue(":student_id", $student_id);
                $update_drop_student->bindValue(":student_status", $student_status);
                
                if($update_drop_student->execute()){

                    $url = "http://localhost/elms/admin/enrollees/subject_insertion.php?username=$user_name&id=$student_id";
                    // header("Location: $url");

                    $get_student_update = $con->prepare("SELECT enrollment_id FROM enrollment
                        WHERE student_id=:student_id
                        AND school_year_id=:school_year_id
                        AND enrollment_status=:enrollment_status
                    ");
                    
                    $get_student_update->bindValue(":student_id", $student_id);
                    $get_student_update->bindValue(":school_year_id", $school_year_id);
                    $get_student_update->bindValue(":enrollment_status", $enrollment_tentative);
                    $get_student_update->execute();

                    if($get_student_update->rowCount() > 0){
                        //
                        $enrollment_status = "enrolled";
                        $enrollment_id = $get_student_update->fetchColumn();

                        // echo $enrollment_id;
                        $enrollment_status = "enrolled";
                        $enrolled_transferee = $con->prepare("UPDATE enrollment
                            SET 
                            -- enrollment_status=:enrollment_status,
                                course_id=:course_id
                            WHERE enrollment_id=:enrollment_id");

                        $enrolled_transferee->bindValue(":course_id", $course_id);
                        $enrolled_transferee->bindValue(":enrollment_id", $enrollment_id);

                        // Prompt message showing the student is placed on that course_id
                        if($enrolled_transferee->execute()){
                            echo "
                                <script>
                                    alert($student_id is placed on $course_id and is now a returnee);
                                </script>
                            ";
                        }

                    }
                }
            }
        }

        $get_student = $con->prepare("SELECT * FROM student
            WHERE student_id=:student_id
            AND student_status=:student_status
            LIMIT 1");
        
        $get_student->bindValue(":student_id", $student_id);
        $get_student->bindValue(":student_status", $student_status);
        $get_student->execute();

        if($get_student->rowCount() > 0){

            $row = $get_student->fetch(PDO::FETCH_ASSOC);
            ?>
                <form class="form-horizontal well" method="post" >
                    <div class="table-responsive">
                    <div class="col-md-8"><h2>Edit Student Account</h2></div>
                    <div class="col-md-4"><label>Academic Year: <?php echo $current_term; ?> <span>(<?php echo $current_semester; ?>)</span> </label></div>
                        <table class="table">
                            <tr>
                                <td><label>Id</label></td>
                                <td>
                                    <input class="form-control input-md" 
                                        readonly id="student_id" name="student_id" placeholder="Student Id" type="text" 
                                        value='<?php echo $row['student_id']?>'>
                                </td>

                                <td ><label>Grade Level</label></td> 
                                <td colspan="1">
                                    <?php 
                                        if($student_course_level != 0){
                                            echo '<input value="Grade ' . $student_course_level . '" readonly class="form-control input-md" type="text">';
                                        }else{
                                            echo '<input value="Not Set" readonly class="form-control input-md" type="text">';
                                             
                                        }
                                    ?>

                                </td>

                                <td ><label>LRN</label></td> 
                                <td colspan="1">
                                    <input class="form-control input-md" 
                                          name="lrn" placeholder="LRN:136-746-XXX" type="text" 
                                        value='<?php echo $row['lrn']?>'>
                                </td>
                            </tr>
                            <tr>
                                <td><label>Firstname</label></td>
                                <td>
                                    <input  value='<?php echo $row['firstname']?>' class="form-control input-md" id="FNAME" name="FNAME" placeholder="First Name" type="text">
                                </td>
                                <td><label>Lastname</label></td>
                                <td colspan="2">
                                    <input value='<?php echo $row['lastname']?>'  class="form-control input-md" id="LNAME" name="LNAME" placeholder="Last Name" type="text">
                                </td> 
                                <td>
                                    <input value='<?php echo $row['middle_name']?>' class="form-control input-md" id="MI" name="MI" placeholder="MI"  maxlength="1" type="text">
                                </td>
                            </tr>
                            <tr>
                                <td><label>Address</label></td>
                                <td colspan="5"  >
                                <input  value='<?php echo $row['address']?>' class="form-control input-md" id="PADDRESS" name="PADDRESS" placeholder="Permanent Address" type="text" >
                                </td> 
                            </tr>
                            <tr>
                                <td ><label>Sex </label></td> 
                                <td colspan="2">
                                    <label>
                                        <input checked id="optionsRadios1" name="optionsRadios" type="radio" value="Female">Female 
                                        <input id="optionsRadios2" name="optionsRadios" type="radio" value="Male"> Male
                                    </label>
                                </td>
                                <td ><label>Date of birth</label></td>
                                <td colspan="2"> 
                                <div class="input-group" >
                                <div class="input-group-addon"> 
                                    <i class="fa fa-calendar"></i>
                                </div>
                                <input value='<?php echo $row['birthday']?>'  name="BIRTHDATE"  id="BIRTHDATE"  type="text" class="form-control input-md"   data-inputmask="'alias': 'mm/dd/yyyy'" data-mask >
                                </div>             
                                </td>
                            </tr>

                            <tr><td><label>Place of Birth</label></td>
                                <td colspan="5">
                                <input value='<?php echo $row['birthplace']?>'  class="form-control input-md" id="BIRTHPLACE" name="BIRTHPLACE" placeholder="Place of Birth" type="text" >
                            </td>
                            </tr>

                            <tr>
                                <td><label>Nationality</label></td>
                                <td colspan="2"><input value='<?php echo $row['nationality']?>'   class="form-control input-md" id="NATIONALITY" name="NATIONALITY" placeholder="Nationality" type="text" >
                                            </td>
                                <td><label>Religion</label></td>
                                <td colspan="2"><input  value='<?php echo $row['religion']?>'  class="form-control input-md" id="RELIGION" name="RELIGION" placeholder="Religion" type="text" >
                                </td>
                            </tr>
                            <tr>
                                <td><label>Contact No.</label></td>
                                    <td value='<?php echo $row['contact_number']?>'  colspan="2"><input class="form-control input-md" id="CONTACT" name="CONTACT" placeholder="Contact Number" type="number" maxlength="11" >
                                    </td>
                                
                                <td><label>Civil Status</label></td>
                            <td colspan="2">
                                <select class="form-control input-sm" name="CIVILSTATUS">
                                    <option value="Single">Single</option>
                                    <option value="Married">Married</option> 
                                    <option value="Widow">Widow</option>
                                </select>
                            </td>
                            </tr>

                            
                            <tr>
                            <td>
                                
                                <label>Strand Section</label></td>
                                <td colspan="2">
                                    <select class="form-control input-sm" name="course_id">
                                        <?php

                                            $selected_course_id = 0; 

                                            $active_yes = "yes";
                                            $get_course = $con->prepare("SELECT * FROM course
                                                                        WHERE school_year_term = :school_year_term
                                                                        AND active = :active
                                                                        ORDER BY school_year_term ASC");

                                            $get_course->bindValue(":school_year_term", $current_term);
                                            $get_course->bindValue(":active", $active_yes);
                                            $get_course->execute();

                                            while ($get_row = $get_course->fetch(PDO::FETCH_ASSOC)) {

                                                $selected = ($get_row['course_id'] == $student_course_id) ? 'selected' : '';

                                                echo "<option value='{$get_row['course_id']}' {$selected}>{$get_row['program_section']}</option>";
                                            }
                                        ?>
                                    </select>
 
                                </td>
                            
                               

                                <td><label>Student Status</label></td>
                                <td colspan="3">
                                    <!-- <select class="form-control input-sm" name="student_status">
                                        <option value="Transferee">Transferee</option>
                                        <option value="Regular">New Enrollee (Regular)</option> 
                                    </select> -->

                                    <select class="form-control input-sm" name="student_status">
                                        <option value="Transferee" <?php if ($row['student_status'] == "Transferee") { echo "selected"; } ?>>Transferee</option>
                                        <option value="Regular" <?php if ($row['student_status'] == "Regular") { echo "selected"; } ?>>New Enrollee (Regular)</option> 
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td><label>Username</label></td>
                                <td colspan="2">
                                <input  value='<?php echo $row['username']?>' class="form-control input-md" id="USER_NAME" name="USER_NAME" placeholder="Username" type="text">
                                </td>
                                <td><label>Password</label></td>
                                <td colspan="2">
                                    <input value="12345"  class="form-control input-md" id="PASS" name="PASS" placeholder="Password" type="password">
                                </td>
                            </tr>
                            <tr>
                                <td><label>Guardian</label></td>
                                <td colspan="2">
                                    <input value='<?php echo $row['guardian_name']?>'  class="form-control input-md" id="GUARDIAN" name="GUARDIAN" placeholder="Parents/Guardian Name" type="text">
                                </td>
                                <td><label>Contact No.</label></td>
                                <td colspan="2"><input value='<?php echo $row['guardian_contact_number']?>'  class="form-control input-md" id="GCONTACT" name="GCONTACT" placeholder="Contact Number" type="number" ></td>
                            </tr>
                            <tr>
                            <td></td>
                                <td colspan="5">	
                                    <button class="btn btn-success btn-lg" name="student_admission_edit_btn" type="submit">Submit</button>
                                </td>
                            </tr>
                        </table>
                    </div>
                </form>

            <?php
        }

    }

?>

