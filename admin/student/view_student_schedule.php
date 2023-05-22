<?php 

    include('../registrar_enrollment_header.php');
    include('../../enrollment/classes/StudentEnroll.php');
    include('../../enrollment/classes/Section.php');
    include('../../enrollment/classes/OldEnrollees.php');
    include('../classes/Course.php');


        if(isset($_GET['id']) && isset($_GET['yid'])){

        $enroll = new StudentEnroll($con);

        $school_year_obj = $enroll->GetActiveSchoolYearAndSemester();
        $current_school_year_semester = $school_year_obj['period'];
        $current_school_year_term = $school_year_obj['term'];
        $current_school_year_id = $school_year_obj['school_year_id'];

        $course_id = $_GET['id'];
        $school_year_id = $_GET['yid'];

        $section = new Section($con, $course_id);

        $section_name = $section->GetSectionName();

        $sy_query = $con->prepare("SELECT period FROm school_year
            WHERE school_year_id=:school_year_id
            LIMIT 1");
        
        $sy_query->bindValue(":school_year_id", $school_year_id);
        $sy_query->execute();
        $period = "";
        if($sy_query->rowCount() > 0){
            $row = $sy_query->fetch(PDO::FETCH_ASSOC);

            $period = $row['period'];
        }

        ?>
             
            <div class="row col-md-12">
                <div class="col-md-11 mt-4">
                    <h5 class="text-center">(S.Y <?php echo $current_school_year_term;?> <?php echo $current_school_year_semester;?> Semester)</h5>
                    <h4 class="text-center"><?php echo $section_name;?> Weekly Schedule</h4>
                    <table class="table table-striped table-bordered table-hover" style="font-size:14px" cellspacing="0">
                        <thead>
                            <tr class="text-center">
                            <th rowspan="2">Subject</th>
                            <th rowspan="2">Monday</th>
                            <th rowspan="2">Tuesday</th>
                            <th rowspan="2">Wednesday</th>
                            <th rowspan="2">Thursday</th>
                            <th rowspan="2">Friday</th>
                            <th rowspan="2">Teacher</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php
                            $subject_query = $con->prepare(" SELECT 
                                    t1.subject_id as t1Subject_id,
                                    t1.subject_code,
                                    t1.subject_title,
                                    t2.schedule_day,
                                    t2.schedule_time,

                                    t3.firstname,
                                    t3.lastname

                                FROM subject as t1
                                LEFT JOIN subject_schedule as t2 ON t1.subject_id = t2.subject_id
                                LEFT JOIN teacher as t3 ON t3.teacher_id = t2.teacher_id

                                WHERE t1.course_id=:course_id 
                                AND t1.semester=:semester

                            ");

                            $subject_query->bindValue("course_id", $course_id); 
                            $subject_query->bindValue("semester", $period); 
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
                                    
                                    // Create a new array for the subject if it doesn't exist
                                    if (!isset($merged_data[$subject_id])) {
                                        $merged_data[$subject_id] = array(
                                            'subject_title' => $subject_schedule['subject_title'],
                                            'firstname' => $subject_schedule['firstname'],
                                            'lastname' => $subject_schedule['lastname'],
                                            'Monday' => array(),
                                            'Tuesday' => array(),
                                            'Wednesday' => array(),
                                            'Thursday' => array(),
                                            'Friday' => array()
                                        );
                                    }

                                    // Add the schedule time to the corresponding day's array
                                    switch ($subject_schedule['schedule_day']) {
                                        case 'M':
                                            $merged_data[$subject_id]['Monday'][] = $subject_schedule['schedule_time'];
                                            break;
                                        case 'T':
                                            $merged_data[$subject_id]['Tuesday'][] = $subject_schedule['schedule_time'];
                                            break;
                                        case 'W':
                                            $merged_data[$subject_id]['Wednesday'][] = $subject_schedule['schedule_time'];
                                            break;
                                        case 'TH':
                                            $merged_data[$subject_id]['Thursday'][] = $subject_schedule['schedule_time'];
                                            break;
                                        case 'F':
                                            $merged_data[$subject_id]['Friday'][] = $subject_schedule['schedule_time'];
                                            break;
                                    }
                                }
                            }

                            // Loop through the merged data and output the final table
                            foreach ($merged_data as $subject_data) {

                                // print_r($subject_data);
                                echo '<tr class="text-center">';
                                echo '<td>'.$subject_data['subject_title'].'</td>';
                                echo '<td>'.implode('<br>', $subject_data['Monday']).'</td>';
                                echo '<td>'.implode('<br>', $subject_data['Tuesday']).'</td>';
                                echo '<td>'.implode('<br>', $subject_data['Wednesday']).'</td>';
                                echo '<td>'.implode('<br>', $subject_data['Thursday']).'</td>';
                                echo '<td>'.implode('<br>', $subject_data['Friday']).'</td>';
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
        <?php
    }

?>