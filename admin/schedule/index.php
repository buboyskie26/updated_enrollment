<?php 

    include('../admin_enrollment_header.php');
    include('../../admin/classes/Course.php');
    include('../../enrollment/classes/StudentEnroll.php');

    $createUrl = base_url . "/create.php";

    $course = new Course($con, null);
    $studentEnroll = new StudentEnroll($con);

    $school_year_obj = $studentEnroll->GetActiveSchoolYearAndSemester();

    $current_school_year_id = $school_year_obj['school_year_id'];
    $current_school_year_term = $school_year_obj['term'];
    $current_school_year_period = $school_year_obj['period'];
?>

<div class="row col-md-12">
            <h2 class="text-center page-header">Schedule List</h2>
            <!-- <a href="<?php echo $createUrl?>">
                <button class="btn btn-sm btn-success">Add Schedule</button>
            </a>     -->
        <div class="col-md-10 offset-md-1">
            <div class="table-responsive" style="margin-top:2%;"> 
                <div class="mb-3">
              
                    <a href="<?php echo $createUrl?>">
                        <button class="btn btn-success">Add Schedule</button>
                    </a>  
                </div>
                <table  class="table table-striped table-bordered table-hover "  style="font-size:13px" cellspacing="0"  > 
                    <thead>
                        <tr class="text-center"> 
                            <th rowspan="2" width="20%">Program Section</th>  
                            <th rowspan="2" width="20%">Time</th>
                            <th rowspan="2">Days</th>
                            <th rowspan="2"width="20%">Subject</th>
                            <th rowspan="2"width="15%">Grade Level</th>  
                            <th rowspan="2">Room</th>
                            <th rowspan="2" width="20%">S.Y</th>  
                            <th rowspan="2">Teacher</th>  
                            <th rowspan="2">Action</th>  
                        </tr>	
                    </thead> 	
                    <tbody>
                        <?php 
                            $username = "";
                            // $sectionScheduleGradeElevenFirst = $schedule->GetSectionScheduleGradeElevenFirst($username, $student_id, 11, "First");

                            $query = $con->query("SELECT t1.* ,

                                    t2.term, t2.period, 

                                    t3.subject_title,

                                    t5.program_section, t5.course_id, t5.course_level,

                                    t4.firstname, t4.lastname
                            
                                    FROM subject_schedule as t1

                                    LEFT JOIN school_year as t2 ON t2.school_year_id = t1.school_year_id
                                    LEFT JOIN subject as t3 ON t3.subject_id = t1.subject_id
                                    LEFT JOIN teacher as t4 ON t4.teacher_id = t1.teacher_id
                                    LEFT JOIN course as t5 ON t5.course_id = t1.course_id

                                ");

                            // $query->bindValue("");
                            $query->execute();

                            if($query->rowCount() > 0){
                                while ($row = $query->fetch(PDO::FETCH_ASSOC)) {

                                    $program_section = $row['program_section'];
                                    $term = $row['term'];
                                    $period = $row['period'];
                                    $firstname = $row['firstname'];
                                    $lastname = $row['lastname'];
                                    $fullname = $firstname . " " . $lastname;
                                    $schedule_time = $row['schedule_time'];
                                    $schedule_day = $row['schedule_day'];
                                    $schedule_day = $row['schedule_day'];
                                    $subject_title = $row['subject_title'];
                                    $course_level = $row['course_level'];
                                    $room = $row['room'];
                                    $term = $row['term'];
                                    $period = $row['period'];

                                    echo "<tr class='text-center'>";
                                        echo "
                                            <td>
                                                <a href=''>
                                                    " . $program_section . "    
                                                </a>
                                            </td>
                                        ";
                                        echo "<td>$schedule_time</td>";
                                        echo "<td>$schedule_day</td>";
                                        echo "<td>$subject_title</td>";
                                        echo "<td>Grade $course_level</td>";
                                        echo "<td>$room</td>";
                                        echo "<td>($term - $period Semester)</td>";
                                        echo "<td>$fullname</td>";
                                        echo "<td>
                                        </td>";
                                         
                                    echo "</tr>";
                                }
                            }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="table-responsive" style="margin-top:2%;"> 
                            <h4 class="text-center">S.Y <?php echo $current_school_year_term;?></h4>
            <label style="margin-left: 6px;" for="">Filter Here</label>
            <?php 
            
                echo $course->GetCourseAvailableSelectionForCurrentSY();
            ?>
            <!-- <table  class="table table-striped table-bordered table-hover "  style="font-size:13px" cellspacing="0"  > 
                <thead>
                    <tr class="text-center"> 
                        <th>Subject</th>
                        <th>Room</th>
                        <th>Day</th>
                        <th>Time</th>
                    </tr>	
                </thead> 	
                <tbody>
                    <?php 
                    
                    ?>
                </tbody>
            </table> -->


            <div style="margin-bottom: 15px;" id="insert_table"></div>
            
            <hr>
            <hr>
        </div>                 
</div>


<script>
    $('#create_student_course_id').on('change', function() {

        var course_id = $("#create_student_course_id").val();


        $.ajax({
            url: '../ajax/schedule/get_schedule_section.php',
            type: 'POST',
            data: {
                course_id
            },
            dataType: 'json',
            success: function(response) {
                 console.log(response);



                $('#insert_table').html("");

                if(response.length > 0){

                    var html = `
                        <table id="res" class="mt-4 table table-striped table-bordered table-hover "  style="font-size:13px" cellspacing="0"  > 
                            <thead>
                                <tr class='text-center'> 
                                    <th rowspan="2">Subject</th>
                                    <th rowspan="2">Room</th>
                                    <th rowspan="2">Day</th>
                                    <th rowspan="2">Time</th>
                                </tr>	
                            </thead>
                        `;
            
                    $.each(response, function(index, value) {

                        var subject_title = value.subject_title;
                        var schedule_day = value.schedule_day;
                        var schedule_time = value.schedule_time;
                        var time_from = value.time_from;
                        var time_to = value.time_to;
                        var room = value.room;

                        html += `
                            <body>
                                <tr class='text-center'>
                                    <td>${subject_title}</td>
                                    <td>${room}</td>
                                    <td>${schedule_day}</td>
                                    <td>${schedule_time}</td>
                                </tr>
                            `;
                    });

                    html += `
                        </table>
                    `;
                    $('#insert_table').html(html);
                }
                else{
                    var nothing = `
                        <div class="alert alert-warning text-center" role="alert">
                            No schedule found
                        </div>
                    `;
                    $('#insert_table').html(nothing);
                }
            }
        });
    });
</script>
 