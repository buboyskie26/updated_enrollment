  
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

            $program_section = $_POST['program_section'];
            $program_id = $_POST['program_id'];
            $capacity = $_POST['capacity'];
            $adviser_teacher_id = $_POST['adviser_teacher_id'];
            $room = $_POST['room'];
            $course_level = (int) $_POST['course_level'];

            $is_active = "yes";
            $not_full = "no";

            // echo $room;
            // echo $adviser_teacher_id;
            // echo $capacity;

            $insert = $con->prepare("INSERT INTO course
                (program_section, program_id, capacity, adviser_teacher_id, room, school_year_term, active, is_full, course_level)
                VALUES(:program_section, :program_id, :capacity, :adviser_teacher_id, :room, :school_year_term, :active, :is_full, :course_level)");
            
            $insert->bindValue(":program_section", $program_section);
            $insert->bindValue(":program_id", $program_id);
            $insert->bindValue(":capacity", $capacity);
            $insert->bindValue(":adviser_teacher_id", $adviser_teacher_id);
            $insert->bindValue(":room", $room);
            $insert->bindValue(":school_year_term", $current_school_year_term);
            $insert->bindValue(":active", $is_active);
            $insert->bindValue(":is_full", $not_full);
            $insert->bindValue(":course_level", $course_level, PDO::PARAM_INT);


            if($insert->execute()){

                if(isset($_SESSION['process_enrollment'])
                    && $_SESSION['process_enrollment'] == 'transferee'){

                        AdminUser::success("New section has been created.", "../admission/transferee_process_enrollment.php?step2=true&id=$pending_enrollees_id");
                        exit();

                }               
                if(isset($_SESSION['process_enrollment'])
                    && $_SESSION['process_enrollment'] == 'non_transferee'){

                        AdminUser::success("New section has been created.", "../admission/process_enrollment.php?step2=true&id=$pending_enrollees_id");
                        exit();

                }

              
            }else{
                AdminUser::error("Something went wrong", "create.php");
                exit();
            }
    }

?>
    <div class='col-md-10 row offset-md-1'>
       <h4 class='text-center mb-3'>Add Section for (S.Y <?php echo $current_school_year_term; ?> <?php echo $current_school_year_period; ?> Semester)</h4>
        <form method='POST'>
            <?php echo $trackDropdown;?>
            <div class='form-group mb-2'>
                <input class='form-control' type='text' placeholder='e.g: STEM11-A, ABM11-A' name='program_section'>
            </div>

            <div class='form-group mb-2'>
                <select class='form-control' name='course_level'>
                    <option value='11'>Grade 11</option>
                    <option value='12'>Grade 12</option>
                </select>
            </div>
            <div class='form-group mb-2'>
                <input class='form-control' type='text' placeholder='Room Capacity' name='capacity'>
            </div>

            <div class='form-group mb-2'>
                <input class='form-control' type='text' placeholder='Adviser Name' name='adviser_teacher_id'>
            </div>

              <div class='form-group mb-2'>
                <input class='form-control' type='text' placeholder='Room' name='room'>
            </div>

            <!-- <div class='form-group mb-2'>
                <select class='form-control' name='course_level'>
                    <option value='11'>Grade 11</option>
                    <option value='12'>Grade 12</option>
                </select>
            </div> -->
           

            <button type='submit' class='btn btn-primary' name='create_section_btn'>Save</button>
        </form>
    </div>