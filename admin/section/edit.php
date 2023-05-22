  
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


    if(isset($_GET['section_id'])){

        $course_id = $_GET['section_id'];
        
        if(isset($_POST['edit_section_btn']) &&
            isset($_POST['program_section']) && 
            isset($_POST['program_id']) &&
            isset($_POST['capacity']) &&
            isset($_POST['adviser_teacher_id']) &&
            isset($_POST['room']) &&
            isset($_POST['course_level'])
            
            ){

                $program_section = $_POST['program_section'];
                $program_id = $_POST['program_id'];
                $capacity = $_POST['capacity'];
                $adviser_teacher_id = $_POST['adviser_teacher_id'];
                $room = $_POST['room'];
                $course_level = (int) $_POST['course_level'];

                $is_active = "yes";
                $not_full = "no";

                // Active and full is auto update.

                $update = $con->prepare("UPDATE course 
                    SET program_section = :program_section,
                    program_id = :program_id,
                    capacity = :capacity,
                    adviser_teacher_id = :adviser_teacher_id,
                    room = :room 

                    WHERE course_id = :course_id");
                // Bind the parameters
                $update->bindParam(':program_section', $program_section);
                $update->bindParam(':program_id', $program_id);
                $update->bindParam(':capacity', $capacity);
                $update->bindParam(':adviser_teacher_id', $adviser_teacher_id);
                $update->bindParam(':room', $room);
                $update->bindParam(':course_id', $course_id, PDO::PARAM_INT);
                if($update->execute()){
                    echo "edit successfully";
                    
                }
                


        }

        $get_section = $con->prepare("SELECT * FROM course

            LEFT JOIN program ON program.program_id = course.program_id
            WHERE course_id=:course_id
            LIMIT 1");
        
        $get_section->bindValue(":course_id", $course_id);
        $get_section->execute();

        if($get_section->rowCount() > 0){

            $row = $get_section->fetch(PDO::FETCH_ASSOC);
            $program_id = $row['program_id'];
            $editProgramSelection = $section->editProgramSelection($program_id);

        ?>
            <div class='col-md-10 row offset-md-1'>
                <h3 class='text-center mb-3'>Edit Section in (SY:<?php echo $current_school_year_term; ?>-<?php echo $current_school_year_period; ?> Semester)</h4>
                <form method='POST'>

                    <label class="mb-2" for="">Strand Name</label>
                    <?php echo $editProgramSelection;?>
                    <!-- <?php echo $row['program_name'];?> -->

                    <div class='form-group mb-2'>
                        <label class="mb-2" for="">Section Name</label>
                        <input class='form-control' type='text'
                        placeholder='e.g: STEM11-A, ABM11-A'
                        name='program_section'
                        value="<?php echo $row['program_section']?>"
                        
                        >
                    </div>

                    <div class='form-group mb-2'>
                        <label class="mb-2" for="">Grade Level</label>
                        <select class='form-control' name='course_level'>
                            <?php
                                $get_course = $con->prepare("SELECT course_level, course_id FROM course
                                    WHERE course_id = :course_id
                                    LIMIT 1");

                                $get_course->bindValue(":course_id", $course_id);
                                $get_course->execute();

                                $get_course_level_row = $get_course->fetch(PDO::FETCH_ASSOC);
                            ?>
                            <option value='11' <?php if ($get_course_level_row['course_level'] == 11) echo "selected"; ?>>Grade 11</option>
                            <option value='12' <?php if ($get_course_level_row['course_level'] == 12) echo "selected"; ?>>Grade 12</option>
                        </select>
                    </div>

                    <div class='form-group mb-2'>
                        <label class="mb-2" for="">Room Capacity</label>

                        <input class='form-control' type='text'
                        placeholder='Room Capacity' value="<?php echo $row['capacity']?>" name='capacity'>
                    </div>

                    <div class='form-group mb-2'>
                        <label class="mb-2" for="">Advisery Name</label>

                        <!-- <input class='form-control' type='text'
                            value="<?php echo $row['adviser_teacher_id']?>"
                            placeholder='Adviser Name' name='adviser_teacher_id'> -->

                        <select class="form-control" name="adviser_teacher_id" id="adviser_teacher_id">

                            <?php
                                $sql = $con->prepare("SELECT 
                                    teacher_id, firstname, lastname 
                                    
                                    FROM teacher");
                                $sql->execute();

                                $teacher_id = $row['adviser_teacher_id'];
                                if($sql->rowCount() > 0){

                                    echo "<option value='' selected>Choose Adviser</option>";

                                    while($teacher = $sql->fetch(PDO::FETCH_ASSOC)){
                                        if ($teacher['teacher_id'] == $teacher_id) {
                                            echo "<option value='" . $teacher['teacher_id'] . "' selected>" . $teacher['firstname'] . " " . $teacher['lastname'] . "</option>";
                                        } else {
                                            echo "<option value='" . $teacher['teacher_id'] . "'>" . $teacher['firstname'] . " " . $teacher['lastname'] . "</option>";
                                        }
                                    }
                                } 
                            ?>
                        </select>
                    </div>

                    <div class='form-group mb-2'>
                        <label class="mb-2" for="">Room</label>

                        <input class='form-control' type='text'
                            value="<?php echo $row['room']?>"
                        
                        placeholder='Room' name='room'>
                    </div>

                    <!-- <div class='form-group mb-2'>
                        <select class='form-control' name='course_level'>
                            <option value='11'>Grade 11</option>
                            <option value='12'>Grade 12</option>
                        </select>
                    </div> -->
                

                    <button type='submit' class='btn btn-primary' name='edit_section_btn'>Save</button>
                </form>
            </div>
        <?php
        }


    }


