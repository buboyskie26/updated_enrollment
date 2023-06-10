<?php  
 
    include('../classes/Department.php');
    include('../../enrollment/classes/StudentEnroll.php');
    include('../../enrollment/classes/Schedule.php');
    include('../../enrollment/classes/Enrollment.php');
    include('../../enrollment/classes/OldEnrollees.php');
    include('../../enrollment/classes/SchoolYear.php');
    include('../../enrollment/classes/Section.php');
    include('../../includes/classes/Student.php');
    include('../classes/Course.php');

    include('../registrar_enrollment_header.php');

    // $teacher = new Teacher($con, $adminLoggedIn);

    $admin = new AdminUser($con, $registrarLoggedIn);

    $table =  $admin->createTable();

    if(isset($_POST['submit_teacher'])){
        $wasSuccessful = $teacher->insertTeacher($_POST['department_id'],
            $_POST['username'], $_POST['firstname'], $_POST['lastname']);
    }
    
    if(isset($_GET['teacher_id'])){
        $teacher_id = $_GET['teacher_id'];

        $createFormTeacherCourse = $teacher->createTeacherCourse($teacher_id);

        echo $teacher_id;
    }

    //
    $studentEnroll = new StudentEnroll($con);
    $schedule = new Schedule($con, $studentEnroll);
  
    $course = new Course($con, $studentEnroll);

    $school_year_obj = $studentEnroll->GetActiveSchoolYearAndSemester();

    $current_school_year_id = $school_year_obj['school_year_id'];
    $current_school_year_term = $school_year_obj['term'];
    $current_school_year_period = $school_year_obj['period'];

    $school_year = new SchoolYear($con, $current_school_year_id);

    $student = new Student($con, null);

    $string = "ABE4-A";

    // $pattern = '/(\d+)/';
    // $replacement = '${1}';

    // $newString = preg_replace_callback($pattern, function($matches) {
    //     return intval($matches[0]) + 1;
    // }, $string);

    // echo $newString;

    $createUrl = base_url . "/create.php";

    // $check = $school_year->DoesEndPeriodIsOver($current_school_year_id);

    # ONLY FOR S.Y PAGE
    $startEnrollmentInit = $school_year->DoesStartEnrollmentComesIn();
    $endEnrollmentInit = $school_year->DoesEndEnrollmentComesIn($current_school_year_id);
    $end = $school_year->EndOfCurrentSemesterInit($current_school_year_id);

    // $allRegularNewStudents = $student->GetAllOngoingRegularStudent();

    // print_r($allRegularNewStudents);

    // $get_shs_tertiary_section = $con->prepare("SELECT * 
    
    //     FROM course
    //     WHERE school_year_term=:school_year_term
    //     AND active=:active
    //     AND is_tertiary=1
    //     ");

    // $get_shs_tertiary_section->bindValue(":school_year_term", $current_school_year_term);
    // $get_shs_tertiary_section->bindValue(":active", "yes");
    // $get_shs_tertiary_section->execute();

    // if($get_shs_tertiary_section->rowCount() > 0){
    //     $getTertiaryCoursesForMovingUp = $get_shs_tertiary_section->fetchAll(PDO::FETCH_ASSOC);

    //     foreach ($getTertiaryCoursesForMovingUp as $key => $value) {

    //             $tertiary_program_id = $value['program_id'];
    //             $tertiary_course_level = $value['course_level'];
    //             $course_id = $value['course_id'];

    //             echo $course_id;
    //     }
    // }

    if(isset($_POST['populate_subject_btnx']) && isset($_POST['course_id'])
        && isset($_POST['program_id']) && isset($_POST['course_level'])){

        $course_id = $_POST['course_id'];
        $program_id = $_POST['program_id'];
        $course_level = $_POST['course_level'];

        $get_subject_program = $con->prepare("SELECT * FROM subject_program
            WHERE program_id=:program_id
            AND course_level=:course_level
            ");

        $get_subject_program->bindValue(":program_id", $program_id);
        $get_subject_program->bindValue(":course_level", $course_level);
        $get_subject_program->execute();
        
        # TODO: Check if in subject_program subjects are already in the subject table.
        // if yes, it is already in.
        # TODO: Set subject code = HUMSS-ES12-SECTION-NAME (HUMMS12-A)

        if($get_subject_program->rowCount() > 0){

            $isSubjectCreated = false;

            $insert_section_subject = $con->prepare("INSERT INTO subject
                (subject_title, description, subject_program_id, unit, semester, program_id, course_level, course_id, subject_type, subject_code)
                VALUES(:subject_title, :description, :subject_program_id, :unit, :semester, :program_id, :course_level, :course_id, :subject_type, :subject_code)");

            while($row = $get_subject_program->fetch(PDO::FETCH_ASSOC)){

                $program_program_id = $row['subject_program_id'];
                $program_course_level = $row['course_level'];
                $program_semester = $row['semester'];
                $program_subject_type = $row['subject_type'];
                $program_subject_title = $row['subject_title'];
                $program_subject_description = $row['description'];
                $program_subject_unit = $row['unit'];
                $program_subject_code = $row['subject_code'];

                # TODO Subject Code Init In S.Y
                // $program_subject_code = $row['subject_code'] . $program_section; 

                
                $insert_section_subject->bindValue(":subject_title", $program_subject_title);
                $insert_section_subject->bindValue(":description", $program_subject_description);
                $insert_section_subject->bindValue(":subject_program_id", $program_program_id);
                $insert_section_subject->bindValue(":unit", $program_subject_unit);
                $insert_section_subject->bindValue(":semester", $program_semester);
                $insert_section_subject->bindValue(":program_id", $program_id);
                $insert_section_subject->bindValue(":course_level", $program_course_level);
                $insert_section_subject->bindValue(":course_id", $course_id);
                $insert_section_subject->bindValue(":subject_type", $program_subject_type);
                $insert_section_subject->bindValue(":subject_code", $program_subject_code);

                // $insert_section_subject->execute();
                if($insert_section_subject->execute()){
                    $isSubjectCreated = true;
                }
            }
            if($isSubjectCreated == true){
                // echo "Successfully populated subjects in course_id $course_id";
            }
        }else{
            echo "program id not matched";
        }
    }

    if(isset($_POST['tertiary_populate_subject_btnx']) && isset($_POST['course_tertiary_id'])
        && isset($_POST['program_id']) && isset($_POST['course_level'])){

        $course_tertiary_id = $_POST['course_tertiary_id'];
        $program_id = $_POST['program_id'];
        $course_level = $_POST['course_level'];
        $program_section = $_POST['program_section'];

        $get_subject_program = $con->prepare("SELECT * FROM subject_program
            WHERE program_id=:program_id
            AND course_level=:course_level
            ");

        $get_subject_program->bindValue(":program_id", $program_id);
        $get_subject_program->bindValue(":course_level", $course_level);
        $get_subject_program->execute();
        
        # TODO: Check if in subject_program subjects are already in the subject table.
        // if yes, it is already in.
        # TODO: Set subject code = HUMSS-ES12-SECTION-NAME (HUMMS12-A)

        if($get_subject_program->rowCount() > 0){

            $isSubjectCreated = false;

            $insert_section_subject = $con->prepare("INSERT INTO subject_tertiary
                (subject_title, description, subject_program_id, unit, semester, program_id, course_level, course_tertiary_id, subject_type, subject_code)
                VALUES(:subject_title, :description, :subject_program_id, :unit, :semester, :program_id, :course_level, :course_tertiary_id, :subject_type, :subject_code)");

            while($row = $get_subject_program->fetch(PDO::FETCH_ASSOC)){

                $program_program_id = $row['subject_program_id'];
                $program_course_level = $row['course_level'];
                $program_semester = $row['semester'];
                $program_subject_type = $row['subject_type'];
                $program_subject_title = $row['subject_title'];
                $program_subject_description = $row['description'];
                $program_subject_unit = $row['unit'];
                $program_subject_code = $row['subject_code'];

                # TODO Subject Code Init In S.Y
                // $program_subject_code = $row['subject_code'] . $program_section; 

                
                $insert_section_subject->bindValue(":subject_title", $program_subject_title);
                $insert_section_subject->bindValue(":description", $program_subject_description);
                $insert_section_subject->bindValue(":subject_program_id", $program_program_id);
                $insert_section_subject->bindValue(":unit", $program_subject_unit);
                $insert_section_subject->bindValue(":semester", $program_semester);
                $insert_section_subject->bindValue(":program_id", $program_id);
                $insert_section_subject->bindValue(":course_level", $program_course_level);
                $insert_section_subject->bindValue(":course_tertiary_id", $course_tertiary_id);
                $insert_section_subject->bindValue(":subject_type", $program_subject_type);
                $insert_section_subject->bindValue(":subject_code", $program_subject_code);

                // $insert_section_subject->execute();
                if($insert_section_subject->execute()){
                    $isSubjectCreated = true;
                }
            }
            if($isSubjectCreated == true){
                echo "Successfully populated subjects in course_id $course_tertiary_id";
            }
        }else{
            echo "program id not matched";
        }
    }

?>

<script src="../assets/js/common.js"></script>

<?php

    echo "
        <div class='row col-md-12'>
            <div style='width:100%;' class='teacher_section'>
                $table
            </div>
    ";
?>
   
    <hr>
    
    <!-- SHS AUTO -->
    <div style="display: none;"  >
        <div class="col-lg-12">
            <div class="row">
                <div class="col-lg-12 text-center">
                    <h4 class="page-header">Active Enrollment Strand Sections (SY: <?php echo $current_school_year_term;?>-<?php echo $current_school_year_period;?> Semester)</h4>
                </div>
            </div>
            
        </div>

        <div class="table-responsive" style="margin-top:2%;"> 
            <div class="mb-3">
                <a href="<?php echo directoryPath . "create.php"; ?>">
                    <button class=" btn btn-success">Add Section</button>
                </a> 
            </div>
            <table  class="table table-striped table-bordered table-hover "  style="font-size:13px" cellspacing="0"  > 
                <thead>
                    <tr class="text-center"> 
                        <th rowspan="2">Course Id</th>
                        <th rowspan="2">Track_Section</th>
                        <th rowspan="2">Capacity</th>
                        <th rowspan="2">Total Student</th>  
                        <th rowspan="2">School Year</th>  
                        <th rowspan="2">Add Subject</th>
                        <!-- <th class="text-center" colspan="3">Schedule</th>  -->
                    </tr>	
                </thead> 	
                <tbody>
                    <?php 
                        $username = "";
                        // $sectionScheduleGradeElevenFirst = $schedule->GetSectionScheduleGradeElevenFirst($username, $student_id, 11, "First");

                        $query = $con->prepare("SELECT * FROM course
                            WHERE active='yes'
                            AND school_year_term=:school_year_term
                            
                            ");

                        $query->bindValue(":school_year_term", $current_school_year_term);
                        $query->execute();

                        if($query->rowCount() > 0){
                            while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
                                $course_id = $row['course_id'];
                                $course_level = $row['course_level'];
                                $program_section = $row['program_section'];
                                $program_id = $row['program_id'];
                                $school_year_term = $row['school_year_term'];
                                
                                $doesPopulated = $course->CheckSectionPopulatedBySubject($course_id);

                                $button = "";

                                $url = directoryPath . "strand_show.php?id=$course_id";

                                $section_url = "$program_section";

                                if($doesPopulated == true){
                                    $button = "
                                        <td>
                                            <button class='btn btn-sm btn-outline-success'>Populated</button>
                                        </td>
                                    ";
                                    $section_url = "
                                        <a href='$url' style='color: whitesmoke;' >
                                            ".$row['program_section']." 
                                        </a>
                                    ";
                                }
                                else{
                                    $button = "
                                        <td>
                                            <form method='POST'>
                                                <input type='hidden' name='course_id'value='$course_id'>
                                                <input type='hidden' name='program_id'value='$program_id'>
                                                <input type='hidden' name='course_level' value='$course_level'>
                                                <button type='submit' name='populate_subject_btn' class='btn btn-sm btn-success populate-btn' style='display: inline-block;'>Populate</button>
                                            </form>
                                        </td>
                                    "; 
                                }

                                echo "<tr class='text-center'>";
                                    echo "<td>" . $row['course_id'] . "</td>";
                                    echo "<td>
                                        $section_url
                                    </td>";
                                    echo "<td>" . $row['capacity'] . "</td>";
                                    echo "<td>2</td>";
                                    echo "<td>$school_year_term</td>";
                                    echo "$button";
                                echo "</tr>";
                            }
                        }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- <script>
  // select all populate buttons
  const populateBtns = document.querySelectorAll('.populate-btn');

  // loop through buttons and trigger click event
  populateBtns.forEach(btn => {
    btn.click();
  });
</script> -->

<!-- 

<?php  include('../includes/footer.php');?> -->