<?php 

    include('../registrar_enrollment_header.php');
    include('../../enrollment/classes/StudentEnroll.php');
    include('../../enrollment/classes/OldEnrollees.php');
    include('../classes/Course.php');


    $enrol = new StudentEnroll($con);
    $old_enroll = new OldEnrollees($con, $enrol);

    if(isset($_GET['id'])){

        $student_id = $_GET['id'];

        $editUrl = directoryPath . "edit.php?id=$student_id";

        $username = $enrol->GetStudentUsername($student_id);

        $studentObj = $enrol->GetStudentCourseLevelYearIdCourseId($username);

        $student_course_id = $studentObj['course_id'];
        $student_course_level =  $studentObj['course_level'];
        

        // echo $student_course_level . "qwe";
        $student_program_id = $enrol->GetStudentProgramId($student_course_id);

        
        $school_year_obj = $enrol->GetActiveSchoolYearAndSemester();

        $current_semester = $school_year_obj['period'];
        $current_term = $school_year_obj['term'];
        $current_school_year_id = $school_year_obj['school_year_id'];

        $student_fullname = $enrol->GetStudentFullName($student_id);
        ?>
            <div class="row">
                <div class="col-md-12">
                    <div class="table-responsive">		
                        <h5 class="text-center">

                            <!-- <?php echo "<a href='$editUrl'>$username</a>";     ?> -->
                            <?php echo $student_fullname;?>
                            List of Subject Load</h5>	

                        <?php 

                            if($old_enroll->CheckIfDroppedStudentEnrolledInCurrentSY($student_id, $current_school_year_id) > 0){
                                echo "
                                    <button class='btn btn-primary btn-sm'>
                                        <a style='color: white;' href='$editUrl'>Re-activate</a>
                                    </button>
                                ";
                            }
                        ?>
                        
                        <table id="dash-table" class="table table-sm table-striped table-bordered table-hover table-responsive" style="font-size:13px" cellspacing="0">
                            <thead>
                                <tr>
                                    <th rowspan="2">Title</th>
                                    <th rowspan="2">Description</th>  
                                    <th rowspan="2">Unit</th>
                                    <th>Semester</th>  
                                    <th>Course Level</th>  
                                    <th>Remarks</th>  
                                </tr>	
                            </thead> 
                            <tbody>
                                <?php

                                    $student_status = "Drop";
                                    $arr = [];
                                    $samp = $con->prepare("SELECT t1.subject_title FROM subject as t1
                                        INNER JOIN student_subject_grade as t2 ON t1.subject_id=t2.subject_id
                                        WHERE t2.student_id=:student_id
                                        ");   

                                    $samp->bindValue(":student_id", $student_id);
                                    $samp->execute(); 

                                    if($samp->rowCount() > 0){

                                        // $as = $samp->fetchAll(PDO::FETCH_ASSOC);
                                        while($row = $samp->fetch(PDO::FETCH_COLUMN)){
                                            array_push($arr, $row);

                                        }
                                        // print_r($arr);
                                    }

                                    $sql = $con->prepare("SELECT t1.*
                                    --  t2.subject_title, t2.subject_id 
                                    
                                        FROM subject_program as t1
                                        WHERE t1.program_id=:program_id
                                    
                                        -- LEFT JOIN subject as t2 ON t2.subject_program_id = t1.subject_program_id
                                        -- LEFT JOIN student_subject as t3 ON t2.subject_id = t3.subject_id

                                        -- WHERE t2.course_id=:course_id
                                        ORDER BY t1.course_level, t1.semester
                                        ");   

                                    // $sql->bindValue(":student_status", $student_status);
                                    // $sql->bindValue(":course_id", $student_course_id);
                                    $sql->bindValue(":program_id", $student_program_id);
                                    $sql->execute(); 

                                    if($sql->rowCount() > 0){

                                        // $all = $sql->fetchAll(PDO::FETCH_ASSOC);

                                        // echo sizeof($all);
                                        // print_r($all);
                                        $name = "";

                                        while($row = $sql->fetch(PDO::FETCH_ASSOC)){

                                            // $student_id = "";
                                            $subject_program_id = $row['subject_program_id'];
                                            $subject_title = $row['subject_title'];
                                            $description = $row['description'];
                                            $unit = $row['unit'];
                                            $course_level = $row['course_level'];
                                            $semester = $row['semester'];


                                            // foreach ($arr as $key => $value) {
                                            //     # code...
                                            //     if($value == $subject_title){
                                            //         $name = $subject_program_id;
                                            //     }
                                            // }
                                            // $key = array_search($subject_title, $arr);
                                            // if ($key !== false) {
                                            //     $name = $subject_program_id;
                                            // }

                                            if (in_array($subject_title, $arr)) {
                                                $name = $subject_program_id;
                                                $name = "
                                                    <i style='color: green;' class='fas fa-check'></i>
                                                ";

                                            }else{
                                                $name = "
                                                    <i style='color: orange;' class='fas fa-times'></i>
                                                    ";
                                            }
                                           


                                            // echo $name;


                                            // echo $subject_id;
                                            // echo "<br>";

                                            // $get_my_subject = $con->prepare("SELECT *
                                            //     FROM student_subject

                                            //     WHERE subject_id=:subject_id
                                            //     AND student_id=:student_id
                                            //     LIMIT 1");
                                            
                                            // $get_my_subject->bindValue(":subject_id", $subject_id);
                                            // $get_my_subject->bindValue(":student_id", $student_id);
                                            // $get_my_subject->execute();

                                            // $my_sub = "";
                                            // if($get_my_subject->rowCount() > 0){
                                            //     // echo "has one";
                                            //     $ro = $get_my_subject->fetch(PDO::FETCH_ASSOC);
                                            //     $my_sub = $ro['subject_id'];

                                            //     // echo $my_sub;
                                            // }

                                            
                                            echo "
                                                <tr>
                                                    <td>$subject_title</td>
                                                    <td>$description</td>
                                                    <td>$unit</td>
                                                    <td>$semester</td>
                                                    <td>$course_level</td>
                                                    <td class='text-center'>$name</td>
                                                </tr>
                                            ";
                                        }
                                    }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                
                <div class="col-md-12">
                    <div class="table-responsive">	
                        <?php 
                        
                         
                        ?>	
                        <h5 class="text-center">Grade <?php echo $student_course_level?> <?php echo $current_semester?> Semester (<?php echo $current_term?>) Available Subject Load</h5>	
                        <table id="dash-table" class="table table-sm table-striped table-bordered table-hover table-responsive" style="font-size:14px" cellspacing="0">
                            <thead>
                                <tr>
                                    <th rowspan="2">ID</th>
                                    <th rowspan="2">Title</th>
                                    <th rowspan="2">Description</th>  
                                    <th rowspan="2">Unit</th>
                                    <th>Semester</th>  
                                    <th>Course Level</th>  
                                    <th>Subject Type</th>  
                                    <th></th>  
                                </tr>	
                            </thead> 
                            <tbody>
                                <?php

                                    $student_status = "Drop";

                                    $sql = $con->prepare("SELECT * FROM subject_program
                                        WHERE program_id = :program_id
                                        AND course_level = :course_level
                                        AND semester = :semester
                                        ");   

                                    // echo $student_program_id;
                                    // echo "<br>";

                                    // echo $student_course_level;
                                    // echo "<br>";

                                    // echo $current_semester;
                                    // echo "<br>";

                                    $sql->bindValue(":program_id", $student_program_id);
                                    $sql->bindValue(":course_level", $student_course_level);
                                    $sql->bindValue(":semester", $current_semester);
                                    $sql->execute(); 

                                    if($sql->rowCount() > 0){

                                        while($row = $sql->fetch(PDO::FETCH_ASSOC)){

                                            $subject_program_id = $row['subject_program_id'];
                                            $subject_code = $row['subject_code'];
                                            $subject_title = $row['subject_title'];
                                            $description = $row['description'];
                                            $unit = $row['unit'];
                                            $semester = $row['semester'];
                                            $subject_type = $row['subject_type'];
                                            $course_level = $row['course_level'];
                          
                                            $url = directoryPath . "drop_show.php?id=$subject_program_id";
                                            $button = "
                                                <a href='$url'>
                                                    <button class='btn btn-sm btn-success'>Evalute</button>
                                                </a>
                                            ";
                                            echo "
                                                <tr>
                                                    <td>$subject_program_id</td>
                                                    <td>$subject_title</td>
                                                    <td>$description</td>
                                                    <td>$unit</td>
                                                    <td>$semester</td>
                                                    <td>$course_level</td>
                                                    <td>$subject_type</td>
                                                    <td></td>
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