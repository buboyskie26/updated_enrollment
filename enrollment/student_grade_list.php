<?php 

    require_once('./classes/StudentEnroll.php');
    // require_once('../includes/config.php');
    require_once('../includes/studentHeader.php');


    if(isset($_SESSION['username'])){
        $username = $_SESSION['username'];
    }

    $studentEnroll = new StudentEnroll($con);
    $userLoggedInId = $studentEnroll->GetStudentId($username);
    $course_main_id = $studentEnroll->GetStudentCourseId($username);
    $section_name = $studentEnroll->GetStudentCourseName($username);
    $student_course_level = $studentEnroll->GetStudentCourseLevel($username);

    $school_year_obj = $studentEnroll->GetActiveSchoolYearAndSemester();

    $current_school_year_id = $school_year_obj['school_year_id'];
    $current_school_year_term = $school_year_obj['term'];
    $current_school_year_period = $school_year_obj['period'];

    ?>

        <div class="row col-md-12">
            
            <h3 class="text-center">Grade Report</h3>
            <!-- GRADE 11 With Section and School Year Semester (Detailed) -->
            <?php
                $GRADE_TWELVE = 12;
                $GRADE_ELEVEN = 11;
                $FIRST_SEMESTER = "First";
                ?>
                <div class="table-responsive" style="margin-top:2%;"> 
                    <form action="customer/controller.php?action=delete" method="POST"> 
                            <?php 
                                // Section Based on the enrollment.
                                $enrollment_school_year = $studentEnroll->GetStudentSectionGradeElevenSchoolYear($username, $userLoggedInId, 11, "First");

                                if($enrollment_school_year !== null){
                                    $term = $enrollment_school_year['term'];
                                    $period = $enrollment_school_year['period'];
                                    $school_year_id = $enrollment_school_year['school_year_id'];

                                    echo "
                                        <h4 style='font-weight: 500;' class='text-primary text-center mb-3'>Grade 11 $section_name $period Semester (SY $term)</h4>
                                    ";
                                }
                            
                            ?> 					
                            <table style="font-weight: 200;" class="table table-striped table-bordered table-hover "  style="font-size:12px" cellspacing="0"  > 
                                <thead>
                                    <tr class="text-center"> 
                                        <th class="text-success" rowspan="2">Subject</th>
                                        <th class="text-success" rowspan="2">Type</th>
                                        <th class="text-success" rowspan="2">Description</th>  
                                        <th class="text-success" rowspan="2">Unit</th>
                                        <th class="text-muted" colspan="4">Schedule</th> 
                                    </tr>	
                                    <tr> 
                                        <!-- <th>Day</th> 
                                        <th>Time</th>
                                        <th>Room</th> 
                                        <th>Section</th>   -->
                                        <th class="text-success">Semester</th>  
                                        <th class="text-success">Course Level</th>  
                                        <th class="text-success">Remarks</th>  
                                    </tr>
                                </thead> 	 
                                <tbody>
                                    <?php 
                                        // $listOfSubjects = $studentEnroll->GetStudentSubjectListWithGrades($username);
                                        $listOfSubjects = $studentEnroll->GetStudentSectionGradeElevenSemester($username, $userLoggedInId, $GRADE_ELEVEN, $FIRST_SEMESTER);

                                        if($listOfSubjects !== null){

                                            foreach ($listOfSubjects as $key => $value) {

                                                $subject_id = $value['subject_id'];

                                                

                                                $query_student_subject_grade = $con->prepare("SELECT 
                                                
                                                    student_id, remarks 

                                                    FROM student_subject_grade
                                                    WHERE subject_id=:subject_id
                                                    AND student_id=:student_id
                                                    LIMIT 1");

                                                $query_student_subject_grade->bindValue(":subject_id", $subject_id);
                                                $query_student_subject_grade->bindValue(":student_id", $userLoggedInId);
                                                $query_student_subject_grade->execute();
                                                
                                                $remarksOutput = "";
                                                $course_level = $value['course_level'] != "" ? "Grade " . $value['course_level'] : "";

                                                if($query_student_subject_grade->rowCount() > 0){

                                                    $row_ssg = $query_student_subject_grade->fetch(PDO::FETCH_ASSOC);

                                                    $remarksOutput = $row_ssg['remarks'];

                                                    $student_id = $row_ssg['student_id'] != "" ? $row_ssg['student_id'] : "";
                                                }
                                                $enrolled_status = "N_E";

                                                $student_enrolled_subject_id = "";

                                                if($student_enrolled_subject_id == $subject_id && $student_id == $userLoggedInId){
                                                    $enrolled_status = "Enrolled";
                                                }
                                            
                                                echo '<tr class="text-center">'; 
                                                        echo '<td>'.$value['subject_code'].'</td>';
                                                        echo '<td>'.$value['subject_type'].'</td>';
                                                        echo '<td>'.$value['subject_title'].'</td>';
                                                        echo '<td>'.$value['unit'].'</td>';
                                                        // echo '<td>'.$schedule_day.'</td>';
                                                        // echo '<td>'.$schedule_time.'</td>';
                                                        // echo '<td>'.$room.'</td>';
                                                        // echo '<td>'.$section.'</td>';
                                                        echo '<td>'.$value['semester'].'</td>';
                                                        echo '<td>'.$course_level.'</td>';
                                                        echo '<td>'.$remarksOutput.'</td>';
                                                echo '</tr>';
                                            }     
                                        }
                                        else{
                                            echo "No Datax was found for Grade 11 1st Semester.";
                                        }
                                        
                                    ?>
                                </tbody>
                            </table>
                    </form>
                </div>
                <?php
            ?> 

            <!-- GRADE 12 With Section and School Year Semester (Detailed) -->
            <?php
                $GRADE_TWELVE = 12;
                $GRADE_ELEVEN = 11;
                $SECOND_SEMESTER = "Second";
                $enrollment_school_year = 0;

                ?>
                <div class="table-responsive" style="margin-top:2%;"> 
                    <form action="customer/controller.php?action=delete" method="POST"> 
                            <?php 
                                $enrollment_school_year = $studentEnroll->GetStudentSectionGradeElevenSchoolYear($username, $userLoggedInId, 11, "Second");

                                // Section Based on the enrollment.
                                if($enrollment_school_year !== null){
                                    $term = $enrollment_school_year['term'];
                                    $period = $enrollment_school_year['period'];
                                    $school_year_id = $enrollment_school_year['school_year_id'];
                                    echo "
                                        <h4 class='text-center mb-3'>Grade $GRADE_ELEVEN $section_name $SECOND_SEMESTER Semester (SY $term) SY-ID $school_year_id</h4>
                                    ";
                                }
                            ?> 					
                            <table  class="table table-striped table-bordered table-hover "  style="font-size:12px" cellspacing="0"  > 
                                <thead>
                                    <tr> 
                                        <th rowspan="2">Subject</th>
                                        <th rowspan="2">Type</th>
                                        <th rowspan="2">Description</th>  
                                        <th rowspan="2">Unit</th>
                                        <th colspan="4">Schedule</th> 
                                    </tr>	
                                    <tr> 
                                        <!-- <th>Day</th> 
                                        <th>Time</th>
                                        <th>Room</th> 
                                        <th>Section</th>   -->
                                        <th>Semester</th>  
                                        <th>Course Level</th>  
                                        <th>Remarks</th>  
                                    </tr>
                                </thead> 	 
                                <tbody>
                                    <?php 
                                    
                                        // $listOfSubjects = $studentEnroll->GetStudentSubjectListWithGrades($username);
                                        $listOfSubjects = $studentEnroll->GetStudentSectionGradeElevenSemester($username, $userLoggedInId, $GRADE_ELEVEN, $SECOND_SEMESTER);
                                        
                                        if($listOfSubjects !== null){
                                            foreach ($listOfSubjects as $key => $value) {

                                                $subject_id = $value['subject_id'];

                                                $query_student_subject_grade = $con->prepare("SELECT student_id, remarks  
                                                    
                                                    FROM student_subject_grade
                                                    WHERE subject_id=:subject_id
                                                    AND student_id=:student_id
                                                    LIMIT 1");

                                                $query_student_subject_grade->bindValue(":subject_id", $subject_id);
                                                $query_student_subject_grade->bindValue(":student_id", $userLoggedInId);
                                                $query_student_subject_grade->execute();
                                                
                                                $remarksOutput = "";
                                                    $course_level = $value['course_level'] != "" ? "Grade " . $value['course_level'] : "";

                                                if($query_student_subject_grade->rowCount() > 0){

                                                    $row_ssg = $query_student_subject_grade->fetch(PDO::FETCH_ASSOC);

                                                    $remarksOutput = $row_ssg['remarks'];

                                                    $student_id = $row_ssg['student_id'] != "" ? $row_ssg['student_id'] : "";
                                                    
                                                }
                                                $enrolled_status = "N_E";

                                                $student_enrolled_subject_id = "";

                                                if($student_enrolled_subject_id == $subject_id && $student_id == $userLoggedInId){
                                                    $enrolled_status = "Enrolled";
                                                }
                                            
                                                echo '<tr>'; 
                                                        echo '<td>'.$value['subject_code'].'</td>';
                                                        echo '<td>'.$value['subject_type'].'</td>';
                                                        echo '<td>'.$value['subject_title'].'</td>';
                                                        echo '<td>'.$value['unit'].'</td>';
                                                        // echo '<td>'.$schedule_day.'</td>';
                                                        // echo '<td>'.$schedule_time.'</td>';
                                                        // echo '<td>'.$room.'</td>';
                                                        // echo '<td>'.$section.'</td>';
                                                        echo '<td>'.$value['semester'].'</td>';
                                                        echo '<td>'.$course_level.'</td>';
                                                        echo '<td>'.$remarksOutput.'</td>';
                                                echo '</tr>';
                                            }     
                                        }
                                        else{
                                            echo "No Data was found for Grade 11 2nd Semester. (We are not in the Second Semester Yet)";
                                        }
                                        
                                    ?>
                                </tbody>
                            </table>
                    </form>
                </div>
                <?php
            ?>                      

 
        
            <!-- GRADE 12 FIRST SEMESTER-->
            <?php
                $GRADE_TWELVE = 12;
                if($student_course_level == $GRADE_TWELVE){
                    ?>
                    <div class="table-responsive" style="margin-top:2%;"> 

                        <form action="customer/controller.php?action=delete" method="POST"> 
                                <?php 
                                if($student_course_level)
                                    echo "
                                        <h4 class='text-center mb-3'>Grade 12 Section $section_name First Semester</h4>
                                    ";
                                ?> 					
                                <table  class="table table-striped table-bordered table-hover "  style="font-size:12px" cellspacing="0"  > 
                                    <thead>
                                        <tr> 
                                            <th rowspan="2">Subject</th>
                                            <th rowspan="2">Type</th>
                                            <th rowspan="2">Description</th>  
                                            <th rowspan="2">Unit</th>
                                            <th colspan="4">Schedule</th> 
                                        </tr>	
                                        <tr> 
                                            <!-- <th>Day</th> 
                                            <th>Time</th>
                                            <th>Room</th> 
                                            <th>Section</th>   -->
                                            <th>Semester</th>  
                                            <th>Course Level</th>  
                                            <th>Status</th>  
                                            <th>Remarks</th>  
                                        </tr>
                                    </thead> 	 
                                    <tbody>
                                        <?php 
                                        
                                            // $listOfSubjects = $studentEnroll->GetStudentSubjectListWithGrades($username);
                                            $listOfSubjects = $studentEnroll->GetStudentsStrandSubjectsForGradeTwelve($username, $GRADE_TWELVE, $FIRST_SEMESTER);
                                
                                            foreach ($listOfSubjects as $key => $value) {

                                                $subject_id = $value['subject_id'];

                                                $query_student_subject_grade = $con->prepare("SELECT student_id, remarks  
                                                    
                                                    FROM student_subject_grade
                                                    WHERE subject_id=:subject_id
                                                    AND student_id=:student_id
                                                    LIMIT 1");

                                                $query_student_subject_grade->bindValue(":subject_id", $subject_id);
                                                $query_student_subject_grade->bindValue(":student_id", $userLoggedInId);
                                                $query_student_subject_grade->execute();
                                                
                                                $remarksOutput = "";
                                                $course_level = $value['course_level'] != "" ? "Grade " . $value['course_level'] : "";

                                                if($query_student_subject_grade->rowCount() > 0){

                                                    $row_ssg = $query_student_subject_grade->fetch(PDO::FETCH_ASSOC);

                                                    $remarksOutput = $row_ssg['remarks'];

                                                    $student_id = $row_ssg['student_id'] != "" ? $row_ssg['student_id'] : "";
                                                    
                                                }
                                                $enrolled_status = "N_E";

                                                $student_enrolled_subject_id = "";

                                                if($student_enrolled_subject_id == $subject_id && $student_id == $userLoggedInId){
                                                    $enrolled_status = "Enrolled";
                                                }
                                            
                                                echo '<tr>'; 
                                                        echo '<td>'.$value['subject_code'].'</td>';
                                                        echo '<td>'.$value['subject_type'].'</td>';
                                                        echo '<td>'.$value['subject_title'].'</td>';
                                                        echo '<td>'.$value['unit'].'</td>';
                                                        // echo '<td>'.$schedule_day.'</td>';
                                                        // echo '<td>'.$schedule_time.'</td>';
                                                        // echo '<td>'.$room.'</td>';
                                                        // echo '<td>'.$section.'</td>';
                                                        echo '<td>'.$value['semester'].'</td>';
                                                        echo '<td>'.$course_level.'</td>';
                                                        echo '<td>'.$enrolled_status.'</td>';
                                                        echo '<td>'.$remarksOutput.'</td>';
                                                echo '</tr>';
                                            }
                                            
                                        ?>
                                    </tbody>
                                </table>
                        </form>
                    </div>
                    <?php
                }
            ?>

            <!-- GRADE 12 SECOND SEMESTER-->
            <?php
                $GRADE_TWELVE = 12;
                if($student_course_level == $GRADE_TWELVE){
                    ?>
                    <div class="table-responsive" style="margin-top:2%;"> 

                        <form action="customer/controller.php?action=delete" method="POST"> 
                                <?php 
                                if($student_course_level)
                                    echo "
                                        <h4 class='text-center mb-3'>Grade 12 Section $section_name Second Semester</h4>
                                    ";
                                ?> 					
                                <table  class="table table-striped table-bordered table-hover "  style="font-size:12px" cellspacing="0"  > 
                                    <thead>
                                        <tr> 
                                            <th rowspan="2">Subject</th>
                                            <th rowspan="2">Type</th>
                                            <th rowspan="2">Description</th>  
                                            <th rowspan="2">Unit</th>
                                            <th colspan="4">Schedule</th> 
                                        </tr>	
                                        <tr> 
                                            <!-- <th>Day</th> 
                                            <th>Time</th>
                                            <th>Room</th> 
                                            <th>Section</th>   -->
                                            <th>Semester</th>  
                                            <th>Course Level</th>  
                                            <th>Status</th>  
                                            <th>Remarks</th>  
                                        </tr>
                                    </thead> 	 
                                    <tbody>
                                        <?php 
                                        
                                            // $listOfSubjects = $studentEnroll->GetStudentSubjectListWithGrades($username);
                                            $listOfSubjects = $studentEnroll->GetStudentsStrandSubjectsForGradeTwelve($username, $GRADE_TWELVE, $SECOND_SEMESTER);
                                
                                            foreach ($listOfSubjects as $key => $value) {

                                                $subject_id = $value['subject_id'];

                                                $query_student_subject_grade = $con->prepare("SELECT student_id, remarks  
                                                    
                                                    FROM student_subject_grade
                                                    WHERE subject_id=:subject_id
                                                    AND student_id=:student_id
                                                    LIMIT 1");

                                                $query_student_subject_grade->bindValue(":subject_id", $subject_id);
                                                $query_student_subject_grade->bindValue(":student_id", $userLoggedInId);
                                                $query_student_subject_grade->execute();
                                                
                                                $remarksOutput = "";
                                                $course_level = $value['course_level'] != "" ? "Grade " . $value['course_level'] : "";

                                                if($query_student_subject_grade->rowCount() > 0){

                                                    $row_ssg = $query_student_subject_grade->fetch(PDO::FETCH_ASSOC);

                                                    $remarksOutput = $row_ssg['remarks'];

                                                    $student_id = $row_ssg['student_id'] != "" ? $row_ssg['student_id'] : "";
                                                    
                                                }
                                                $enrolled_status = "N_E";

                                                $student_enrolled_subject_id = "";

                                                if($student_enrolled_subject_id == $subject_id && $student_id == $userLoggedInId){
                                                    $enrolled_status = "Enrolled";
                                                }
                                            
                                                echo '<tr>'; 
                                                        echo '<td>'.$value['subject_code'].'</td>';
                                                        echo '<td>'.$value['subject_type'].'</td>';
                                                        echo '<td>'.$value['subject_title'].'</td>';
                                                        echo '<td>'.$value['unit'].'</td>';
                                                        // echo '<td>'.$schedule_day.'</td>';
                                                        // echo '<td>'.$schedule_time.'</td>';
                                                        // echo '<td>'.$room.'</td>';
                                                        // echo '<td>'.$section.'</td>';
                                                        echo '<td>'.$value['semester'].'</td>';
                                                        echo '<td>'.$course_level.'</td>';
                                                        echo '<td>'.$enrolled_status.'</td>';
                                                        echo '<td>'.$remarksOutput.'</td>';
                                                echo '</tr>';
                                            }
                                            
                                        ?>
                                    </tbody>
                                </table>
                        </form>
                    </div>
                    <?php
                }
            ?>
        </div>
    <?php
?>