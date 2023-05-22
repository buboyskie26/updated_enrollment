<?php 
    
    include('../registrar_enrollment_header.php');
    include('../../enrollment/classes/StudentEnroll.php');
    include('../../enrollment/classes/OldEnrollees.php');
    include('../../enrollment/classes/Transferee.php');
    include('../classes/Course.php');

    if(isset($_GET['id'])){
        
        $student_id = $_GET['id'];

        $enroll = new StudentEnroll($con);
        $oldEnroll = new OldEnrollees($con, $enroll);
        $transferee = new Transferee($con, $enroll);

        $username = $enroll->GetStudentUsername($student_id);
        $student_fullname = $enroll->GetStudentFullName($student_id);

        $school_year_obj = $enroll->GetActiveSchoolYearAndSemester();
        $school_year_id = $school_year_obj['school_year_id'];
        $current_school_year_term = $school_year_obj['term'];
        $current_school_year_period = $school_year_obj['period'];

        $student_course_level = $enroll->GetStudentCourseLevel($username);
        $student_course_id = $enroll->GetStudentCourseId($username);
        // echo $student_id;


        if(isset($_POST['transferee_subject_load_btn'])){

            // $title = $("#subject_title").val();

            $subject_ids = $_POST['transfeeree_subject_ids'];
            $is_transferee = "yes";
            $not_transferee = "no";

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

            foreach ($subject_ids as $key => $value) {
                # code...
                $subject_id = $value;
                echo "<br>";

                # TODO: 1. Check if already inserted
                $subjectLoadsIds = array();

                $shsStudentSubject = $transferee->GetSHSStudentEnrolledSubjects($username, $subject_id);

                foreach ($shsStudentSubject as $key => $enrolledSubject) {
                    $subjectLoadsIds[$enrolledSubject['subject_id']] = true;
                }
                
                if (isset($subjectLoadsIds[$subject_id])) {
                    echo $subject_id . " is already in the subject loads";
                    array_push($array_error, $subject_id);
                    echo "<br>";
                }

                # 2. Insert is_transferee as yes if this was a transferee subject.
                if(empty($array_error)){

                    $checkIfNotTransfereeSubject = $transferee->CheckIfNotTransfereeSubject($username, $subject_id);
                    
                    // if($checkIfNotTransfereeSubject == true){
                    //     // echo "true";
                    //     $insert_transferee->bindValue(":student_id", $student_id);
                    //     $insert_transferee->bindValue(":subject_id", $subject_id);
                    //     $insert_transferee->bindValue(":school_year_id", $school_year_id);
                    //     $insert_transferee->bindValue(":course_level", $student_course_level);
                    //     $insert_transferee->bindValue(":is_transferee", $not_transferee);

                    //     if(false){
                    //     // if($insert_transferee->execute()){

                    //         // Enrolled
                    //         $enrollment_status = "enrolled";

                    //         $enrolled_transferee->bindValue(":enrollment_status", $enrollment_status);
                    //         $enrolled_transferee->bindValue(":course_id", $student_course_id);
                    //         $enrolled_transferee->bindValue(":student_id", $student_id);
                            
                    //         if($enrolled_transferee->execute()){
                    //             echo "Subject were inserted.";
                    //             echo "<br>";
                    //             $properSubjectsInserted = true;
                    //         }
                    //     }
                    // }else  if($checkIfNotTransfereeSubject == false){
                    //     // echo "false";
                    //     # NOTE: BUG IF Student transfered in Grade 12 2nd Sem
                    //     # NOT FIX YET.

                    //     echo $subject_id;

                    //     $insert_transferee->bindValue(":student_id", $student_id);
                    //     $insert_transferee->bindValue(":subject_id", $subject_id);
                    //     $insert_transferee->bindValue(":school_year_id", $school_year_id);
                    //     $insert_transferee->bindValue(":course_level", $student_course_level);
                    //     $insert_transferee->bindValue(":is_transferee", $is_transferee);
                    //     // $insert_transferee->execute();

                    //     // If transferee subject, student grade_subject must be remarked as 'Passed'
                    //     if(false){
                    //     // if($insert_transferee->execute()){

                    //         $lastInsertId = $con->lastInsertId();

                    //         $mark_passed_transferee_subject->bindValue(":student_id", $student_id);
                    //         $mark_passed_transferee_subject->bindValue(":subject_id", $subject_id);
                    //         $mark_passed_transferee_subject->bindValue(":remarks", $passed_remark);
                    //         $mark_passed_transferee_subject->bindValue(":student_subject_id", $lastInsertId);
                    //         $mark_passed_transferee_subject->bindValue(":is_transferee", "yes");
                    //         $mark_passed_transferee_subject->bindValue(":subject_title", $subject_title);
                    //         $mark_passed_transferee_subject->bindValue(":course_id", $course_id);
                    //         // $mark_passed_transferee_subject->execute();

                    //         # TODO: It should redirect to the transfee_enrollees.php
                    //     }
                        
                    // }

                    
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

                    if (!in_array($subject_title, $studentSemesterSubjects)) {
                        $isTransfereeSubj = true;

                        echo $subject_title . " is now CREDITED";

                        $insert_transferee->bindValue(":student_id", $student_id);
                        $insert_transferee->bindValue(":subject_id", $subject_id);
                        $insert_transferee->bindValue(":school_year_id", $school_year_id);
                        $insert_transferee->bindValue(":course_level", $student_course_level);
                        $insert_transferee->bindValue(":is_transferee", $is_transferee);

                        // if(false){
                        if($insert_transferee->execute()){

                            $lastInsertId = $con->lastInsertId();

                            $mark_passed_transferee_subject->bindValue(":student_id", $student_id);
                            $mark_passed_transferee_subject->bindValue(":subject_id", $subject_id);
                            $mark_passed_transferee_subject->bindValue(":remarks", $passed_remark);
                            $mark_passed_transferee_subject->bindValue(":student_subject_id", $lastInsertId);
                            $mark_passed_transferee_subject->bindValue(":is_transferee", "yes");
                            $mark_passed_transferee_subject->bindValue(":subject_title", $subject_title);
                            $mark_passed_transferee_subject->bindValue(":course_id", $subject_course_id);
                            
                            $mark_passed_transferee_subject->execute();

                            # TODO: It should redirect to the transfee_enrollees.php
                        }
                    }

                    else if (in_array($subject_title, $studentSemesterSubjects)) {

                        $isTransfereeSubj = false;
                        echo "$subject_title is now inserted.";
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
                                echo "Subject were inserted.";
                                echo "<br>";
                            }
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
                $update_transferee_into_regular->bindValue(":student_status", $current_status);

                if($update_transferee_into_regular->execute()){
                    
                    // echo "Transferee student becomes Regular.";
                    echo "New Transferee enrolee becomes Old student.";
                    echo "<br>";
                }
            }
        }
        ?>
            <div class="table-responsive" style="margin-top:5%;"> 
                <div class="text-right">
                    <?php 
                        if($oldEnroll->CheckStudentUpdatedSection($username) == true){

                            $showUnAssignedSection = $oldEnroll->UpdateStudentSectionDropdown($username, $student_id);
                            echo $showUnAssignedSection;
                        }
                    ?>
                </div>

                <form action="" method="POST">
                    <h4 class="text-center">Transferee Insertion Subject Section</h4>
                    <p><?php echo $student_fullname;?> Transfered at SY:(<?php echo $current_school_year_term; ?>) <?php echo $current_school_year_period; ?> Semester</p>
                    <span>All green subjects are the credited subjects and the blank color is the current semester subjects</span>
                    <table class="table table-striped table-bordered table-hover "  style="font-size:13px" cellspacing="0"  > 
                        <thead>
                            <tr class="text-center"> 
                                <th></th>
                               
                                <th rowspan="2">ID</th>
                                <th rowspan="2">Code</th>
                                <th rowspan="2">Title</th>  
                                <th rowspan="2">Unit</th>
                                <th colspan="4">Schedule</th> 
                            </tr>	
                            <tr class="text-center"> 
                                 <th class="text-center">
                                    <input type="checkbox" id="select-all-checkbox">
                                </th>
                                <th>Day</th>
                                <th>Time</th>
                                <th>Room</th> 
                                <th>Section</th>  
                                <th>Semester</th>  
                                <th>Course Level</th>  
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

                                        echo '<tr style="background-color: '.$color.';">'; 
                                            echo '
                                            <td class="text-center">
                                                <input  name="transfeeree_subject_ids[]" class="checkbox" value="'.$subject_id.'" type="checkbox">
                                            </td>';
                                            echo '<td>'.$subject_id.'</td>';
                                            echo '<td >'.$subject_code.'</td>';
                                            echo '<td class="subject_title">'.$subject_title.'</td>';
                                            echo '<td>'.$unit.'</td>';
                                            echo '<td></td>';
                                            echo '<td></td>';
                                            echo '<td></td>';
                                            echo '<td></td>';
                                            echo '<td></td>';
                                            echo '<td></td>';
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
                    <button type="submit" name="transferee_subject_load_btn" class="btn btn-success btn-sm">Add Subject</button>
                    <button type="submit" name="transferee_unload_subject_btn" class="btn btn-danger btn-sm">Unload Subject</button>

                </form>
            </div>
        <?php
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

    // $(document).ready(function() {
    //     // alert('qwe')
    //     // Add event listener to "check all" checkbox
    //     $('#checkAllV2').on('change', function() {

    //         // Get the checked state of "check all" checkbox
    //         var isChecked = $(this).prop('checked');

    //         // console.log(isChecked)
    //         // Set the checked state of all checkboxes in the table loop accordingly
    //         $('.checkbox').prop('checked', isChecked);
    //     });

    //     $(".checkbox").on("click", function() {

    //     // Get the values from the associated <td> elements
    //     var subjectTitle = $(this).closest("tr").find(".subject_titlev2").val();
    //     // Log the values for testing
    //     console.log("Subject Title: " + subjectTitle);


    //     });
    // });


</script>