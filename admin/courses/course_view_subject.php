<?php 
    include('../registrar_enrollment_header.php');
    include('../../enrollment/classes/StudentEnroll.php');
    include('../../enrollment/classes/Section.php');
    include('../../enrollment/classes/Schedule.php');
    include('../classes/Course.php');

    
    if(!AdminUser::IsRegistrarAuthenticated()){
        header("location: /dcbt/registrarLogin.php");
        exit();
    }

    $studentEnroll = new StudentEnroll($con);
    $schedule = new Schedule($con, $studentEnroll);

    $createUrl = base_url . "/create.php";

    $school_year_obj = $studentEnroll->GetActiveSchoolYearAndSemester();

    $current_school_year_id = $school_year_obj['school_year_id'];
    $current_school_year_term = $school_year_obj['term'];
    $current_school_year_period = $school_year_obj['period'];

    if(isset($_GET['id'])){

            $course_id = $_GET['id'];

            $section = new Section($con, $course_id);

            $section_name = $section->GetSectionName();
            $section_s_y = $section->GetSectionSY();
            $section_level = $section->GetSectionGradeLevel();
            $section_advisery = $section->GetSectionAdvisery();

            $strand_name = "";
                    
            if (preg_match('/^[a-zA-Z]+/', $section_name, $matches)) {
                $strand_name = $matches[0];
                $strand_name; // Output: "STEM" or "HUMMS"
            }


            if(isset($_POST['remove_schedule_btn']) &&
                isset($_POST['subject_schedule_id'])){

                $subject_schedule_id = $_POST['subject_schedule_id'];


                $wasSuccess = $schedule->RemoveSchedule($subject_schedule_id);
                if($wasSuccess){
                    
                    AdminUser::remove("Schedule Id: $subject_schedule_id has been removed",
                        "course_view_subject.php?id=$course_id");
                    exit();
                }
            }
            
        ?>
        <div class="row col-md-12">
                    <h2 class="text-center page-header">First Semester</h2>
                <div class="col-md-12 offset-md-0">
                    <div class="table-responsive" style="margin-top:2%;"> 
                        <!-- <div class="mb-3">
                            <a href="<?php echo $createUrl?>">
                                <button class="btn btn-success">Add Strand</button>
                            </a>  
                        </div> -->
                        <!-- <h5>Grade 11</h5> -->
                        <table  class="table table-bordered table-hover "  style="overflow-y: auto;
                            font-size: 13px;
                            min-width: 700px;" cellspacing="0"  > 
                            <thead>
                                <tr class="text-center"> 
                                    <th rowspan="2">Course Description</th>  
                                    <th rowspan="2">Units</th>  
                                    <th rowspan="2">Class No / Sec</th>  
                                    <th rowspan="2">Days</th>  
                                    <th rowspan="2">Time</th>  
                                    <th rowspan="2">Room</th>  
                                    <th rowspan="2">Instructor</th>  
                                    <th rowspan="2"></th>  
                                </tr>	
                            </thead> 	
                            <tbody>

                                <?php 
                                   
                                    $subject_query = $con->prepare("SELECT 
                                    
                                        t1.*, t2.*, t3.firstname, t3.lastname,

                                        t4.schedule_time, t4.schedule_day, t4.time_from,t4.time_to,
                                        t4.room as t4Room, t4.subject_schedule_id
                                    
                                        FROM subject as t1

                                        LEFT JOIN course as t2  ON t2.course_id = t1.course_id
                                        LEFT JOIN teacher as t3  ON t3.teacher_id = t2.adviser_teacher_id

                                        LEFT JOIN subject_schedule as t4  ON t4.subject_id = t1.subject_id
                                        
                                        WHERE t1.course_id=:course_id

                                        
                                        ");

                                    $subject_query->bindValue(":course_id", $course_id);
                                    $subject_query->execute();

                                    // $subjects = $subject_query->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_ASSOC);

                                    $previous_title = '';
                                    if($subject_query->rowCount() > 0){

                                        while ($row = $subject_query->fetch(PDO::FETCH_ASSOC)) {

                                            $subject_title = $row['subject_title'];
                                            $subject_schedule_id = $row['subject_schedule_id'];
                                            $course_id = $row['course_id'];
                                            $unit = $row['unit'];
                                            $program_section = $row['program_section'];
                                            $subject_id = $row['subject_id'];

                                            $ref_subject_id = $subject_id;
                                            $room = $row['room'];
                                            // $room = $row['t4Room'];

                                            // $cont = $subject_id . "/" . $program_section;

                                            $url = "add_subject_schedule.php?id=$subject_id&cid=$course_id";

                                            
                                            $edit_url = "<a href='edit_subject_schedule.php?id=$subject_schedule_id'>
                                                        <button class='btn btn-sm btn-primary'>Edit</button></a>    
                                            ";

                                            if ($subject_title == $previous_title) {
                                                $subject_title = '';
                                                $unit = '';
                                                $program_section = '';
                                                $ref_subject_id = '';
                                                $url = "";

                                            } else {
                                                $previous_title = $subject_title;
                                            }

                                            $firstname = $row['firstname'];
                                            $lastname = $row['lastname'];

                                            $time_from = $row['time_from'];
                                            $time_to = $row['time_to'];

                                            $teacher_fullname = $firstname . " " . $lastname;


                                            $schedule_day = $row['schedule_day'];
                                            $schedule_time = $row['schedule_time'];

                                            $schedule_time = $schedule_time == "" ? "N/A" : $schedule_time;
                                            $schedule_day = $schedule_day == "" ? "N/A" : $schedule_day;

                                            $btn_form = "";

                                            if($subject_schedule_id != ''){
                                                $btn_form = "
                                                   <form method='POST'>
                                                        <input type='hidden' name='subject_schedule_id' value='$subject_schedule_id'>
                                                        <button type='submit' name='remove_schedule_btn' class='btn btn-sm btn-danger'>
                                                            <i class='fas fa-times'></i>
                                                        </button> 
                                                    </form>
                                                ";
                                            }
                                            echo "<tr class='text-center'>";
                                                echo "<td>
                                                    <a style='text-decoration: none;' href='$url'>
                                                        $subject_title
                                                    </a>
                                                </td>";
                                                echo "<td>$unit</td>";
                                                echo "<td>$ref_subject_id   $program_section</td>";
                                                echo "<td>$schedule_day</td>";
                                                echo "<td>$time_from - $time_to</td>";
                                                echo "<td>$room</td>";
                                                echo "<td>$teacher_fullname</td>";
                                                echo "<td>
                                                    $edit_url
                                                    $btn_form
                                                </td>";
                                            echo "</tr>";
                                        }
                                    }
                                ?>
                            </tbody>
                        </table>


                        <br>
                        <br>

        <h5 class='text-center'>
    <?php echo $section_name . ' ' . $current_school_year_period . ' Semester (SY: ' . $current_school_year_term . ')';?>
</h5>


                        <div class="container mb-3 text-center">
                            <div class="row">
                                <div class="col-sm-6">
                                <div class="form-group">
                                    <label>Strand:</label>
                                    <a class="form-control-static"><?php echo $strand_name;?></a>
                                </div>
                                <div class="form-group">
                                    <label>Grade:</label>
                                    <a class="form-control-static">Grade <?php echo $section_level;?></a>
                                </div>
                                <div class="form-group">
                                    <label>Section:</label>
                                    <a class="form-control-static"><?php echo $section_name;?></a>
                                </div>
                                </div>
                                <div class="col-sm-6">
                                <div class="form-group">
                                    <label>Adviser:</label>
                                    <a class="form-control-static"><?php echo $section_advisery;?></a>
                                </div>
                                <div class="form-group">
                                    <label>School Year:</label>
                                    <a class="text-dark form-control-static"><?php echo $current_school_year_term;?></a>
                                </div>
                                <div class="form-group">
                                    <label>Term:</label>
                                    <a class="text-dark form-control-static"><?php echo $current_school_year_period;?></a>
                                </div>
                                </div>
                            </div>
                        </div>
                    <table class="table table-striped table-bordered table-hover" style="font-size:14px" cellspacing="0">

                        <thead>
                            <tr class="text-center">
                            <th rowspan="2">Subject</th>
                            <th rowspan="2">Monday</th>
                            <th rowspan="2">Tuesday</th>
                            <th rowspan="2">Wednesday</th>
                            <th rowspan="2">Thursday</th>
                            <th rowspan="2">Friday</th>
                            <th rowspan="2">Room</th>
                            <th rowspan="2">Teacher</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php
                            $subject_query = $con->prepare(" SELECT 
                                    t1.subject_id,
                                    t1.subject_code,
                                    t1.subject_title, t1.course_id,
                                    t2.schedule_day,
                                    t2.schedule_time, t2.room,
                                    t2.subject_schedule_id,
                                    t2.time_from,
                                    t2.time_to,

                                    t3.firstname,
                                    t3.lastname

                                FROM subject as t1
                                LEFT JOIN subject_schedule as t2 ON t1.subject_id = t2.subject_id
                                LEFT JOIN teacher as t3 ON t3.teacher_id = t2.teacher_id

                                WHERE t1.course_id=:course_id 
                                -- AND t1.semester=:semester

                            ");

                            $subject_query->bindValue("course_id", $course_id); 
                            // $subject_query->bindValue("semester", $period); 
                            $subject_query->execute();

                            $subjects = $subject_query->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_ASSOC);

                            // print_r($subjects);
                            // if(false){
                            //     print_r($subjects);
                            //   foreach ($subjects as $subject_id => $subject_data) {

                            //     echo '<tr class="text-center">';
                            //     echo '<td rowspan="'.count($subject_data).'">'.$subject_data[0]['subject_title'].'</td>';

                            //     $first = true;
                            //     foreach ($subject_data as $subject_schedule) {

                            //         // print_r($subject_schedule);
                            //       if ($first) {
                            //         $first = false;
                            //       } else {
                            //         echo '<tr class="text-center">';
                            //       }

                            //       echo '<td>'.($subject_schedule['schedule_day'] === 'M' ? $subject_schedule['schedule_time'] : '').'</td>';
                            //       echo '<td>'.($subject_schedule['schedule_day'] === 'T' ? $subject_schedule['schedule_time'] : '').'</td>';
                            //       echo '<td>'.($subject_schedule['schedule_day'] === 'W' ? $subject_schedule['schedule_time'] : '').'</td>';
                            //       echo '<td>'.($subject_schedule['schedule_day'] === 'TH' ? $subject_schedule['schedule_time'] : '').'</td>';
                            //       echo '<td>'.($subject_schedule['schedule_day'] === 'F' ? $subject_schedule['schedule_time'] : '').'</td>';
                            //       echo '</tr>';
                            //     }
                            //   }
                            // }

                            $merged_data = array();

                            // Loop through each subject
                            foreach ($subjects as $subject_id => $subject_data) {

                                // Loop through each schedule for the subject
                                foreach ($subject_data as $subject_schedule) {

                                    // print_r($subject_schedule);
                                    
                                    // Create a new array for the subject if it doesn't exist
                                    if (!isset($merged_data[$subject_id])) {
                                        $merged_data[$subject_id] = array(
                                            'subject_title' => $subject_schedule['subject_title'],
                                            'course_id' => $subject_schedule['course_id'],
                                            'subject_id' => $subject_id,
                                            
                                            'room' => $subject_schedule['room'],
                                            'subject_schedule_id' => $subject_schedule['subject_schedule_id'],
                                            'firstname' => $subject_schedule['firstname'],
                                            'lastname' => $subject_schedule['lastname'],
                                            'Monday' => array(),
                                            'Monday_URL' => array(),
                                            'Tuesday' => array(),
                                            'Tuesday_URL' => array(),
                                            'Wednesday' => array(),
                                            'Wednesday_URL' => array(),
                                            'Thursday' => array(),
                                            'Thursday_URL' => array(),
                                            'Friday' => array(),
                                            'Friday_URL' => array()
                                        );
                                    }

                                    // Add the schedule time to the corresponding day's array
                                    switch ($subject_schedule['schedule_day']) {

                                        case 'M':
                                            $merged_data[$subject_id]['Monday'][] = $subject_schedule['schedule_time'];
                                            $merged_data[$subject_id]['Monday_URL'][] = $subject_schedule['subject_schedule_id'];
                                            break;
                                        case 'T':
                                            $merged_data[$subject_id]['Tuesday'][] = $subject_schedule['schedule_time'];
                                            $merged_data[$subject_id]['Tuesday_URL'][] = $subject_schedule['subject_schedule_id'];

                                            break;
                                        case 'W':
                                            $merged_data[$subject_id]['Wednesday'][] = $subject_schedule['schedule_time'];
                                            $merged_data[$subject_id]['Wednesday_URL'][] = $subject_schedule['subject_schedule_id'];

                                            break;
                                        case 'TH':
                                            $merged_data[$subject_id]['Thursday'][] = $subject_schedule['schedule_time'];
                                            $merged_data[$subject_id]['Thursday_URL'][] = $subject_schedule['subject_schedule_id'];
                                            break;
                                        case 'F':
                                            $merged_data[$subject_id]['Friday'][] = $subject_schedule['schedule_time'];
                                            $merged_data[$subject_id]['Friday_URL'][] = $subject_schedule['subject_schedule_id'];
                                            break;
                                    }
                                }
                            }

                            // Loop through the merged data and output the final table
                            foreach ($merged_data as $subject_data) {

                                $subject_schedule_id = $subject_data['subject_schedule_id'];
                                $course_id = $subject_data['course_id'];
                                $subject_id = $subject_data['subject_id'];
                                // $subject_schedule_id = $subject_data['schedule_time'];

                                // echo $subject_schedule_id;

                                $im = implode('<br>', $subject_data['Monday']);

                                // $edit_url = "add_subject_schedule.php?id=$subject_schedule_id";
                                $add_url = "add_subject_schedule.php?id=$subject_id&cid=$course_id";

                                echo '<tr class="text-center">';

                                echo '<td>
                                    <a class="text-dark" href="'.$add_url.'">
                                        '.$subject_data['subject_title'].'
                                    </a>
                                </td>';

                                echo '<td>
                                        <a class="text-muted" href="edit_subject_schedule.php?id='.implode('<br>', $subject_data['Monday_URL']).'">
                                            '.implode('<br>', $subject_data['Monday']).'
                                        </a>
                                    </td>';
                                echo'<td>
                                        <a class="text-muted" href="edit_subject_schedule.php?id='.implode('<br>', $subject_data['Tuesday_URL']).'">
                                            '.implode('<br>', $subject_data['Tuesday']).'
                                        </a>
                                    </td>';
                                echo '<td>
                                        <a class="text-muted" href="edit_subject_schedule.php?id='.implode('<br>', $subject_data['Wednesday_URL']).'">
                                            '.implode('<br>', $subject_data['Wednesday']).'
                                        </a>
                                    </td>';
                                echo '<td>
                                        <a class="text-muted"  href="edit_subject_schedule.php?id='.implode('<br>', $subject_data['Thursday_URL']).'">
                                            '.implode('<br>', $subject_data['Thursday']).'
                                        </a>
                                    </td>';
                                echo '<td>
                                    <a class="text-muted"  href="edit_subject_schedule.php?id='.implode('<br>', $subject_data['Friday_URL']).'">
                                        '.implode('<br>', $subject_data['Friday']).'
                                    </a>
                                </td>';
                                echo '<td>'.$subject_data['room'].'</td>';
                                echo '<td>'.$subject_data['firstname'].' '.$subject_data['lastname'].'</td>';
                                echo '</tr>';
                            }
                                
                            if (empty($subjects)) {
                                echo '<tr class="text-center">';
                                echo '<td colspan="6">No data found</td>';
                                echo '</tr>';
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

