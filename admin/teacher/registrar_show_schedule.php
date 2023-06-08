<?php 

    include('../registrar_enrollment_header.php');
    include('../../enrollment/classes/StudentEnroll.php');
    include('../../enrollment/classes/Teacher.php');
    include('../classes/Course.php');
    require_once('../../admin/classes/AdminUser.php');

    if(!AdminUser::IsRegistrarAuthenticated()){
        header("Location: /dcbt/registrar_login.php");
    }

    // echo "show sched";

    if(isset($_GET['id'])){

        $teacher_id = $_GET['id'];
        $enroll = new StudentEnroll($con);

        $school_year_obj = $enroll->GetLatestSchoolYearAndSemester();

        $currentYear = $school_year_obj[0];
        $current_semester = $school_year_obj[1];
        $school_year_id = $school_year_obj[2];

        ?>
        <div class="row col-md-12">
            <div class="col-md-12">
                <div class=" ">
                    <h4 class="text-center"> My Schedule of (S.Y<?php echo $currentYear;?>) <?php echo $current_semester;?> Semester</h4>
                    <table  class="mt-4 table table-striped table-bordered table-hover "  style="font-size:13px" cellspacing="0"  > 
                        <thead>
                            <tr class="text-center"> 
                                <th rowspan="2">Title</th>
                                <th rowspan="2">Section</th>
                                <th rowspan="2">Day</th>
                                <th rowspan="2">Time</th>
                                <th rowspan="2">Room</th>
                                <th rowspan="2"></th>
                            </tr>	
                        </thead> 	 
                        <tbody>
                            <?php 
                                $teacher_status = "active";

                                $sql = $con->prepare("SELECT 
                                
                                    t1.time_from, t1.time_to, t1.schedule_day, t1.room,
                                    t2.subject_title,

                                    t3.program_section

                                    FROM subject_schedule as t1

                                    INNER JOIN subject as t2 ON t2.subject_id = t1.subject_id
                                    INNER JOIN course as t3 ON t3.course_id = t2.course_id
                                    -- INNER JOIN teacher as t3 ON t3.teacher_id = t1.teacher_id
                                    WHERE t1.teacher_id=:teacher_id
                                    ");

                                $sql->bindValue(":teacher_id", $teacher_id);
                                $sql->execute();

                                if($sql->rowCount() > 0){
                                    while($row = $sql->fetch(PDO::FETCH_ASSOC)){

                                        $subject_title = $row['subject_title'];
                                        $time_from = $row['time_from'];
                                        $time_to = $row['time_to'];
                                        $schedule_day = $row['schedule_day'];
                                        $room = $row['room'];
                                        $program_section = $row['program_section'];
                                        

                                        echo '<tr class="text-center">'; 
                                            echo '<td>'.$subject_title.'</td>';
                                            echo '<td>'.$program_section.'</td>';
                                            echo '<td>'.$schedule_day.'</td>';
                                            echo '<td>'.$time_to.' '.$time_from.'</td>';
                                            echo '<td>'.$room.'</td>';

                                        echo '</tr>';
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


