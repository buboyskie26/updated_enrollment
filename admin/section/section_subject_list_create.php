  <?php

    include('../registrar_enrollment_header.php');
    include('../../enrollment/classes/Section.php');
    include('../../enrollment/classes/StudentEnroll.php');
    include('../classes/Subject.php');


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