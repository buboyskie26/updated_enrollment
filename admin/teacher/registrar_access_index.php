<?php 

    include('../registrar_enrollment_header.php');
    include('../../enrollment/classes/StudentEnroll.php');
    include('../../enrollment/classes/Schedule.php');
    require_once('../../admin/classes/AdminUser.php');

    $createUrl = base_url . "/registrar_create_schedule.php";
    // echo "im in subject enroll";
    $enroll = new StudentEnroll($con);

    $schedule = new Schedule($con, $enroll);
    $school_year_obj = $enroll->GetLatestSchoolYearAndSemester();

	$currentYear = $school_year_obj[0];
	$current_semester = $school_year_obj[1];
	$school_year_id = $school_year_obj[2];

    if(!AdminUser::IsRegistrarAuthenticated()){
        header("Location: /dcbt/registrarLoggedIn.php");
        exit();
    }
?>
    <div class="row col-md-12">
        <div class="col-md-12">
            <div class="container mb-4">
                <h3 class="text-muted text-center page-header">Teacher Section</h3>
            </div>
 
            <div class="col-md-12">
                <div class=" ">
                    <table  class="table table-striped table-bordered table-hover "  style="font-size:13px" cellspacing="0"  > 
                        <thead>
                            <tr class="text-center"> 
                                <th rowspan="2">Id</th>
                                <th rowspan="2">Name</th>
                                <th rowspan="2">Subject Load</th>
                                <th rowspan="2">Status</th>
                                <th rowspan="2">Date Creation</th>
                                <th rowspan="2">Action</th>
                            </tr>	
                        </thead> 	 
                        <tbody>
                            <?php 
                                $teacher_status = "active";

                                $sql = $con->prepare("SELECT * FROM teacher
                                    WHERE teacher_status=:teacher_status
                                    ");

                                $sql->bindValue(":teacher_status", $teacher_status);
                                $sql->execute();

                                if($sql->rowCount() > 0){
                                
                                    while($row = $sql->fetch(PDO::FETCH_ASSOC)){

                                        $fullName = $row['firstname']." ". $row['lastname']; 
                                        $teacher_id = $row['teacher_id'];
                                        $teacher_status = $row['teacher_status'];
                                        $date_creation = $row['date_creation'];
                                        
                                        $subjectLoads = $schedule->GetTeacherSubjectLoad($teacher_id);
                                        
                                        // $edit_url = directoryPath . "edit.php?id=$teacher_id";
                                        $show_schedule_url = directoryPath . "registrar_show_schedule.php?id=$teacher_id";
                                        $create_sched_url = directoryPath . "registrar_create_schedule.php?id=$teacher_id";
                                        $schedule_url = directoryPath . "registrar_profile_show.php?id=$teacher_id";

                                        echo '<tr class="text-center">'; 
                                                echo '<td>'.$teacher_id.'</td>';
                                                echo '<td>
                                                    <a style= "color: whitesmoke;" href="'.$schedule_url.'">
                                                        '.$fullName.'
                                                    </a>
                                                </td>';
                                                echo '<td>'.$subjectLoads.'</td>';
                                                echo '<td>'.$teacher_status.'</td>';
                                                echo '<td>'.$date_creation.'</td>';
                                                echo 
                                                '<td> 
                                                    <a href="'.$show_schedule_url.'">
                                                        <button class="btn btn-sm btn-success">Show</button>
                                                    </a>

                                                    <a href="'.$create_sched_url.'">
                                                        <button class="btn btn-sm btn-secondary">Add Schedule</button>
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
