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

        $student_course_id = $student_obj['course_id'];
        $student_id = $student_obj['student_id'];
        $student_course_level = $student_obj['course_level'];

        $userLoggedInId = $student_id;
        $course_main_id = $student_course_id;


        $isFinished = $old_enroll->CheckGrade11StudentFinishedSubject($username);

        if($isFinished == true && $student_course_level == 11){
            
            // Update move_up student course level to 12

            // $wasMoveUpSuccess = $old_enroll->StudentMoveUpToGrade12($username);

            // if($wasMoveUpSuccess){
            //     echo "Student was move_up to Course_Level 12";
            // }
        }

        //  if($isFinished == true){
        //         echo "Student was move_up to Course_Level 12";
        //  }

        ?>
        <div class="row">

            <div class="row col-md-12">
                <div class="table-responsive" style="margin-top:5%;"> 
                    <h4 class='text-center'>Overall Grade Report of <?php echo $student_fullname;?></h4>

                    <a href="add_subject_load.php?id=<?php echo $student_id?>">
                        <button class="btn btn-primary">Add Subject Load</button>
                    </a>
                    
                    <form method="POST">  					
                            <table id="grade_report_insertion" 
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
                                        <th>Course Level</th>  
                                        <th>Status</th>  
                                        <th class="text-center">Remarks</th> 
                                        <th>Section Name</th>  
                                        <th>Term</th>  
                                    </tr>
                                </thead> 	 
                                <tbody>
                                    <?php 
                                    
                                        $ifStudentCourseGrade12 = $old_enroll->CheckGrade12AlignedSections($student_id);

                                        $listOfSubjects = $studentEnroll->GetStudentSubjectListWithGradesv2($username, $ifStudentCourseGrade12);
 
                                        // $hasCompleteTheGradeElevenSecondSem = $enroll->GetStudentStrandSubjectFrom1stTo2ndSem($username, $student_id);
                                        // if($hasCompleteTheGradeElevenSecondSem == true){
                                        //     echo "$username passed all the subjects in his Grade 11.";
                                        //     // NOTIFICATION FOR PASSING THE STUDENT GRADE 11 SUBJECTS IN THEIR PROFILE.
                                        //     $wasMoveUpSuccess = $old_enroll->StudentMoveUpToGrade12($username);
                                        // }

                                        foreach ($listOfSubjects as $key => $value) {

                                            $course_id = isset($value['course_id']) ? $value['course_id'] : "";
                                            $subject_id = isset($value['subject_id']) ? $value['subject_id'] : "";
                                            $subject_code = isset($value['subject_code']) ? $value['subject_code'] : "";
                                            $subject_title = isset($value['subject_title']) ? $value['subject_title'] : "";
                                            $unit = isset($value['unit']) ? $value['unit'] : "";
                                            $semester = isset($value['semester']) ? $value['semester'] : "";
                                            $course_level = isset($value['course_level']) ? "Grade " . $value['course_level'] : "";

                                            $program_section = isset($value['program_section']) ? $value['program_section'] : "N/A";
                                            $school_year_term = isset($value['school_year_term']) ? $value['school_year_term'] : "";

                                            $query_student_subject = $con->prepare("SELECT 
                                            
                                                subject_id, student_subject_id

                                                FROM student_subject

                                                WHERE subject_id=:subject_id
                                                AND student_id=:student_id
                                                LIMIT 1");

                                            $query_student_subject->bindValue(":subject_id", $subject_id);
                                            $query_student_subject->bindValue(":student_id", $userLoggedInId);
                                            $query_student_subject->execute();

                                            //
                                            $enrolled_status = "False";

                                            $student_subject_id = null;

                                                // echo $subject_id . " 1 ";

                                            if($query_student_subject->rowCount() > 0){
                                                
                                                $row = $query_student_subject->fetch(PDO::FETCH_ASSOC);

                                                $student_subject_subject_id = $row['subject_id'];
                                                $student_subject_id = $row['student_subject_id'];

                                                // echo $student_subject_subject_id . " ";
                                                if($subject_id == $student_subject_subject_id){
                                                    $enrolled_status = "Enrolled";
                                                }

                                                // echo "<br>";

                                                // echo $student_subject_subject_id . " 2 ";
                                                // echo "<br>";

                                            }

                                        
                                          
                                            //
                                            $query_student_subject_grade = $con->prepare("SELECT 

                                                t1.student_id, t1.remarks, t1.subject_id,
                                                t1.subject_title, t1.course_id,

                                                t2.school_year_term, t2.program_section

                                                FROM student_subject_grade as t1

                                                -- WHERE subject_id=:subject_id

                                                LEFT JOIN course AS t2 ON t2.course_id = t1.course_id
                                                
                                                WHERE t1.subject_title=:subject_title
                                                AND t1.student_id=:student_id

                                                LIMIT 1");

                                            // $query_student_subject_grade->bindValue(":subject_id", $subject_id);
                                            $query_student_subject_grade->bindValue(":subject_title", $subject_title);
                                            $query_student_subject_grade->bindValue(":student_id", $userLoggedInId);
                                            $query_student_subject_grade->execute();
                                            
                                            $remarksOutput = "";
                                            $row_student_grade = null;

                                            // $subject_id_grade = 0;

                                            if($query_student_subject_grade->rowCount() > 0){

                                                $row_student_grade = $query_student_subject_grade->fetch(PDO::FETCH_ASSOC);

                                                $remarksOutput = $row_student_grade['remarks'];

                                                // Overwrite subject_id if that subject title already 
                                                // taken by the student.
                                                # Applicable: Returnee, Transferee who may taken the subject
                                                # in another sections
                                                $subject_id = $row_student_grade['subject_id'];
                                                $course_id = $row_student_grade['course_id'];

                                                $program_section = $row_student_grade['program_section'];
                                                $school_year_term = $row_student_grade['school_year_term'];

                                                $subject_title_graded = $row_student_grade['subject_title'];

                                                 
                                                if($subject_title == $subject_title_graded){
                                                    // $enrolled_status = "Enrolled";
                                                }
                                            }

                                            // echo $course_id;

                                            $gradeButton = "insertGrade($student_id, $subject_id,
                                                $student_subject_id, \"$subject_title\", $course_id)";
                                            
                                                // echo $enrolled_status;
    
                                            if($enrolled_status == "Enrolled" && $remarksOutput == ""){
                                                // If was enrolled and not yet given remarks.
                                                $remarksOutput = "
                                                    <input style='width: 50px;' class='form-control' type='text' id='remark_input' name='remark_input'>
                                                    <button type='button' onclick='$gradeButton' class='btn btn-sm btn-success'>Add</button>
                                                ";
                                            }

                                            if($remarksOutput == ""){
                                                $remarksOutput = "NOT AVAILABLE";
                                            }

                                            echo '<tr class="text-center">'; 
                                                    echo '<td>'.$subject_id.'</td>';
                                                    echo '<td>'.$subject_code.'</td>';
                                                    echo '<td>'.$subject_title.'</td>';
                                                    echo '<td>'.$unit.'</td>';
                                                    // echo '<td>'.$schedule_day.'</td>';
                                                    // echo '<td>'.$schedule_time.'</td>';
                                                    // echo '<td>'.$room.'</td>';
                                                    // echo '<td>'.$section.'</td>';
                                                    echo '<td>'.$semester.'</td>';
                                                    echo '<td>'.$course_level.'</td>';
                                                    echo '<td>'.$enrolled_status.'</td>';
                                                    echo '<td class="text-center">'.$remarksOutput.'</td>';
                                                    echo '<td class="text-center">'.$program_section.' ('.$course_id.')</td>';
                                                    echo '<td class="text-center">'.$school_year_term.'</td>';

                                            echo '</tr>';
                                        }
                                        
                                    ?>
                                </tbody>
                            </table>
                    </form>
                </div>
            </div>

            
            <!-- <h3>Enrolled Subjectsv2</h3> -->
            <div class="table-responsive" style="margin-top:5%;"> 
                <form action="customer/controller.php?action=delete" method="POST">  					
                        <table  class="table table-striped table-bordered table-hover "  style="font-size:13px" cellspacing="0"  > 
                            <thead>
                                <tr> 
                                    <th rowspan="2">Id</th>
                                    <th rowspan="2">Title</th>
                                    <th rowspan="2">Description</th>  
                                    <th rowspan="2">Unit</th>
                                    <th rowspan="2">Semester</th> 
                                    <th rowspan="2">Grade Level</th> 
                                    <th rowspan="2">Graded</th> 
                                </tr>	
                            </thead> 	 
                            <tbody>
                                <?php 

                                    // $listOfSubjects = $studentEnroll->GetStudentsStrandSubjects($username);
                                    $student_program_id = $studentEnroll->GetStudentProgramId($course_main_id);
                                    
                                    $arr = [];

                                    $samp = $con->prepare("SELECT t1.subject_title
                                    --      , t1.subject_id

                                            FROM subject as t1
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
                                    }

                                    $arr2 = [];

                                    $samp2 = $con->prepare("SELECT t1.subject_id

                                            FROM subject as t1
                                            INNER JOIN student_subject_grade as t2 ON t1.subject_id=t2.subject_id
                                            WHERE t2.student_id=:student_id

                                            ");   
                                    $samp2->bindValue(":student_id", $student_id);
                                    $samp2->execute(); 

                                    if($samp2->rowCount() > 0){

                                        // $as = $samp2->fetchAll(PDO::FETCH_ASSOC);
                                        while($row2 = $samp2->fetch(PDO::FETCH_COLUMN)){
                                            array_push($arr2, $row2);
                                        }

                                        // print_r($arr2);
                                    }

                                    $sql = $con->prepare("SELECT t1.*

                                        FROM subject_program as t1
                                        WHERE t1.program_id=:program_id
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
                                            // $subject_id = $row['subject_id'];
                                            $subject_title = $row['subject_title'];
                                            $description = $row['description'];
                                            $unit = $row['unit'];
                                            $course_level = $row['course_level'];
                                            $semester = $row['semester'];

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

                                            
                                            // if (in_array($subject_title, $arr2)) {
                                            //     $name = $subject_program_id;
                                            //     $name = "
                                            //         <i style='color: green;' class='fas fa-check'></i>
                                            //     ";
                                            // }else{
                                            //     $name = "
                                            //         <i style='color: orange;' class='fas fa-times'></i>
                                            //     ";
                                            // }
                                            $subject_id = "";
                                            $remarkButton = "insertGradev2($student_id, $subject_id)";
                                            
                                            $remarkButton = "
                                                <input style='width: 50px;' class='form-control' type='text' id='mark_subject' name='mark_subject'>
                                                <button type='button' onclick='$remarkButton' class='btn btn-sm btn-success'>Mark</button>
                                            ";
                                            echo "
                                                <tr>
                                                    <td>1</td>
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
                </form>
            </div>
        </div>
   
        <?php    
    }
?>
 



