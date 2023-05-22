<?php 

    require_once('./classes/StudentEnroll.php');
    require_once('./classes/OldEnrollees.php');
    require_once('./classes/Schedule.php');
    require_once('../includes/studentHeader.php');

    $studentEnroll = new StudentEnroll($con);
    $old_enroll = new OldEnrollees($con, $studentEnroll);
    $schedule = new Schedule($con, $studentEnroll);
    $userLoggedInId = $studentEnroll->GetStudentId($studentLoggedIn);
    $course_main_id = $studentEnroll->GetStudentCourseId($studentLoggedIn);
    $section_name = $studentEnroll->GetStudentCourseName($studentLoggedIn);

?>
<div class="row col-md-12 table-responsive" style="margin-top:5%;">
<h3 class="text-center mb-3">Enrollment Schedule</h3>
<table  class=" table table-striped table-bordered table-hover "  style="font-size:13px" cellspacing="0"  > 
    <thead>
        <tr class="text-center"> 
            <!-- <th>
            </th> -->
            <th rowspan="2">Section</th>
            <th rowspan="2">Enrolled Date</th>  
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
                    $enrollment_date = $row['enrollment_date'];

                    $subject_count  = $old_enroll->GetSubjectCount($course_id, $school_year_id);

                    $period = $row['period'];
                    echo '<tr class="text-center">'; 

                        echo '<td>
                            <a style="text-decoration:none;" href="enrolled_schedule.php?id='.$course_id.'&yid='.$school_year_id.'">
                                '.$program_section.'
                            </a>
                        </td>';
                        echo '<td>'.$enrollment_date.'</td>';
                        echo '<td>'.$term.'</td>';
                        echo '<td>'.$period.'</td>';
                        echo '<td>'.$subject_count.'</td>';
                    echo '</tr>';
                }
            }
        ?>
    </tbody>
</table>


</div>

