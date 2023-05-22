<script src="../assets/js/common.js"></script>

<?php 
    include('../registrar_enrollment_header.php');
    require_once('../../enrollment/classes/StudentEnroll.php');
    require_once('../../enrollment/classes/OldEnrollees.php');
    require_once('../../enrollment/classes/Pending.php');
    require_once('../classes/Course.php');

    $studentEnroll = new StudentEnroll($con);
    $oldEnroll = new OldEnrollees($con, $studentEnroll);
    $pending = new Pending($con);

    $course = new Course($con, $studentEnroll);

    if(!isset($_SESSION['registrarLoggedIn'])){
        header("Location: " . base_url . "/index.php");
        exit();
    }
    else if(isset($_SESSION['registrarLoggedIn']) 
        && isset($_GET['username'])
         && isset($_GET['id'])
        ){

        $student_username = $_GET['username'];
        $old_student_status = $oldEnroll->GetOldStudentStatus($student_username);
        $studentNewEnrolee = $oldEnroll->DoesStudentNewEnrollee($student_username);
            
        $base_url = 'http://localhost/elms/admin';

        $student_id = $_GET['id'];

        $student_fullname = $studentEnroll->GetStudentFullName($student_id);
        $student_firstname = $studentEnroll->GetStudentFirstname($student_id);

        $pending_form_submission = $pending->GetSubmittedOn($student_firstname);

        $confirmStudent = "enrolledStudent(\"$student_username\")";

        // $username2 = "(\"$username\")";
        $recommendedSubject = $studentEnroll->GetSHSNewStudentSubjectProgramBased($student_username);
        $studentCourseYear = $studentEnroll->GetStudentCourseName($student_username);
        $school_year_obj = $studentEnroll->GetActiveSchoolYearAndSemester();

        $student_course_level = $studentEnroll->GetStudentCourseLevel($student_username);

        $student_course_id = $studentEnroll->GetStudentCourseId($student_username);
        $student_program_id = $studentEnroll->GetStudentProgramId($student_course_id);


        // echo $student_id;
        $student_program_section = $studentEnroll->GetStudentProgramSection($student_course_id);

        $grade_level = $student_course_level >=  11  ? "Grade" : "";

        $current_school_year_term = $school_year_obj['term'];
        $current_school_year_semester = $school_year_obj['period'];
        $school_year_id = $school_year_obj['school_year_id'];
        // $forget_establish_time_url = "admin/schedule/create.php";

        // Prevent admin to set the final list of subject if the remaining 
        // were not all scheduled

        $forget_establish_time_url = $base_url . '/schedule/create.php';

        $unscheduledSubjectLeft = false;

        $display = "block";
            

        // Student who click the apply for next semester as new semester had established
        if($old_student_status == "Regular" || $old_student_status == "Returnee" || $old_student_status == "Transferee"){

            if(isset($_POST['subject_load_btn']) && isset($_POST['subject_ids'])){

                $subject_ids = $_POST['subject_ids'];
                
                // $pre_subject_ids = $_POST['pre_subject_ids'];
                $array_success = [];

                $successInsertingSubjectLoad = false;

                // Update student + 1 to the total_student
                $active = "yes";
                $is_full = "no";
                $total_student = 1;

                $sql_insert = $con->prepare("INSERT INTO student_subject 
                    (student_id, subject_id, school_year_id, course_level)
                    VALUES(:student_id, :subject_id, :school_year_id, :course_level)");


                foreach ($subject_ids as $key => $value) {
                
                    $subject_id = $value;
                    $query_sub = $con->prepare("SELECT pre_subject_id 
                        FROM subject
                        WHERE subject_id=:subject_id
                        LIMIT 1");

                    $query_sub->bindValue(":subject_id", $subject_id);
                    $query_sub->execute();

                    $pre_subject_id = $query_sub->rowCount() > 0 ? $query_sub->fetchColumn() : -1;

                    $array_error = [];

                    # Validation of <prerequisite
                    // TODO: FIX THE BUG.
                    $validSemesterScope = $studentEnroll->CheckPreRequisiteSubject($student_username, $subject_id);

                    if($validSemesterScope == false){
                        $errorMessage = "Not within the semester scope";
                        echo $errorMessage;
                        array_push($array_error, $errorMessage);
                        // echo "<script>alert('$errorMessage');</script>";
                    }

                    if($pre_subject_id != 0){
                        // echo $pre_subject_id;

                        $failedSubjects = $studentEnroll->GetSHSStudentFailedSubject($student_username);
                        $unavailable_array = [];

                        foreach ($failedSubjects as $key => $val) {
                            //
                            if($val['subject_id'] == $pre_subject_id){
                                // Subject id that are unavailable.
                                array_push($unavailable_array, $subject_id);
                            }
                        }
                        // print_r($unavailable_array);
                        // Better performance validation.
                        $unAvailableArray = array();

                        foreach ($unavailable_array as $key => $subject_id_failed) {
                            // subject_id_failed as a key and true is a value
                            $unAvailableArray[$subject_id_failed] = true;
                        }

                        if(isset($unAvailableArray[$subject_id])){
                            array_push($array_error, $subject_id);
                            echo "subject_id $subject_id is not available to take";
                            echo "<br>";
                        }
                    }
                    # 2. If subject_id is already in the student_subject (doubled enrolled).
                    # 3. Check if the subject is within only the scoped of 
                    # what subject should be included in the subject loads.
                    # 4. If the subject that registrar wanted to enroll must be marked first.
                    $shsStudentSubject = $oldEnroll->GetSHSStudentEnrolledSubjects($student_username, $subject_id);
                
                    $subjectLoadsIds = array();
                    // Loop through the results and add the subject IDs to the array
                    foreach ($shsStudentSubject as $key => $students_enrolled_subject) {
                        // subject_id as a key and true is a value
                        $subjectLoadsIds[$students_enrolled_subject['subject_id']] = true;
                    }

                    // Check if the subject ID exists in the array
                    # 2. If subject_id is already in the student_subject (doubled enrolled).
                    if (isset($subjectLoadsIds[$subject_id])) {
                        echo $subject_id . " is already in the subject loads";
                        array_push($array_error, $subject_id);
                        echo "<br>";
                    }
                    
                    if(empty($array_error))
                    {
                        // $sql_insert = $con->prepare("INSERT INTO student_subject 
                        //     (student_id, subject_id, school_year_id, course_level)
                        //     VALUES(:student_id, :subject_id, :school_year_id, :course_level)");

                        $sql_insert->bindValue(":student_id", $student_id);
                        $sql_insert->bindValue(":subject_id", $subject_id);
                        $sql_insert->bindValue(":school_year_id", $school_year_id);
                        $sql_insert->bindValue(":course_level", $student_course_level);

                        if($sql_insert->execute()){
                        // if($sql_insert->execute()){

                            $successInsertingSubjectLoad = true;
                            array_push($array_success, $subject_id);
                        }
                        // If new enrollee Update into Old
                        
                        // $newIntoOldSuccess = $oldEnroll->UpdateSHSStudentNewToOld($student_id);
                    }
                }
                // if($isSectionFull == true){
                //     echo "
                //         <script>
                //             alert('Section is full. It should validated at registering the student.!')
                //         </script>
                //     ";
                //     array_push($array_error, "Full section");
                // }
                
                // 
                if($successInsertingSubjectLoad == true 
                    ){
                    // echo "hitt";
                    // Update students status
                    // Grade 11 1nd sem -> Grade 11 2nd sem & Enroll Student
                    // Grade 12 1st sem -> Grade 12 2nd sem & Enroll Student
                    // Graduate What todo next?.

                    $wasSuccess = $oldEnroll->UpdateSHSStudentStatus($student_username);
                    
                }
                $isSuccess = false;
                if(sizeof($array_success) > 0 && empty($array_error)){

                    // Check registrar should include all available subjects
                    // if not yet ALL included, no updating status will happen
                    // TODO:
                    if($wasSuccess){
                        // echo "was Success ";
                        
                    }else{
                        echo "not success";
                    }

                    foreach ($array_success as $key => $subject_ids_inserted) {
                        # subject_id that added in the student_subject db
                        echo "You have successfully inserted subject_id $subject_ids_inserted to the database";
                        $isSuccess = true;
                    }

                    if($isSuccess == true){
                        $url = directoryPath . "old_enrollees.php";
                        header("Location: $url");
                        
                        exit();
                    }
                }
            }

            // Unload subjects
            // The other changing values on the db is not supported.

            if(isset($_POST['unload_subject_btn'])){

                $subject_ids = $_POST['subject_ids'];
                $array_success = [];

                foreach ($subject_ids as $key => $value) {
                    $subject_id = $value;
                    
                    // Check first if the data is there before deletion.
                    $unload_subject = $con->prepare("DELETE FROM student_subject
                        WHERE subject_id=:subject_id
                        AND student_id=:student_id");

                    $unload_subject->bindValue(":subject_id", $subject_id);
                    // Be aware of the url bug.
                    $unload_subject->bindValue(":student_id", $student_id);
                    $unload_subject->execute();

                }
            }

            ?>
                <div class="row col-md-12">

                    <div class="container">
                        <h4 class="text-center text-primary">Enrollment Form</h4>
                        <hr>
                        <div class="row">
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label class="mb-2" for="">Form ID</label>
                                    <input readonly value="0003" type="text" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label class="mb-2" for="">Student Type</label>
                                    <input readonly value="New" type="text" class="form-control">
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="form-group">
                                    <label class="mb-2" for="">Student no.</label>
                                    <input readonly value="N/A" type="text" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="mb-2" for="">Status Evaluation.</label>
                                    <input readonly value="Evaluation" type="text" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label class="mb-2" for="">Submitted on.</label>
                                    <input readonly value="<?php echo $pending_form_submission;?>" type="text" class="form-control">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive" style="margin-top:5%;"> 
                        <form action="" method="POST">
                            <h4 style="font-weight: bold;" class="mb-3 mt-4 text-primary text-center"><?php echo $student_program_section; ?> Subjects Curriculum</h4>

                            <table class="table table-striped table-bordered table-hover "  style="font-size:13px" cellspacing="0"  > 
                                <thead>
                                    <tr> 
                                        <th></th>
                                        <th rowspan="2">ID</th>
                                        <th rowspan="2">Code</th>
                                        <th rowspan="2">Description</th>  
                                        <th rowspan="2">Unit</th>
                                        <th colspan="4">Schedule</th> 
                                    </tr>	
                                    <tr> 
                                        <th class="text-center">
                                            <input type="checkbox" id="select-all-checkbox">Select
                                        </th>
                                        <th>Day</th>
                                        <th>Time</th>
                                        <th>Room</th> 
                                        <th>Section</th>  
                                        <th>Semester</th>  
                                        <th>Course Level</th>  
                                        <th>Status</th>  
                                        <th>Pre-Req ID</th>  
                                    </tr>
                                </thead> 
                                    <tbody>
                                        <?php

                                            // echo $old_student_status;
                                            $listOfSubjects = $studentEnroll->GetStudentsStrandSubjects($student_username);
                                            $failed_subject_id = null;
                                            $unavaible_subject_id = null;
                                            // poll
                                            foreach ($listOfSubjects as $key => $value) {

                                                $subject_id = $value['subject_id'];
                                                $pre_subject_id = $value['pre_subject_id'] != 0 ? $value['pre_subject_id'] : "";
                                                $failed_remark = "Failed";

                                                // echo $pre_subject_id . " ";
                                                // MUST BE REMOVED.
                                                $query_failed = $con->prepare("SELECT subject_id 

                                                    FROM student_subject_grade

                                                    -- AND subject_id=:subject_id
                                                    WHERE remarks=:remarks
                                                    AND student_id=:student_id

                                                    LIMIT 1");
                                                
                                                $query_failed->bindValue(":remarks", $failed_remark);
                                                $query_failed->bindValue(":student_id", $student_id);
                                                // $query_failed->bindValue(":subject_id", $subject_id);
                                                $query_failed->execute();

                                                if($query_failed->rowCount() > 0){
                                                    // echo "got " . $subject_id;
                                                    $failed_subject_id = $query_failed->fetchColumn();
                                                }
                                            
                                                // echo $pre_subject_id;
                                                $failedSubjects = $studentEnroll->GetSHSStudentFailedSubject($student_username);
                                                $fs_unavail_array = [];

                                                foreach ($failedSubjects as $key => $val) {
                                                    // 
                                                    if($pre_subject_id == $val['subject_id']){

                                                        // Subject id that are unavailable.
                                                        array_push($fs_unavail_array, $subject_id);
                                                    }
                                                }

                                                $query_student_subject = $con->prepare("SELECT 
                                                    subject_id, student_subject_id FROM student_subject

                                                    WHERE subject_id=:subject_id
                                                    AND student_id=:student_id
                                                    LIMIT 1");

                                                $query_student_subject->bindValue(":subject_id", $subject_id);
                                                $query_student_subject->bindValue(":student_id", $student_id);
                                                $query_student_subject->execute();

                                                // Shorthand of Not_Enrolled
                                                $enrolled_status = "N_E";
                                                
                                                if($query_student_subject->rowCount() > 0){
                                                    
                                                    $row = $query_student_subject->fetch(PDO::FETCH_ASSOC);

                                                    $student_subject_subject_id = $row['subject_id'] != "" ? $row['subject_id'] : "";
                                                    
                                                    if($student_subject_subject_id){
                                                        if($subject_id == $student_subject_subject_id){
                                                            $enrolled_status = "Enrolled";
                                                        }
                                                    }
                                                }

                                                foreach ($fs_unavail_array as $key => $un_avail) {

                                                    if($subject_id == $un_avail){
                                                        $enrolled_status = "N/A";
                                                    }
                                                }

                                                $tr_color = "none";
                                                $style_back = "-color";
                                                // Enable to see the UI distinction.
                                                // The color green appears only to the subjects with the same
                                                // semester of the setted school_year_id.

                                                // echo $value['course_level'] . " ";
                                                
                                                if(
                                                    $current_school_year_semester == $value['semester']
                                                    && $student_course_level == $value['course_level'] 
                                                    && $enrolled_status == "N_E"){

                                                    $tr_color = " green";
                                                    $style_back = "background-color";
                                                }
                                                else if($current_school_year_semester == $value['semester'] 
                                                    && $enrolled_status == "N/A"){
                                                    $tr_color = "3px solid yellow";
                                                    $style_back = "border";
                                                }

                                                echo '<tr style="'.$style_back.': '.$tr_color.';">'; 
                                                        echo 
                                                        '<td  class="text-center">
                                                            <input name="subject_ids[]" class="checkbox"  value="'.$subject_id.'" type="checkbox">
                                                        </td>';
                                                        echo '<td>'.$value['subject_id'].'</td>';
                                                        echo '<td>'.$value['subject_code'].'</td>';
                                                        echo '<td>'.$value['subject_title'].'</td>';
                                                        echo '<td>'.$value['unit'].'</td>';
                                                        echo '<td></td>';
                                                        echo '<td></td>';
                                                        echo '<td></td>';
                                                        echo '<td></td>';
                                                        echo '<td>'.$value['semester'].'</td>';
                                                        echo '<td>'.$value['course_level'].'</td>';
                                                        echo '<td>'.$enrolled_status.'</td>';
                                                        echo '<td>'.$pre_subject_id.'</td>';
                                                echo '</tr>';

                                            }
                                        ?>
                                    </tbody> 
                            </table>

                            <?php 
                                # Check if Graduate, Tentative
                                # Check joining the query for the student_subject_grade student_subject_id
                                # if is student_subject_id, it it should delete all the student_subject_grade
                                # 
                                $doesFinished = $oldEnroll->DoesStudentFinishedAllSubjectLoads($student_username);
                                // if($doesFinished == true){
                                //     echo "
                                //         <p>* SHS Candidate for Graduation</p>
                                //     ";
                                // }else{
                                //     echo "Have more Subjects to finished";
                                // }
                            ?>
                            <button type="submit" name="subject_load_btn" class="btn btn-success btn-sm">Enroll Subject</button>
                            <!-- <button type="submit" name="unload_subject_btn" class="btn btn-danger btn-sm">Unload Subject</button> -->
                        </form>
                    </div>
            <?php   
        }
    }
?>

<script>
    window.addEventListener('load', function() {
    document.getElementById('select-all-checkbox').click();
    });

        
        document.getElementById('select-all-checkbox').addEventListener('click', function() {
        var checkboxes = document.getElementsByClassName('checkbox');

        for (var i = 0; i < checkboxes.length; i++) {
            checkboxes[i].checked = this.checked;
        }
        });


</script>
