<script src="../assets/js/common.js"></script>

<?php 

    include('../registrar_enrollment_header.php');
    include('../../enrollment/classes/StudentEnroll.php');
    include('../../enrollment/classes/OldEnrollees.php');
    include('../classes/Subject.php');
    include('../classes/Course.php');

    $enroll = new StudentEnroll($con);
    $old_enroll = new OldEnrollees($con, $enroll);
    $course = new Course($con, $enroll);

    if(isset($_GET['id'])){

        $student_id = $_GET['id'];

        
        $subject = new Subject($con, $student_id);

        $school_year_obj = $enroll->GetLatestSchoolYearAndSemester();

        $current_term = $school_year_obj[0];
        $current_semester = $school_year_obj[1];
        $school_year_id = $school_year_obj[2];

        $student_username = $enroll->GetStudentUsername($student_id);

        $userLoggedInId = $enroll->GetStudentId($student_username);
        $course_main_id = $enroll->GetStudentCourseId($student_username);


        $student_course = $course->GetStudentCourse($student_username);

        $student_obj = $enroll->GetStudentCourseLevelYearIdCourseId($student_username);


        $student_course_level = $student_obj['course_level'];
        // $student_course_level = $enroll->GetStudentCourseLevel($student_username);
        $section_name = $enroll->GetStudentCourseName($student_username);

        $student_course_id = $student_obj['course_id'];
        $student_status = $student_obj['student_status'];


        $get_student = $con->prepare("SELECT * FROM student
            WHERE student_id=:student_id
            AND student_status=:student_status
            LIMIT 1");
        
        $get_student->bindValue(":student_id", $student_id);
        $get_student->bindValue(":student_status", $student_status);
        $get_student->execute();


        $GRADE_ELEVEN = 11;
        $GRADE_TWELVE = 11;
        $FIRST_SEMESTER = "First";
        $SECOND_SEMESTER = "Second";

        if($get_student->rowCount() > 0){

            $row = $get_student->fetch(PDO::FETCH_ASSOC);


            // $program_id = $row['program_id'];
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
            // $pending_enrollees_id = $row['pending_enrollees_id'];
            // $password = $row['password'];
            $civil_status = $row['civil_status'];
            $nationality = $row['nationality'];
            $age = $row['age'];
            $guardian_name = $row['guardian_name'];
            $guardian_contact_number = $row['guardian_contact_number'];
            $lrn = $row['lrn'];
            $student_unique_id = $row['student_unique_id'];


            if(isset($_GET['profile']) && $_GET['profile'] == "show"){
            ?>
                <div class="row col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <h4><?php echo $lastname?>, <?php echo $firstname;?> <?php echo $middle_name;?>,</h4>
                            <hr>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="container">
                                        <div class="form-group">
                                            <label for="">Student No.</label>
                                            <p for=""><?php echo $student_unique_id;?></p>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="container">
                                        <div class="form-group">
                                            <label for="">Level</label>
                                            <p for=""><?php echo $student_course_level;?></p>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="container">
                                        <div class="form-group">
                                            <label for="">Section</label>
                                            <p for=""><?php echo $section_name;?></p>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="container">
                                        <div class="form-group">
                                            <label for="">Status</label>
                                            <p for="">Enrolled</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr>
                    <hr>

                    <div class="card">
                        <div class="card-body">
                                                <div class="row col-md-12">
                        <div class="col-md-6 text-center">
                            <a href="view_details.php?id=<?php echo $student_id;?>">
                                <button class="btn btn-lg btn-primary">
                                    Student Information
                                </button>
                            </a>
                        </div>

                       <div class="col-md-6 text-center">
                            <a href="view_details.php?subject=show&id=<?php echo $student_id;?>">
                                <button class="btn btn-lg btn-outline-primary">
                                    Enrolled Subjects
                                </button>
                            </a>
                        </div>

                    </div>
                        </div>
                    </div>

                    <hr>
                    <hr>
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <h2 class="mb-3 text-center text-primary">Student Information</h2>
                                <hr>
                                <div class="col-md-4 mb-4">
                                    <label style="font-weight: bold;" class="text-muted">
                                        Academic Year: <?php echo $current_term; ?> 
                                        <span>(<?php echo $current_semester; ?>)</span> 
                                    </label>
                                </div>
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
                                        <td><label>Guardian</label></td>
                                        <td colspan="2">
                                            <input value='<?php echo $row['guardian_name']?>'  class="form-control input-md" id="GUARDIAN" name="GUARDIAN" placeholder="Parents/Guardian Name" type="text">
                                        </td>
                                        <td><label>Contact No.</label></td>
                                        <td colspan="2"><input value='<?php echo $row['guardian_contact_number']?>'  class="form-control input-md" id="GCONTACT" name="GCONTACT" placeholder="Contact Number" type="number" ></td>
                                    </tr>
                                
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
            <?php
            }



            if(isset($_GET['subject']) 
                && $_GET['subject'] == "show" && isset($_GET['id'])){

                $studentEnroll = new StudentEnroll($con);

                $username = $studentEnroll->GetStudentUsername($_GET['id']);
                $userLoggedInId = $studentEnroll->GetStudentId($username);
                $course_main_id = $studentEnroll->GetStudentCourseId($username);
                $student_course_level = $studentEnroll->GetStudentCourseLevel($username);

                $school_year_obj = $studentEnroll->GetActiveSchoolYearAndSemester();

                $current_school_year_id = $school_year_obj['school_year_id'];
                $current_school_year_term = $school_year_obj['term'];
                $current_school_year_period = $school_year_obj['period'];

                ?>
                <div class="row col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <h4><?php echo $lastname?>, <?php echo $firstname;?> <?php echo $middle_name;?>,</h4>
                            <hr>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="container">
                                        <div class="form-group">
                                            <label for="">Student No.</label>
                                            <p for=""><?php echo $student_unique_id;?></p>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="container">
                                        <div class="form-group">
                                            <label for="">Level</label>
                                            <p for=""><?php echo $student_course_level;?></p>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="container">
                                        <div class="form-group">
                                            <label for="">Section</label>
                                            <p for=""><?php echo $section_name;?></p>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="container">
                                        <div class="form-group">
                                            <label for="">Status</label>
                                            <p for="">Enrolled</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr>
                    <hr>

                    <div class="card">
                        <div class="card-body">
                            <div class="row col-md-12">
                                <div class="col-md-6 text-center">
                                    <a href="view_details.php?profile=show&id=<?php echo $student_id;?>">
                                        <button class="btn btn-lg btn-outline-primary">
                                            Student Information
                                        </button>
                                    </a>
                                </div>

                            <div class="col-md-6 text-center">
                                    <a href="view_details.php?subject=show&id=<?php echo $student_id;?>">
                                        <button class="btn btn-lg btn-primary">
                                            Enrolled Subjects
                                        </button>
                                    </a>
                                </div>

                            </div>
                        </div>
                    </div>

                    <hr>

                    <!-- GRADE 11 2ND SEM SHOULE BE REMOVE - the bottom is the replace on-->
                    <!-- If you only have enrolled in the semester, it only shows -->
                    <?php 

                        if($subject->CheckStudentGradeLevelEnrolledSubjectSemesterv2(
                            $student_id, $SECOND_SEMESTER, $GRADE_ELEVEN)){
                            

                            # ONLY FOR GRADE 11 2nd BECAUSE OF MOVING UP.
                            $isFinished = $old_enroll->CheckIfGradeLevelSemesterSubjectWereAllPassed(
                                $student_id, $GRADE_ELEVEN, $SECOND_SEMESTER);

                                // echo "wee";
                            ?>
                                <div class="row col-md-12 table-responsive"
                                    style="margin-top:5%;">
                                    <div class="table-responsive" style="display: none; margin-top:2%;"> 
                                        <?php 
                                        
                                            $GRADE_TWELVE = 12;
                                            $GRADE_ELEVEN = 11;
                                            $SECOND_SEMESTER = "Second";

                                            // Section Based on the enrollment.
                                            $enrollment_school_year = $studentEnroll->GetStudentSectionGradeElevenSchoolYear($username, $userLoggedInId, $GRADE_ELEVEN, $SECOND_SEMESTER);

                                            if($enrollment_school_year !== null){
                                                $term = $enrollment_school_year['term'];
                                                $period = $enrollment_school_year['period'];
                                                $school_year_id = $enrollment_school_year['school_year_id'];
                                                $enrollment_course_id = $enrollment_school_year['course_id'];

                                                $enrollment_section_name = $enroll->GetStudentCourseNameByCourseId($enrollment_course_id);

                                                echo "
                                                    <h4 style='font-weight: 500;' class='text-primary text-center mb-3'>
                                                        Grade 11 $enrollment_section_name $period Semester (SY $term)
                                                    </h4>
                                                ";
                                            }
                                        
                                        ?> 	

                                        <?php 
        
                                            $moveUpBtn = "moveUpAction(\"$student_username\")";
                                            
                                            if($isFinished == true && $student_course_level != $GRADE_TWELVE){
                                                echo "
                                                    <button type='button' onclick='$moveUpBtn' class='btn btn-success'>
                                                        Move Up
                                                    </button>				
                                                ";
                                            }
                                        ?>
                                        <table style=" font-weight: 200;" class="table table-striped table-bordered table-hover "  style="font-size:12px" cellspacing="0"  > 
                                            <thead>
                                                <tr class="text-center"> 
                                                    <th class="text-success" rowspan="2">Subject</th>
                                                    <th class="text-success" rowspan="2">Type</th>
                                                    <th class="text-success" rowspan="2">Description</th>  
                                                    <th class="text-success" rowspan="2">Unit</th>
                                                    <th class="text-muted" colspan="4">Schedule</th> 
                                                </tr>	
                                                <tr> 
                                                    <!-- <th>Day</th> 
                                                    <th>Time</th>
                                                    <th>Room</th> 
                                                    <th>Section</th>   -->
                                                    <th class="text-success">Semester</th>  
                                                    <th class="text-success">Course Level</th>  
                                                    <th class="text-success">Remarks</th>  
                                                </tr>
                                            </thead> 	 
                                            <tbody>
                                                <?php 

                                                    // $listOfSubjects = $studentEnroll->GetStudentSubjectListWithGrades($username);
                                                    $listOfSubjects = $studentEnroll->
                                                        GetStudentSectionGradeElevenSemester($username,
                                                            $student_id, $GRADE_ELEVEN, $SECOND_SEMESTER);

                                                    if($listOfSubjects !== null){



                                                        foreach ($listOfSubjects as $key => $value) {

                                                            $subject_id = $value['subject_id'];
                                                            $course_level = $value['course_level'];

                                                            $remarks_url = "";

                                                            $query_student_subject = $con->prepare("SELECT 
                                                        
                                                                t1.subject_id, t1.student_subject_id as t1_student_subject_id,
                                                                
                                                                t2.student_subject_id as t2_student_subject_id,
                                                                t2.remarks

                                                                FROM student_subject as t1

                                                                LEFT JOIN student_subject_grade as t2 ON t2.student_subject_id = t1.student_subject_id

                                                                WHERE t1.subject_id=:subject_id
                                                                AND t1.student_id=:student_id
                                                                LIMIT 1");

                                                            $query_student_subject->bindValue(":subject_id", $subject_id);
                                                            $query_student_subject->bindValue(":student_id", $student_id);
                                                            $query_student_subject->execute();


                                                            $t1_student_subject_id = null;

                                                                // echo $subject_id . " 1 ";

                                                            if($query_student_subject->rowCount() > 0){

                                                                $row = $query_student_subject->fetch(PDO::FETCH_ASSOC);

                                                                $student_subject_subject_id = $row['subject_id'];
                                                                $t1_student_subject_id = $row['t1_student_subject_id'];
                                                                $t2_student_subject_id = $row['t2_student_subject_id'];
                                                                $remarks = $row['remarks'];

                                                                // echo $t1_student_subject_id . " ";
                                                                // echo $t2_student_subject_id . " ";

                                                                if($t1_student_subject_id == $t2_student_subject_id){

                                                                    $remarks_url = $remarks;

                                                                }else if($student_subject_subject_id == $subject_id
                                                                    && $t1_student_subject_id != $t2_student_subject_id){

                                                                    $remarks_url = "
                                                                        <a href='shs_transferee_remarks.php?student_id=$student_id&s_id=$subject_id&page=view_details'>
                                                                            <button type='button' class='btn btn-sm btn-primary'>Remark</button>
                                                                        </a>
                                                                    ";
                                                                }
                                                            }
                                                        
                                                            echo '<tr class="text-center">'; 
                                                                    echo '<td>'.$value['subject_code'].'</td>';
                                                                    echo '<td>'.$value['subject_type'].'</td>';
                                                                    echo '<td>'.$value['subject_title'].'</td>';
                                                                    echo '<td>'.$value['unit'].'</td>';
                                                                    // echo '<td>'.$schedule_day.'</td>';
                                                                    // echo '<td>'.$schedule_time.'</td>';
                                                                    // echo '<td>'.$room.'</td>';
                                                                    // echo '<td>'.$section.'</td>';
                                                                    echo '<td>'.$value['semester'].'</td>';
                                                                    echo '<td>'.$course_level.'</td>';
                                                                    echo '<td>'.$remarks_url.'</td>';
                                                            echo '</tr>';
                                                        }     
                                                    }
                                                    else{
                                                        echo "No Datax was found for Grade 11 2nd Semester.";
                                                    }
                                                    
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            <?php
                        } 
                    ?>

                    <!-- GRADE 11 1st SEM -->
                    <div class="row col-md-12 table-responsive"
                        style="margin-top:5%;">
                        <div class="table-responsive" style="margin-top:2%;"> 
                            

                            <!-- <h3>Grade 11 First Semester</h3>	 -->
                            <?php 
                                // echo "wee";
                                $GRADE_TWELVE = 12;
                                $GRADE_ELEVEN = 11;
                                $FIRST_SEMESTER = "First";

                                // Section Based on the enrollment.
                                $enrollment_school_year = $studentEnroll->GetStudentSectionGradeElevenSchoolYear($username, $userLoggedInId, 11, "First");

                                if($enrollment_school_year !== null){
                                    $term = $enrollment_school_year['term'];
                                    $period = $enrollment_school_year['period'];
                                    $school_year_id = $enrollment_school_year['school_year_id'];
                                    $enrollment_course_id = $enrollment_school_year['course_id'];


                                    $enrollment_section_name = $enroll->GetStudentCourseNameByCourseId($enrollment_course_id);

                                    echo "
                                        <h4 style='font-weight: 500;' class='text-primary text-center mb-3'>
                                            Grade 11 $enrollment_section_name $period Semester (SY $term)
                                        </h4>
                                    ";
                                }else{
                                    echo "
                                        <h3>Grade 11 First Semester</h3>	
                                    ";
                                }
                            ?>	
                            
                            <?php 
                                # ONLY FOR GRADE 11 2nd BECAUSE OF MOVING UP.
                                $isFinished = $old_enroll->CheckIfGradeLevelSemesterSubjectWereAllPassed(
                                    $student_id, $GRADE_ELEVEN, $SECOND_SEMESTER);
                                
                                $moveUpBtn = "moveUpAction(\"$student_username\")";
                                
                                if($isFinished == true && $student_course_level != $GRADE_TWELVE){
                                    echo "
                                        <button type='button' onclick='$moveUpBtn' class='btn btn-success'>
                                            Move Up
                                        </button>				
                                    ";
                                }
                            ?>
                            <table style="font-weight: 200;" class="table table-striped table-bordered table-hover "  style="font-size:12px" cellspacing="0"  > 
                                <thead>
                                    <tr class="text-center"> 
                                        <th class="text-success" rowspan="2">Subject</th>
                                        <th class="text-success" rowspan="2">Type</th>
                                        <th class="text-success" rowspan="2">Description</th>  
                                        <th class="text-success" rowspan="2">Unit</th>
                                        <th class="text-muted" colspan="4">Schedule</th> 
                                    </tr>	
                                    <tr> 
                                        <!-- <th>Day</th> 
                                        <th>Time</th>
                                        <th>Room</th> 
                                        <th>Section</th>   -->
                                        <th class="text-success">Semester</th>  
                                        <th class="text-success">Course Level</th>  
                                        <th class="text-success">Remarks</th>  
                                    </tr>
                                </thead> 	 
                                <tbody>
                                    <?php 

                                        // $listOfSubjects = $studentEnroll->GetStudentSubjectListWithGrades($username);
                                        $listOfSubjects = $studentEnroll
                                            ->GetStudentCurriculumBasedOnSemesterSubject($username,
                                        $userLoggedInId, $GRADE_ELEVEN, $FIRST_SEMESTER);

                                        if($listOfSubjects !== null){

                                            foreach ($listOfSubjects as $key => $value) {
                                                 
                                            $subject_id = $value['subject_id'];
                                                $course_level = $value['course_level'];

                                                $remarks_url = "";

                                                $query_student_subject = $con->prepare("SELECT 
                                            
                                                    t1.subject_id, t1.student_subject_id as t1_student_subject_id,
                                                    
                                                    t2.student_subject_id as t2_student_subject_id,
                                                    t2.remarks

                                                    FROM student_subject as t1

                                                    LEFT JOIN student_subject_grade as t2 ON t2.student_subject_id = t1.student_subject_id

                                                    WHERE t1.subject_id=:subject_id
                                                    AND t1.student_id=:student_id
                                                    LIMIT 1");

                                                $query_student_subject->bindValue(":subject_id", $subject_id);
                                                $query_student_subject->bindValue(":student_id", $student_id);
                                                $query_student_subject->execute();


                                                $t1_student_subject_id = null;

                                                    // echo $subject_id . " 1 ";

                                                if($query_student_subject->rowCount() > 0){

                                                    $row = $query_student_subject->fetch(PDO::FETCH_ASSOC);

                                                    $student_subject_subject_id = $row['subject_id'];
                                                    $t1_student_subject_id = $row['t1_student_subject_id'];
                                                    $t2_student_subject_id = $row['t2_student_subject_id'];
                                                    $remarks = $row['remarks'];

                                                    // echo $t1_student_subject_id . " ";
                                                    // echo $t2_student_subject_id . " ";

                                                    if($t1_student_subject_id == $t2_student_subject_id){

                                                        $remarks_url = $remarks;

                                                    }else if($student_subject_subject_id == $subject_id
                                                        && $t1_student_subject_id != $t2_student_subject_id){

                                                        $remarks_url = "
                                                            <a href='shs_transferee_remarks.php?student_id=$student_id&s_id=$subject_id&page=view_details'>
                                                                <button type='button' class='btn btn-sm btn-primary'>Remark</button>
                                                            </a>
                                                        ";
                                                    }
                                                }
                                            
                                                echo '<tr class="text-center">'; 
                                                        echo '<td>'.$value['subject_code'].'</td>';
                                                        echo '<td>'.$value['subject_type'].'</td>';
                                                        echo '<td>'.$value['subject_title'].'</td>';
                                                        echo '<td>'.$value['unit'].'</td>';
                                                        // echo '<td>'.$schedule_day.'</td>';
                                                        // echo '<td>'.$schedule_time.'</td>';
                                                        // echo '<td>'.$room.'</td>';
                                                        // echo '<td>'.$section.'</td>';
                                                        echo '<td>'.$value['semester'].'</td>';
                                                        echo '<td>'.$value['course_level'].'</td>';
                                                        echo '<td>'.$remarks_url.'</td>';
                                                echo '</tr>';
                                            }     
                                        }
                                        else{
                                            echo "No Datax was found for Grade 11 1st Semester.";
                                        }
                                        
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- GRADE 11 2nd SEM -->
                    <div class="row col-md-12 table-responsive"
                        style="margin-top:5%;">
                        <div class="table-responsive" style="margin-top:2%;"> 
                            
                            <?php 
                                // echo "wee";
                                $GRADE_TWELVE = 12;
                                $GRADE_ELEVEN = 11;
                                $FIRST_SEMESTER = "First";

                                // Section Based on the enrollment.
                                $enrollment_school_year = $studentEnroll->GetStudentSectionGradeLevelSemester($username, $userLoggedInId,
                                    $GRADE_ELEVEN, $SECOND_SEMESTER);

                                if($enrollment_school_year !== null){
                                    $term = $enrollment_school_year['term'];
                                    $period = $enrollment_school_year['period'];
                                    $school_year_id = $enrollment_school_year['school_year_id'];
                                    $enrollment_course_id = $enrollment_school_year['course_id'];


                                    $enrollment_section_name = $enroll->GetStudentCourseNameByCourseId($enrollment_course_id);

                                    echo "
                                        <h4 style='font-weight: 500;' class='text-primary text-center mb-3'>
                                            Grade 11 $enrollment_section_name $period Semester (SY $term)
                                        </h4>
                                    ";
                                }else{
                                    echo "
                                        <h3>Grade 11 Second Semester</h3>	
                                    ";
                                }
                            ?>		

                            <table style="font-weight: 200;" class="table table-striped table-bordered table-hover "  style="font-size:12px" cellspacing="0"  > 
                                <thead>
                                    <tr class="text-center"> 
                                        <th class="text-success" rowspan="2">Subject</th>
                                        <th class="text-success" rowspan="2">Type</th>
                                        <th class="text-success" rowspan="2">Description</th>  
                                        <th class="text-success" rowspan="2">Unit</th>
                                        <th class="text-muted" colspan="4">Schedule</th> 
                                    </tr>	
                                    <tr> 
                                        <!-- <th>Day</th> 
                                        <th>Time</th>
                                        <th>Room</th> 
                                        <th>Section</th>   -->
                                        <th class="text-success">Semester</th>  
                                        <th class="text-success">Course Level</th>  
                                        <th class="text-success">Remarks</th>  
                                    </tr>
                                </thead> 	 
                                <tbody>
                                    <?php 

                                        // $listOfSubjects = $studentEnroll->GetStudentSubjectListWithGrades($username);
                                        $listOfSubjects = $studentEnroll
                                            ->GetStudentCurriculumBasedOnSemesterSubject($username,
                                        $userLoggedInId, $GRADE_ELEVEN, $SECOND_SEMESTER);

                                        if($listOfSubjects !== null){

                                            foreach ($listOfSubjects as $key => $value) {
                                                 
                                            $subject_id = $value['subject_id'];
                                                $course_level = $value['course_level'];

                                                $remarks_url = "";

                                                $query_student_subject = $con->prepare("SELECT 
                                            
                                                    t1.subject_id, t1.student_subject_id as t1_student_subject_id,
                                                    
                                                    t2.student_subject_id as t2_student_subject_id,
                                                    t2.remarks

                                                    FROM student_subject as t1

                                                    LEFT JOIN student_subject_grade as t2 ON t2.student_subject_id = t1.student_subject_id

                                                    WHERE t1.subject_id=:subject_id
                                                    AND t1.student_id=:student_id
                                                    LIMIT 1");

                                                $query_student_subject->bindValue(":subject_id", $subject_id);
                                                $query_student_subject->bindValue(":student_id", $student_id);
                                                $query_student_subject->execute();


                                                $t1_student_subject_id = null;

                                                    // echo $subject_id . " 1 ";

                                                if($query_student_subject->rowCount() > 0){

                                                    $row = $query_student_subject->fetch(PDO::FETCH_ASSOC);

                                                    $student_subject_subject_id = $row['subject_id'];
                                                    $t1_student_subject_id = $row['t1_student_subject_id'];
                                                    $t2_student_subject_id = $row['t2_student_subject_id'];
                                                    $remarks = $row['remarks'];

                                                    // echo $t1_student_subject_id . " ";
                                                    // echo $t2_student_subject_id . " ";

                                                    if($t1_student_subject_id == $t2_student_subject_id){

                                                        $remarks_url = $remarks;

                                                    }else if($student_subject_subject_id == $subject_id
                                                        && $t1_student_subject_id != $t2_student_subject_id){

                                                        $remarks_url = "
                                                            <a href='shs_transferee_remarks.php?student_id=$student_id&s_id=$subject_id&page=view_details'>
                                                                <button type='button' class='btn btn-sm btn-primary'>Remark</button>
                                                            </a>
                                                        ";
                                                    }
                                                }
                                            
                                                echo '<tr class="text-center">'; 
                                                        echo '<td>'.$value['subject_code'].'</td>';
                                                        echo '<td>'.$value['subject_type'].'</td>';
                                                        echo '<td>'.$value['subject_title'].'</td>';
                                                        echo '<td>'.$value['unit'].'</td>';
                                                        // echo '<td>'.$schedule_day.'</td>';
                                                        // echo '<td>'.$schedule_time.'</td>';
                                                        // echo '<td>'.$room.'</td>';
                                                        // echo '<td>'.$section.'</td>';
                                                        echo '<td>'.$value['semester'].'</td>';
                                                        echo '<td>'.$value['course_level'].'</td>';
                                                        echo '<td>'.$remarks_url.'</td>';
                                                echo '</tr>';
                                            }     
                                        }
                                        else{
                                            echo "No Datax was found for Grade 11 1st Semester.";
                                        }
                                        
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>


                    <!-- GRADE 12 1st SEM -->
                    <div class="row col-md-12 table-responsive"
                        style="margin-top:5%;">
                        <div class="table-responsive" style="margin-top:2%;"> 
                            
                            <?php 
                                // echo "wee";
                                $GRADE_TWELVE = 12;
                                $GRADE_ELEVEN = 11;
                                $FIRST_SEMESTER = "First";

                                // Section Based on the enrollment.
                                $enrollment_school_year = $studentEnroll->GetStudentSectionGradeLevelSemester($username, $userLoggedInId,
                                    $GRADE_TWELVE, $FIRST_SEMESTER);

                                if($enrollment_school_year !== null){
                                    $term = $enrollment_school_year['term'];
                                    $period = $enrollment_school_year['period'];
                                    $school_year_id = $enrollment_school_year['school_year_id'];
                                    $enrollment_course_id = $enrollment_school_year['course_id'];


                                    $enrollment_section_name = $enroll->GetStudentCourseNameByCourseId($enrollment_course_id);

                                    echo "
                                        <h4 style='font-weight: 500;' class='text-primary text-center mb-3'>
                                            Grade 12 $enrollment_section_name $period Semester (SY $term)
                                        </h4>
                                    ";
                                }else{
                                    echo "<h3 class='text-center'>Grade 12 First Semester</h3>";	
                                    
                                }
                            ?>		

                            <table style="font-weight: 200;" class="table table-striped table-bordered table-hover "  style="font-size:12px" cellspacing="0"  > 
                                <thead>
                                    <tr class="text-center"> 
                                        <th class="text-success" rowspan="2">Subject</th>
                                        <th class="text-success" rowspan="2">Type</th>
                                        <th class="text-success" rowspan="2">Description</th>  
                                        <th class="text-success" rowspan="2">Unit</th>
                                        <th class="text-muted" colspan="4">Schedule</th> 
                                    </tr>	
                                    <tr class="text-center"> 

                                        <!-- <th>Day</th> 
                                        <th>Time</th>
                                        <th>Room</th> 
                                        <th>Section</th>   -->
                                        <th class="text-success">Semester</th>  
                                        <th class="text-success">Course Level</th>  
                                        <th class="text-success">Remarks</th>  
                                    </tr>
                                </thead> 	 
                                <tbody>
                                    <?php 

                                        // $listOfSubjects = $studentEnroll->GetStudentSubjectListWithGrades($username);
                                        $listOfSubjects = $studentEnroll
                                            ->GetStudentCurriculumBasedOnSemesterSubject($username,
                                        $userLoggedInId, $GRADE_TWELVE, $FIRST_SEMESTER);

                                        if($listOfSubjects !== null){

                                            foreach ($listOfSubjects as $key => $value) {
                                                 
                                            $subject_id = $value['subject_id'];
                                                $course_level = $value['course_level'];

                                                $remarks_url = "";

                                                $query_student_subject = $con->prepare("SELECT 
                                            
                                                    t1.subject_id, t1.student_subject_id as t1_student_subject_id,
                                                    
                                                    t2.student_subject_id as t2_student_subject_id,
                                                    t2.remarks

                                                    FROM student_subject as t1

                                                    LEFT JOIN student_subject_grade as t2 ON t2.student_subject_id = t1.student_subject_id

                                                    WHERE t1.subject_id=:subject_id
                                                    AND t1.student_id=:student_id
                                                    LIMIT 1");

                                                $query_student_subject->bindValue(":subject_id", $subject_id);
                                                $query_student_subject->bindValue(":student_id", $student_id);
                                                $query_student_subject->execute();


                                                $t1_student_subject_id = null;

                                                    // echo $subject_id . " 1 ";

                                                if($query_student_subject->rowCount() > 0){

                                                    $row = $query_student_subject->fetch(PDO::FETCH_ASSOC);

                                                    $student_subject_subject_id = $row['subject_id'];
                                                    $t1_student_subject_id = $row['t1_student_subject_id'];
                                                    $t2_student_subject_id = $row['t2_student_subject_id'];
                                                    $remarks = $row['remarks'];

                                                    // echo $t1_student_subject_id . " ";
                                                    // echo $t2_student_subject_id . " ";

                                                    if($t1_student_subject_id == $t2_student_subject_id){

                                                        $remarks_url = $remarks;

                                                    }else if($student_subject_subject_id == $subject_id
                                                        && $t1_student_subject_id != $t2_student_subject_id){

                                                        $remarks_url = "
                                                            <a href='shs_transferee_remarks.php?student_id=$student_id&s_id=$subject_id&page=view_details'>
                                                                <button type='button' class='btn btn-sm btn-primary'>Remark</button>
                                                            </a>
                                                        ";
                                                    }
                                                }
                                            
                                                echo '<tr class="text-center">'; 
                                                        echo '<td>'.$value['subject_code'].'</td>';
                                                        echo '<td>'.$value['subject_type'].'</td>';
                                                        echo '<td>'.$value['subject_title'].'</td>';
                                                        echo '<td>'.$value['unit'].'</td>';
                                                        // echo '<td>'.$schedule_day.'</td>';
                                                        // echo '<td>'.$schedule_time.'</td>';
                                                        // echo '<td>'.$room.'</td>';
                                                        // echo '<td>'.$section.'</td>';
                                                        echo '<td>'.$value['semester'].'</td>';
                                                        echo '<td>'.$value['course_level'].'</td>';
                                                        echo '<td>'.$remarks_url.'</td>';
                                                echo '</tr>';
                                            }     
                                        }
                                        else{
                                            echo "No Datax was found for Grade 11 1st Semester.";
                                        }
                                        
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>


                    <!-- GRADE 12 1st SEM -->
                    <div class="row col-md-12 table-responsive"
                        style="margin-top:5%;">
                        <div class="table-responsive" style="margin-top:2%;"> 
                            
                            <?php 
                                // echo "wee";
                                $GRADE_TWELVE = 12;
                                $GRADE_ELEVEN = 11;
                                $FIRST_SEMESTER = "First";

                                // Section Based on the enrollment.
                                $enrollment_school_year = $studentEnroll->GetStudentSectionGradeLevelSemester($username, $userLoggedInId,
                                    $GRADE_TWELVE, $SECOND_SEMESTER);

                                if($enrollment_school_year !== null){
                                    $term = $enrollment_school_year['term'];
                                    $period = $enrollment_school_year['period'];
                                    $school_year_id = $enrollment_school_year['school_year_id'];
                                    $enrollment_course_id = $enrollment_school_year['course_id'];


                                    $enrollment_section_name = $enroll->GetStudentCourseNameByCourseId($enrollment_course_id);

                                    echo "
                                        <h4 style='font-weight: 500;' class='text-primary text-center mb-3'>
                                            Grade 12 $enrollment_section_name $period Semester (SY $term)
                                        </h4>
                                    ";
                                }else{
                                    echo "<h3 class='text-center'>Grade 12 Second Semester</h3>";	
                                    
                                }
                            ?>		

                            <table style="font-weight: 200;" class="table table-striped table-bordered table-hover "  style="font-size:12px" cellspacing="0"  > 
                                <thead>
                                    <tr class="text-center"> 
                                        <th class="text-success" rowspan="2">Subject</th>
                                        <th class="text-success" rowspan="2">Type</th>
                                        <th class="text-success" rowspan="2">Description</th>  
                                        <th class="text-success" rowspan="2">Unit</th>
                                        <th class="text-muted" colspan="4">Schedule</th> 
                                    </tr>	
                                    <tr class="text-center"> 

                                        <!-- <th>Day</th> 
                                        <th>Time</th>
                                        <th>Room</th> 
                                        <th>Section</th>   -->
                                        <th class="text-success">Semester</th>  
                                        <th class="text-success">Course Level</th>  
                                        <th class="text-success">Remarks</th>  
                                    </tr>
                                </thead> 	 
                                <tbody>
                                    <?php 

                                        // $listOfSubjects = $studentEnroll->GetStudentSubjectListWithGrades($username);
                                        $listOfSubjects = $studentEnroll
                                            ->GetStudentCurriculumBasedOnSemesterSubject($username,
                                        $userLoggedInId, $GRADE_TWELVE, $SECOND_SEMESTER);

                                        if($listOfSubjects !== null){

                                            foreach ($listOfSubjects as $key => $value) {
                                                 
                                            $subject_id = $value['subject_id'];
                                                $course_level = $value['course_level'];

                                                $remarks_url = "";

                                                $query_student_subject = $con->prepare("SELECT 
                                            
                                                    t1.subject_id, t1.student_subject_id as t1_student_subject_id,
                                                    
                                                    t2.student_subject_id as t2_student_subject_id,
                                                    t2.remarks

                                                    FROM student_subject as t1

                                                    LEFT JOIN student_subject_grade as t2 ON t2.student_subject_id = t1.student_subject_id

                                                    WHERE t1.subject_id=:subject_id
                                                    AND t1.student_id=:student_id
                                                    LIMIT 1");

                                                $query_student_subject->bindValue(":subject_id", $subject_id);
                                                $query_student_subject->bindValue(":student_id", $student_id);
                                                $query_student_subject->execute();


                                                $t1_student_subject_id = null;

                                                    // echo $subject_id . " 1 ";

                                                if($query_student_subject->rowCount() > 0){

                                                    $row = $query_student_subject->fetch(PDO::FETCH_ASSOC);

                                                    $student_subject_subject_id = $row['subject_id'];
                                                    $t1_student_subject_id = $row['t1_student_subject_id'];
                                                    $t2_student_subject_id = $row['t2_student_subject_id'];
                                                    $remarks = $row['remarks'];

                                                    // echo $t1_student_subject_id . " ";
                                                    // echo $t2_student_subject_id . " ";

                                                    if($t1_student_subject_id == $t2_student_subject_id){

                                                        $remarks_url = $remarks;

                                                    }else if($student_subject_subject_id == $subject_id
                                                        && $t1_student_subject_id != $t2_student_subject_id){

                                                        $remarks_url = "
                                                            <a href='shs_transferee_remarks.php?student_id=$student_id&s_id=$subject_id&page=view_details'>
                                                                <button type='button' class='btn btn-sm btn-primary'>Remark</button>
                                                            </a>
                                                        ";
                                                    }
                                                }
                                            
                                                echo '<tr class="text-center">'; 
                                                        echo '<td>'.$value['subject_code'].'</td>';
                                                        echo '<td>'.$value['subject_type'].'</td>';
                                                        echo '<td>'.$value['subject_title'].'</td>';
                                                        echo '<td>'.$value['unit'].'</td>';
                                                        // echo '<td>'.$schedule_day.'</td>';
                                                        // echo '<td>'.$schedule_time.'</td>';
                                                        // echo '<td>'.$room.'</td>';
                                                        // echo '<td>'.$section.'</td>';
                                                        echo '<td>'.$value['semester'].'</td>';
                                                        echo '<td>'.$value['course_level'].'</td>';
                                                        echo '<td>'.$remarks_url.'</td>';
                                                echo '</tr>';
                                            }     
                                        }
                                        else{
                                            echo "No Datax was found for Grade 11 1st Semester.";
                                        }
                                        
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                <?php
            }

        }


    }
?>