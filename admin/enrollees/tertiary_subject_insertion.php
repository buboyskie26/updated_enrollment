<script src="../assets/js/common.js"></script>

<?php 
    include('../registrar_enrollment_header.php');
    require_once('../../enrollment/classes/StudentEnroll.php');
    require_once('../../enrollment/classes/OldEnrollees.php');
    require_once('../classes/Course.php');

    $studentEnroll = new StudentEnroll($con);
    $oldEnroll = new OldEnrollees($con, $studentEnroll);

    $course = new Course($con, $studentEnroll);

    if(!isset($_SESSION['registrarLoggedIn'])){
        header("Location: " . base_url . "/index.php");
        exit();
    }

    else if(isset($_SESSION['registrarLoggedIn']) 
         
        && isset($_GET['id'])
        ){

        // $student_username = $_GET['username'];
        $student_username = $studentEnroll->GetStudentUsername($_GET['id']);
        $old_student_status = $oldEnroll->GetOldStudentStatus($student_username);
        $studentNewEnrolee = $oldEnroll->DoesStudentNewEnrollee($student_username);
            
        $base_url = 'http://localhost/elms/admin';

        $student_id = $_GET['id'];

        $student_fullname = $studentEnroll->GetStudentFullName($student_id);

        $confirmStudent = "enrolledStudent(\"$student_username\")";

        // $username2 = "(\"$username\")";
        $recommendedSubject = $studentEnroll->GetSHSNewStudentSubjectProgramBased($student_username);
        $studentCourseYear = $studentEnroll->GetStudentCourseName($student_username);
        $school_year_obj = $studentEnroll->GetActiveSchoolYearAndSemester();

        $student_course_level = $studentEnroll->GetStudentCourseLevel($student_username);

        $student_course_id = $studentEnroll->GetStudentCourseId($student_username);
        $student_program_id = $studentEnroll->GetStudentProgramId($student_course_id);

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
        if($old_student_status == "Regular"){

            if(isset($_POST['tertiary_subject_load_btn']) && isset($_POST['tertiary_subject_ids'])){

                $subject_ids = $_POST['tertiary_subject_ids'];
                $array_success = [];

                $successInsertingSubjectLoad = false;

                // Update student + 1 to the total_student
                $active = "yes";
                $is_full = "no";
                $total_student = 1;

                // print_r($subject_ids);

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

                    #Validation of prerequisite
                    // TODO: FIX THE BUG.

                    // $validSemesterScope = $studentEnroll->CheckPreRequisiteSubject($student_username, $subject_id);

                    // if($validSemesterScope == false){
                    //     $errorMessage = "Not within the semester scope";
                    //     echo $errorMessage;
                    //     array_push($array_error, $errorMessage);
                    // }
                    
                    # 2. If subject_id is already in the student_subject (doubled enrolled).
                    # 3. Check if the subject is within only the scoped of 
                    # what subject should be included in the subject loads.
                    # 4. If the subject that registrar wanted to enroll must be marked first.

                    $shsStudentSubject = $oldEnroll->GetTertiaryStudentEnrolledSubjects($student_username, $subject_id);

                    $subjectLoadsIds = array();

                    // Loop through the results and add the subject IDs to the array
                    foreach ($shsStudentSubject as $key => $students_enrolled_subject) {
                        // subject_id as a key and true is a value
                        $subjectLoadsIds[$students_enrolled_subject['subject_tertiary_id']] = true;
                    }

                    // Check if the subject ID exists in the array
                    # 2. If subject_id is already in the student_subject (doubled enrolled).
                    if (isset($subjectLoadsIds[$subject_id])) {

                        $already_inserted = $con->prepare("SELECT subject_title
                            FROM subject_tertiary
                            WHERE subject_tertiary_id=:subject_tertiary_id
                            LIMIT 1
                            ");

                        $already_inserted->bindValue(":subject_tertiary_id", $subject_id);
                        $already_inserted->execute();

                        if($already_inserted->rowCount() > 0){
                            $subject_title = $already_inserted->fetchColumn();
                            echo $subject_title . " is already in the subject loads";
                            array_push($array_error, $subject_title);
                            echo "<br>";
                        }

                    }
                    
                    if(empty($array_error))
                    {
                        $sql_insert = $con->prepare("INSERT INTO student_subject_tertiary
                            (student_id, subject_tertiary_id, school_year_id)
                            VALUES(:student_id, :subject_tertiary_id, :school_year_id)");

                        $sql_insert->bindValue(":student_id", $student_id);
                        $sql_insert->bindValue(":subject_tertiary_id", $subject_id);
                        $sql_insert->bindValue(":school_year_id", $school_year_id);
                        // $sql_insert->bindValue(":course_level", $student_course_level);

                        if($sql_insert->execute()){
                        // if($sql_insert->execute()){
                            $successInsertingSubjectLoad = true;
                            array_push($array_success, $subject_id);
                        }
                    }
                }
                 
                if($successInsertingSubjectLoad == true 
                    ){
                    // echo "hitt";
                    // Update students status
                    // Grade 11 1nd sem -> Grade 11 2nd sem & Enroll Student
                    // Grade 12 1st sem -> Grade 12 2nd sem & Enroll Student
                    // Graduate What todo next?.

                    $wasSuccess = $oldEnroll->UpdateTertiaryStudentStatus($student_username);
                    
                    if($wasSuccess){
                        // echo "enrolled new studfent";
                    }
                }
                $isSuccess = false;
                if(sizeof($array_success) > 0 && empty($array_error)){

                    // Check registrar should include all available subjects
                    // if not yet ALL included, no updating status will happen
                    // TODO:
                    // if($wasSuccess){
                    //     // echo "was Success ";
                        
                    // }else{
                    //     echo "not success";
                    // }

                    foreach ($array_success as $key => $subject_ids_inserted) {
                        # subject_id that added in the student_subject db
                        echo "You have successfully inserted subject_id $subject_ids_inserted to the database";
                        $isSuccess = true;
                    }

                    if($isSuccess == true){
                        $url = directoryPath . "old_enrollees.php";
                        header("Location: $url");
                        // exit();
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
                    <h5 style="font-weight: 500;" class="text-center text-primary">Available Subjects for S.Y <?php echo $current_school_year_term;?> <?php echo $current_school_year_semester;?> Semester</h5>

                    <div class="text-right">
                       
                        <?php 
                            if($oldEnroll->CheckStudentUpdatedSection($student_username) == true){
                                $showUnAssignedSection = $oldEnroll->UpdateStudentSectionDropdown($student_username, $student_id);
                                echo $showUnAssignedSection;
                            }
                        ?>
                    </div>

                    <div class="table-responsive" style="margin-top:5%;"> 
                        <form action="" method="POST">

                            <table class="table table-striped table-bordered table-hover "  style="font-size:13px" cellspacing="0"  > 
                                <thead>
                                    <tr class="text-center"> 
                                        <th>
                                            <input type="checkbox" id="select-all-checkbox">                                        </th>
                                        <th rowspan="2">ID</th>
                                        <th rowspan="2">Code</th>
                                        <th rowspan="2">Description</th>  
                                        <th rowspan="2">Unit</th>
                                        <th rowspan="2">Year level</th>
                                        <th rowspan="2">Available Semester</th>
                                    </tr>	
                                </thead> 
                                    <tbody>
                                        <?php

                                            // echo $old_student_status;
                                            $listOfSubjects = $studentEnroll->GetStudentTertiarySubjects($student_username);

                                             
                                            foreach ($listOfSubjects as $key => $row) {


                                                $subject_tertiary_id = $row['subject_tertiary_id'];
                                                $subject_code = $row['subject_code'];
                                                $unit = $row['unit'];
                                                $subject_title = $row['subject_title'];
                                                $course_level = $row['course_level'];
                                                $semester = $row['semester'];

                                                echo"
                                                    <tr class='text-center'>
                                                        <td  class='text-center'>
                                                            <input name='tertiary_subject_ids[]' class='checkbox'  value='$subject_tertiary_id' type='checkbox'>
                                                        </td>
                                                        <td>$subject_tertiary_id</td>
                                                        <td>$subject_code</td>
                                                        <td>$subject_title</td>
                                                        <td>$unit</td>
                                                        <td>$course_level Year</td>
                                                        <td>$semester</td>
                                                    </tr>
                                                ";
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
                            <button type="submit" name="tertiary_subject_load_btn" class="btn btn-success btn-sm">Add Subject</button>
                            <button type="submit" name="unload_subject_btn" class="btn btn-danger btn-sm">Unload Subject</button>
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
