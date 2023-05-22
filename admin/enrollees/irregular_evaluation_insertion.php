<?php 
    include('../registrar_enrollment_header.php');
    require_once('../../enrollment/classes/StudentEnroll.php');
    require_once('../../enrollment/classes/SectionTertiary.php');
    require_once('../classes/Subject.php');
    require_once('../../enrollment/classes/OldEnrollees.php');
    require_once('../classes/Course.php');


    if(isset($_GET['id'])){


        $enrol = new StudentEnroll($con);
        $subject = new Subject($con, "");

        $student_id = $_GET['id'];

        $student_fullname = $enrol->GetStudentFullName($student_id);

        $student_username = $enrol->GetStudentUsername($student_id);

        $student_course_tertiary_id = $enrol->GetStudentCourseTertiaryId($student_username);

        $section = new SectionTertiary($con, $student_course_tertiary_id);

        $student_tertiary_section = $section->GetSectionName();
        $student_tertiary_section_term = $section->GetCourseTertiaryTerm();


        $student_tertiary_course_id = $enrol->GetStudentCourseTertiaryId($student_username);

        $school_year_obj = $enrol->GetActiveSchoolYearAndSemester();

        $current_school_year_id = $school_year_obj['school_year_id'];
        $current_school_year_term = $school_year_obj['term'];
        $current_school_year_period = $school_year_obj['period'];

        $student_program_id = $enrol->GetStudentTertiaryProgramId($student_tertiary_course_id);

        $tertiarySectipnDropdown = $section->IrregularStudentSectionList($student_id, $student_program_id);

        // echo $student_program_id;
       
        if(isset($_POST['irregular_tertiary_subject_load_btn'])){

            $subject_ids = $_POST['subject_ids'];
                
            // $pre_subject_ids = $_POST['pre_subject_ids'];
            $array_success = [];

            $successInsertingSubjectLoad = false;

            // Update student + 1 to the total_student
            $active = "yes";
            $is_full = "no";
            $total_student = 1;

            $sql_insert = $con->prepare("INSERT INTO student_subject_tertiary 
                (student_id, subject_tertiary_id, school_year_id, subject_program_id, is_retake)
                VALUES(:student_id, :subject_tertiary_id, :school_year_id, :subject_program_id, :is_retake)");

            foreach ($subject_ids as $key => $value) {
            
                $subject_tertiary_id = $value;

                // echo $subject_tertiary_id;

                $requisite_subject_title = $subject->GetPreRequisiteSubjectTitle($subject_tertiary_id);
                // $subject_tertiary_course_id = $subject->GetSubjectTertiaryCourseId($subject_tertiary_id);

                $subject_program_id = $subject->GetTertiarySubjectId($subject_tertiary_id);
                $checkIfSubjectTakenAndPassed = $subject->CheckIfPreReqSubjectIsPassed($requisite_subject_title, $subject_tertiary_id, $student_id);

                // Check if re-take subject.
                $checkRetakeSubject = $subject->CheckRetakeSubject($requisite_subject_title,
                    $subject_tertiary_id, $student_id);

                $checkIfAlreadyInsertedButNotYetRemarked = $subject->CheckIfAlreadyInsertedButNotYetRemarked($requisite_subject_title,
                    $subject_tertiary_id, $student_id);

                // echo $requisite_subject_title . " subject";



                if($checkIfSubjectTakenAndPassed == true){
                    echo "$requisite_subject_title has already been passed.";
                }else if($checkIfAlreadyInsertedButNotYetRemarked == true){

                    echo "$requisite_subject_title has already been inserted but not yet remarked";
                }
                else if($checkRetakeSubject == true){

                    // echo "retake";

                    $sql_insert->bindValue(":student_id", $student_id);
                    $sql_insert->bindValue(":subject_tertiary_id", $subject_tertiary_id);
                    $sql_insert->bindValue(":school_year_id", $current_school_year_id);
                    $sql_insert->bindValue(":subject_program_id", $subject_program_id);
                    $sql_insert->bindValue(":is_retake", "yes");

                    if($sql_insert->execute() && $sql_insert->rowCount() > 0){
                        echo "$requisite_subject_title has retake the subject and enrolled.";

                        # Insert student enrollment_id with the appropriate year standing
                        # Based on major subjects.
                        
                    }
                }

                else if($checkRetakeSubject == false 
                    && $checkIfSubjectTakenAndPassed == false
                    && $checkIfAlreadyInsertedButNotYetRemarked == false){

                    // echo " $requisite_subject_title is not retake and subject is not taken and passed ";
                    // echo "<br>";
                    // echo "$requisite_subject_title is not taken yet or student had failed.";
                    // echo "$requisite_subject_title is already taken";


                    // echo "$requisite_subject_title is not taken and passed";

                    // Check if subject is available
                    # Passed the pre-requisite.

                    $sql_insert->bindValue(":student_id", $student_id);
                    $sql_insert->bindValue(":subject_tertiary_id", $subject_tertiary_id);
                    $sql_insert->bindValue(":school_year_id", $current_school_year_id);
                    $sql_insert->bindValue(":subject_program_id", $subject_program_id);
                    $sql_insert->bindValue(":is_retake", "no");

                    if($sql_insert->execute() && $sql_insert->rowCount() > 0){
                        echo "$requisite_subject_title has been enrolled";

                        # Insert student enrollment_id with the appropriate year standing
                        # Based on major subjects.
                    }
                }
            }

        }

        ?>


        <div class="row col-md-12">
            <div class="container">
                <h4 style="font-weight: 500;" class="mb-3 text-primary text-center">Enrollment Subject List of S.Y <?php echo $current_school_year_term;?> <?php echo $current_school_year_period;?> Semester</h4>
                <hr>
                <div class="table-responsive">	
                  <form method="POST">
                        <table id="dash-table" class="table table-striped table-bordered table-hover table-responsive" style="font-size:14px" cellspacing="0">

                        <div class="form-group mb-3">
                            <label class="text-center mb-2" for="">Current Section</label>
                            <input style="width: 35%;" type="text" readonly
                                value="<?php echo $student_tertiary_section;?> ( <?php echo $student_tertiary_section_term;?> )" class="text-center form-control">
                        </div>

                            <!-- <select style="width: 35%;" name="assign_section" id="assign_section"
                                 class="text-center mb-3 form-control">

                                <option value="">Assign Section</option>
                                <option value="BSCS-1A">BSCS-1A</option>
                                <option value="BSCS-2A">BSCS-2A</option>
                            </select> -->

                            <?php
                            
                                // dropd
                                echo $tertiarySectipnDropdown;
                            ?>

                            <thead>
                                <tr class="text-success text-center">
                                    <th class="text-center">
                                        <input type="checkbox" id="select-all-checkbox">
                                    </th>
                                    <th>Subject Id</th>
                                    <th>Code</th>
                                    <th>Description</th>
                                    <th>Unit</th>
                                    <th>Semester</th>
                                    <th>Standing</th>
                                    <th>Course-Section</th>
                                    <th>S.Y</th>
                                    <th class="text-center" width="20%" >Pre-Req</th>
                                </tr>	
                            </thead> 
                            <tbody>
                                <?php

                                    $program_id = $student_program_id;
                                    
                                    $sql = $con->prepare("SELECT 
                                        t1.subject_title,
                                        t1.subject_tertiary_id,
                                        t1.subject_code,
                                        t1.course_level,
                                        t1.semester,
                                        t1.subject_program_id as t1_subject_program_id,

                                        t1.unit,

                                        t3.pre_subject_id,
                                        t3.pre_req_subject_title,

                                        t2.program_section,
                                        t2.school_year_term


                                        
                                        FROM subject_tertiary as t1

                                        LEFT JOIN course_tertiary as t2 ON t1.course_tertiary_id = t2.course_tertiary_id
                                        LEFT JOIN subject_program as t3 ON t3.subject_program_id = t1.subject_program_id


                                        WHERE t2.active = :active
                                        AND t2.school_year_term = :school_year_term
                                        AND t1.semester = :semester
                                        AND (
                                            t1.program_id = :program_id
                                            OR t1.subject_type != :subject_type
                                        )

                                        ORDER BY t2.program_section ASC,

                                            t1.semester,
                                            t1.course_level
                                        ");

                                    $sql->bindValue(":active", "yes");
                                    $sql->bindValue(":school_year_term", $current_school_year_term);
                                    $sql->bindValue(":semester", $current_school_year_period);
                                    $sql->bindValue(":subject_type", "SPECIALIZED_SUBJECTS");
                                    $sql->bindValue(":program_id", $student_program_id);

                                    $sql->execute();

                                    if($sql->rowCount() > 0){

                                        while($row = $sql->fetch(PDO::FETCH_ASSOC)){

                                            $subject_title = $row['subject_title'];
                                            $subject_tertiary_id = $row['subject_tertiary_id'];
                                            $subject_code = $row['subject_code'];
                                            $semester = $row['semester'];
                                            $course_level = $row['course_level'];
                                            $program_section = $row['program_section'];
                                            $unit = $row['unit'];
                                            $course_level = $row['course_level'];
                                            $pre_subject_id = $row['pre_subject_id'];
                                            $pre_req_subject_title = $row['pre_req_subject_title'];
                                            $school_year_term = $row['school_year_term'];

                                            
                                            $subject_title_pre_requisite = "";

                                            // echo $pre_subject_id;
                                            // echo "<br>";

                                            // echo $t1_subject_program_id;
                                            // echo 1;
                                            // echo "<br>";

                                           

                                            echo "
                                                <tr class='text-center'>
                                                    <td  class='text-center'>
                                                            <input name='subject_ids[]' class='checkbox'  value='$subject_tertiary_id' type='checkbox'>
                                                    </td>
                                                    <td>$subject_tertiary_id</td>
                                                    <td>$subject_code</td>
                                                    <td>$subject_title</td>
                                                    <td>$unit</td>
                                                    <td>$semester</td>
                                                    <td>$course_level</td>
                                                    <td>$program_section</td>
                                                    <td>$school_year_term</td>
                                                    
                                                    <td>$pre_req_subject_title</td>
                                                    
                                                </tr>
                                            ";
                                        }
                                    }
                                ?>
                            </tbody>
                        </table>
                        <button type="submit" name="irregular_tertiary_subject_load_btn" class="btn btn-success btn">Add Subject & Enroll</button>
                  </form>   

                </div>
            </div>
            <hr>
            <hr>
  
            <div class="container">
                <h4 class="mb-2 text-success text-center">Student Taken Subject Report</h4>
                <h5 style="font-weight: bold;" class="mb-3 text-center text-muted"><?php echo $student_fullname;?></h5>
                <div class="table-responsive">	

                    <table id="dash-table" class="table table-striped table-bordered table-hover table-responsive" style="font-size:14px" cellspacing="0">
                        <thead>
                            <tr class="text-primary text-center">
                                
                                <th>Subject Id</th>
                                <th>Code</th>
                                <th>Description</th>
                                <th>Unit</th>
                                <th>S.Y</th>
                                <th>Semester</th>
                                <th>Standing</th>
                                <th>Course-Section</th>
                                <th>Remarks</th>
                            </tr>	
                        </thead> 
                        <tbody>
                            <?php

                                $sql = $con->prepare("SELECT 
                                    t2.subject_title,
                                    t2.subject_tertiary_id,
                                    t2.subject_code,
                                    t2.course_level,
                                    t2.semester,

                                    t2.unit,

                                    t4.program_section,
                                    t4.school_year_term,

                                    t3.remarks
                                    
                                    FROM student_subject_tertiary as t1

                                    INNER JOIN subject_tertiary as t2 ON t2.subject_tertiary_id = t1.subject_tertiary_id


                                    LEFT JOIN student_subject_grade_tertiary as t3 ON t3.student_subject_tertiary_id = t1.student_subject_tertiary_id

                                    LEFT JOIN course_tertiary as t4 ON t4.course_tertiary_id= t2.course_tertiary_id

                                    WHERE t1.student_id=:student_id
                                    ");

                                $sql->bindValue(":student_id", $student_id);
                                $sql->execute();
                                
                                // $sql = $con->prepare("SELECT 
                                //     t2.subject_title,
                                //     t2.subject_tertiary_id,
                                //     t2.subject_code,
                                //     t2.course_level,
                                //     t2.semester,

                                //     t2.unit,

                                //     t3.program_section,
                                //     t3.school_year_term,

                                //     t1.remarks
                                    
                                //     FROM student_subject_grade_tertiary as t1

                                //     LEFT JOIN subject_tertiary as t2 ON t2.subject_tertiary_id = t1.subject_tertiary_id
                                //     LEFT JOIN course_tertiary as t3 ON t3.course_tertiary_id= t2.course_tertiary_id

                                //     WHERE t1.student_id=:student_id
                                //     ");

                                // $sql->bindValue(":student_id", $student_id);
                                // $sql->execute();

                                if($sql->rowCount() > 0){

                                    while($row = $sql->fetch(PDO::FETCH_ASSOC)){

                                        $subject_title = $row['subject_title'];
                                        $subject_tertiary_id = $row['subject_tertiary_id'];
                                        $subject_code = $row['subject_code'];
                                        $course_level = $row['course_level'];
                                        $program_section = $row['program_section'];
                                        $unit = $row['unit'];
                                        $semester = $row['semester'];
                                        $remarks = $row['remarks'];

                                        if($remarks == ""){
                                            $remarks = "
                                                <i style='color:orange;'class='fas fa-times'></i>
                                            ";
                                        }
                                        $school_year_term = $row['school_year_term'];
                                        
                                        $course_level = $row['course_level'];
                                        echo "
                                            <tr class='text-center'>
                                                <td>$subject_tertiary_id</td>
                                                <td>$subject_code</td>
                                                <td>$subject_title</td>
                                                <td>$unit</td>
                                                <td>$school_year_term</td>
                                                <td>$semester</td>
                                                <td>$course_level</td>
                                                <td>$program_section</td>
                                                <td>$remarks</td>
                                                
                                            </tr>
                                        ";
                                    }
                                }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="container">
                <h4 class="mb-2 text-success text-center">ABE Curriculum</h4>
                <div class="table-responsive">	

                    <table id="dash-table" class="table table-striped table-bordered table-hover table-responsive" style="font-size:14px" cellspacing="0">
                        <thead>
                            <tr class="text-primary text-center">
                                
                                <th>Code</th>
                                <th>Description</th>
                                <th>Unit</th>
                                <th>Semester</th>
                                <th>Standing</th>
                                <th>Requisite</th>
                            </tr>	
                        </thead> 
                        <tbody>
                            <?php
                                
                                $query = $con->prepare("SELECT 

                                    t1.subject_code,
                                    t1.subject_title,
                                    t1.unit,
                                    t1.semester,
                                    t1.course_level,
                                    t1.pre_req_subject_title

                                    FROM subject_program as t1 

                                    WHERE t1.program_id=:program_id
                                    ");
                            
                                $query->bindValue(":program_id", $student_program_id);
                                $query->execute();

                                if($query->rowCount() > 0){

                                    while($row = $query->fetch(PDO::FETCH_ASSOC)){
                                     // echo "wewwe";

                                        $subject_code = isset($row['subject_code']) ? $row['subject_code'] : "";
                                        $subject_title = isset($row['subject_title']) ? $row['subject_title'] : "";
                                        $unit = isset($row['unit']) ? $row['unit'] : "";
                                        $semester = isset($row['semester']) ? $row['semester'] : "";
                                        $course_level = isset($row['course_level']) ? $row['course_level'] : "";
                                        $pre_req_subject_title = $row['pre_req_subject_title'] !== "" ? $row['pre_req_subject_title'] : "";

                                        echo "
                                            <tr class='text-center'>
                                                <td>$subject_code</td>
                                                <td>$subject_title</td>
                                                <td>$unit</td>
                                                <td>$semester</td>
                                                <td>$course_level</td>
                                                <td>$pre_req_subject_title</td>
                                            </tr>
                                        ";
                                    }
                                } 
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>


        </div>

            <script>
                $(document).ready(function() {

                    $('#create_course_tertiary_id').change(function() {
                        var courseTertiaryId = $(this).val();
                        var student_id = parseInt($("#student_id_dropdown").val());
                        
                        // console.log(student_id);

                        var confirmed = confirm("Are you sure you want to update the section of the student?");

                        if (confirmed) {

                            // console.log('confirm')
                            $.ajax({
                                url: '../ajax/enrollee/update_irregular_section.php',
                                method: 'POST',
                                data: { courseTertiaryId, student_id },
                                success: function(response) {
                                
                                    // console.log(response);
                                    location.reload();
                                },
                                error: function(xhr, status, error) {
                                    // Handle any errors that occur during the AJAX request
                                }
                            });
                        }

                    });
                });
            </script>
        <?php
    }
?>