  
  <?php

    include('../registrar_enrollment_header.php');
    include('../../enrollment/classes/Section.php');
    include('../../includes/classes/Student.php');
    include('../../enrollment/classes/StudentEnroll.php');
    include('../../enrollment/classes/Enrollment.php');
    include('../classes/Subject.php');

    $createProgramSelection = "2";

    $section = new Section($con, null);

    $enroll = new StudentEnroll($con);

    $enrollment = new Enrollment($con, $enroll);

    $school_year_obj = $enroll->GetActiveSchoolYearAndSemester();

    $current_school_year_term = $school_year_obj['term'];
    $current_school_year_period = $school_year_obj['period'];
    $current_school_year_id = $school_year_obj['school_year_id'];



    if(isset($_GET['id'])){

        $student_id = $_GET['id'];

        $student_username = $enroll->GetStudentUsername($student_id);


        $student = new Student($con, $student_username);

        $student_statusv2 = $student->GetStudentStatusv2();
        $admission_status = $student->GetStudentAdmissionStatus();

        // echo $student_statusv2;

        $student_name = $enroll->GetStudentFullName($student_id);
        $student_obj = $enroll->GetStudentCourseLevelYearIdCourseId($student_username);

        $student_course_id = $student_obj['course_id'];
        $student_course_level = $student_obj['course_level'];

        // $program_id = $_GET['pid'];

        $program_id = $enroll->GetStudentProgramId($student_course_id);


        $student_enrollment_id = $enrollment->GetEnrollmentId($student_id, 
            $student_course_id, $current_school_year_id);
        
        $get_student_course = $con->prepare("SELECT *
            FROM course
            WHERE program_id = :program_id
            AND course_id = :course_id
            LIMIT 1
            ");

        $get_student_course->bindValue(":program_id", $program_id);
        $get_student_course->bindValue(":course_id", $student_course_id);
        $get_student_course->execute();

        $row = null;

        if($get_student_course->rowCount() > 0){

            $row = $get_student_course->fetch(PDO::FETCH_ASSOC);

            if(isset($_POST['shift_student_section_btn'])){

                $update_student = $con->prepare("UPDATE student
                    SET course_id=:course_id
                    WHERE course_id=:prev_course_id
                    AND student_id=:student_id
                    ");

                $update_student_enrollment = $con->prepare("UPDATE enrollment
                        SET course_id=:course_id
                        WHERE course_id=:prev_course_id
                        AND school_year_id=:school_year_id
                        AND student_id=:student_id
                    ");
        
                if(isset($_POST['course_create_shift_id'])){
                    
                    $course_create_shift_id = $_POST['course_create_shift_id'];

                    $program_id = $row['program_id'];
                    $course_level = $row['course_level'];
                    $capacity = $row['capacity'];
                    $school_year_term = $row['school_year_term'];
                    $active = "yes";
                    
                    $sql = $con->prepare("INSERT INTO course
                        (program_section, program_id, course_level, capacity, school_year_term, active)
                        VALUES(:program_section, :program_id, :course_level, :capacity, :school_year_term, :active)");
                    
                    $sql->bindValue(":program_section", $course_create_shift_id);
                    $sql->bindValue(":program_id", $program_id);
                    $sql->bindValue(":course_level", $course_level);
                    $sql->bindValue(":capacity", $capacity);
                    $sql->bindValue(":school_year_term", $current_school_year_term);
                    $sql->bindValue(":active", $active);

                    // if(false){
                    if($sql->execute()){

                        // echo "Insert course success";

                        $last_course_id = $con->lastInsertId();
                        // Create Subjects inside thise section
                        $get_subject = $con->prepare("SELECT * FROM subject_program
                        
                            WHERE program_id=:program_id
                            AND course_level=:course_level
                        ");

                        $get_subject->bindValue(":program_id", $program_id);
                        $get_subject->bindValue(":course_level", $course_level);
                        $get_subject->execute();

                        // Add to the subject.
                        $add_subject = $con->prepare("INSERT INTO subject
                            (subject_code, subject_title, subject_program_id, description,
                                unit, semester, program_id, course_level, subject_type, course_id)
                            VALUES(:subject_code, :subject_title, :subject_program_id, :description,
                                :unit, :semester, :program_id, :course_level, :subject_type, :course_id)");
                        
                        if($get_subject->rowCount() > 0){

                            // echo "got the program id subjects";

                            $last_subject_inserted_id = null;

                            $is_subject_exec = false;
                            while($subject = $get_subject->fetch(PDO::FETCH_ASSOC)){

                                $subject_title = $subject['subject_title'];
                                $description = $subject['description'];

                                $subject_code = $subject['subject_code'];

                                $unit = $subject['unit'];
                                $semester = $subject['semester'];
                                $program_id = $subject['program_id'];
                                $subject_program_id = $subject['subject_program_id'];
                                $subject_course_level = $subject['course_level'];
                                $subject_type = $subject['subject_type'];

                                // $add_subject = $con->prepare("INSERT INTO subject
                                //     (subject_code, subject_title, subject_program_id, description,
                                //         unit, semester, program_id, course_level, subject_type, course_id)
                                //     VALUES(:subject_code, :subject_title, :subject_program_id, :description,
                                //         :unit, :semester, :program_id, :course_level, :subject_type, :course_id)");

                                $subject_code = $subject_code . $course_create_shift_id;
                                $add_subject->bindValue(":subject_code", $subject_code);
                                $add_subject->bindValue(":subject_title", $subject_title);
                                $add_subject->bindValue(":subject_program_id", $subject_program_id);
                                $add_subject->bindValue(":description", $description);
                                $add_subject->bindValue(":unit", $unit);
                                $add_subject->bindValue(":semester", $semester);
                                $add_subject->bindValue(":program_id", $program_id);
                                $add_subject->bindValue(":course_level", $subject_course_level);
                                $add_subject->bindValue(":subject_type", $subject_type);
                                $add_subject->bindValue(":course_id", $last_course_id);

                                if($add_subject->execute()){
                                    $is_subject_exec = true;
                                    // echo "inserted subject in that section";
                                    // echo "<br>";
                                    // $last_subject_inserted_id = $con->lastInsertId();
                                }else{
                                    echo "add_subject did not come up";
                                }
                            }

                            if($is_subject_exec == true){
                                $update_student->bindValue(":course_id", $last_course_id);
                                $update_student->bindValue(":prev_course_id", $student_course_id);
                                $update_student->bindValue(":student_id", $student_id);

                                if($update_student->execute()){

                                    $update_student_enrollment->bindValue(":course_id", $last_course_id);
                                    $update_student_enrollment->bindValue(":prev_course_id", $student_course_id);
                                    $update_student_enrollment->bindValue(":school_year_id", $current_school_year_id);
                                    $update_student_enrollment->bindValue(":student_id", $student_id);

                                    if($update_student_enrollment->execute()){

                                        $get_recent_course = $con->prepare("SELECT subject_id FROM subject
                                            WHERE course_id=:course_id
                                            AND semester=:semester");

                                        $get_recent_course->bindValue(":course_id", $last_course_id);
                                        $get_recent_course->bindValue(":semester", $current_school_year_period);
                                        $get_recent_course->execute();

                                        if($get_recent_course->rowCount() > 0){

                                            // echo "im on delete";
                                            
                                            $delete = $con->prepare("DELETE FROM student_subject
                                                WHERE student_id=:student_id
                                                AND course_id=:course_id");

                                            $delete->bindValue(":student_id", $student_id);    
                                            $delete->bindValue(":course_id", $student_course_id); 
                                               
                                            if($delete->execute()){
                                            $successInserted = false;

                                            $add_student_subject_load = $con->prepare("INSERT INTO student_subject
                                                    (student_id, subject_id, course_level, school_year_id, course_id)
                                                    VALUES (:student_id, :subject_id, :course_level, :school_year_id, :course_id)");


                                                while($row = $get_recent_course->fetch(PDO::FETCH_ASSOC)){

                                                    $subject_id_to_insert = $row['subject_id'];

                                                    $add_student_subject_load->bindValue(":student_id", $student_id);
                                                    $add_student_subject_load->bindValue(":subject_id", $subject_id_to_insert);
                                                    $add_student_subject_load->bindValue(":course_level", $student_course_level);
                                                    $add_student_subject_load->bindValue(":school_year_id", $current_school_year_id);
                                                    $add_student_subject_load->bindValue(":course_id", $last_course_id);

                                                    if($add_student_subject_load->execute()){
                                                        echo "successully inserted $subject_id_to_insert subject id";
                                                        $successInserted = true;
                                                    }
                                                }

                                                if($successInserted == true){
                                                    echo "<script>alert('Student Shift section successfully.');</script>";
                                                    header("Location: section_shifting.php?id=$student_id&pid=$program_id");
                                                    exit();
                                                }
                                            }
                                        }   
                                    }

                                }
                            }

                        }
                    }else{
                        echo "insert course id did not come up";
                    }
                }

                if(isset($_POST['course_shift_id']) && $_POST['course_shift_id'] != 0){

                    $course_shift_id = $_POST['course_shift_id'];



                    $isSuccessfullyShifted = false;

                    $get_student_subject = $con->prepare("SELECT 
                        subject_id

                        FROM student_subject 
                        WHERE enrollment_id=:enrollment_id
                        AND student_id=:student_id");

                    $get_student_subject->bindValue(":enrollment_id", $student_enrollment_id);
                    $get_student_subject->bindValue(":student_id", $student_id);
                    $get_student_subject->execute();


                    // Make sure course_shift_id is not the same with sutdent
                    # current course_id.


                    # TODO. Get the Flow of If STEM11-A is Full
                    # And registrar pull-out student from STEM11-A into STEM11-B
                    # It should be not full state
                    # IF registrar positioned the student, the IsFull should be bypass.
                    
                    $shiftedCourseIsFull = $section->CheckShiftedCourseIsFull($course_shift_id,
                        $current_school_year_id);

                    // if($shiftedCourseIsFull){
                    //     echo "full";
                    // }else{
                    //     echo "not";
                    // }

                    # APPLICABLE ONLY FOR STUDENT WHO HAVE REGULAR SUBJECTS.

                    if(false){
                    // if($student_course_id != $course_shift_id 
                    //     && $shiftedCourseIsFull == false 
                    //     && $student_statusv2 == "Regular"
                    //     && $admission_status != "Transferee"){

                        # Student Course Id
                        # Enrollment Course Id
                        # Student Subject_Ids should matched with the Enrollment Course_Id

                        $update_student->bindValue(":course_id", $course_shift_id);
                        $update_student->bindValue(":prev_course_id", $student_course_id);
                        $update_student->bindValue(":student_id", $student_id);

                        if(false){
                        // if($update_student->execute()){

                            $update_student_enrollment->bindValue(":course_id", $course_shift_id);
                            $update_student_enrollment->bindValue(":prev_course_id", $student_course_id);
                            $update_student_enrollment->bindValue(":school_year_id", $current_school_year_id);
                            $update_student_enrollment->bindValue(":student_id", $student_id);

                            if($update_student_enrollment->execute()){
                            // if(false){

                                # Student Course Id
                                # Enrollment Course Id

                                $recent_student_course_id = $enroll->GetStudentCourseIdById($student_id);
                                $enrollment_student_course_id = $enrollment->GetStudentEnrollmentCourseId(
                                    $student_id, $current_school_year_id);

                                if($recent_student_course_id == $enrollment_student_course_id){

                                    $get_student_subject = $con->prepare("SELECT subject_id 

                                        FROM student_subject
                                        WHERE enrollment_id=:enrollment_id
                                        AND student_id=:student_id");

                                    $get_student_subject->bindValue(":enrollment_id", $student_enrollment_id);
                                    $get_student_subject->bindValue(":student_id", $student_id);
                                    $get_student_subject->execute();


                                    // if(false){
                                    if($get_student_subject->rowCount() > 0){

                                        $get_student_subject_count = $get_student_subject->rowCount();

                                        $get_shift_section = $con->prepare("SELECT subject_id 

                                            FROM subject

                                            WHERE course_id=:course_id
                                            AND semester=:semester");

                                        $get_shift_section->bindValue(":course_id", $course_shift_id);
                                        $get_shift_section->bindValue(":semester", $current_school_year_period);
                                        $get_shift_section->execute();

                                        $get_current_section = $con->prepare("SELECT subject_id 

                                            FROM subject

                                            WHERE course_id=:course_id
                                            AND semester=:semester");

                                        $get_current_section->bindValue(":course_id", $student_course_id);
                                        $get_current_section->bindValue(":semester", $current_school_year_period);
                                        $get_current_section->execute();

                                        $get_current_section_list = $get_current_section->fetchAll(PDO::FETCH_ASSOC);

                                        $get_current_section_count = count($get_current_section_list);

                                        // echo $get_current_section_count;
                                        if($get_shift_section->rowCount() > 0){

                                            $get_shift_course_count = $get_shift_section->rowCount();

                                            if($get_student_subject_count == $get_shift_course_count 
                                                && $get_current_section_count == $get_shift_course_count){

                                                $get_shift_section_list = $get_shift_section->fetchAll(PDO::FETCH_ASSOC);

                                                // print_r($get_current_section_list);
                                                foreach ($get_shift_section_list as $key => $shift_section) {
                                                    # code...

                                                    $shift_course_subject_id =  $shift_section['subject_id'];
                                                    $current_section = $get_current_section_list[$key];

                                                    $prev_subject_id = $current_section['subject_id'];

                                                    $update_student_subject = $con->prepare("UPDATE student_subject

                                                        SET subject_id=:subject_id
                                                        WHERE enrollment_id=:enrollment_id
                                                        AND student_id=:student_id
                                                        AND subject_id=:current_subject_id
                                                    "); 

                                                    $update_student_subject->bindValue(":subject_id", $shift_course_subject_id);
                                                    $update_student_subject->bindValue(":enrollment_id", $student_enrollment_id);
                                                    $update_student_subject->bindValue(":student_id", $student_id);
                                                    $update_student_subject->bindValue(":current_subject_id", $prev_subject_id);

                                                    if($update_student_subject->execute()){
                                                        $isSuccessfullyShifted = true;

                                                    }

                                                }
                                            }else{
                                                echo "not equal";
                                            }


                                        }
                                    }


                                    if($isSuccessfullyShifted == true){

                                        // $student_course_id = $student_obj['course_id'];
                                        $student_course_id = $enroll->GetStudentCourseIdById($student_id);
                                        $newSection = $section->GetSectionNameByCourseId($student_course_id);

                                        // echo "Success Shifted";
                                        AdminUser::success("Successfully Shifted to: $newSection",
                                            "section_shifting.php?id=$student_id&pid=$program_id");
                                        exit();
                                    }      
                                }
 
                            }

                        }

                    }else if($admission_status == "Transferee"){

                        if($course_shift_id == 0 || $course_shift_id == null){
                            AdminUser::error("Please choose valid section", "section_shifting.php?id=$student_id&pid=$program_id");
                            exit();
                        }

                        $update_student->bindValue(":course_id", $course_shift_id);
                        $update_student->bindValue(":prev_course_id", $student_course_id);
                        $update_student->bindValue(":student_id", $student_id);

                        // if(false){
                        if($update_student->execute()){

                            $update_student_enrollment->bindValue(":course_id", $course_shift_id);
                            $update_student_enrollment->bindValue(":prev_course_id", $student_course_id);
                            $update_student_enrollment->bindValue(":school_year_id", $current_school_year_id);
                            $update_student_enrollment->bindValue(":student_id", $student_id);

                            if($update_student_enrollment->execute()){
                                

                                $recent_student_course_id = $enroll->GetStudentCourseIdById($student_id);
                                $enrollment_student_course_id = $enrollment->GetStudentEnrollmentCourseId(
                                    $student_id, $current_school_year_id);

                                if($recent_student_course_id == $enrollment_student_course_id){
                                    // echo "equal";

                                    $shiftSubjectIds = [];
                                    $currentSubjectIds = []; 

                                    $get_inserted_subjects = $con->prepare("SELECT 
                                        t2.subject_id, t2.subject_title

                                        FROM student_subject  as t1

                                        INNER JOIN subject as t2 ON t2.subject_id = t1.subject_id

                                        WHERE t1.enrollment_id=:enrollment_id
                                        AND t1.student_id=:student_id");

                                    $get_inserted_subjects->bindValue(":enrollment_id", $student_enrollment_id);
                                    $get_inserted_subjects->bindValue(":student_id", $student_id);
                                    $get_inserted_subjects->execute();
                                    
                                    // $inserted_subjects = $get_inserted_subjects->fetchAll(PDO::FETCH_ASSOC);

                                    if($get_inserted_subjects->rowCount() > 0){

                                        $isReadyForDeletion = false;


                                        while($row = $get_inserted_subjects->fetch(PDO::FETCH_ASSOC)){

                                            $inserted_subject_title = $row['subject_title'];

                                            array_push($currentSubjectIds, $row);

                                            # Get the subject_title on the selected shift course _id

                                            $get_shifted_course_subject = $con->prepare("SELECT subject_id

                                                FROM subject

                                                WHERE course_id=:course_id
                                                AND subject_title=:subject_title
                                                LIMIT 1");

                                            $get_shifted_course_subject->bindValue(":course_id", $course_shift_id);
                                            $get_shifted_course_subject->bindValue(":subject_title", $inserted_subject_title);
                                            $get_shifted_course_subject->execute();

                                            if($get_shifted_course_subject->rowCount() > 0){

                                                $isReadyForDeletion = true;


                                                $shifted_row = $get_shifted_course_subject->fetch(PDO::FETCH_ASSOC);
                                                $shifted_subject_id = $shifted_row['subject_id'];

                                                // echo $shifted_subject_id;
                                                // echo "<br>";
                                                array_push($shiftSubjectIds, $shifted_subject_id);

                                            }
                                            
                                        }

                                        $shiftedSuccess = false;

                                        if(count($currentSubjectIds) == count($shiftSubjectIds)){
                                            
                                            foreach ($currentSubjectIds as $key => $value) {
                                                # code...
                                                $current_subject_ids = $value['subject_id'];

                                                $eachShiftSubjectIds = $shiftSubjectIds[$key];

                                                // echo $current_subject_ids;
                                                // echo "<br>";

                                                $update_student_subject = $con->prepare("UPDATE student_subject

                                                    SET subject_id=:subject_id
                                                    WHERE enrollment_id=:enrollment_id
                                                    AND student_id=:student_id
                                                    AND subject_id=:current_subject_id
                                                "); 

                                                $update_student_subject->bindValue(":subject_id", $eachShiftSubjectIds);
                                                $update_student_subject->bindValue(":enrollment_id", $student_enrollment_id);
                                                $update_student_subject->bindValue(":student_id", $student_id);
                                                $update_student_subject->bindValue(":current_subject_id", $current_subject_ids);

                                                if($update_student_subject->execute()){
                                                    $shiftedSuccess = true;

                                                }

                                            }
                                            if($shiftedSuccess == true){

                                                $student_course_id = $enroll->GetStudentCourseIdById($student_id);
                                                $newSection = $section->GetSectionNameByCourseId($student_course_id);

                                                // echo "Success Shifted";
                                                AdminUser::success("Transferee Successfully Shifted All Subjects Section to: $newSection",
                                                    "section_shifting.php?id=$student_id&pid=$program_id");
                                                exit();
                                            }
                                        }
                                    }


                                }


                            }

                        }
                    }

                }

            }

            if($row == null){
                AdminUser::error("Something Went Wrong.", "section_shifting.php?id=$student_id&pid=$program_id");
                exit();
            }

            $createNextLetter = $section->AutoCreateAnotherSection($row['program_section']);
            
            ?>
                <div class='col-md-10 row offset-md-1'>
                    <h3 class='text-center mb-3'>Shifting Student Section In <?php echo $current_school_year_period; ?> Semester</h4>
                    <h5 class='text-center mb-3'><?php echo $student_name;?> From <?php echo $row['program_section']?></h5>
                    <form method='POST'>

                        <div class='form-group mb-2'>

                            <?php
                                $other = $con->prepare("SELECT *
                                
                                    FROM course
                                    WHERE program_id = :program_id
                                    AND course_level = :course_level
                                    AND program_section != :program_section
                                    AND school_year_term = :school_year_term
                                    ");

                                $other->bindValue(":program_id", $program_id);
                                $other->bindValue(":course_level", $student_course_level);
                                $other->bindValue(":program_section", $row['program_section']);
                                $other->bindValue(":school_year_term", $row['school_year_term']);
                                
                                $other->execute();
                                if($other->rowCount() > 0){
                                    ?>
                                    <label for="">Other Section:</label>

                                    <select class="form-control" name="course_shift_id" id="course_shift_id">
                                        <option value="" selected>Move All Student subject(s) & Shift Section To:</option>

                                        <?php
                                            while ($other_row = $other->fetch(PDO::FETCH_ASSOC)) {
                                                echo "<option value='{$other_row['course_id']}'>{$other_row['program_section']}</option>";
                                            }
                                        ?>
                                    </select>
                                    <?php
                                }
                                else if($other->rowCount() == 0){
                                    ?>
                                        <label for="">* New & None created section:</label>
                                        <!-- <select class="form-control" name="course_create_shift_id" id="course_create_shift_id">
                                            <?php
                                                $other_row = $other->fetch(PDO::FETCH_ASSOC);
                                                echo "<option value='$createNextLetter'>{$createNextLetter}</option>";
                                            ?>
                                        </select> -->
                                        <input type="text" name="course_create_shift_id"
                                            class="form-control" value="<?php echo $createNextLetter;?>">
                                    <?php
                                }
                            ?>
                        </div>

                        <button type='submit' class='btn btn-primary' name='shift_student_section_btn'>Shift here</button>
                    </form>
                </div>
            <?php
        }
    }

