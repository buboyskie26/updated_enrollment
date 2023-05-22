<?php 
    
    include('../registrar_enrollment_header.php');
    include('../../enrollment/classes/StudentEnroll.php');
    include('../../enrollment/classes/OldEnrollees.php');
    include('../../enrollment/classes/Transferee.php');
    include('../../enrollment/classes/Enrollment.php');
    include('../../includes/classes/Student.php');
    include('../classes/Course.php');

    $enroll = new StudentEnroll($con);
    $oldEnroll = new OldEnrollees($con, $enroll);
    $transferee = new Transferee($con, $enroll);
    $enrollment = new Enrollment($con, $enroll);

    $school_year_obj = $enroll->GetActiveSchoolYearAndSemester();
    $school_year_id = $school_year_obj['school_year_id'];
    $current_school_year_term = $school_year_obj['term'];
    $current_school_year_period = $school_year_obj['period'];

    if(isset($_GET['id']) && !isset($_GET['inserted']) && !isset($_GET['success'])){
        
        $student_id = $_GET['id'];
        # For the second form
        unset($_SESSION['subject_ids']);


        $username = $enroll->GetStudentUsername($student_id);
        $student_fullname = $enroll->GetStudentFullName($student_id);


        $student = new Student($con, $username);
        
        $student_address = $student->GetStudentAddress();
        $student_contact = $student->GetGuardianNameContact();

        $student_course_level = $enroll->GetStudentCourseLevel($username);
        $student_course_id = $enroll->GetStudentCourseId($username);
        // echo $student_id;
        $student_course_level = $enroll->GetStudentCourseLevel($username);
        $student_course_id = $enroll->GetStudentCourseId($username);
        $student_program_section = $enroll->GetStudentProgramSection($student_course_id);
        $student_program_id = $enroll->GetStudentProgramId($student_course_id);

        $get_student_form_id = $enrollment->GetEnrollmentFormId($student_id, $student_course_id,$school_year_id);

        $unique_form_id = $enrollment->GenerateEnrollmentFormId();

        if(isset($_POST['transferee_subject_load_btn'])){

            // $subject_ids = isset($_POST['transferee_credited_subject_ids']) ? $_POST['transferee_credited_subject_ids'] : null;

            $is_transferee = "yes";
            $not_transferee = "no";

            $subject_ids = isset($_POST['transferee_credited_subject_ids']) ? $_POST['transferee_credited_subject_ids'] : array();

            // $asd = $_POST['transferee_subject'];
            // echo $asd;

            $insert_transferee = $con->prepare("INSERT INTO student_subject
                    (student_id, subject_id, school_year_id, course_level, is_transferee)
                    VALUES(:student_id, :subject_id, :school_year_id, :course_level, :is_transferee)
                    ");

            $passed_remark = "Passed";

            $mark_passed_transferee_subject = $con->prepare("INSERT INTO student_subject_grade
                    (student_id, subject_id, remarks, student_subject_id, is_transferee, subject_title, course_id)
                    VALUES(:student_id, :subject_id, :remarks, :student_subject_id, :is_transferee, :subject_title, :course_id)
                    ");
                
            $enrolled_transferee = $con->prepare("UPDATE enrollment
                SET  enrollment_status=:enrollment_status
                    -- course_id=:course_id
                WHERE course_id=:course_id
                AND student_id=:student_id
                ");
            
         
            // If transferee transferred as Grade 11 1st sem (FRESH)
            // It becomes Regular
            $regular_status = "Regular";
            $current_status = "Transferee";
            $old_enrollee = 0;

            $array_error = [];

            $properSubjectsInserted = false;

            $lastInsertId = null;
            $isTransfereeSubj = false;

            $studentSemesterSubjects = $oldEnroll->GetStudentCurrentSemesterSubjects($username, $current_school_year_period);

            $transfereeList = $transferee->GetTransfereeSubjectSemesterv2($username);

            // exit; // Important to exit after the redirect.

            $subject_ids = isset($_POST['transferee_credited_subject_ids']) 
                ? $_POST['transferee_credited_subject_ids'] : array();
            
            # REF
            if(!empty($subject_ids)){

                foreach ($transfereeList as $subject) {

                    $subject_id = $subject['subject_id'];
                    
                    // $_SESSION['subject_ids'][] = $subject_id;

                    $get_sub_title = $con->prepare("SELECT subject_title, course_id 

                            FROM subject
                            WHERE subject_id=:subject_id
                            LIMIT 1");

                    $get_sub_title->bindValue(":subject_id", $subject_id);
                    $get_sub_title->execute();

                    if($get_sub_title->rowCount() > 0){

                        $row = $get_sub_title->fetch(PDO::FETCH_ASSOC);
                        $subject_title = $row['subject_title'];

                        $subject_course_id = $row['course_id'];
                        echo $subject_title;
                    }

                    if (in_array($subject_id, $subject_ids)) {

                        // Checked as Credited Subjects
                        // echo "Subject ID: $subject_id is checked.<br>";
                        // echo "<br>";

                        $insert_transferee->bindValue(":student_id", $student_id);
                        $insert_transferee->bindValue(":subject_id", $subject_id);
                        $insert_transferee->bindValue(":school_year_id", $school_year_id);
                        $insert_transferee->bindValue(":course_level", $student_course_level);
                        $insert_transferee->bindValue(":is_transferee", $is_transferee);

                        if(false){
                        // if($insert_transferee->execute()){

                            // echo "Subject ID: $subject_id is checked.<br>";
                            // echo "<br>";

                            $lastInsertId = $con->lastInsertId();

                            $mark_passed_transferee_subject->bindValue(":student_id", $student_id);
                            $mark_passed_transferee_subject->bindValue(":subject_id", $subject_id);
                            $mark_passed_transferee_subject->bindValue(":remarks", $passed_remark);
                            $mark_passed_transferee_subject->bindValue(":student_subject_id", $lastInsertId);
                            $mark_passed_transferee_subject->bindValue(":is_transferee", "yes");
                            $mark_passed_transferee_subject->bindValue(":subject_title", $subject_title);
                            $mark_passed_transferee_subject->bindValue(":course_id", $subject_course_id);
                            
                            $mark_passed_transferee_subject->execute();
                        }

                    } else {
                        // The subject is unchecked
                        // Checked as not Credited Subjects

                        // echo "Subject ID: $subject_id is unchecked.<br>";
                        // echo "<br>";

                        $isTransfereeSubj = false;

                        // echo "$subject_title is now inserted.";
                        //
                        $insert_transferee->bindValue(":student_id", $student_id);
                        $insert_transferee->bindValue(":subject_id", $subject_id);
                        $insert_transferee->bindValue(":school_year_id", $school_year_id);
                        $insert_transferee->bindValue(":course_level", $student_course_level);
                        $insert_transferee->bindValue(":is_transferee", "no");

                        if(false){
                        // if($insert_transferee->execute()){

                            // Enrolled
                            $enrollment_status = "enrolled";

                            $enrolled_transferee->bindValue(":enrollment_status", $enrollment_status);
                            $enrolled_transferee->bindValue(":course_id", $student_course_id);
                            $enrolled_transferee->bindValue(":student_id", $student_id);
                            
                            if($enrolled_transferee->execute()){
                                // echo "$subject_title subject were inserted.";
                                // echo "<br>";
                                $properSubjectsInserted = true;
                            }
                        }

                    }
                }

                if($properSubjectsInserted == true){

                    $update_transferee_into_regular = $con->prepare("UPDATE student
                        SET new_enrollee=:new_enrollee
                        WHERE student_id=:student_id
                        AND student_status=:student_status");

                    $update_transferee_into_regular->bindValue(":new_enrollee", $old_enrollee);
                    $update_transferee_into_regular->bindValue(":student_id", $student_id);
                    $update_transferee_into_regular->bindValue(":student_status", "Transferee");

                    if($update_transferee_into_regular->execute()){
                        
                        // echo "Transferee student becomes Regular.";
                        // echo "New Transferee enrolee becomes Old student.";

                        echo "<br>";
                    }
                }
            }

            $subject_ids = isset($_POST['transferee_credited_subject_ids']) ? $_POST['transferee_credited_subject_ids'] : array();

            # For Next Proceed.
            if(!empty($subject_ids)){
                $subjectInitialized = false;

                foreach ($transfereeList as $subject) {

                    $subject_id = $subject['subject_id'];
                    
                    if (in_array($subject_id, $subject_ids)) {

                        $_SESSION['subject_ids'][] = array(
                            'subject_id' => $subject_id,
                            'status' => 'checked'
                        );

                        $subjectInitialized = true;
                    } else {
                        // Checked as not Credited Subjects
                        // echo "Subject ID: $subject_id is unchecked.<br>";
                        // echo "<br>";

                        $_SESSION['subject_ids'][] = array(
                            'subject_id' => $subject_id,
                            'status' => 'unchecked'
                        );

                        $subjectInitialized = true;
                    }
                }
                if($subjectInitialized == true && $properSubjectsInserted == true){

                    // header("Location: transferee_insertion.php?inserted=success&id=" . $student_id);
                    // exit();
                }

            }
 

           
        }

        if(isset($_POST['inserted_transferee_subject_btn'])){

            $transfereeSubjects = $con->prepare("SELECT 
                t1.is_transferee, t1.is_final,
                t1.student_subject_id,
                t1.school_year_id,

                t2.* 
                
                FROM student_subject as t1

                INNER JOIN subject as t2 ON t2.subject_id = t1.subject_id
                LEFT JOIN student_subject_grade as t3 ON t3.student_subject_id = t1.student_subject_id

                WHERE t1.student_id=:student_id
                AND t1.is_final=0
                AND t1.school_year_id=:school_year_id");

            $transfereeSubjects->bindValue(":student_id", $student_id);
            $transfereeSubjects->bindValue(":school_year_id", $school_year_id);
            $transfereeSubjects->execute();

            if($transfereeSubjects->rowCount() > 0){
                $final = 1;
                $isSuccess = false;

                $update = $con->prepare("UPDATE student_subject
                    SET is_final=:is_final
                    WHERE student_subject_id=:student_subject_id
                    AND student_id=:student_id
                    AND school_year_id=:school_year_id");

                while($row = $transfereeSubjects->fetch(PDO::FETCH_ASSOC)){

                    $student_subject_id = $row['student_subject_id'];

                    $update->bindParam(":is_final", $final);
                    $update->bindParam(":student_subject_id", $student_subject_id);
                    $update->bindParam(":student_id", $student_id);
                    $update->bindParam(":school_year_id", $school_year_id);
                    $update->execute();
                    $isSuccess = true;
                }
                if($isSuccess == true){

                    $enrolledSuccess = $oldEnroll->EnrolledStudentInTheEnrollment($school_year_id,
                        $student_id);

                    $newToOld = $oldEnroll->UpdateSHSStudentNewToOld($student_id);

                    if($enrolledSuccess && $newToOld){

                        AdminUser::success("Student successfully enrolled", "transferee_insertion.php?success=true&id=$student_id");
                            // header("Location: ");
                        exit();

                        // header("Location: transferee_insertion.php?success=true&id=$student_id");
                        // exit();
                    }   
                }

                            



            }
        }
        
        ?>
            <div class="col-md-12 row">

               
                <div class="table-responsive" style="margin-top:5%;"> 
                    <div class="text-right">
                        <?php 
                            // if($oldEnroll->CheckStudentUpdatedSection($username) == true){

                            //     $showUnAssignedSection = $oldEnroll->UpdateStudentSectionDropdown($username, $student_id);
                            //     echo $showUnAssignedSection;
                            // }
                        ?>
                    </div>

                    <div class="container">
                        <h4 class="text-center text-primary">Enrollment Details</h4>
                        <hr>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-2 form-group">
                                    <label class="mb-2" for="">Enrollment ID</label>
                                    <input readonly value="<?php echo $get_student_form_id;?>" type="text" class="form-control">
                                </div>
                                <div class="mb-2 form-group">
                                    <label class="mb-2" for="">Name</label>
                                    <input readonly value="<?php echo $student_fullname; ?>" type="text" class="form-control">
                                </div>
                             
                                <div class="mb-2 form-group">
                                    <label class="mb-2" for="">Status</label>
                                    <input readonly value="Transferee" type="text" class="form-control">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-2 form-group">
                                    <label class="mb-2" for="">Program & Section</label>
                                    <input readonly value="<?php echo $student_program_section ?>" type="text" class="form-control">
                                </div>
                                <div class="mb-2 form-group">
                                    <label class="mb-2" for="">Semester</label>
                                    <input readonly value="<?php echo $current_school_year_period ?>" type="text" class="form-control">
                                </div>
                                <div class="mb-2 form-group">
                                    <label class="mb-2" for="">Academic Year</label>
                                    <input readonly value="<?php echo $current_school_year_term ?>" type="text" class="form-control">
                                </div>
                            </div>
                        </div>
                    </div>

                    <form style="display: none;" action="" method="POST">
                        <table class="mt-3 table table-striped table-bordered table-hover "  style="font-size:13px" cellspacing="0"  > 
                        <h4 style="font-weight: bold;"
                            class="mb-3 mt-4 text-primary text-center">
                            <?php echo $student_program_section; ?> Subjects Curriculum
                        </h4>
                        <!-- <p><?php echo $student_fullname;?> Transfered at SY:(<?php echo $current_school_year_term; ?>) <?php echo $current_school_year_period; ?> Semester</p> -->
                        <span class="mb-3 ">* All green subjects are the possible credited subjects</span>
                        <table class="mt-3 table table-striped table-bordered table-hover "  style="font-size:13px" cellspacing="0"  > 
                            <thead>
                                <tr class="text-center"> 
                                <th class="text-center">
                                        <input type="checkbox" id="select-all-checkbox"> Credit
                                    </th>
                                    <th rowspan="2">ID</th>
                                    <th rowspan="2">Code</th>
                                    <th rowspan="2">Title</th>  
                                    <th rowspan="2">Unit</th>
                                </tr>	
                            </thead> 
                            <tbody>
                                <?php

                                    $transfereeList = $transferee->GetTransfereeSubjectSemesterv2($username);

                                    if($transfereeList != null){

                                        foreach ($transfereeList as $key => $value) {
                                            # code...

                                            $subject_id = isset($value['subject_id']) ? $value['subject_id'] : "";
                                            $subject_code = isset($value['subject_code']) ? $value['subject_code'] : "";
                                            $subject_title = isset($value['subject_title']) ? $value['subject_title'] : "";
                                            $unit = isset($value['unit']) ? $value['unit'] : "";
                                        
                                            $transferee = isset($value['transferee']) ? $value['transferee'] : false;

                                            $color = "";
                                            if($transferee== true){
                                                $color = "green";
                                            }else{
                                                $color = "";
                                            }
                                            echo '<tr 
                                                class="text-center"style="background-color: '.$color.';">'; 
                                                echo '
                                                <td >
                                                    <input  name="transferee_credited_subject_ids[]" class="checkbox" value="'.$subject_id.'" type="checkbox">
                                                </td>';
                                                echo '<td>'.$subject_id.'</td>';
                                                echo '<td >'.$subject_code.'</td>';
                                                echo '<td class="subject_title">'.$subject_title.'</td>';
                                                echo '<td>'.$unit.'</td>';
                                            echo '</tr>';
                                        }
                                    }else{
                                        echo "<br>";
                                        echo "Transferee Subjects is null";
                                        echo "<br>";
                                    }
                                ?>
                            </tbody> 
                        </table>

                        <!-- <h5>Extension</h5>
                        <table class="mt-3 table table-striped table-bordered table-hover "  style="font-size:13px" cellspacing="0"  > 
                            <thead>
                                <tr class="text-center"> 
                                <th class="text-center">
                                        <input type="checkbox" id="select-all-checkbox"> Credit
                                    </th>
                                    <th rowspan="2">ID</th>
                                    <th rowspan="2">Code</th>
                                    <th rowspan="2">Title</th>  
                                    <th rowspan="2">Unit</th>
                                </tr>	
                            </thead> 
                            <tbody>
                                <?php

                                    // $transfereeListv2 = $transferee->GetTransfereeSubjectSemesterv2($username);
                                    $transfereeListv2 =  $con->prepare("SELECT * FROM course as t1
                                    
                                        INNER JOIN subject as t2 ON t2.course_id = t1.course_id
                                        WHERE t1.program_id !=:program_id
                                        AND t1.school_year_term =:school_year_term
                                        AND t1.course_level <=:course_level
                                        
                                        ");

                                    $transfereeListv2->bindValue(":program_id", $student_program_id);
                                    $transfereeListv2->bindValue(":school_year_term", $current_school_year_term);
                                    $transfereeListv2->bindValue(":course_level", $student_course_level);
                                    $transfereeListv2->execute();

                                    if($transfereeListv2 != null){

                                        foreach ($transfereeListv2 as $key => $value) {
                                            # code...

                                            $subject_id = isset($value['subject_id']) ? $value['subject_id'] : "";
                                            $subject_code = isset($value['subject_code']) ? $value['subject_code'] : "";
                                            $subject_title = isset($value['subject_title']) ? $value['subject_title'] : "";
                                            $unit = isset($value['unit']) ? $value['unit'] : "";
                                        
                                            $transferee = isset($value['transferee']) ? $value['transferee'] : false;

                                            $color = "";
                                            if($transferee== true){
                                                $color = "green";
                                            }else{
                                                $color = "";
                                            }
                                            echo '<tr 
                                                class="text-center"style="background-color: '.$color.';">'; 
                                                echo '
                                                <td >
                                                    <input  name="transferee_credited_subject_ids[]" class="checkbox" value="'.$subject_id.'" type="checkbox">
                                                </td>';
                                                echo '<td>'.$subject_id.'</td>';
                                                echo '<td >'.$subject_code.'</td>';
                                                echo '<td class="subject_title">'.$subject_title.'</td>';
                                                echo '<td>'.$unit.'</td>';
                                            echo '</tr>';
                                        }
                                    }else{
                                        echo "<br>";
                                        echo "Transferee Subjects is null";
                                        echo "<br>";
                                    }
                                ?>
                            </tbody> 
                        </table> -->

                        <button type="submit" name="transferee_subject_load_btn"
                            id="transferee_subject_load_btn"
                            onclick="return confirm('Are you sure you want to insert & enroll??')"
                            class="btn btn-success btn-sm">Insert & Enroll</button>
                        <!-- <button type="submit" name="transferee_unload_subject_btn" class="btn btn-danger btn-sm">Unload Subject</button> -->
                    </form>

                    <form action="" method="POST">
                        <table class="mt-3 table table-striped table-bordered table-hover "  style="font-size:13px" cellspacing="0"  > 
                        <h4 style="font-weight: bold;"
                            class="mb-3 mt-4 text-primary text-center">
                           List of Subjects
                        </h4>

                        <table class="mt-3 table table-striped table-bordered table-hover "  style="font-size:13px" cellspacing="0"  > 
                            <thead>
                                <tr class="text-center"> 
                                    <th rowspan="2">ID</th>
                                    <th rowspan="2">Section</th>
                                    <th rowspan="2">Code</th>
                                    <th rowspan="2">Description</th>  
                                    <th rowspan="2">Unit</th>
                                    <th rowspan="2">Status</th>
                                </tr>	
                            </thead> 
                            <tbody>
                                <?php
                                    $totalUnits = 0;

                                    $transfereeSubjects = $con->prepare("SELECT 
                                        t1.is_transferee, t1.is_final, t4.program_section,
                                        t1.student_subject_id as t2_student_subject_id, 
                                        t3.student_subject_id as t3_student_subject_id,
                                        t2.* FROM student_subject as t1

                                        INNER JOIN subject as t2 ON t2.subject_id = t1.subject_id
                                        LEFT JOIN student_subject_grade as t3 ON t3.student_subject_id = t1.student_subject_id
                                        LEFT JOIN course as t4 ON t4.course_id = t2.course_id

                                        WHERE t1.student_id=:student_id
                                        AND t1.is_final=0
                                        AND t1.school_year_id=:school_year_id");

                                    $transfereeSubjects->bindValue(":student_id", $student_id);
                                    $transfereeSubjects->bindValue(":school_year_id", $school_year_id);
                                    $transfereeSubjects->execute();

                                    if($transfereeSubjects->rowCount() > 0){
                                        while($row = $transfereeSubjects->fetch(PDO::FETCH_ASSOC)){

                                            $subject_id = $row['subject_id'];
                                            $subject_title = $row['subject_title'];
                                            $subject_code = $row['subject_code'];
                                            $is_transferee = $row['is_transferee'];
                                            $unit = $row['unit'];
                                            $program_section = $row['program_section'];



                                            // $totalUnits += $unit;

                                            $text = "
                                                <p style='font-weight: bold;' class='text-primary'>Normal</p>
                                            
                                            ";
                                            if($is_transferee == "yes"){
                                                $text = "
                                                    <p style='font-weight: bold;' class='text-success'>Credited</p>
                                                ";
                                            }if($is_transferee == "no"){
                                                $totalUnits += $unit;

                                            }

                                            echo "
                                                <tr class='text-center'>
                                                    <td>$subject_id</td>
                                                    <td>$program_section</td>
                                                    
                                                    <td>$subject_code</td>
                                                    <td>$subject_title</td>
                                                    <td>$unit</td>
                                                    <td>
                                                        $text
                                                    </td>
                                                </tr>
                                            ";
                                        }
                                    }
                                ?>
                            </tbody> 
                                <?php
                                    if($totalUnits != 0){
                                        ?>
                                        <tr class="text-center">
                                            <td colspan="3"  style="font-weight:bold;text-align: right;" >Total Units</td>
                                            <td><?php echo $totalUnits;?></td>
                                        </tr> 
                                        <?php
                                    }
                                ?>
                             
                        </table>
                          
                        <button type="submit" name="inserted_transferee_subject_btn"
                            id="inserted_transferee_subject_btn"
                            onclick="return confirm('Are you sure you want to insert.?')"
                            class="btn btn-success btn">Enroll</button>
                            
                    </form>

            </div>

        <?php
    }

    if(isset($_GET['inserted'])){

        if($_GET['id']){

            // $enroll = new StudentEnroll($con);
            // $oldEnroll = new OldEnrollees($con, $enroll);
            // $transferee = new Transferee($con, $enroll);

            $inserted = $_GET['inserted'];
            $student_id = $_GET['id'];


            // echo $inserted;
            // echo $student_id;

            $username = $enroll->GetStudentUsername($student_id);
            $student_fullname = $enroll->GetStudentFullName($student_id);

            $student = new Student($con, $username);

            $student_address = $student->GetStudentAddress();
            $student_contact = $student->GetGuardianNameContact();

            // $school_year_obj = $enroll->GetActiveSchoolYearAndSemester();
            // $school_year_id = $school_year_obj['school_year_id'];
            // $current_school_year_term = $school_year_obj['term'];
            // $current_school_year_period = $school_year_obj['period'];

            $student_course_level = $enroll->GetStudentCourseLevel($username);
            $student_course_id = $enroll->GetStudentCourseId($username);
            $student_program_section = $enroll->GetStudentProgramSection($student_course_id);

            if(isset($_POST['insert_enroll_transferee'])){

                // echo "asd";

                # HERE

                $insert_transferee = $con->prepare("INSERT INTO student_subject
                        (student_id, subject_id, school_year_id, course_level, is_transferee)
                        VALUES(:student_id, :subject_id, :school_year_id, :course_level, :is_transferee)
                        ");

                $passed_remark = "Passed";

                $mark_passed_transferee_subject = $con->prepare("INSERT INTO student_subject_grade
                        (student_id, subject_id, remarks, student_subject_id, is_transferee, subject_title, course_id)
                        VALUES(:student_id, :subject_id, :remarks, :student_subject_id, :is_transferee, :subject_title, :course_id)
                        ");
                    
                $enrolled_transferee = $con->prepare("UPDATE enrollment
                    SET  enrollment_status=:enrollment_status
                        -- course_id=:course_id
                    WHERE course_id=:course_id
                    AND student_id=:student_id
                    ");

                if (isset($_SESSION['subject_ids']) && is_array($_SESSION['subject_ids'])) {
                    foreach ($_SESSION['subject_ids'] as $subject) {

                        $subject_id = isset($subject['subject_id']) ? $subject['subject_id'] : 0;
                        $status =  $subject['status'];

                        // echo $subject_id;
                        // echo $status . " ";
                        // echo "<br>";

                        if($status === "checked"){


                                # v3
                            $get_sub_title = $con->prepare("SELECT subject_title, course_id 

                                FROM subject
                                WHERE subject_id=:subject_id
                                LIMIT 1");

                            $get_sub_title->bindValue(":subject_id", $subject_id);
                            $get_sub_title->execute();

                            if($get_sub_title->rowCount() > 0){

                                $row = $get_sub_title->fetch(PDO::FETCH_ASSOC);
                                $subject_title = $row['subject_title'];

                                $subject_course_id = $row['course_id'];
                                // echo $subject_title;
                            }

                            // Checked as Credited Subjects
                            // echo "Subject ID: $subject_id is checked.<br>";
                            // echo "<br>";

                            $insert_transferee->bindValue(":student_id", $student_id);
                            $insert_transferee->bindValue(":subject_id", $subject_id);
                            $insert_transferee->bindValue(":school_year_id", $school_year_id);
                            $insert_transferee->bindValue(":course_level", $student_course_level);
                            $insert_transferee->bindValue(":is_transferee", "yes");

                            // if(false){
                            if($insert_transferee->execute()){
                                // echo "Subject ID: $subject_id is checked.<br>";
                                // echo "<br>";

                                $lastInsertId = $con->lastInsertId();

                                $mark_passed_transferee_subject->bindValue(":student_id", $student_id);
                                $mark_passed_transferee_subject->bindValue(":subject_id", $subject_id);
                                $mark_passed_transferee_subject->bindValue(":remarks", $passed_remark);
                                $mark_passed_transferee_subject->bindValue(":student_subject_id", $lastInsertId);
                                $mark_passed_transferee_subject->bindValue(":is_transferee", "yes");
                                $mark_passed_transferee_subject->bindValue(":subject_title", $subject_title);
                                $mark_passed_transferee_subject->bindValue(":course_id", $subject_course_id);
                                
                                $mark_passed_transferee_subject->execute();
                            }
                        }else{

                            $isTransfereeSubj = false;

                            // echo "$subject_title is now inserted.";
                            //
                            $insert_transferee->bindValue(":student_id", $student_id);
                            $insert_transferee->bindValue(":subject_id", $subject_id);
                            $insert_transferee->bindValue(":school_year_id", $school_year_id);
                            $insert_transferee->bindValue(":course_level", $student_course_level);
                            $insert_transferee->bindValue(":is_transferee", "no");

                            // if(false){
                            if($insert_transferee->execute()){

                                // Enrolled
                                $enrollment_status = "enrolled";

                                $enrolled_transferee->bindValue(":enrollment_status", $enrollment_status);
                                $enrolled_transferee->bindValue(":course_id", $student_course_id);
                                $enrolled_transferee->bindValue(":student_id", $student_id);

                                if($enrolled_transferee->execute()){
                                    // echo "$subject_title subject were inserted.";
                                    // echo "<br>";
                                    $properSubjectsInserted = true;
                                }
                            }
                        }


                    }

                    # v2

                    if($properSubjectsInserted == true){

                        $update_transferee_into_regular = $con->prepare("UPDATE student
                            SET new_enrollee=:new_enrollee
                            WHERE student_id=:student_id
                            AND student_status=:student_status");

                        $update_transferee_into_regular->bindValue(":new_enrollee", 0);
                        $update_transferee_into_regular->bindValue(":student_id", $student_id);
                        $update_transferee_into_regular->bindValue(":student_status", "Transferee");

                        if($update_transferee_into_regular->execute()){
                            
                            // echo "Transferee student becomes Regular.";
                            // echo "New Transferee enrolee becomes Old student.";
                            // echo "<br>";

                            // SWAL MESSAGE.
                            header("Location: transfee_enrollees.php");
                            exit();
                        }
                    }
                }

            }

            ?>

                <div class="row col-md-12">
                    <div class="container">
                        <h4 class="text-center text-primary">Student Information</h4>
                        <hr>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="mb-2" for="">Name</label>
                                    <input readonly value="<?php echo $student_fullname; ?>" type="text" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label class="mb-2" for="">Address</label>
                                    <input readonly value="<?php echo $student_address; ?>" type="text" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label class="mb-2" for="">Contact Number.</label>
                                    <input readonly value="<?php echo $student_contact; ?>" type="text" class="form-control">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="mb-2" for="">Program & Section</label>
                                    <input readonly value="<?php echo $student_program_section ?>" type="text" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label class="mb-2" for="">Semester</label>
                                    <input readonly value="<?php echo $current_school_year_period ?>" type="text" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label class="mb-2" for="">Academic Year</label>
                                    <input readonly value="<?php echo $current_school_year_term ?>" type="text" class="form-control">
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <hr>
                    <div class="container">
                        <h4 class="text-center text-success">List of Enrolled Subject</h4>
                        <div class="table-responsive" style="margin-top:5%;"> 

                            <form method="POST">

                            <table class="table table-striped table-bordered table-hover "  style="font-size:13px" cellspacing="0"  > 
                                <thead>
                                    <tr class="text-center"> 
                                        <th rowspan="2">ID</th>
                                        <th rowspan="2">Code</th>
                                        <th rowspan="2">Description</th>  
                                        <th rowspan="2">Unit</th>
                                        <th rowspan="2">Status</th>
                                    </tr>	
                                </thead>

                                <tbody>

                                    <?php 

                                        $totalUnits = 0;
                                        if (isset($_SESSION['subject_ids']) && is_array($_SESSION['subject_ids'])) {
                                            foreach ($_SESSION['subject_ids'] as $subject) {

                                                $subject_id = isset($subject['subject_id']) ? $subject['subject_id'] : 0;

                                                if ($subject_id != 0) {
                                                    $subject_id =  $subject['subject_id'];
                                                    $status =  $subject['status'];

                                                    $sql = $con->prepare("SELECT * FROM subject
                                                        WHERE subject_id=:subject_id");
                                                    
                                                    $sql->bindValue(":subject_id", $subject_id);
                                                    $sql->execute();

                                                    if($sql->rowCount() > 0){

                                                        $row = $sql->fetch(PDO::FETCH_ASSOC);

                                                        $subject_id = $row['subject_id'];
                                                        $subject_title = $row['subject_title'];
                                                        $subject_code = $row['subject_code'];
                                                        $unit = $row['unit'];

                                                        $subject_status = "";
                                                        $totalUnits += $unit;

                                                        if($status === "checked"){
                                                            $subject_status = "CREDITED";
                                                        }
                                                        echo "
                                                            <tr class='text-center'>

                                                                <td>$subject_id</td>
                                                                <td>$subject_code</td>
                                                                <td>$subject_title</td>
                                                                <td>$unit</td>
                                                                <td>$subject_status</td>
                                                            </tr>
                                                        ";
                                                    }

                                                }
                                            
                                            }
                                            // Clear the subject_ids array from $_SESSION if needed
                                            // unset($_SESSION['subject_ids']);
                                        }   
                                    ?>
                                </tbody>
                            

                                <?php
                                    if($totalUnits != 0){
                                        echo "
                                        <tr>
                                            <td colspan='3'style='text-align: right;' >Total Units</td>
                                            <td style='font-weight:bold;'>$totalUnits</td>
                                        </tr> 
                                        ";
                                        
                                    }
                                ?>
                            
                            </table>
                            <?php
                                if($totalUnits != 0){
                                     ?>
                                    <button type="submit" class="btn btn-success"
                                        name="insert_enroll_transferee" 
                                        >Print</button>
 
                                    <a href='transferee_insertion.php?id=<?php echo $student_id;?>'>
                                        <button type='button' class='btn btn-outline-primary btn-sm'>Go back</button>
                                    </a>
                                    <!-- <button class="btn btn-primary">Print</button> -->
                                    <?php
                                    // echo "
                                    //     <button type='submit' name='insert_enroll_transferee'
                                    //     class='btn btn-success btn-sm'>Insert & Enroll</button>
                                    //    <a href='transferee_insertion.php?id=$student_id'>
                                    //         <button type='button' onclick='unsetSubjectIds()' class='btn btn-outline-primary btn-sm'>Go back</button>
                                    //     </a>
                                    // ";
                                }
                            ?>
                            </form>
                        </div>
                    </div>
                </div>
            <?php
        }

    }

    if(isset($_GET['success'])){

        if($_GET['id']){
            $student_id = $_GET['id'];

            $username = $enroll->GetStudentUsername($student_id);
            $student_fullname = $enroll->GetStudentFullName($student_id);


            $student = new Student($con, $username);
            
            $student_address = $student->GetStudentAddress();
            $student_contact = $student->GetGuardianNameContact();

            $student_course_level = $enroll->GetStudentCourseLevel($username);
            $student_course_id = $enroll->GetStudentCourseId($username);
            // echo $student_id;
            $student_course_level = $enroll->GetStudentCourseLevel($username);
            $student_course_id = $enroll->GetStudentCourseId($username);
            $student_program_section = $enroll->GetStudentProgramSection($student_course_id);
            $student_program_id = $enroll->GetStudentProgramId($student_course_id);

            $get_student_form_id = $enrollment->GetEnrollmentFormId($student_id, $student_course_id,$school_year_id);

            $unique_form_id = $enrollment->GenerateEnrollmentFormId();

            ?>

            <div class="col-md-12 row">

               
                <div class="table-responsive" style="margin-top:5%;"> 
                    <div class="text-right">
                        <?php 
                            // if($oldEnroll->CheckStudentUpdatedSection($username) == true){

                            //     $showUnAssignedSection = $oldEnroll->UpdateStudentSectionDropdown($username, $student_id);
                            //     echo $showUnAssignedSection;
                            // }
                        ?>
                    </div>

                    <div class="container">
                        <h4 style="font-weight: bold;" class="text-center text-success">Enrollment Details</h4>
                        <hr>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-2 form-group">
                                    <label class="mb-2" for="">Enrollment ID</label>
                                    <input readonly value="<?php echo $get_student_form_id;?>" type="text" class="form-control">
                                </div>
                                <div class="mb-2 form-group">
                                    <label class="mb-2" for="">Name</label>
                                    <input readonly value="<?php echo $student_fullname; ?>" type="text" class="form-control">
                                </div>
                             
                                <div class="mb-2 form-group">
                                    <label class="mb-2" for="">Status</label>
                                    <input readonly value="Transferee" type="text" class="form-control">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-2 form-group">
                                    <label class="mb-2" for="">Program & Section</label>
                                    <input readonly value="<?php echo $student_program_section ?>" type="text" class="form-control">
                                </div>
                                <div class="mb-2 form-group">
                                    <label class="mb-2" for="">Semester</label>
                                    <input readonly value="<?php echo $current_school_year_period ?>" type="text" class="form-control">
                                </div>
                                <div class="mb-2 form-group">
                                    <label class="mb-2" for="">Academic Year</label>
                                    <input readonly value="<?php echo $current_school_year_term ?>" type="text" class="form-control">
                                </div>
                            </div>
                        </div>
                    </div>
                    <form action="" method="POST">
                        <table class="mt-3 table table-striped table-bordered table-hover "  style="font-size:13px" cellspacing="0"  > 
                        <h4 style="font-weight: bold;"
                            class="mb-3 mt-4 text-success text-center">
                           List of Subjects
                        </h4>

                        <table class="mt-3 table table-striped table-bordered table-hover "  style="font-size:13px" cellspacing="0"  > 
                            <thead>
                                <tr class="text-muted text-center"> 
                                    <th rowspan="2">ID</th>
                                    <th rowspan="2">Code</th>
                                    <th rowspan="2">Description</th>  
                                    <th rowspan="2">Unit</th>
                                    <th rowspan="2">Status</th>
                                </tr>	
                            </thead> 
                            <tbody>
                                <?php
                                    $totalUnits = 0;

                                    $transfereeSubjects = $con->prepare("SELECT 
                                        t1.is_transferee, t1.is_final,
                                        t1.student_subject_id as t2_student_subject_id, 
                                        t3.student_subject_id as t3_student_subject_id,
                                        t2.* FROM student_subject as t1

                                        INNER JOIN subject as t2 ON t2.subject_id = t1.subject_id
                                        LEFT JOIN student_subject_grade as t3 ON t3.student_subject_id = t1.student_subject_id

                                        WHERE t1.student_id=:student_id
                                        AND t1.is_final=1
                                        AND t1.school_year_id=:school_year_id");

                                    $transfereeSubjects->bindValue(":student_id", $student_id);
                                    $transfereeSubjects->bindValue(":school_year_id", $school_year_id);
                                    $transfereeSubjects->execute();

                                    if($transfereeSubjects->rowCount() > 0){
                                        while($row = $transfereeSubjects->fetch(PDO::FETCH_ASSOC)){

                                            $subject_id = $row['subject_id'];
                                            $subject_title = $row['subject_title'];
                                            $subject_code = $row['subject_code'];
                                            $is_transferee = $row['is_transferee'];
                                            $unit = $row['unit'];

                                            $totalUnits += $unit;

                                            $text = "
                                                <p style='font-weight: bold;' class='text-primary'>Normal</p>
                                            
                                            ";
                                            if($is_transferee == "yes"){
                                                $text = "
                                                    <p style='font-weight: bold;' class='text-success'>Credited</p>
                                                ";
                                            } 

                                            echo "
                                                <tr class='text-center'>
                                                    <td>$subject_id</td>
                                                    <td>$subject_code</td>
                                                    <td>$subject_title</td>
                                                    <td>$unit</td>
                                                    <td>
                                                        $text
                                                    </td>
                                                </tr>
                                            ";
                                        }
                                    }
                                ?>
                            </tbody> 
                                <?php
                                    if($totalUnits != 0){
                                        ?>
                                        <tr class="text-center">
                                            <td colspan="3"  style="font-weight:bold;text-align: right;" >Total Units</td>
                                            <td><?php echo $totalUnits;?></td>
                                        </tr> 
                                        <?php
                                    }
                                ?>
                             
                        </table>
                          
                        <button type="submit" name="print_btn"
                            id="print_btn"
                            onclick="return confirm('Are you sure you want to print?')"
                            class="btn btn-primary btn">Print</button>
                    </form>

                </div>
            </div>
            <?php
        }
    }
?>

<script>


    // window.addEventListener('load', function() {
    //     document.getElementById('select-all-checkbox').click();
    // });
              
    document.getElementById('select-all-checkbox').addEventListener('click', function() {
        var checkboxes = document.getElementsByClassName('checkbox');

        for (var i = 0; i < checkboxes.length; i++) {
            checkboxes[i].checked = this.checked;
        }
    });

</script>