<?php 

    include('../admin_enrollment_header.php');
    include('../../enrollment/classes/StudentEnroll.php');
    // include('../adminHeader.php');
    require_once('../../admin/classes/AdminUser.php');

    $createUrl = base_url . "/create.php";

    // echo "im in subject enroll";
    $enroll = new StudentEnroll($con);

    $school_year_obj = $enroll->GetLatestSchoolYearAndSemester();

	$currentYear = $school_year_obj[0];
	$current_semester = $school_year_obj[1];
	$school_year_id = $school_year_obj[2];

    if(!AdminUser::IsAuthenticated()){
        header("Location: /dcbt/adminLoggedIn.php");
        exit();
    }
?>

    <div class="row col-md-12">
        <div class="col-md-12">
            <div class="container mb-4">
                <h2 class="page-header">Teacher Section</h2>
                <a href="<?php echo $createUrl?>">
                    <button class="btn btn-sm btn-success">Add Teacher</button>
                </a>    
            </div>
 
            <div class="col-md-12">
                <div class=" ">
                    <table  class="table table-striped table-bordered table-hover "  style="font-size:13px" cellspacing="0"  > 
                        <thead>
                            <tr class="text-center"> 
                                <th rowspan="2">Id</th>
                                <th rowspan="2">Name</th>
                                <th rowspan="2">Subject</th>
                                <th rowspan="2">Section</th>
                                <th rowspan="2">Day</th>
                                <th rowspan="2">Time</th>
                                <th rowspan="2">Room</th>
                                <th rowspan="2"></th>
                            </tr>	
                        </thead> 	 
                        <tbody>
                            <?php 
                                $drop_status = "Drop";

                                $sql = $con->prepare("SELECT 
                                    t1.*, 
                                    t2.program_section,
                                    t3.subject_title as teacher_subject_title,
                                    t4.firstname,t4.lastname

                                    FROM subject_schedule as t1

                                    LEFT JOIN course as t2 ON t2.course_id = t1.course_id
                                    LEFT JOIN subject as t3 ON t3.subject_id = t1.subject_id
                                    LEFT JOIN teacher as t4 ON t4.teacher_id = t1.teacher_id

                                    -- WHERE school_year_id=:school_year_id
                                    -- AND t2.teacher_status=:teacher_status
                                    ");

                                // $sql->bindValue(":student_status", $drop_status);
                                // $sql->bindValue(":school_year_id", $school_year_id);
                                // $sql->bindValue(":teacher_status", "active");
                                $sql->execute();

                                if($sql->rowCount() > 0){
                                
                                    while($row = $sql->fetch(PDO::FETCH_ASSOC)){

                                        $fullName = $row['firstname']." ". $row['lastname']; 
                                        $teacher_id = $row['teacher_id'];
                                        $program_section = $row['program_section'];
                                        $schedule_day = $row['schedule_day'];
                                        $schedule_time = $row['schedule_time'];
                                        $room = $row['room'];
                                        $teacher_subject_title = $row['teacher_subject_title'];

                                        $gradeUrl = "http://localhost/dcbt/admin/enrollees/student_grade_report.php?id=$teacher_id";

                                        $profile_url = directoryPath . "profile_show.php?id=$teacher_id";
                                        
                                        $edit_url = directoryPath . "edit.php?id=$teacher_id";
                                        echo '<tr class="text-center">'; 
                                                echo '<td>'.$teacher_id.'</td>';
                                                echo '<td>
                                                    <a style= "color: whitesmoke;" href="'.$profile_url.'">
                                                        '.$fullName.'
                                                    </a>
                                                </td>';
                                                echo  '<td> '.$teacher_subject_title.'</td>';
                                                echo '<td>'.$program_section.'</td>';
                                                echo  '<td> '.$schedule_day.'</td>';
                                                echo  '<td> '.$schedule_time.'</td>';
                                                echo  '<td> '.$room.'</td>';
                                                echo 
                                                '<td> 
                                                    <a href="'.$edit_url.'">
                                                        <button class="btn btn-sm btn-primary">Edit</button>
                                                    </a>
                                                </td>';

                                              
                                        echo '</tr>';
                                    }
                                }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
<script>

   function confirmAsReturneeBtn(username) {

    $.ajax({
        url: '../ajax/enrollee/markAsReturned.php', // replace with your PHP script URL
        type: 'POST',
        data: {
            // add any data you want to send to the server here
            username
        },
        success: function(response) {
            // console.log(response);
            alert(response)
            location.reload();
        },
        error: function(xhr, status, error) {
        }
    });
}
</script>