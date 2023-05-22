<script src="../assets/js/common.js"></script>

<?php 

    include('../registrar_enrollment_header.php');
    include('../../enrollment/classes/StudentEnroll.php');
    include('../../enrollment/classes/OldEnrollees.php');

    if(isset($_GET['id'])){

        $student_id = $_GET['id'];
        $enroll = new StudentEnroll($con);
        $old_enroll = new OldEnrollees($con, $enroll);

        $username =  $enroll->GetStudentUsername($student_id);
        // if(isset($_SESSION['username'])){
        //     $username = $_SESSION['username'];
        // }
        $studentEnroll = new StudentEnroll($con);

        $student_fullname = $studentEnroll->GetStudentFullName($student_id);

        $student_obj = $enroll->GetStudentCourseLevelYearIdCourseId($username);

        $student_course_id = $student_obj['course_tertiary_id'];


        $student_id = $student_obj['student_id'];
        $student_course_level = $student_obj['course_level'];

        $student_program_id = $studentEnroll->GetStudentTertiaryProgramId($student_course_id);

        // echo $student_program_id;

        $userLoggedInId = $student_id;
        $course_main_id = $student_course_id;

        $isFinished = $old_enroll->CheckGrade11StudentFinishedSubject($username);

        if($isFinished == true && $student_course_level == 11){
            
            // Update move_up student course level to 12
            $wasMoveUpSuccess = $old_enroll->StudentMoveUpToGrade12($username);
            if($wasMoveUpSuccess){
                echo "Student was move_up to Course_Level 12";
            }
        }

        //  if($isFinished == true){
        //         echo "Student was move_up to Course_Level 12";
        //  }

        ?>
        <div class="row">

            <div class="row col-md-12">


                <div class="table-responsive" style="margin-top:5%;"> 
                    <h4 class='text-center'>Check List Grade Report of <?php echo $student_fullname;?></h4>

                    <a href="add_subject_load.php?id=<?php echo $student_id?>">
                        <button class="btn btn-primary">Add Subject Load</button>
                    </a>

                    <div class="form-group">

                        <label style='font-weight: bold;' class='text-center mb-2'>Current Status</label>
                            <select style="width: 35%;" class="form-control"
                            name="tertiary_student_status" id="tertiary_student_status">
                                <?php
                                    $sql = $con->prepare("SELECT student_status FROM student
                                        WHERE is_tertiary=:is_tertiary
                                        AND student_id=:student_id
                                        LIMIT 1
                                    ");

                                    $sql->bindValue(":is_tertiary", 1);
                                    $sql->bindValue(":student_id", $student_id);
                                    $sql->execute();

                                    if ($sql->rowCount() > 0) {
                                        $student_status = $sql->fetchColumn();

                                        $options = array('Regular', 'Irregular');

                                        foreach ($options as $option) {
                                            $selected = ($option == $student_status) ? 'selected' : '';
                                            echo "<option value='$option' $selected>$option</option>";
                                        }
                                    }
                                    ?>

                                <input type='hidden' id='student_id_dropdown'name='student_id_dropdown' value="<?php echo $student_id; ?>" />

                            </select>
                    </div>

 

                    
                    <form method="POST">  					
                            <table id="tertiary_grade_report_insertion" 
                                class="table table-striped table-bordered table-hover "
                                style="font-size:13.8px" cellspacing="0"> 
                                
                                <thead>
                                    <tr class="text-center"> 
                                        <th rowspan="2">Id</th>
                                        <th rowspan="2">Code</th>
                                        <th rowspan="2">Description</th>  
                                        <th rowspan="2">Unit</th>
                                    </tr>	
                                    <tr class="text-center"> 
                                        <th>Semester</th>  
                                        <th>Year Level</th>  
                                        <th class="text-center">Remarks</th> 
                                        <th>Section Name</th>  
                                        <th>Term</th>  
                                    </tr>
                                </thead> 	 
                                <tbody>
                                    <?php 
                                    
                                        $ifStudentCourseGrade12 = $old_enroll->CheckGrade12AlignedSections($student_id);

                                        $listOfSubjects = $studentEnroll->GetTertiaryGradeReportv2($username, $student_program_id, $student_course_id);

                                        foreach ($listOfSubjects as $key => $value) {

                                            $t2_student_subject_tertiary_id = $value['t2_student_subject_tertiary_id'];
                                            $t3_student_subject_tertiary_id = $value['t3_student_subject_tertiary_id'];
                                            $subjectId = $value['subjectId'];

                                            // echo $t2_student_subject_tertiary_id;
                                            // echo "<br>";

                                            // echo $t2_student_subject_tertiary_id;
                                            // echo "<br>";

                                            $remarks = $value['remarks'];

                                            $student_subject_tertiary_id = isset($value['student_subject_tertiary_id']) ? $value['student_subject_tertiary_id'] : "";

                                            $subject_code = isset($value['subject_code']) ? $value['subject_code'] : "";
                                            $subject_title = isset($value['subject_title']) ? $value['subject_title'] : "";
                                            $unit = isset($value['unit']) ? $value['unit'] : "";
                                            $semester = isset($value['semester']) ? $value['semester'] : "";
                                            $course_level = isset($value['course_level']) ? $value['course_level'] : "";

                                            $program_section = isset($value['program_section']) ? $value['program_section'] : "N/A";
                                            $school_year_term = isset($value['school_year_term']) ? $value['school_year_term'] : "";

                                            // $subjectId = 0;
                                            $remarksOutput = "";

                                            // echo $t3_subject_tertiary_id;
                                            // echo "<br>";



                                            $gradeButton = "tertiaryInsertGrade($student_id, $subjectId,
                                                $student_subject_tertiary_id)";

                                            if($remarks == "Passed"){
                                                $remarksOutput = "Passed";
                                            }else if($remarks == "Failed"){

                                                $remarksOutput = "Failed";
                                            }
                                            else if($t2_student_subject_tertiary_id != $t3_student_subject_tertiary_id
                                                && $remarks != "Passed" && $remarks != "Failed"){

                                                $remarksOutput = "
                                                    <div class='d-flex flex-column align-items-center'>
                                                        <div class='text-center'>
                                                            <input value='Passed' style='width: 50px; text-align: center;' class='form-control' type='text' id='tertiary_remark_input' name='tertiary_remark_input'>
                                                        </div>
                                                        <div>
                                                            <button type='button' onclick='$gradeButton' class='btn btn-sm btn-success'>Add</button>
                                                        </div>
                                                    </div>
                                                ";  

                                            }
                                            else{
                                                $remarksOutput = "Not Available";
                                            }

                                            echo '<tr class="text-center">'; 
                                                    echo '<td>0</td>';
                                                    echo '<td>'.$subject_code.'</td>';
                                                    echo '<td>'.$subject_title.'</td>';
                                                    echo '<td>'.$unit.'</td>';
                                                    echo '<td>'.$semester.'</td>';
                                                    echo '<td>'.$course_level.' Year</td>';
                                                    echo '<td class="text-center">'.$remarksOutput.'</td>';
                                                    echo '<td class="text-center">'.$program_section.' 0</td>';
                                                    echo '<td class="text-center">'.$school_year_term.'</td>';
                                            echo '</tr>';
                                        }
                                        
                                    ?>
                                </tbody>
                            </table>
                    </form>
                </div>

                <script>
                    $(document).ready(function() {

                        $('#tertiary_student_status').change(function() {

                            var courseTertiaryId = $(this).val();
                            // var student_id = parseInt($("#student_id_dropdown").val());
                            
                            // console.log(student_id);

                            var confirmed = confirm("Are you sure you want to update the section of the student?");

                            console.log(courseTertiaryId);
                            if (confirmed) {

                                // console.log('confirm')
                                // $.ajax({
                                //     url: '../ajax/enrollee/update_irregular_section.php',
                                //     method: 'POST',
                                //     data: { courseTertiaryId, student_id },
                                //     success: function(response) {
                                    
                                //         // console.log(response);
                                //         location.reload();
                                //     },
                                //     error: function(xhr, status, error) {
                                //         // Handle any errors that occur during the AJAX request
                                //     }
                                // });
                            }

                        });
                    });
                </script>

                <hr>
                <hr>
                <hr>


                <div class="table-responsive" style="margin-top:5%;"> 
                    <h4 class='text-center'>Retake Subjects</h4>
                    <form method="POST">  					
                            <table id="tertiary_grade_report_insertion" 
                                class="table table-striped table-bordered table-hover "
                                style="font-size:13.8px" cellspacing="0"> 
                                
                                <thead>
                                    <tr class="text-center"> 
                                        <th rowspan="2">Code</th>
                                        <th rowspan="2">Description</th>  
                                        <th rowspan="2">Unit</th>
                                    </tr>	
                                    <tr class="text-center"> 
                                        <th>Semester</th>  
                                        <th>Year Level</th>  
                                        <th>Course/Section</th>  
                                        <th>Remarks</th>  
                                    </tr>
                                </thead> 	 
                                <tbody>
                                    <?php 

                                        $retake_subject = $con->prepare("SELECT 
                                            t2.*,
                                            
                                            t1.student_subject_tertiary_id,

                                            t1.student_subject_tertiary_id as t1_student_subject_tertiary_id,
                                            t3.student_subject_tertiary_id as t3_student_subject_tertiary_id,
                                            t3.remarks,

                                            t4.program_section

                                            FROM student_subject_tertiary as t1
                                        
                                            INNER JOIN subject_tertiary as t2 ON t2.subject_tertiary_id = t1.subject_tertiary_id
                                            LEFT JOIN student_subject_grade_tertiary as t3 ON t3.student_subject_tertiary_id = t1.student_subject_tertiary_id
                                            LEFT JOIN course_tertiary as t4 ON t4.course_tertiary_id = t2.course_tertiary_id

                                            WHERE t1.is_retake=:is_retake
                                            ");

                                        $retake_subject->bindValue(":is_retake", "yes"); 
                                        $retake_subject->execute(); 

                                        if($retake_subject->rowCount() > 0){

                                            while($row_retake = $retake_subject->fetch(PDO::FETCH_ASSOC)){

                                                    $t2_student_subject_tertiary_id = $row_retake['t1_student_subject_tertiary_id'];
                                                    $t3_student_subject_tertiary_id = $row_retake['t3_student_subject_tertiary_id'];

                                                    $subject_tertiary_id = $row_retake['subject_tertiary_id'];
                                                    $student_subject_tertiary_id = $row_retake['student_subject_tertiary_id'];

                                                    $subject_code = $row_retake['subject_code'];
                                                    $subject_title = $row_retake['subject_title'];
                                                    $unit = $row_retake['unit'];
                                                    $semester = $row_retake['semester'];
                                                    $course_level = $row_retake['course_level'];

                                                    $remarks = $row_retake['remarks'];
                                                    $program_section = $row_retake['program_section'];


                                                    $gradeButton = "tertiaryInsertGrade($student_id, $subject_tertiary_id,
                                                        $student_subject_tertiary_id)";
 
                                                $remarksOutputBtn = "";
                                                    if($remarks == "Passed"){
                                                        $remarksOutputBtn = "Passed";
                                                    }else if($remarks == "Failed"){

                                                        $remarksOutputBtn = "Failed";
                                                    }
                                                    else if($t2_student_subject_tertiary_id != $t3_student_subject_tertiary_id
                                                        && $remarks != "Passed" && $remarks != "Failed"){

                                                        $remarksOutputBtn = "
                                                            <div class='d-flex flex-column align-items-center'>
                                                                <div class='text-center'>
                                                                    <input value='Passed' style='width: 50px; text-align: center;' class='form-control' type='text' id='tertiary_remark_input' name='tertiary_remark_input'>
                                                                </div>
                                                                <div>
                                                                    <button type='button' onclick='$gradeButton' class='btn btn-sm btn-success'>Add</button>
                                                                </div>
                                                            </div>
                                                        ";  

                                                    }

                                                    // echo $subjectTertiaryId;

                                                    // echo "$subjectId is here";


                                                    // $remarksOutputBtn = "
                                                    //     <div class='d-flex flex-column align-items-center'>
                                                    //             <div class='text-center'>
                                                    //                 <input value='Passed' style='width: 50px; text-align: center;' class='form-control' type='text' id='tertiary_remark_input' name='tertiary_remark_input'>
                                                    //             </div>
                                                    //             <div>
                                                    //                 <button type='button' onclick='$gradeButton' class='btn btn-sm btn-success'>Add</button>
                                                    //             </div>
                                                    //         </div>
                                                    //     ";

                                                    echo "
                                                    
                                                        <tr class='text-center'>
                                                            <td>$subject_code</td>
                                                            <td>$subject_title</td>
                                                            <td>$unit</td>
                                                            <td>$semester</td>
                                                            <td>$course_level Year</td>
                                                            <td>$program_section</td>
                                                            <td>$remarksOutputBtn</td>
                                                        </tr>
                                                    ";
                                            }
                                        }
                                        
                                    ?>
                                </tbody>
                            </table>
                    </form>
                </div>

                <hr>
                <hr>
                <hr>

                <div class="table-responsive" style="margin-top:5%;"> 
                    <h4 class='text-center'>Subject Taken In Other Course</h4>
                    <form method="POST">  					
                            <table id="tertiary_grade_report_insertion" 
                                class="table table-striped table-bordered table-hover "
                                style="font-size:13.8px" cellspacing="0"> 
                                
                                <thead>
                                    <tr class="text-center"> 
                                        <th rowspan="2">Code</th>
                                        <th rowspan="2">Description</th>  
                                        <th rowspan="2">Unit</th>
                                    </tr>	
                                    <tr class="text-center"> 
                                        <th>Semester</th>  
                                        <th>Year Level</th>  
                                        <th>Course/Section</th>  
                                        <th>Remarks</th>  
                                    </tr>
                                </thead> 	 
                                <tbody>
                                    <?php 

                                        $query = $con->prepare("SELECT 
                                        
                                            t1.subject_tertiary_id,
                                            t1.subject_title,
                                            t1.subject_code,
                                            t1.unit,
                                            t1.semester,
                                            t1.course_level,

                                            t3.remarks,
                                            t4.program_section,

                                            t2.student_subject_tertiary_id,
                                            t2.student_subject_tertiary_id as t2_student_subject_tertiary_id,
                                            t3.student_subject_tertiary_id as t3_student_subject_tertiary_id
                                        
                                            FROM subject_tertiary as t1
                                        
                                            INNER JOIN student_subject_tertiary as t2 ON t2.subject_tertiary_id = t1.subject_tertiary_id
                                            LEFT JOIN student_subject_grade_tertiary as t3 ON t3.student_subject_tertiary_id = t2.student_subject_tertiary_id
                                            LEFT JOIN course_tertiary as t4 ON t4.course_tertiary_id = t1.course_tertiary_id
                                            WHERE t1.program_id !=:program_id
                                            AND t2.student_id=:student_id
                                            ");
                                            
                                        $query->bindValue(":program_id", $student_program_id); 
                                        $query->bindValue(":student_id", $student_id); 
                                        $query->execute(); 

                                        if($query->rowCount() > 0){

                                            while($row = $query->fetch(PDO::FETCH_ASSOC)){

                                                $t2_student_subject_tertiary_id = $row['t2_student_subject_tertiary_id'];
                                                $t3_student_subject_tertiary_id = $row['t3_student_subject_tertiary_id'];

                                                $student_subject_tertiary_id = $row['student_subject_tertiary_id'];


                                                $subject_tertiary_id = $row['subject_tertiary_id'];
                                                $subject_title = $row['subject_title'];
                                                $subject_code = $row['subject_code'];
                                                $unit = $row['unit'];
                                                $semester = $row['semester'];
                                                $course_level = $row['course_level'];
                                                $remarks = $row['remarks'];

                                                echo $remarks;

                                                $program_section = $row['program_section'];


                                                $gradeButton = "tertiaryInsertGrade($student_id, $subject_tertiary_id,
                                                    $student_subject_tertiary_id)";

                                                $remarksOutputBtn = "";

                                                if($remarks == "Passed"){
                                                    $remarksOutputBtn = "Passed";
                                                }else if($remarks == "Failed"){

                                                    $remarksOutputBtn = "Failed";
                                                }
                                                else if($t2_student_subject_tertiary_id 
                                                    != $t3_student_subject_tertiary_id
                                                    && $remarks != "Passed" && $remarks != "Failed"){

                                                    $remarksOutputBtn = "
                                                        <div class='d-flex flex-column align-items-center'>
                                                            <div class='text-center'>
                                                                <input value='Passed' style='width: 50px; text-align: center;' class='form-control' type='text' id='tertiary_remark_input' name='tertiary_remark_input'>
                                                            </div>
                                                            <div>
                                                                <button type='button' onclick='$gradeButton' class='btn btn-sm btn-success'>Add</button>
                                                            </div>
                                                        </div>
                                                    ";  

                                                }

                                                echo "
                                                    <tr class='text-center'>
                                                        <td>$subject_code</td>
                                                        <td>$subject_title</td>
                                                        <td>$unit</td>
                                                        <td>$semester</td>
                                                        <td>$course_level</td>
                                                        <td>$program_section</td>
                                                        <td>$remarksOutputBtn</td>
                                                    </tr>
                                                ";
                                              
                                            }
                                        }

                                         
                                        
                                    ?>
                                </tbody>
                            </table>
                    </form>
                </div>



                <!-- <div class="table-responsive" style="margin-top:5%;"> 
                    <h4 class='text-center'>Overall Grade Report of <?php echo $student_fullname;?></h4>

                    <a href="add_subject_load.php?id=<?php echo $student_id?>">
                        <button class="btn btn-primary">Add Subject Load</button>
                    </a>
                    
                    <form method="POST">  					
                            <table id="tertiary_grade_report_insertion" 
                                class="table table-striped table-bordered table-hover "
                                style="font-size:13.8px" cellspacing="0"> 
                                
                                <thead>
                                    <tr class="text-center"> 
                                        <th rowspan="2">Id</th>
                                        <th rowspan="2">Code</th>
                                        <th rowspan="2">Description</th>  
                                        <th rowspan="2">Unit</th>
                                    </tr>	
                                    <tr class="text-center"> 
                                        <th>Semester</th>  
                                        <th>Year Level</th>  
                                        <th class="text-center">Remarks</th> 
                                        <th>Section Name</th>  
                                        <th>Term</th>  
                                    </tr>
                                </thead> 	 
                                <tbody>
                                    <?php 
                                    
                                        $ifStudentCourseGrade12 = $old_enroll->CheckGrade12AlignedSections($student_id);

                                        $listOfSubjects = $studentEnroll->GetTertiaryGradeReport($username, $student_program_id, $student_course_id);

                                        foreach ($listOfSubjects as $key => $value) {

                                            
                                            // $subjectId = isset($value['course_id']) ? $value['course_id'] : "";
                                            // $course_id = isset($value['course_id']) ? $value['course_id'] : "";

                                            $remarks = isset($value['remarks']) ? $value['remarks'] : "";

                                            $subjectId = isset($value['subjectId']) ? $value['subjectId'] : "";
                                            $subjectTertiaryId = isset($value['subjectTertiaryId']) ? $value['subjectTertiaryId'] : "";
                                            $student_subject_tertiary_id = isset($value['student_subject_tertiary_id']) ? $value['student_subject_tertiary_id'] : "";

                                            // echo $subjectId;

                                            $subject_code = isset($value['subject_code']) ? $value['subject_code'] : "";
                                            $subject_title = isset($value['subject_title']) ? $value['subject_title'] : "";
                                            $unit = isset($value['unit']) ? $value['unit'] : "";
                                            $semester = isset($value['semester']) ? $value['semester'] : "";
                                            $course_level = isset($value['course_level']) ? $value['course_level'] : "";

                                            $program_section = isset($value['program_section']) ? $value['program_section'] : "N/A";
                                            $school_year_term = isset($value['school_year_term']) ? $value['school_year_term'] : "";

                                            $query_student_subject = $con->prepare("SELECT 
                                            
                                                subject_id, student_subject_id

                                                FROM student_subject

                                                WHERE subject_id=:subject_id
                                                AND student_id=:student_id
                                                LIMIT 1");

                                            $query_student_subject->bindValue(":subject_id", $subjectId);
                                            $query_student_subject->bindValue(":student_id", $userLoggedInId);
                                            $query_student_subject->execute();

                                            //
                                            $enrolled_status = "False";

                                            $student_subject_id = null;
                                          
                                            $remarksOutput = "NOT AVAILABLE";

                                            $gradeButton = "tertiaryInsertGrade($student_id, $subjectId,
                                                $student_subject_tertiary_id)";

                                            if($subjectId != "" && $student_subject_tertiary_id != "" &&
                                                $subjectTertiaryId == $subjectId && $remarks != "Passed" && $remarks != "Failed"){

                                                // echo $subjectTertiaryId;

                                                // echo "$subjectId is here";
                                                $remarksOutput = "
                                                   <div class='d-flex flex-column align-items-center'>
                                                        <div class='text-center'>
                                                            <input value='Passed' style='width: 50px; text-align: center;' class='form-control' type='text' id='tertiary_remark_input' name='tertiary_remark_input'>
                                                        </div>
                                                        <div>
                                                            <button type='button' onclick='$gradeButton' class='btn btn-sm btn-success'>Add</button>
                                                        </div>
                                                    </div>
                                                ";
                                            }
                                            else if($subjectTertiaryId == $subjectId && $remarks == "Passed"){

                                                // echo "$subjectId is here";
                                                $remarksOutput = "
                                                  Passed
                                                ";
                                            }
                                            else if($subjectTertiaryId == $subjectId && $remarks == "Failed"){

                                                // echo $remarks;

                                                // echo "$subjectId is here";
                                                $remarksOutput = "
                                                  Failed
                                                ";
                                            }
                                            else{
                                                $remarksOutput = "NOT AVAILABLE";
                                            }

                                            echo '<tr class="text-center">'; 
                                                    echo '<td>'.$subjectId.'</td>';
                                                    echo '<td>'.$subject_code.'</td>';
                                                    echo '<td>'.$subject_title.'</td>';
                                                    echo '<td>'.$unit.'</td>';
                                                    echo '<td>'.$semester.'</td>';
                                                    echo '<td>'.$course_level.'</td>';
                                                    echo '<td class="text-center">'.$remarksOutput.'</td>';
                                                    echo '<td class="text-center">'.$program_section.' 0</td>';
                                                    echo '<td class="text-center">'.$school_year_term.'</td>';
                                            echo '</tr>';
                                        }
                                        
                                    ?>
                                </tbody>
                            </table>
                    </form>
                </div> -->

                                        
            </div>

        </div>
   
        <?php    
    }
?>
 



