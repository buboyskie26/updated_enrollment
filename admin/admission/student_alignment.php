<?php  
 
    include('../../enrollment/classes/StudentEnroll.php');
    include('../../enrollment/classes/Enrollment.php');
    include('../../includes/classes/Student.php');
 

    include('../registrar_enrollment_header.php');


    if(isset($_GET['id'])){

        $student_id = $_GET['id'];

        $enroll = new StudentEnroll($con);

        $student_username = $enroll->GetStudentUsername($student_id);

        $student = new Student($con, $student_username);

        $school_year_obj = $enroll->GetActiveSchoolYearAndSemester();

        $current_school_year_term = $school_year_obj['term'];
        $current_school_year_period = $school_year_obj['period'];

        $firstname = $student->GetFirstName();
        $lastname = $student->GetLastName();
        $student_unique_id = $student->GetStudentUniqueId();
        $student_course_level = $student->GetStudentCourseLevel();
        $student_course_id = $enroll->GetStudentCourseId($student_username);
        $student_course_name = $enroll->GetStudentCourseName($student_username);
        $student_program_id = $enroll->GetStudentProgramId($student_course_id);



        $fullname = $firstname . " " . $lastname;

        if(isset($_POST['alignment_update_btn'])
            && isset($_POST['course_id'])){

            $selected_course_id = $_POST['course_id'];

            

            $wasSuccess = $student->UpdateStudentSection($student_id, $selected_course_id);

            if($wasSuccess){
                AdminUser::success("Section Update Success", "process_enrollment.php?student_id=$student_id&step2=true&manual=true");
            }
        }

        ?>
            <div class='col-md-10 row offset-md-1'>
                <div class="card">
                    <div class="card-header">
                        <span class='text-center mb-3 text-primary'>Student Section Alignment</span>
                        <h4 class='text-center mb-3'><?php echo $fullname;?></h4>
                    </div>
                    <div class="card-body">
                        <form method='POST'>

                            <div class='form-group mb-2'>
                                <label class='mb-2'>Student Id</label>
                                <input readonly value="<?php echo $student_unique_id;?>" class='form-control' type='text' placeholder='e.g: STEM11-A, ABM11-A' name='program_section'>
                            </div>

                            <div class='form-group mb-2'>
                                <label class='mb-2'>Current Section</label>

                                <!-- <input class='form-control' value="<?php echo $student_course_name;?>" type='text' placeholder='Section' name='course_id'> -->
                                <select class="form-control" name="course_id" id="course_id">
                                    <?php
                                        $query = $con->prepare("SELECT * FROM course
                                            WHERE program_id=:program_id");

                                        $query->bindValue(":program_id", $student_program_id);
                                        $query->execute();
                                        
                                        echo "<option value='' disabled selected>Choose Teacher</option>";

                                        if ($query->rowCount() > 0) {
                                            while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
                                                $selected = "";  

                                                // Add condition to check if the option should be selected
                                                if ($row['course_id'] == $student_course_id) {
                                                    $selected = "selected";
                                                }

                                                echo "<option value='" . $row['course_id'] . "' $selected>" . $row['program_section'] . "</option>";
                                            }
                                        }
                                    ?>
                                </select>
                            </div>

                            <div class='form-group mb-2'>
                                <label class='mb-2'>Current Level</label>
                                <input value="<?php echo $student_course_level;?>" 
                                    class='form-control' type='text' placeholder='e.g: STEM11-A, ABM11-A' name='program_section'>

                                
                            </div>

                            <div class="modal-footer">
                                <button type='submit' class='btn btn-primary' name='alignment_update_btn'>Save</button>
                            </div>


                        </form>
                    </div>
                </div>
            </div>
        <?php
    }
?>