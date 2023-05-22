<script src="../assets/js/common.js"></script>

<?php 

    include('../admin_enrollment_header.php');
    include('../../enrollment/classes/StudentEnroll.php');
    include('../../enrollment/classes/Schedule.php');

    $studentEnroll = new StudentEnroll($con);
    $schedule = new Schedule($con, $studentEnroll);
  
    // The Section should have a list of Subject
    if(isset($_GET['id'])){
        $course_id = $_GET['id'];
    ?>
        <!-- GRADE 11 First Semester -->
        <div class="row col-md-12">
            <div class="col-lg-12">
                <div class="row">
                    <div class="col-lg-12 text-center">
                        <h3 class="page-header">Schedule for Grade 11</h3>
                    </div>
                </div>
            </div>
            <div class="table-responsive" style="margin-top:5%;"> 
                <form method="POST">  					
                        <?php 
                            $first_semester_period = "First";
                            $school_year_obj = $schedule->GetSectionSchoolYearByCourseId($course_id, $first_semester_period);
                            
                            if($school_year_obj != null){
                                $term = $school_year_obj['term'];
                                $period = $school_year_obj['period'];
                                $school_year_id = $school_year_obj['school_year_id'];

                                echo "
                                    <h4 class='mb-3'>Grade 11 $period Semester (SY $term) SY-ID $school_year_id</h4>
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
                                    // $listOfSubjects = $studentEnroll->GetStudentsStrandSubjects($username);

                                    $sectionScheduleGradeElevenFirst = $schedule->GetSectionScheduleGradeElevenFirstByCourseId($course_id, "First");

                                    if($sectionScheduleGradeElevenFirst !== null){
                                        foreach ($sectionScheduleGradeElevenFirst as $key => $value) {

                                            $subject_id = $value['subject_id'];

                                            $day = $schedule->GetDayFullName($value['schedule_day']);

                                            $schedule_time = $value['schedule_time'] != "" ? $value['schedule_time'] : "TBA";
                                            $room = $value['room'] != "" ? $value['room'] : "TBA";
                                            // $subject_id = $value['t1Subject_id'];


                                            $url = "http://localhost/elms/admin/schedule/create.php";

                                            if($schedule_time == "TBA"){

                                                $schedule_time = "
                                                    <a href='$url' class='btn btn-primary btn-sm'>
                                                        Add Sched
                                                    </a>
                                                ";
                                            }
                                            echo '<tr>'; 
                                                    echo '<td>'.$value['subject_id'].'</td>';
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


                    <!-- GRADE 11 Second Semester -->
  
        <div class="table-responsive" style="margin-top:5%;"> 
            <form method="POST">  					
                    <?php 
                        $second_semester_period = "Second";
                        $school_year_obj = $schedule->GetSectionSchoolYearByCourseId($course_id, $second_semester_period);

                        if($school_year_obj != null){
                            $term = $school_year_obj['term'];
                            $period = $school_year_obj['period'];
                            $school_year_id = $school_year_obj['school_year_id'];
                            echo "
                                <h4 class='mb-3'>Grade 11 $period Semester (SY $term) SY-ID $school_year_id</h4>
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
                                // $listOfSubjects = $studentEnroll->GetStudentsStrandSubjects($username);

                                $sectionScheduleGradeElevenFirst = $schedule->GetSectionScheduleGradeElevenFirstByCourseId($course_id, "Second");

                                if($sectionScheduleGradeElevenFirst !== null){
                                    foreach ($sectionScheduleGradeElevenFirst as $key => $value) {

                                        $subject_id = $value['subject_id'];

                                        $day = $schedule->GetDayFullName($value['schedule_day']);

                                        $schedule_time = $value['schedule_time'] != "" ? $value['schedule_time'] : "TBA";
                                        $room = $value['room'] != "" ? $value['room'] : "TBA";
                                        // $subject_id = $value['t1Subject_id'];


                                        $url = "http://localhost/elms/admin/schedule/create.php";

                                        if($schedule_time == "TBA"){

                                            $schedule_time = "
                                                <a href='$url' class='btn btn-primary btn-sm'>
                                                    Add Sched
                                                </a>
                                            ";
                                        }
                                        echo '<tr>'; 
                                                echo '<td>'.$value['subject_id'].'</td>';
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

        <!-- GRADE 12 First Semester -->
        <!-- GRADE 12 Second Semester -->
        </div>



    <?php
    }
    else{
        echo "Not found";
        return;
    }
?>

