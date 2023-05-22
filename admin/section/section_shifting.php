  
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
    $current_school_year_id = $school_year_obj['school_year_id'];

    if(isset($_GET['id'])
        && isset($_GET['pid'])
    ){

        $student_id = $_GET['id'];

        $student_username = $enroll->GetStudentUsername($student_id);
        $student_name = $enroll->GetStudentFullName($student_id);
        $student_obj = $enroll->GetStudentCourseLevelYearIdCourseId($student_username);

        $student_course_id = $student_obj['course_id'];
        $student_course_level = $student_obj['course_level'];

        $program_id = $_GET['pid'];

        // if(isset($_POST['shift_student_section_btn']) &&
        //     isset($_POST['program_section']) && 
        //     isset($_POST['program_id']) &&
        //     isset($_POST['capacity']) &&
        //     isset($_POST['adviser_teacher_id']) &&
        //     isset($_POST['room']) &&
        //     isset($_POST['course_level'])
            
        //     ){

        //         $program_section = $_POST['program_section'];
        //         $program_id = $_POST['program_id'];
        //         $capacity = $_POST['capacity'];
        //         $adviser_teacher_id = $_POST['adviser_teacher_id'];
        //         $room = $_POST['room'];
        //         $course_level = (int) $_POST['course_level'];

        //         $is_active = "yes";
        //         $not_full = "no";

        //         // Active and full is auto update.

        //         $update = $con->prepare("UPDATE course 
        //             SET program_section = :program_section,
        //             program_id = :program_id,
        //             capacity = :capacity,
        //             adviser_teacher_id = :adviser_teacher_id,
        //             room = :room 

        //             WHERE course_id = :course_id");
        //         // Bind the parameters
        //         $update->bindParam(':program_section', $program_section);
        //         $update->bindParam(':program_id', $program_id);
        //         $update->bindParam(':capacity', $capacity);
        //         $update->bindParam(':adviser_teacher_id', $adviser_teacher_id);
        //         $update->bindParam(':room', $room);
        //         $update->bindParam(':course_id', $course_id, PDO::PARAM_INT);
        //         if($update->execute()){
        //             echo "edit successfully";
                    
        //         }

        // }
        

        $get_student_course = $con->prepare("SELECT *
            FROM course
            WHERE program_id = :program_id
            AND course_id = :course_id
            LIMIT 1
            ");

        $get_student_course->bindValue(":program_id", $program_id);
        $get_student_course->bindValue(":course_id", $student_course_id);
        $get_student_course->execute();

        if($get_student_course->rowCount() > 0){

            $row = $get_student_course->fetch(PDO::FETCH_ASSOC);


            if(isset($_POST['shift_student_section_btn'])){

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
                }

                if(isset($_POST['course_shift_id'])){
                    // Shifting did not checked if the capacity is full.

                    $course_shift_id = $_POST['course_shift_id'];

                    // Make sure course_shift_id is not the same with sutdent
                    # current course_id.

                    if($student_course_id != $course_shift_id){

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

                                $get_recent_course = $con->prepare("SELECT subject_id FROM subject
                                    WHERE course_id=:course_id
                                    AND semester=:semester");

                                $get_recent_course->bindValue(":course_id", $course_shift_id);
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
                                            $add_student_subject_load->bindValue(":course_id", $course_shift_id);

                                            if($add_student_subject_load->execute()){
                                                echo "successully inserted $subject_id_to_insert subject id";
                                                $successInserted = true;
                                            }
                                        }

                                        if($successInserted == true){

                                            // echo "<script>alert('Student Shift section successfully.');</script>";
                                            // header("Location: section_shifting.php?id=$student_id&pid=$program_id");
                                            // exit();

                                            echo "<script>
                                                    alert('Student has been shifted successfully.');
                                                    setTimeout(function() {
                                                        window.location.href = 'section_shifting.php?id=$student_id&pid=$program_id';
                                                    }, 3000); // delay in milliseconds (3 seconds)
                                                </script>";
                                            exit();
                                        }
                                    }
                                }   
                            }

                        }

                    }



                }
            }

            $createNextLetter = $section->AutoCreateAnotherSection($row['program_section']);
            
            ?>
                <div class='col-md-10 row offset-md-1'>
                    <h3 class='text-center mb-3'>Shifting Student Section</h4>
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
                                        <option value="" selected>Shift Section To:</option>

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

