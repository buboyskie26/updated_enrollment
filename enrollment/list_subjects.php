<?php 

    require_once('./classes/StudentEnroll.php');
    require_once('./classes/OldEnrollees.php');
    require_once('./classes/Schedule.php');
    // require_once('../includes/config.php');
    require_once('../includes/studentHeader.php');

    if(isset($_SESSION['username'])){
        $username = $_SESSION['username'];
        echo "
            <h5>Rule 1. Be Consistent</h5><span>$username</span>
        ";
    }

    $studentEnroll = new StudentEnroll($con);
    $old_enroll = new OldEnrollees($con, $studentEnroll);
    $schedule = new Schedule($con, $studentEnroll);
    $userLoggedInId = $studentEnroll->GetStudentId($username);
    $course_main_id = $studentEnroll->GetStudentCourseId($username);
    $section_name = $studentEnroll->GetStudentCourseName($username);

    $student_id = $userLoggedInId;

    $GRADE_TWELVE = 12;
    $GRADE_ELEVEN = 11;
    $SECOND_SEMESTER = "Second";
    $FIRST_SEMESTER = "First";
    $enrollment_school_year = 0;


    $course_id = 17; // change this to the desired course ID
    // $stmt = $con->prepare("SELECT * FROM course WHERE course_id = :course_id");
    $stmt = $con->prepare("SELECT 

        course.course_id, course.program_section, subject.subject_title

        FROM course 
        LEFT JOIN subject ON course.course_id = subject.course_id
        ORDER BY course.course_id, subject.subject_title");
        
    $stmt->execute();
    $course_data = $stmt->fetch(PDO::FETCH_ASSOC);

    // Retrieve the subject data for the given course_id
    $stmt = $con->prepare("SELECT * FROM subject WHERE course_id = :course_id");
    $stmt->bindValue(':course_id', $course_id, PDO::PARAM_INT);
    $stmt->execute();
    $subject_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
            <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>
            <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js"></script>

            <title>Document</title>
        </head>
        <body>
            <h3 class="text-center">Section Schedule</h3>
            <div class="table-responsive" style="margin-top:5%;"> 
                <form action="customer/controller.php?action=delete" method="POST">  					
                    
                    <table  class="table table-striped table-bordered table-hover "  style="font-size:13px" cellspacing="0"  > 
                        <thead>
                            <tr class="text-center"> 
                                <!-- <th>
                                </th> -->
                                <th rowspan="2">Section</th>
                                <th rowspan="2">S.Y</th>  
                                <th rowspan="2">Semester</th>
                                <th rowspan="2">Subject Count</th>  
                            </tr>	

                        <tbody>
                            <?php 

                                $sql = $con->prepare("SELECT 

                                    t1.* , t2.*, t3.*
                                    
                                    FROM enrollment as t1

                                    LEFT JOIN course as t2 ON t2.course_id = t1.course_id
                                    LEFT JOIN school_year as t3 ON t3.school_year_id = t1.school_year_id
                                    WHERE t1.student_id=:student_id
                                    AND t1.enrollment_status=:enrollment_status");

                                $sql->bindValue(":student_id", $userLoggedInId);
                                $sql->bindValue(":enrollment_status", "enrolled");

                                $sql->execute();
                                if($sql->rowCount() > 0){

                                    while($row = $sql->fetch(PDO::FETCH_ASSOC)){
                                        $course_id = $row['course_id'];
                                        $school_year_id = $row['school_year_id'];
                                        $term = $row['term'];
                                        $program_section = $row['program_section'];

                                        $subject_count  = $old_enroll->GetSubjectCount($course_id, $school_year_id);

                                        $period = $row['period'];
                                        echo '<tr class="text-center">'; 

                                            echo '<td>
                                                <a style="text-decoration:none;" href="enrolled_schedule.php?id='.$course_id.'&yid='.$school_year_id.'">
                                                    '.$program_section.'
                                                </a>
                                            </td>';
                                            echo '<td>'.$term.'</td>';
                                            echo '<td>'.$period.'</td>';
                                            echo '<td>'.$subject_count.'</td>';
                                        echo '</tr>';
                                    }
                                }
                            ?>
                        </tbody>
                    </table>


                    <br>
                    <br>
                    <!-- <table class="table table-striped table-bordered table-hover " style="font-size:13px" cellspacing="0" >
                        <thead>
                            <tr class="text-center">
                                <th>Course ID</th>
                                <th>Course Name</th>
                                <th>Subject Name</th>
                            </tr>
                        </thead>
                        <tbody>
                            
                            <?php 
                                if (!empty($course_data)) {
                                    
                                    $count_subject_data = count($subject_data) + 1;
                                    $course_data_id = $course_data['course_id'];
                                    $course_data_program_section = $course_data['program_section'];

                                    echo "<tr class='text-center'>
                                            <td rowspan='$count_subject_data'>$course_data_id</td>
                                            <td rowspan='$count_subject_data'>$course_data_program_section</td>
                                        </tr>";

                                    foreach ($subject_data as $subject) {
                                        $subject_title = $subject['subject_title'];
                                        echo "<tr class='text-center'>
                                                <td>$subject_title</td>
                                            </tr>";
                                    }
                                } else {
                                    echo "<tr class='text-center'>
                                            <td colspan='3'>No data found</td>
                                        </tr>";
                                }
                                ?>
                        </tbody>
                    </table> -->
                </form>
            </div>

        <!-- Grade 11 1st sem REGARDLESS OF STUDENT_sTATUS-->
        <h3 class="text-center">Section Schedule</h3>
        <div class="table-responsive" style="margin-top:5%;"> 
            <form action="customer/controller.php?action=delete" method="POST">  					
                    <?php 
                        // Section Based on the enrollment.
                        $enrollment_school_year = $studentEnroll->GetStudentSectionGradeElevenSchoolYear($username, $userLoggedInId, 11, "First");
                        if($enrollment_school_year !== null){

                            $term = $enrollment_school_year['term'];
                            $period = $enrollment_school_year['period'];
                            $school_year_id = $enrollment_school_year['school_year_id'];
                            echo "
                                <h4 class='mb-3'>Grade 11 $section_name $FIRST_SEMESTER Semester (SY $term) SY-ID $school_year_id</h4>
                            ";
                        }
                    ?> 	
                    <span>Grade 11 1st Semester</span>
                    <table  class="table table-striped table-bordered table-hover "  style="font-size:13px" cellspacing="0"  > 
                        <thead>
                            <tr> 
                                <th rowspan="2">Subject Id</th>
                                <th rowspan="2">Code</th>
                                <th rowspan="2">Title</th>  
                                <th class="text-center" colspan="3">Schedule</th> 
                            </tr>	
                            <tr class="text-center"> 
                                <th>Day</th> 
                                <th>Time</th>
                                <th>Room</th> 
                            </tr>
                        </thead> 	 
                        <tbody>
                            <?php 
                                // $listOfSubjects = $studentEnroll->GetStudentsStrandSubjects($username);
                                $sectionScheduleGradeElevenFirst = $schedule->GetSectionScheduleGradeElevenFirst($username, $student_id, 11, "First");
                                // echo $student_id;
                                if($sectionScheduleGradeElevenFirst != null){
                                    foreach ($sectionScheduleGradeElevenFirst as $key => $value) {

                                        $subject_id = $value['subject_id'];

                                        $query_student_subject = $con->prepare("SELECT subject_id, student_subject_id
                                            FROM student_subject
                                            WHERE subject_id=:subject_id
                                            AND student_id=:student_id
                                            LIMIT 1");

                                        $query_student_subject->bindValue(":subject_id", $subject_id);
                                        $query_student_subject->bindValue(":student_id", $userLoggedInId);
                                        $query_student_subject->execute();
 
                                        $enrolled_status = "N_E";
                                        
                                        if($query_student_subject->rowCount() > 0){
                                            
                                            $row = $query_student_subject->fetch(PDO::FETCH_ASSOC);

                                            $student_subject_subject_id = $row['subject_id'] != "" ? $row['subject_id'] : "";

                                            if($student_subject_subject_id){
                                                if($subject_id == $student_subject_subject_id){
                                                    $enrolled_status = "Enrolled";
                                                }
                                            }
                                        }

                                        $day = $schedule->GetDayFullName($value['schedule_day']);

                                        $schedule_time = $value['schedule_time'] != "" ? $value['schedule_time'] : "TBA";
                                        $room = $value['room'] != "" ? $value['room'] : "TBA";
                                        $subject_id = $value['t1Subject_id'];

                                        echo '<tr>'; 
                                                echo '<td>'.$subject_id.'</td>';
                                                echo '<td>'.$value['subject_code'].'</td>';
                                                echo '<td>'.$value['subject_title'].'</td>';
                                                echo '<td class="text-center">'.$day.'</td>';
                                                echo '<td class="text-center">'.$schedule_time.'</td>';
                                                echo '<td class="text-center">'.$room.'</td>';
                                        echo '</tr>';

                                    }
                                }

                            ?>
                        </tbody>
                    </table>
            </form>
        </div>
        <!-- Grade 11 2nd sem REGARDLESS OF STUDENT_sTATUS -->
        <div class="table-responsive" style="margin-top:5%;"> 
            <form action="customer/controller.php?action=delete" method="POST">  					
                    <?php 
                        // Section Based on the enrollment.
                        $enrollment_school_year = $studentEnroll->GetStudentSectionGradeElevenSchoolYear($username, $userLoggedInId, 11, "Second");

                        if($enrollment_school_year !== null){
                            $term = $enrollment_school_year['term'];
                            $period = $enrollment_school_year['period'];
                            $school_year_id = $enrollment_school_year['school_year_id'];
                            echo "
                                <h4 class='mb-3'>Grade $GRADE_ELEVEN $section_name $SECOND_SEMESTER Semester (SY $term) SY-ID $school_year_id</h4>
                            ";
                        }
                    ?> 	
                    <span>Grade 11 Second Semester</span>
                    <table  class="table table-striped table-bordered table-hover "  style="font-size:13px" cellspacing="0"  > 
                        <thead>
                            <tr> 
                                <th rowspan="2">Subject Id</th>
                                <th rowspan="2">Code</th>
                                <th rowspan="2">Title</th>  
                                <th class="text-center" colspan="3">Schedule</th> 
                            </tr>	
                            <tr class="text-center"> 
                                <th>Day</th> 
                                <th>Time</th>
                                <th>Room</th> 
                            </tr>
                        </thead> 	 
                        <tbody>
                            <?php 
                                $listOfSubjects = $studentEnroll->GetStudentsStrandSubjects($username);
                                $sectionScheduleGradeElevenFirst = $schedule->GetSectionScheduleGradeElevenFirst($username, $student_id, 11, "Second");

                                if($sectionScheduleGradeElevenFirst != null){
                                    foreach ($sectionScheduleGradeElevenFirst as $key => $value) {

                                        $subject_id = $value['subject_id'];

                                        $query_student_subject = $con->prepare("SELECT subject_id, student_subject_id
                                            FROM student_subject
                                            WHERE subject_id=:subject_id
                                            AND student_id=:student_id
                                            LIMIT 1");

                                        $query_student_subject->bindValue(":subject_id", $subject_id);
                                        $query_student_subject->bindValue(":student_id", $userLoggedInId);
                                        $query_student_subject->execute();
 
                                        $enrolled_status = "N_E";
                                        
                                        if($query_student_subject->rowCount() > 0){
                                            
                                            $row = $query_student_subject->fetch(PDO::FETCH_ASSOC);

                                            $student_subject_subject_id = $row['subject_id'] != "" ? $row['subject_id'] : "";

                                            if($student_subject_subject_id){
                                                if($subject_id == $student_subject_subject_id){
                                                    $enrolled_status = "Enrolled";
                                                }
                                            }
                                        }

                                        $day = $schedule->GetDayFullName($value['schedule_day']);

                                        $schedule_time = $value['schedule_time'] != "" ? $value['schedule_time'] : "TBA";
                                        $room = $value['room'] != "" ? $value['room'] : "TBA";
                                        $subject_id = $value['t1Subject_id'];

                                        echo '<tr>'; 
                                                echo '<td>'.$subject_id.'</td>';
                                                echo '<td>'.$value['subject_code'].'</td>';
                                                echo '<td>'.$value['subject_title'].'</td>';
                                                echo '<td class="text-center">'.$day.'</td>';
                                                echo '<td class="text-center">'.$schedule_time.'</td>';
                                                echo '<td class="text-center">'.$room.'</td>';
                                        echo '</tr>';

                                    }
                                }

                            ?>
                        </tbody>
                    </table>
            </form>
        </div>


        <!-- Grade 12 1st sem REGARDLESS OF STUDENT_sTATUS -->
        <div class="table-responsive" style="margin-top:5%;"> 
            <?php 
                // Section Based on the enrollment.
                $enrollment_school_year = $studentEnroll->GetStudentSectionGradeTwelveSchoolYear($username, $userLoggedInId, 12, "First");

                if($enrollment_school_year !== null){
                    $term = $enrollment_school_year['term'];
                    $period = $enrollment_school_year['period'];
                    $school_year_id = $enrollment_school_year['school_year_id'];

                    // <h4 class='mb-3'>Grade 11 $section_name $FIRST_SEMESTER Semester (SY $term) SY-ID $school_year_id</h4>

                    echo "
                        <h4 class='mb-3'>Grade 12 $section_name $FIRST_SEMESTER Semester (SY $term) SY-ID $school_year_id</h4>
                    ";
                }
            ?> 	
            <!-- <span>Grade 11 Second Semester</span> -->
            <table  class="table table-striped table-bordered table-hover "  style="font-size:13px" cellspacing="0"  > 
                <thead>
                    <tr> 
                        <th rowspan="2">Subject Id</th>
                        <th rowspan="2">Code</th>
                        <th rowspan="2">Title</th>  
                        <th class="text-center" colspan="3">Schedule</th> 
                    </tr>	
                    <tr class="text-center"> 
                        <th>Day</th> 
                        <th>Time</th>
                        <th>Room</th> 
                    </tr>
                </thead> 	 
                <tbody>
                    <?php 

                        // $listOfSubjects = $studentEnroll->GetStudentsStrandSubjects($username);
                        $sectionScheduleGradeElevenFirst = $schedule->GetSectionScheduleGradeTwelveFirst($username, $student_id, 12, "First");

                        if($sectionScheduleGradeElevenFirst != null){

                            foreach ($sectionScheduleGradeElevenFirst as $key => $value) {

                                $subject_id = $value['subject_id'];

                                $query_student_subject = $con->prepare("SELECT subject_id, student_subject_id
                                    FROM student_subject
                                    WHERE subject_id=:subject_id
                                    AND student_id=:student_id
                                    LIMIT 1");

                                $query_student_subject->bindValue(":subject_id", $subject_id);
                                $query_student_subject->bindValue(":student_id", $userLoggedInId);
                                $query_student_subject->execute();

                                $enrolled_status = "N_E";
                                
                                if($query_student_subject->rowCount() > 0){
                                    
                                    $row = $query_student_subject->fetch(PDO::FETCH_ASSOC);

                                    $student_subject_subject_id = $row['subject_id'] != "" ? $row['subject_id'] : "";

                                    if($student_subject_subject_id){
                                        if($subject_id == $student_subject_subject_id){
                                            $enrolled_status = "Enrolled";
                                        }
                                    }
                                }

                                $day = $schedule->GetDayFullName($value['schedule_day']);

                                $schedule_time = $value['schedule_time'] != "" ? $value['schedule_time'] : "TBA";
                                $room = $value['room'] != "" ? $value['room'] : "TBA";
                                $subject_id = $value['t1Subject_id'];

                                echo '<tr>'; 
                                        echo '<td>'.$subject_id.'</td>';
                                        echo '<td>'.$value['subject_code'].'</td>';
                                        echo '<td>'.$value['subject_title'].'</td>';
                                        echo '<td class="text-center">'.$day.'</td>';
                                        echo '<td class="text-center">'.$schedule_time.'</td>';
                                        echo '<td class="text-center">'.$room.'</td>';
                                echo '</tr>';

                            }
                        }

                    ?>
                </tbody>
            </table>
        </div>    
        
        
        <!-- Grade 12 2nd sem REGARDLESS OF STUDENT_sTATUS -->
        <div class="table-responsive" style="margin-top:5%;"> 
            <?php 
                // Section Based on the enrollment.
                $enrollment_school_year = $studentEnroll->GetStudentSectionGradeTwelveSchoolYear($username, $userLoggedInId, 12, "Second");

                if($enrollment_school_year !== null){
                    $term = $enrollment_school_year['term'];
                    $period = $enrollment_school_year['period'];
                    $school_year_id = $enrollment_school_year['school_year_id'];

                    // <h4 class='mb-3'>Grade 11 $section_name $FIRST_SEMESTER Semester (SY $term) SY-ID $school_year_id</h4>

                    echo "
                        <h4 class='mb-3'>Grade 12 $section_name $FIRST_SEMESTER Semester (SY $term) SY-ID $school_year_id</h4>
                    ";
                }
            ?> 	
            <!-- <span>Grade 11 Second Semester</span> -->
            <table  class="table table-striped table-bordered table-hover "  style="font-size:13px" cellspacing="0"  > 
                <thead>
                    <tr> 
                        <th rowspan="2">Subject Id</th>
                        <th rowspan="2">Code</th>
                        <th rowspan="2">Title</th>  
                        <th class="text-center" colspan="3">Schedule</th> 
                    </tr>	
                    <tr class="text-center"> 
                        <th>Day</th> 
                        <th>Time</th>
                        <th>Room</th> 
                    </tr>
                </thead> 	 
                <tbody>
                    <?php 

                        // $listOfSubjects = $studentEnroll->GetStudentsStrandSubjects($username);
                        $sectionScheduleGradeElevenFirst = $schedule->GetSectionScheduleGradeTwelveFirst($username, $student_id, 12, "Second");

                        if($sectionScheduleGradeElevenFirst != null){

                            foreach ($sectionScheduleGradeElevenFirst as $key => $value) {

                                $subject_id = $value['subject_id'];

                                $query_student_subject = $con->prepare("SELECT subject_id, student_subject_id
                                    FROM student_subject
                                    WHERE subject_id=:subject_id
                                    AND student_id=:student_id
                                    LIMIT 1");

                                $query_student_subject->bindValue(":subject_id", $subject_id);
                                $query_student_subject->bindValue(":student_id", $userLoggedInId);
                                $query_student_subject->execute();

                                $enrolled_status = "N_E";
                                
                                if($query_student_subject->rowCount() > 0){
                                    
                                    $row = $query_student_subject->fetch(PDO::FETCH_ASSOC);

                                    $student_subject_subject_id = $row['subject_id'] != "" ? $row['subject_id'] : "";

                                    if($student_subject_subject_id){
                                        if($subject_id == $student_subject_subject_id){
                                            $enrolled_status = "Enrolled";
                                        }
                                    }
                                }

                                $day = $schedule->GetDayFullName($value['schedule_day']);

                                $schedule_time = $value['schedule_time'] != "" ? $value['schedule_time'] : "TBA";
                                $room = $value['room'] != "" ? $value['room'] : "TBA";
                                $subject_id = $value['t1Subject_id'];

                                echo '<tr>'; 
                                        echo '<td>'.$subject_id.'</td>';
                                        echo '<td>'.$value['subject_code'].'</td>';
                                        echo '<td>'.$value['subject_title'].'</td>';
                                        echo '<td class="text-center">'.$day.'</td>';
                                        echo '<td class="text-center">'.$schedule_time.'</td>';
                                        echo '<td class="text-center">'.$room.'</td>';
                                echo '</tr>';

                            }
                        }

                    ?>
                </tbody>
            </table>
        </div>      

        <h3 class='text-center'>List of Strand Subject</h3>
        <div class="table-responsive" style="margin-top:5%;"> 
            <form action="customer/controller.php?action=delete" method="POST">  					
                    <table  class="table table-striped table-bordered table-hover "  style="font-size:13px" cellspacing="0"  > 
                        <thead>
                            <tr> 
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
                                $listOfSubjects = $studentEnroll->GetStudentsStrandSubjects($username);
                                $student_program_id = $studentEnroll->GetStudentProgramId($course_main_id);
                                
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
            </form>
        </div>
        </body>
        </html>

       
   
    <?php
?>