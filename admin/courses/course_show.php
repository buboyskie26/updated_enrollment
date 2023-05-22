 
<?php 

    include('../admin_enrollment_header.php');
    include('../../enrollment/classes/StudentEnroll.php');
    include('../../enrollment/classes/Schedule.php');
    include('../../enrollment/classes/Section.php');

    $enroll = new StudentEnroll($con);
    $schedule = new Schedule($con, $enroll);
   
    // The Section should have a list of Subject
    if(isset($_GET['id'])){

        $course_id = $_GET['id'];

        $section = new Section($con, $course_id);

        $userLoggedInId = "";
        $username = "";
        $student_id  = "";
        $school_year_obj = $enroll->GetActiveSchoolYearAndSemester();
        $current_school_year_id = $school_year_obj['school_year_id'];
        $current_school_year_term = $school_year_obj['term'];
        $current_school_year_period = $school_year_obj['period'];

        // Grade 12 STEM12-A 2024-2025 1st Semester Subjects Schedule
        // Grade 12 STEM12-A 2024-2025 2nd Semester
    
        $section_name = $section->GetSectionName();

        ?>
        <!-- Grade 12 1st sem REGARDLESS OF STUDENT_sTATUS -->
            <div class="row col-md-12">
                <div class="row mb-3">
                    <!-- <div class="col">
                        <h6 class="text-center">Select School Year Term:</h6>
                    </div> -->
                    <h6 class="text-center">Select School Year Term:</h6>

                    <div class="col-6 offset-md-2">
                        <form method="POST">

                            <input type="hidden" value="<?php echo $course_id;?>" id="course_id">
                            <select name="select_school_year_id" id="select_school_year_id" class="form-select" aria-label="School Year Term">
                             <?php
                                $query = $con->prepare("SELECT DISTINCT e.school_year_id, sy.period
                                    FROM enrollment e

                                    INNER JOIN school_year sy ON e.school_year_id = sy.school_year_id
                                    WHERE e.course_id = :course_id
                                    -- AND sy.period = 11
                                ");

                                $query->bindValue(":course_id", $course_id);
                                $query->execute();

                                echo "<option selected>Select School Year</option>";
                                while($row = $query->fetch(PDO::FETCH_ASSOC)) {
                                    echo "<option value='" . $row['school_year_id'] . "'>" . $row['period'] . "</option>";
                                }
                            ?>     
                            </select>

                        </form>
                    </div>
                </div>

                <div class="table-responsive" style="margin-top:5%;"> 

                    <!-- <?php 
                        // Section Based on the enrollment.

                        $FIRST_SEMESTER = "First";
                        // echo "eewqw";
                        $enrollment_school_year = $schedule->GetStudentSectionGradeTwelveSchoolYear($course_id, 12, "First");
                        if($enrollment_school_year !== null){
                            $term = $enrollment_school_year['term'];
                            $period = $enrollment_school_year['period'];
                            $school_year_id = $enrollment_school_year['school_year_id'];

                            echo "
                                <a href='section_students.php?course_id=$course_id&sy_id=$school_year_id'>
                                    <button class='btn btn-sm btn-success'>Show Students</button>
                                </a>
                                <h4 class='mb-3'>Grade 12 $section_name $FIRST_SEMESTER Semester (SY $term) SY-ID $school_year_id</h4>
                            ";
                        }
                    ?>  -->


                    <div id="insert_table"></div>
                    <!-- <table  class="table table-striped table-bordered table-hover "  style="font-size:13px" cellspacing="0"  > 
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

                                $courseSchedule = $schedule->GetSectionScheduleByCourseId($course_id, "First");
                                if($schedule != null){

                                    foreach ($courseSchedule as $key => $value) {

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
                    </table> -->
                </div>  

                
                <!-- Grade 12 2nd sem REGARDLESS OF STUDENT_sTATUS -->
                <!-- <div class="table-responsive" style="margin-top:5%;"> 
                    <?php 

                        // Section Based on the enrollment.
                        $section_name = $section->GetSectionName();

                        $FIRST_SEMESTER = "First";
                        // echo "eewqw";
                        $enrollment_school_year = $schedule->GetStudentSectionGradeTwelveSchoolYear($course_id, 12, "Second");
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

                                // $sectionScheduleGradeElevenFirst = $schedule->GetSectionScheduleGradeTwelveFirst($username, $student_id, 12, "Second");

                                $courseSchedule = $schedule->GetSectionScheduleByCourseId($course_id, "Second");
                                if($schedule != null){

                                    foreach ($courseSchedule as $key => $value) {

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
                </div>     -->

                
            </div>
        <?php
    }
?>


<script>
$(document).ready(function() {

        $('#select_school_year_id').on('change', function() {

        var school_year_id = parseInt($(this).val());
        if(!school_year_id){
            // school_year_id = "nothing";
        }
        var course_id = parseInt($("#course_id").val());

        sessionStorage.setItem('selected_school_year_id', school_year_id);

        var selectedYear = $("#select_school_year_id").val();
        if(selectedYear == "Select School Year") {
            selectedYear = null;
        }

        $.ajax({
            url: '../ajax/section/get_section.php',
            type: 'POST',
            data: {
                course_id: course_id,
                school_year_id: school_year_id
            },
            dataType: 'json',
            success: function(response) {
                
                // console.log(response)
                if(response.length == 0) {
                    console.log("empty");
                    $('#insert_table').empty(); 
                }
                else{
                    // console.log(response)
                     var section_name = `<?php
                        echo $section_name;
                    ?>`;
                    
                    var html = `
                        <h5 class='text-center'>${section_name} ${response[0].current_semester_period} Semester (SY: ${response[0].current_term}) </h5> 
                        <a href='section_students.php?course_id=${course_id}&sy_id=${school_year_id}'>
                                <button class='btn btn-sm btn-success'>Show Students</button>
                        </a>
                        <table  class="table table-striped table-bordered table-hover "  style="font-size:13px" cellspacing="0"  > 
                            <thead>
                                <tr class='text-center'> 
                                    <th rowspan="2">Subject Id</th>
                                    <th rowspan="2">Code</th>
                                    <th rowspan="2">Title</th>  
                                    <th class="text-center" colspan="3">Schedule</th> 
                                </tr>	
                                <tr class="text-center"> 
                                    <th>Day</th> 
                                    <th>Time</th>
                                    <th>Room</th> 
                                    <th>Teacher</th> 
                                </tr>
                            </thead>
                    `;
                    // var rows = ``;
                    $.each(response, function(index, value) {

                        var teacherFirstName = value.teacherFirstName ?  value.teacherFirstName : '';
                        var teacherLastName = value.teacherLastName ?  value.teacherLastName : '';
                        var result = "TBA";

                        if(teacherFirstName != '' && teacherLastName != ''){
                            result = teacherFirstName + ' ' + teacherLastName;
                        }
                        
                        html += `
                            <body>
                                <tr class='text-center'>
                                    <td>${value.subject_id}</td>
                                    <td>${value.subject_code}</td>
                                    <td>${value.subject_title}</td>
                                    <td>${value.schedule_day ? value.schedule_day : 'TBA'}</td>
                                    <td>${value.schedule_time ? value.schedule_time : `<a href="http://localhost/dcbt/admin/schedule/create.php">
                                            <button class='btn btn-sm btn-primary'>Add</button>
                                        </a>`}</td>
                                    <td>${value.room ? value.room : 'TBA'}</td> 
                                    <td>${result}</td>


                                    <td>
                                        <a href='<?php echo directoryPath; ?>subject_section.php?section_id=${course_id}&subject_id=${value.subject_id}'>
                                            <button class='btn btn-sm btn-primary'>
                                                Edit Subject Section
                                            </button>
                                        </a> 
                                    </td>

                                </tr>
                            </body>
                        `;
                    });

                    html += `
                        </table>
                    `;
                    $('#insert_table').html(html); 
                }
            },
        });
    });
});
 
</script>
