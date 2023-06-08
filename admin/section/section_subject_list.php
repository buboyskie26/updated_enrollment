<?php 
    include('../registrar_enrollment_header.php');
    include('../../enrollment/classes/StudentEnroll.php');
    include('../../enrollment/classes/Section.php');
    include('../../enrollment/classes/Schedule.php');
    include('../classes/Course.php');

    $studentEnroll = new StudentEnroll($con);
    $schedule = new Schedule($con, $studentEnroll);
  
    $course = new Course($con, $studentEnroll);


    if(isset($_GET['id'])){

        $course_id = $_GET['id'];
        $section = new Section($con, $course_id);

        $section_name = $section->GetSectionName();

        $section_program_id = $section->GetProgramIdBySectionId($course_id);
        $course_level = $section->GetSectionGradeLevel();


        $school_year_obj = $studentEnroll->GetActiveSchoolYearAndSemester();

        $current_school_year_id = $school_year_obj['school_year_id'];
        $current_school_year_term = $school_year_obj['term'];
        $current_school_year_period = $school_year_obj['period'];
        ?>
            <div class="row col-md-12">

                <div class="card">
                    <div class="card-header">
                        <h4 class="text-muted text-center"><?php echo $section_name;?> Subjects</h4>
                        <span class="text-primary text-center">S.Y <?php echo $current_school_year_term?> <?php echo $current_school_year_period?> Semester</span>
                    </div>

                    <a href=''>
                        <button class="btn btn-sm btn-outline-primary">
                            Attach
                        </button>
                    </a>

                    <div class="card-body">
                        <table class="table table-hover table-responsive">
                            <thead>
                                <tr class="text-center">
                                    <th>Code</th>
                                    <th>Subject</th>
                                    <th>Grade Level</th>
                                    <th>Semester</th>
                                    <th>Pre-Requisite</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 

                                    $sql = $con->prepare("SELECT 
                                    
                                        t1.*

                                        -- t2.subject_program_id as subject_subject_program_id,
                                        -- t2.subject_title as subject_subject_title
                                    
                                        FROM subject_program as t1

                                        -- LEFT JOIN subject as t2 ON t2.subject_program_id = t1.subject_program_id
                                        -- AND t2.program_id=t1.program_id
                                        -- AND t2.course_level=t1.course_level
                                        -- AND t2.semester=t1.semester

                                        -- INNER JOIN course as t3 ON t3.course_id = t2.course_id

                                        -- WHERE t3.course_id=:course_id
                                        WHERE t1.program_id=:program_id

                                        AND t1.course_level=:course_level
                                        AND (t1.semester='First'
                                            OR
                                            t1.semester='Second'
                                        )

                                        ORDER BY t1.course_level DESC,
                                        t1.semester
                                        ");
                                    
                                    // $sql->bindValue(":course_id", $course_id);
                                    $sql->bindValue(":program_id", $section_program_id);
                                    $sql->bindValue(":course_level", $course_level);
                                    // $sql->bindValue(":semester", $current_school_year_period);
                                    
                                    $sql->execute();

                                    if($sql->rowCount() > 0){

                                        while($row = $sql->fetch(PDO::FETCH_ASSOC)){

                                            $subject_program_id = $row['subject_program_id'];
                                            $subject_code = $row['subject_code'];
                                            $subject_title = $row['subject_title'];
                                            $course_level = $row['course_level'];
                                            $semester = $row['semester'];
                                            // $pre_requisite = $row['pre_requisite'];
                                            $pre_requisite = $row['pre_req_subject_title'];
                                            $subject_type = $row['subject_type'];
                                            // $subject_subject_program_id = $row['subject_subject_program_id'];
                                            // $subject_subject_title = $row['subject_subject_title'];

                                            # Find missing subjects based on subject_program

                                            // echo $subject_subject_title;
                                            // echo "<br>";
                                            // echo $subject_subject_program_id;
                                            // echo "<br>";

                                            $statuss = "N/A";

                                            // echo $subject_program_id;


                                            // $subject_real = $con->prepare("SELECT 
                                                    
                                            //     t1.subject_program_id as t1_subject_program_id,

                                            //     t2.subject_program_id as t2_subject_program_id,
                                            //     t2.subject_title as t2_subject_title

                                            //     FROM subject_program as t1

                                            //     LEFT JOIN subject as t2 ON t2.subject_program_id = t1.subject_program_id

                                            //     WHERE t1.subject_program_id=:subject_program_id
                                            //     LIMIT 1");

                                                // echo $subject_title;

                                            $subject_real = $con->prepare("SELECT 
                                                    
                                                t1.subject_title as t1_subject_title,
                                                t1.subject_code as t1_subject_code,
                                                t1.subject_program_id as t1_subject_program_id

                                                FROM subject as t1 

                                                WHERE t1.subject_program_id=:subject_program_id
                                                AND t1.course_id=:course_id
                                                LIMIT 1");
                                                            
                                            $subject_real->bindValue(":subject_program_id", $subject_program_id);
                                            $subject_real->bindValue(":course_id", $course_id);
                                            $subject_real->execute();

                                            $t1_subject_program_id = null;
                                            $t1_subject_code = null;

                                            if($subject_real->rowCount() > 0){

                                                // $asd = $subject_real->fetchAll(PDO::FETCH_ASSOC);

                                                // print_r($asd);

                                                $row = $subject_real->fetch(PDO::FETCH_ASSOC);

                                                $t1_subject_title = $row['t1_subject_title'];
                                                $t1_subject_code = $row['t1_subject_code'];
                                                
                                                $t1_subject_program_id = $row['t1_subject_program_id'];

                                            }

                                                if($t1_subject_program_id != null 
                                                    && $t1_subject_program_id == $subject_program_id){
                                                        
                                                    $statuss = "
                                                        <i class='fas fa-check'></i>
                                                    ";
                                                }

                                                else{
                                                    $statuss = "
                                                        <button class='btn btn-primary'>Populate</button>
                                                    ";
                                                }

                                                echo $t1_subject_code;

                                                // if($t1_subject_code != null 
                                                //     && $t1_subject_code == $subject_code){

                                                //     $subject_code = $t1_subject_code;

                                                // }

                                                // echo $subject_code;

                                            
                                            echo "
                                                <tr class='text-center'>
                                                    <td>$t1_subject_code</td>
                                                    <td>$subject_title</td>
                                                    <td>Grade $course_level</td>
                                                    <td>$semester</td>
                                                    <td>$pre_requisite</td>
                                                    <td>$subject_type</td>
                                                    <td>$statuss</td>
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
?>