  
  <?php

    include('../registrar_enrollment_header.php');
    include('../../enrollment/classes/Section.php');
    include('../../enrollment/classes/StudentEnroll.php');
    include('../classes/Subject.php');

    $createProgramSelection = "2";

    $section = new Section($con, null);

    $enroll = new StudentEnroll($con);

    $school_year_obj = $enroll->GetActiveSchoolYearAndSemester();

    $current_school_year_term = $school_year_obj['term'];
    $current_school_year_period = $school_year_obj['period'];

    $trackDropdown = $section->createProgramSelection();

    if (isset($_SESSION['pending_enrollees_id'])) {
        $pending_enrollees_id = $_SESSION['pending_enrollees_id'];
        // You can use the $pending_enrollees_id variable as needed, such as redirecting back or displaying the URL.
    }

    if(isset($_POST['create_section_btn']) &&
        isset($_POST['program_section']) && 
        isset($_POST['program_id']) &&
        isset($_POST['capacity']) &&
        isset($_POST['adviser_teacher_id']) &&
        isset($_POST['room']) &&
        isset($_POST['course_level'])
        ){

            $is_tertiary = 1;

            $program_section = $_POST['program_section'];
            $program_id = $_POST['program_id'];
            $capacity = $_POST['capacity'];
            $adviser_teacher_id = $_POST['adviser_teacher_id'];
            $room = $_POST['room'];
            $course_level = (int) $_POST['course_level'];

            $is_active = "yes";
            $not_full = "no";

            $sql = $con->prepare("SELECT t2.department_name FROM program as t1

                INNER JOIN department as t2 ON t2.department_id = t1.department_id 
                WHERE t1.program_id=:program_id
                LIMIT 1");
            $sql->bindValue(":program_id", $program_id);
            $sql->execute();

            if($sql->rowCount() > 0){

                $department_name = $sql->fetchColumn();

                $is_tertiary = ($department_name == "Senior High School") ? 0 : 1;
            }

            # CONTROLLER.
            #                               | POPULATE |
            #  GRADE 11 Section Subjects -> 1st && 2nd Semester Subjects
            #  GRADE 12 Section Subjects -> 2nd Semester Subjects

            # Check if Section Name is already exists in the DB.

            if($section->CheckSetionExistsWithinCurrentSY($program_section,
                $current_school_year_term) == true){
                AdminUser::error("$program_section already exists within $current_school_year_term term", "create.php");
                exit();
            }

            $insert = $con->prepare("INSERT INTO course
                (program_section, program_id, capacity, adviser_teacher_id, room, school_year_term, active, is_full, course_level, is_tertiary)
                VALUES(:program_section, :program_id, :capacity, :adviser_teacher_id, :room, :school_year_term, :active, :is_full, :course_level, :is_tertiary)");
            
            $insert->bindValue(":program_section", $program_section);
            $insert->bindValue(":program_id", $program_id);
            $insert->bindValue(":capacity", $capacity);
            $insert->bindValue(":adviser_teacher_id", $adviser_teacher_id);
            $insert->bindValue(":room", $room);
            $insert->bindValue(":school_year_term", $current_school_year_term);
            $insert->bindValue(":active", $is_active);
            $insert->bindValue(":is_full", $not_full);
            $insert->bindValue(":course_level", $course_level, PDO::PARAM_INT);
            $insert->bindValue(":is_tertiary", $is_tertiary, PDO::PARAM_INT);

            if($insert->execute()){

            // if(false){




                $recently_created_course_id = $con->lastInsertId();

                $sectionObj = new Section($con, $recently_created_course_id);
                
                $created_program_section = $sectionObj->GetSectionName();

                $get_program_section = $section->GetSectionNameByCourseId($recently_created_course_id);

                if($current_school_year_period == "First" 
                    && $course_level == 11){

                    $get_subject_program = $con->prepare("SELECT * FROM subject_program
                        WHERE program_id=:program_id
                        AND course_level=:course_level
                        -- AND semester=:semester
                        ");

                    # Second Semester Subjects only,
                    # None usage of First Semester subject here.
                    
                    $get_subject_program->bindValue(":program_id", $program_id);
                    $get_subject_program->bindValue(":course_level", $course_level);
                    // $get_subject_program->bindValue(":semester", $current_school_year_period);
                    $get_subject_program->execute();

                    if($get_subject_program->rowCount() > 0){

                        $isSubjectCreated = false;

                        $insert_section_subject = $con->prepare("INSERT INTO subject
                            (subject_title, description, subject_program_id, unit, semester,
                                program_id, course_level, course_id, subject_type, subject_code,
                                pre_requisite)
                            VALUES(:subject_title, :description, :subject_program_id, :unit, :semester, 
                                :program_id, :course_level, :course_id, :subject_type, :subject_code,
                                :pre_requisite)");

                        while($row = $get_subject_program->fetch(PDO::FETCH_ASSOC)){

                            $program_program_id = $row['subject_program_id'];
                            $program_course_level = $row['course_level'];
                            $program_semester = $row['semester'];
                            $program_subject_type = $row['subject_type'];
                            $program_subject_title = $row['subject_title'];
                            $program_subject_description = $row['description'];
                            $program_subject_unit = $row['unit'];
                            $program_subject_pre_requisite = $row['pre_req_subject_title'];

                            $program_subject_code = $row['subject_code'] . "-". $get_program_section; 
                            // $program_subject_code = $row['subject_code']; 

                            $insert_section_subject->bindValue(":subject_title", $program_subject_title);
                            $insert_section_subject->bindValue(":description", $program_subject_description);
                            $insert_section_subject->bindValue(":subject_program_id", $program_program_id);
                            $insert_section_subject->bindValue(":unit", $program_subject_unit);
                            $insert_section_subject->bindValue(":semester", $program_semester);
                            $insert_section_subject->bindValue(":program_id", $program_id);
                            $insert_section_subject->bindValue(":course_level", $program_course_level);
                            $insert_section_subject->bindValue(":course_id", $recently_created_course_id);
                            $insert_section_subject->bindValue(":subject_type", $program_subject_type);
                            $insert_section_subject->bindValue(":subject_code", $program_subject_code);
                            $insert_section_subject->bindValue(":pre_requisite", $program_subject_pre_requisite);

                            // $insert_section_subject->execute();
                            if($insert_section_subject->execute()){
                                $isSubjectCreated = true;
                            }
                        }

                        if($isSubjectCreated == true){

                            if(isset($_SESSION['process_enrollment'])
                                && $_SESSION['process_enrollment'] == 'transferee'){

                                    AdminUser::success("New section: $created_program_section has been created.", "../admission/transferee_process_enrollment.php?step2=true&id=$pending_enrollees_id");
                                    exit();

                            }else if(isset($_SESSION['process_enrollment'])
                                && $_SESSION['process_enrollment'] == 'non_transferee'){

                                    AdminUser::success("New section: $created_program_section has been created.", "../admission/process_enrollment.php?step2=true&id=$pending_enrollees_id");
                                    exit();
                            }
                            else{
                                AdminUser::success("New section: $created_program_section has been created.", "index.php");

                            }
                        }
                    }

                }
                
                else if($current_school_year_period == "Second" 
                    && $course_level == 11){

                    $get_subject_program = $con->prepare("SELECT * FROM subject_program
                        WHERE program_id=:program_id
                        AND semester=:semester
                        AND course_level=:course_level
                        ");

                    # Second Semester Subjects only,
                    # None usage of First Semester subject here.
                    
                    $get_subject_program->bindValue(":program_id", $program_id);
                    $get_subject_program->bindValue(":course_level", $course_level);
                    $get_subject_program->bindValue(":semester", $current_school_year_period);
                    $get_subject_program->execute();

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

                            $program_subject_code = $row['subject_code'] . "-". $get_program_section; 
                            // $program_subject_code = $row['subject_code']; 

                            $insert_section_subject->bindValue(":subject_title", $program_subject_title);
                            $insert_section_subject->bindValue(":description", $program_subject_description);
                            $insert_section_subject->bindValue(":subject_program_id", $program_program_id);
                            $insert_section_subject->bindValue(":unit", $program_subject_unit);
                            $insert_section_subject->bindValue(":semester", $program_semester);
                            $insert_section_subject->bindValue(":program_id", $program_id);
                            $insert_section_subject->bindValue(":course_level", $program_course_level);
                            $insert_section_subject->bindValue(":course_id", $recently_created_course_id);
                            $insert_section_subject->bindValue(":subject_type", $program_subject_type);
                            $insert_section_subject->bindValue(":subject_code", $program_subject_code);

                            // $insert_section_subject->execute();
                            if($insert_section_subject->execute()){
                                $isSubjectCreated = true;
                            }
                        }

                        if($isSubjectCreated == true){


                            if(isset($_SESSION['process_enrollment'])
                                && $_SESSION['process_enrollment'] == 'transferee'){

                                    AdminUser::success("New section: $created_program_section has been created.", "../admission/transferee_process_enrollment.php?step2=true&id=$pending_enrollees_id");
                                    exit();

                            }else if(isset($_SESSION['process_enrollment'])
                                && $_SESSION['process_enrollment'] == 'non_transferee'){

                                    AdminUser::success("New section: $created_program_section has been created.", "../admission/process_enrollment.php?step2=true&id=$pending_enrollees_id");
                                    exit();
                            }
                            else{
                                AdminUser::success("New section: $created_program_section has been created.", "index.php");
                            }
                        }
                    }

                }

                else if($current_school_year_period == "First" 
                    && $course_level == 12){

                    $get_subject_program = $con->prepare("SELECT * FROM subject_program
                        WHERE program_id=:program_id
                        -- AND semester=:semester
                        AND course_level=:course_level
                        ");

                    # Second Semester Subjects only,
                    # None usage of First Semester subject here.
                    
                    $get_subject_program->bindValue(":program_id", $program_id);
                    // $get_subject_program->bindValue(":semester", $current_school_year_period);
                    $get_subject_program->bindValue(":course_level", $course_level);
                    $get_subject_program->execute();

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

                            $program_subject_code = $row['subject_code'] . "-". $get_program_section; 
                            // $program_subject_code = $row['subject_code']; 

                            $insert_section_subject->bindValue(":subject_title", $program_subject_title);
                            $insert_section_subject->bindValue(":description", $program_subject_description);
                            $insert_section_subject->bindValue(":subject_program_id", $program_program_id);
                            $insert_section_subject->bindValue(":unit", $program_subject_unit);
                            $insert_section_subject->bindValue(":semester", $program_semester);
                            $insert_section_subject->bindValue(":program_id", $program_id);
                            $insert_section_subject->bindValue(":course_level", $program_course_level);
                            $insert_section_subject->bindValue(":course_id", $recently_created_course_id);
                            $insert_section_subject->bindValue(":subject_type", $program_subject_type);
                            $insert_section_subject->bindValue(":subject_code", $program_subject_code);

                            // $insert_section_subject->execute();
                            if($insert_section_subject->execute()){
                                $isSubjectCreated = true;
                            }
                        }

                        if($isSubjectCreated == true){


                            if(isset($_SESSION['process_enrollment'])
                                && $_SESSION['process_enrollment'] == 'transferee'){

                                    AdminUser::success("New section: $created_program_section has been created.", "../admission/transferee_process_enrollment.php?step2=true&id=$pending_enrollees_id");
                                    exit();

                            }else if(isset($_SESSION['process_enrollment'])
                                && $_SESSION['process_enrollment'] == 'non_transferee'){

                                    AdminUser::success("New section: $created_program_section has been created.", "../admission/process_enrollment.php?step2=true&id=$pending_enrollees_id");
                                    exit();
                            }
                            else{
                                AdminUser::success("New section: $created_program_section has been created.", "index.php");

                            }
                        }
                    }
                }

                if($current_school_year_period == "First" 
                    && $course_level == 1){
                    $get_subject_program = $con->prepare("SELECT * FROM subject_program
                        WHERE program_id=:program_id
                        AND course_level=:course_level
                        -- AND semester=:semester
                        ");

                    # Second Semester Subjects only,
                    # None usage of First Semester subject here.
                    
                    $get_subject_program->bindValue(":program_id", $program_id);
                    $get_subject_program->bindValue(":course_level", $course_level);
                    // $get_subject_program->bindValue(":semester", $current_school_year_period);
                    $get_subject_program->execute();

                    if($get_subject_program->rowCount() > 0){

                        $isSubjectCreated = false;

                        $insert_section_subject = $con->prepare("INSERT INTO subject
                            (subject_title, description, subject_program_id, unit, semester,
                                program_id, course_level, course_id, subject_type, subject_code,
                                pre_requisite)
                            VALUES(:subject_title, :description, :subject_program_id, :unit, :semester, 
                                :program_id, :course_level, :course_id, :subject_type, :subject_code,
                                :pre_requisite)");

                        while($row = $get_subject_program->fetch(PDO::FETCH_ASSOC)){

                            $program_program_id = $row['subject_program_id'];
                            $program_course_level = $row['course_level'];
                            $program_semester = $row['semester'];
                            $program_subject_type = $row['subject_type'];
                            $program_subject_title = $row['subject_title'];
                            $program_subject_description = $row['description'];
                            $program_subject_unit = $row['unit'];
                            $program_subject_pre_requisite = $row['pre_req_subject_title'];

                            $program_subject_code = $row['subject_code'] . "-". $get_program_section; 
                            // $program_subject_code = $row['subject_code']; 

                            $insert_section_subject->bindValue(":subject_title", $program_subject_title);
                            $insert_section_subject->bindValue(":description", $program_subject_description);
                            $insert_section_subject->bindValue(":subject_program_id", $program_program_id);
                            $insert_section_subject->bindValue(":unit", $program_subject_unit);
                            $insert_section_subject->bindValue(":semester", $program_semester);
                            $insert_section_subject->bindValue(":program_id", $program_id);
                            $insert_section_subject->bindValue(":course_level", $program_course_level);
                            $insert_section_subject->bindValue(":course_id", $recently_created_course_id);
                            $insert_section_subject->bindValue(":subject_type", $program_subject_type);
                            $insert_section_subject->bindValue(":subject_code", $program_subject_code);
                            $insert_section_subject->bindValue(":pre_requisite", $program_subject_pre_requisite);

                            // $insert_section_subject->execute();
                            if($insert_section_subject->execute()){
                                $isSubjectCreated = true;
                            }
                        }

                        if($isSubjectCreated == true){


                            if(isset($_SESSION['process_enrollment'])
                                && $_SESSION['process_enrollment'] == 'transferee'){

                                    AdminUser::success("New section: $created_program_section has been created.", "../admission/transferee_process_enrollment.php?step2=true&id=$pending_enrollees_id");
                                    exit();

                            }else if(isset($_SESSION['process_enrollment'])
                                && $_SESSION['process_enrollment'] == 'non_transferee'){

                                    AdminUser::success("New section: $created_program_section has been created.", "../admission/process_enrollment.php?step2=true&id=$pending_enrollees_id");
                                    exit();
                            }
                            else{
                                AdminUser::success("New section: $created_program_section has been created.", "index.php");

                            }
                        }
                    }
                }

            }
            // else{
            //     AdminUser::error("Something went wrong on creation section.", "create.php");
            //     exit();
            // }

            // if($moveupCourseId != null){

            //     $newly_created_tertiary_program = $this->con-> prepare("SELECT 
            //             course_id, course_level, program_id, program_section
                        
            //             FROM course
            //             WHERE course_id=:course_id
            //             LIMIT 1
            //         ");

            //     $newly_created_tertiary_program->bindValue(":course_id", $moveupCourseId);
            //     $newly_created_tertiary_program->execute();

            //     if($newly_created_tertiary_program->rowCount() > 0){

            //         $newly_shs_section_row = $newly_created_tertiary_program->fetch(PDO::FETCH_ASSOC);

            //         $newly_created_shs_program_id = $newly_shs_section_row['program_id'];
            //         $newly_created_shs_course_level = $newly_shs_section_row['course_level'];
            //         $newly_created_shs_program_section = $newly_shs_section_row['program_section'];

            //         $get_subject_program = $this->con->prepare("SELECT * FROM subject_program
            //             WHERE program_id=:program_id
            //             AND course_level=:course_level
            //             ");

            //         $get_subject_program->bindValue(":program_id", $newly_created_shs_program_id);
            //         $get_subject_program->bindValue(":course_level", $newly_created_shs_course_level);
            //         $get_subject_program->execute();

            //         if($get_subject_program->rowCount() > 0){
                        
            //             $isSubjectCreated = false;

            //             while($row = $get_subject_program->fetch(PDO::FETCH_ASSOC)){

            //                 $program_program_id = $row['subject_program_id'];
            //                 $program_course_level = $row['course_level'];
            //                 $program_semester = $row['semester'];
            //                 $program_subject_type = $row['subject_type'];
            //                 $program_subject_title = $row['subject_title'];
            //                 $program_subject_description = $row['description'];
            //                 $program_subject_unit = $row['unit'];

            //                 // $program_section = "";
            //                 // $course_tertiary_id = 0;

            //                 $program_subject_code = $row['subject_code'] . "-" . $newly_created_shs_program_section; 
            //                 // $program_subject_code = $row['subject_code'];

            //                 $insert_section_subject->bindValue(":subject_title", $program_subject_title);
            //                 $insert_section_subject->bindValue(":description", $program_subject_description);
            //                 $insert_section_subject->bindValue(":subject_program_id", $program_program_id);
            //                 $insert_section_subject->bindValue(":unit", $program_subject_unit);
            //                 $insert_section_subject->bindValue(":semester", $program_semester);
            //                 $insert_section_subject->bindValue(":program_id", $newly_created_shs_program_id);
            //                 $insert_section_subject->bindValue(":course_level", $program_course_level);
            //                 $insert_section_subject->bindValue(":course_id", $moveupCourseId);
            //                 $insert_section_subject->bindValue(":subject_type", $program_subject_type);
            //                 $insert_section_subject->bindValue(":subject_code", $program_subject_code);

            //                 // $insert_section_subject->execute();
            //                 if($insert_section_subject->execute()){
            //                     $isSubjectCreated = true;
            //                     $isFinished = true;
            //                 }
            //             }
            //         }
            //     }
            // }

    }

?>
    <div class='col-md-10 row offset-md-1'>

        <div class="card">
            <div class="card-header">
                <h4 class='text-center mb-3'>Add Section for (S.Y <?php echo $current_school_year_term; ?> <?php echo $current_school_year_period; ?> Semester)</h4>
            </div>

            <div class="card-body">
                <form method='POST'>

                    <?php echo $trackDropdown;?>
                    <div class='form-group mb-2'>
                        <label class='mb-2'>Section</label>

                        <input class='form-control' type='text' placeholder='e.g: STEM11-A, ABM11-A' name='program_section'>
                    </div>

                    <div class='form-group mb-2'>
                        <label class='mb-2'>Level</label>

                        <select class="form-select" name="course_level" id="course_level">
                            <option value="" selected>Auto Populate</option>
                        </select>
                    </div>

                    <!-- <div class='form-group mb-2'>
                        <select class='form-control' name='course_level'>
                            <option value='11'>Grade 11</option>
                            <option value='12'>Grade 12</option>
                            <option value='1'>1st Year</option>
                            <option value='2'>2nd Year</option>
                            <option value='3'>3rd Year</option>
                            <option value='4'>4th Year</option>
                        </select>
                    </div> -->

                    <div class='form-group mb-2'>
                        <label class='mb-2'>Capacity</label>
                        <input class='form-control' value="30" type='number' placeholder='Room Capacity' name='capacity'>
                    </div>

                    <!-- <div class='form-group mb-2'>
                        <input class='form-control' type='text' placeholder='Adviser Name' name='adviser_teacher_id'>
                    </div> -->

                    <div class='form-group mb-2'>
                        <label class='mb-2'>Instructor</label>

                        <select class="form-control" name="adviser_teacher_id" id="adviser_teacher_id">
                            <?php
                                $query = $con->prepare("SELECT * FROM teacher");
                                $query->execute();
                                
                                echo "<option value='' disabled selected>Choose Teacher</option>";

                                if ($query->rowCount() > 0) {
                                    while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
                                        $selected = "";  

                                        // Add condition to check if the option should be selected
                                        if ($row['teacher_id'] == $selectedTeacherId) {
                                            $selected = "selected";
                                        }
                                        echo "<option value='" . $row['teacher_id'] . "' $selected>" . $row['firstname'] . " " . $row['lastname'] . "</option>";
                                    }
                                }
                            ?>
                        </select>
                    </div>

                    <div class='form-group mb-2'>
                        <label class='mb-2'>Room</label>
                        <input class='form-control' type='number' placeholder='Room' name='room'>
                    </div>

                    <div class="modal-footer">
                        <button type='submit' class='btn btn-primary' name='create_section_btn'>Save</button>
                    </div>


                </form>
            </div>
        </div>
    </div>


    <script>
        $('#program_id').on('change', function() {

            var program_id = parseInt($(this).val());
            console.log(program_id)

            $.ajax({
                url: '../ajax/section/get_level_from_program.php',
                type: 'POST',
                data: {
                    program_id},
                dataType: 'json',

                success: function(response) {


                    var options = '<option value="">Choose Level</option>';

                    $.each(response, function(index, value) {

                        if(value.level > 5){
                            options += '<option value="' + value.level + '">Grade ' + value.level +'</option>';

                        }
                        else if(value.level <= 4){
                            var yearLabel;
                            switch (value.level) {
                                case "1":
                                    yearLabel = "1st year";
                                    break;
                                case "2":
                                    yearLabel = "2nd year";
                                    break;
                                case "3":
                                    yearLabel = "3rd year";
                                    break;
                                case "4":
                                    yearLabel = "4th year";
                                    break;
                                default:
                                    yearLabel = value.level + "th year";
                            }
                            options += '<option value="' + value.level + '">' + yearLabel + '</option>';
                        }
                    });

                    $('#course_level').html(options);
                }
            });
        });
    </script>