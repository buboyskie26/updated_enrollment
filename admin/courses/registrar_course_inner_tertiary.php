<?php 
    include('../registrar_enrollment_header.php');
    include('../../enrollment/classes/StudentEnroll.php');
    include('../../enrollment/classes/Section.php');
    include('../classes/Course.php');

    
    if(!AdminUser::IsRegistrarAuthenticated()){
        header("location: /dcbt/registrarogin.php");
        exit();
    }

    $studentEnroll = new StudentEnroll($con);


    if(isset($_GET['id'])){
        $program_id = $_GET['id'];

        // echo "qwe";


        if(isset($_POST['populate_tertiary_sectiob_btn'])){

            $course_level = $_POST['course_level'];
            $program_section = $_POST['program_section'];
            $course_tertiary_id = $_POST['course_tertiary_id'];

            $get_subject_program = $con->prepare("SELECT * FROM subject_program
                WHERE program_id=:program_id
                AND course_level=:course_level
                ");

            $get_subject_program->bindValue(":program_id", $program_id);
            $get_subject_program->bindValue(":course_level", $course_level);
            $get_subject_program->execute();

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

                    $program_subject_code = $row['subject_code']  . $program_section;

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
                    
                    if($insert_section_subject->execute()){
                        $isSubjectCreated = true;
                    }

                }

                if($isSubjectCreated == true){
                    echo "Successfully populated subject_tertiary in course_id $course_tertiary_id";
                }

            }


        }

        ?>

        <div class="row col-md-12">
                    <h2 class="text-center page-header">Tertiary Inner</h2>
                    <!-- <a href="<?php echo $createUrl?>">
                        <button class="btn btn-sm btn-success">Add Schedule</button>
                    </a>     -->

                <div class="col-md-10 offset-md-1">
                    <div class="table-responsive" style="margin-top:2%;"> 
                        <table  class="table table-bordered table-hover "  style="font-size:14px" cellspacing="0"  > 
                            <thead>
                                <tr class="text-center"> 
                                    <th rowspan="2">Section Id</th>  
                                    <th rowspan="2">Section Name</th>  
                                    <th rowspan="2">Student</th>  
                                    <th rowspan="2">Schedule Subject</th>  
                                    <th rowspan="2">School Year</th>  
                                    <th rowspan="2">Action</th>  
                                </tr>	
                            </thead> 	
                            <tbody>

                                <?php 
                                    // $sectionScheduleGradeElevenFirst = $schedule->GetSectionScheduleGradeElevenFirst($username, $student_id, 11, "First");
                                    $query = $con->prepare("SELECT * FROM course_tertiary as t1


                                        LEFT JOIN school_year as t2 ON t2.school_year_id = t1.school_year_id
                                        WHERE t1.program_id=:program_id

                                        ORDER BY t2.term ASC,
                                        t1.program_section
                                        ");
        
                                    $query->bindValue(":program_id", $program_id);
                                    $query->execute();

                                    if($query->rowCount() > 0){
                                        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {


                                            $course_tertiary_id = $row['course_tertiary_id'];
                                            $program_section = $row['program_section'];
                                            $capacity = $row['capacity'];
                                            $course_level = $row['course_level'];
                                            $school_year_term = $row['term'];

                                            
                                            $section = new Section($con, $course_tertiary_id);
                                            // $totalStudent = $section->GetTotalNumberOfStudentInSection($course_id);
                                            $totalStudent = 0;
                                            // $scheduleSubject = $section->GetSectionTotalScheduleSubjects($course_id);
                                            $scheduleSubject =0;
                                            // $totalSectionSubject = $section->GetSectionTotalSubjects($course_id);
                                            $totalSectionSubject = 0;


                                            $grade12Sections = 3;
                                            $grade12Student = 7;

                                            // $relativePath = '../section/strand_show.php?id=' . $course_id;
                                            $relativePath = '';
                                            $url = directoryPath . $relativePath;

                                            // $url = directoryPath . "course_show.php?id=$course_id";

                                            echo "<tr class='text-center'>";
                                                echo "<td>
                                                    <a style='color:whitesmoke;' href='strand_show.php?id=$program_id'>
                                                        $course_tertiary_id
                                                    </a>
                                                </td>";
                                                echo "<td>
                                                    <a style='color:whitesmoke;' href='$url'>
                                                        $program_section
                                                    </a>
                                                </td>";
                                                echo "<td>$totalStudent/$capacity</td>";
                                                echo "<td>$scheduleSubject/$totalSectionSubject</td>";
                                                echo "<td>$school_year_term</td>";
                                                echo "<td>
                                                    <a href='course_view_subject.php?id=$course_tertiary_id'>
                                                        <button class='btn btn-sm btn-primary'>View Subjects</button>
                                                    </a>

                                                    <form method='POST'>
                                                        <input name='course_tertiary_id' type='hidden' value='$course_tertiary_id'>
                                                        <input name='course_level' type='hidden' value='$course_level'>
                                                        <input name='program_section' type='hidden' value='$program_section'>
                                                        <button name='populate_tertiary_sectiob_btn' type='submit' class='btn btn-success btn-sm'>Populate</button>
                                                    </form>
                                                </td>";
                                            echo "</tr>";
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
?>



