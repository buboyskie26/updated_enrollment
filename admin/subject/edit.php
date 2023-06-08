<?php

include('../registrar_enrollment_header.php');
include('../../enrollment/classes/StudentEnroll.php');
include('../../enrollment/classes/Section.php');
require_once('../../admin/classes/AdminUser.php');

    if(!AdminUser::IsRegistrarAuthenticated()){
        header("Location: /dcbt/registrar_login.php");
        exit();
    }

    if(isset($_GET['id'])){

        $subject_program_id = $_GET['id'];

        $section = new Section($con, null);

        $enroll = new StudentEnroll($con);

        $school_year_obj = $enroll->GetActiveSchoolYearAndSemester();

        $current_school_year_term = $school_year_obj['term'];
        $current_school_year_period = $school_year_obj['period'];

        $get_subject_program = $con->prepare("SELECT subject_program.*,
        
            program.program_name
            FROM subject_program

            INNER JOIN program on program.program_id = subject_program.program_id
            WHERE subject_program_id=:subject_program_id
            LIMIT 1");
        
        $get_subject_program->bindValue(":subject_program_id", $subject_program_id);
        $get_subject_program->execute();

        if($get_subject_program->rowCount() > 0){

            $row = $get_subject_program->fetch(PDO::FETCH_ASSOC);
            // $program_id = $row['program_id'];
            
            if(isset($_POST['edit_subject_program_btn'])){
                
                $subject_title = $_POST['subject_title'];
                $course_level = $_POST['course_level'];
                $semester = $_POST['semester'];
                $unit = $_POST['unit'];

                // Update the record in the database
                $query = $con->prepare("UPDATE subject_program 

                    SET subject_title = :subject_title,
                    course_level = :course_level,
                    semester = :semester,
                    unit = :unit
                    WHERE subject_program_id = :id");

                $query->bindValue(":subject_title", $subject_title);
                $query->bindValue(":course_level", $course_level);
                $query->bindValue(":semester", $semester);
                $query->bindValue(":unit", $unit);
                $query->bindValue(":id", $subject_program_id);

                $url = directoryPath . "index.php";
                if($query->execute()){
                    header("Location:  $url");
                }
            }
            ?>
                <div class='col-md-10 row offset-md-1'>
                    <h4 class='text-center mb-3'>Under <?php echo $row['program_name']?> Program</h4>
                    <form method='POST'>

                        <div class='form-group mb-2'>
                            <label class="mb-2" for="">Subject Title</label>
                            <input class='form-control' type='text'
                            name='subject_title'
                            value="<?php echo $row['subject_title']?>"
                            >
                        </div>

                        <div class='form-group mb-2'>
                            <label class="mb-2" for="">Grade Level</label>

                            <input class='form-control' type='text'
                            name='course_level'
                            value="<?php echo $row['course_level']?>"
                            >
                        </div>

                        <div class='form-group mb-2'>
                            <label class="mb-2" for="">Semester</label>

                            <input class='form-control' type='text'
                            name='semester'
                            value="<?php echo $row['semester']?>"
                            >
                        </div>

                        <div class='form-group mb-2'>
                            <label class="mb-2" for="">Unit</label>

                            <input class='form-control' type='text'
                            name='unit'
                            value="<?php echo $row['unit']?>"
                            >
                        </div>
    

                        <button type='submit' class='btn btn-primary' name='edit_subject_program_btn'>Save</button>
                    </form>
                </div>
            <?php
        }
    }

?>