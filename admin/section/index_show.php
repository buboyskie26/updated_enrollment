<?php 
    include('../admin_enrollment_header.php');
    include('../../enrollment/classes/StudentEnroll.php');
    include('../../enrollment/classes/Section.php');




    if(isset($_GET['id'])){

        $studentEnroll = new StudentEnroll($con);
        $section = new Section($con, null);

        $school_year_obj = $studentEnroll->GetActiveSchoolYearAndSemester();

        $current_school_year_id = $school_year_obj['school_year_id'];
        $current_school_year_term = $school_year_obj['term'];
        $current_school_year_period = $school_year_obj['period'];


        $program_id = $_GET['id'];

        $get_program = $con->prepare("SELECT * FROM program
            WHERE program_id=:program_id");
        
        $get_program->bindValue(":program_id", $program_id);
        $get_program->execute();

        if($get_program->rowCount() > 0){

            $row = $get_program->fetch(PDO::FETCH_ASSOC);

            ?>
                <div class="col-md-12 row">
                    <div class="card">
                        <div class="card-header">
                            <h4><?php echo $row['acronym']?> Sections</h4>
                        </div>
                    </div>


                    <div class="card">
                        <div class="card-header">
                            <h4>Grade 11</h4>
                        </div>

                        <div class="card-body">
                            <table class="table table-hover table-responsive">
                                <thead>
                                    <tr class="text-center">
                                        <th>Section Id</th>
                                        <th>Section Name</th>
                                        <th>Student</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 

                                        $get_course = $con->prepare("SELECT * FROM course
                                            WHERE program_id=:program_id
                                            AND school_year_term=:school_year_term
                                            AND course_level=:course_level
                                            ");
                                        
                                        $get_course->bindValue(":program_id", $program_id);
                                        $get_course->bindValue(":school_year_term", $current_school_year_term);
                                        $get_course->bindValue(":course_level", 11);
                                        
                                        $get_course->execute();

                                        if($get_course->rowCount() > 0){

                                            while($row_c = $get_course->fetch(PDO::FETCH_ASSOC)){

                                                $program_section = $row_c['program_section'];
                                                $course_id = $row_c['course_id'];

                                                $totalStudent = $section->GetTotalNumberOfStudentInSection($course_id, $current_school_year_id);

                                                $students = 0;
                                                echo "
                                                    <tr class='text-center'>
                                                        <td>$course_id</td>
                                                        <td>$program_section</td>
                                                        <td>$totalStudent</td>
                                                    </tr>
                                                ";

                                            }
                                        }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>



                    <div class="card">
                        <div class="card-header">
                            <h4>Grade 12</h4>
                        </div>

                        <div class="card-body">
                            <table class="table table-hover table-responsive">
                                <thead>
                                    <tr class="text-center">
                                        <th>Section Id</th>
                                        <th>Section Name</th>
                                        <th>Student</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 

                                        $get_course = $con->prepare("SELECT * FROM course
                                            WHERE program_id=:program_id
                                            AND school_year_term=:school_year_term
                                            AND course_level=:course_level
                                            ");
                                        
                                        $get_course->bindValue(":program_id", $program_id);
                                        $get_course->bindValue(":school_year_term", $current_school_year_term);
                                        $get_course->bindValue(":course_level", 12);
                                        
                                        $get_course->execute();

                                        if($get_course->rowCount() > 0){

                                            while($row_c = $get_course->fetch(PDO::FETCH_ASSOC)){

                                                $program_section = $row_c['program_section'];
                                                $course_id = $row_c['course_id'];
                                                $totalStudent = $section->GetTotalNumberOfStudentInSection($course_id, $current_school_year_id);

                                                echo "
                                                    <tr class='text-center'>
                                                        <td>$course_id</td>
                                                        <td>$program_section</td>
                                                        <td>$totalStudent</td>
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

            <?php
        }


    }

?>
