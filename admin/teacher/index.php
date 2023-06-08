
<?php 

    include('../admin_enrollment_header.php');
    include('../../enrollment/classes/StudentEnroll.php');
    include('../../enrollment/classes/Schedule.php');
    require_once('../../admin/classes/AdminUser.php');

    ?>
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="teachers.css">
    </head>
    <?php

    $createUrl = base_url . "/create.php";

    // echo "im in subject enroll";
    $enroll = new StudentEnroll($con);

    $schedule = new Schedule($con, $enroll);
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

        <div class="content">
            <div class="dashboard">
                <h3>Department</h3>

                <div class="form-box">
                    <div class="button-box">
                    <div id="btn"></div>
                    <button type="button" class="toggle-btn" >
                        SHS
                    </button>
                    <button type="button" class="toggle-btn">
                        Tertiary
                    </button>
                    </div>
                </div>
            </div>
            <div class="choices">

                <div class="active" id="teacher-list-div">
                    <a href="index.php" id="teacher-list-a">Teacher List

                    </a>
                </div>
                <div class="none_active" id="subject-load-div">
                    <a href="subject_load.php" id="subject-load-a">Subject Load
                    </a>
                </div>

            </div>

            <section class="teacher-container" id="teacher">
                <!--SHS Teacher List Page-->
                <div class="teach" id="shs-teacher">
                    <h1>Teachers</h1>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Subject Load</th>
                                <th>Status</th>
                                <th>Data added</th>
                                <th>Action</th>
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

                                        
                                        $edit_url = directoryPath . "edit.php?id=$teacher_id";
                                        $schedule_url = directoryPath . "profile_show.php?teacher_details=show&id=$teacher_id";

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
                    <a href="<?php echo $createUrl; ?>">
                        <button id="button-shs" class="add-new">+ Add new</button>
                    </a>
                </div>
            
            </section>

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