

<?php 

    include('../admin_enrollment_header.php');
    include('../../enrollment/classes/StudentEnroll.php');
    include('../../enrollment/classes/Schedule.php');
    require_once('../../admin/classes/AdminUser.php');

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


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="teachers.css">
</head>


<body>
    
</body>
    <div class="row col-md-12">
            <div class="container mb-4">
                <h3 class="page-header">Teacher Section</h3>
                <a href="<?php echo $createUrl?>">
                    <button class="btn btn-sm btn-success">Add Teacher</button>
                </a>    
            </div>
 
            <div class="card">
                <div class="card-body">
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

                                        
                                        $edit_url = directoryPath . "edit.php?id=$teacher_id";
                                        $schedule_url = directoryPath . "profile_show.php?id=$teacher_id";

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
                </div>
            </div>


        <div class="content">
            <div class="dashboard">
                <h3>Department</h3>

                <div class="form-box">
                    <div class="button-box">
                    <div id="btn"></div>
                    <button type="button" class="toggle-btn" onclick="shs()">
                        SHS
                    </button>
                    <button type="button" class="toggle-btn" onclick="college()">
                        College
                    </button>
                    </div>
                </div>
            </div>
            <div class="choices">
                <div id="teacher-list-div"><a href="#" id="teacher-list-a" onClick="sens()">Teacher List</a></div>
                <div id="subject-load-div"> <a href="#" id="subject-load-a" onClick="sub()">Subject Load</a></div>
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
                                        $schedule_url = directoryPath . "profile_show.php?id=$teacher_id";

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
            
            <!--SHS subject load page-->
            <section class="teacher-container" id="subject">
                <div class="teach" id="shs-subject">
                    <h1>Subject Load</h1>
                    <!--table head-->
                    <table>
                        <tr>
                            <th>Subject ID</th>
                            <th>Section</th>
                            <th>Subject name</th>
                            <th>Subject status</th>
                            <th>teacher</th>
                            <th>Action</th>
                        </tr>
                        <tr>
                            <td>23</td>
                            <td class="td-section">jeriko</td>
                            <td>4</td>
                            <td>dsddsds</td>
                            <td>23/23/2</td>
                            <td>fafa</td>
                        </tr>
                        <tr>
                            <td>223</td>
                            <td class="td-section">jeriko</td>
                            <td>4</td>
                            <td>dsddsds</td>
                            <td>23/23/2</td>
                            <td>fafa</td>
                        </tr>
                        <tr>
                            <td>243</td>
                            <td class="td-section">jeriko</td>
                            <td>4</td>
                            <td>dsddsds</td>
                            <td>23/23/2</td>
                            <td>fafa</td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td class="td-section">jeriko</td>
                            <td>4</td>
                            <td>dsddsds</td>
                            <td>23/23/2</td>
                            <td>fafa</td>
                        </tr>
                    </table>
                </div>
                <!--College subject load page-->
                <div class="teach" id="college-subject">
                    <h1>Subject Load</h1>
                    <table>
                        <tr>
                            <th>Subject ID</th>
                            <th>Section</th>
                            <th>Subject name</th>
                            <th>Subject status</th>
                            <th>teacher</th>
                            <th>Action</th>
                        </tr>
                        <tr>
                            <td>23</td>
                            <td class="td-section">jeriko</td>
                            <td>4</td>
                            <td>dfdfdfsddsds</td>
                            <td>23/23/2</td>
                            <td>ffdfdfafa</td>
                        </tr>
                        <tr>
                            <td>223</td>
                            <td class="td-section">jeriko</td>
                            <td>4</td>
                            <td>dsddsds</td>
                            <td>23/23/2</td>
                            <td>fafa</td>
                        </tr>
                        <tr>
                            <td>243</td>
                            <td class="td-section">jeriko</td>
                            <td>4</td>
                            <td>dsddsds</td>
                            <td>23/23/2</td>
                            <td>fafa</td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td class="td-section">jeriko</td>
                            <td>4</td>
                            <td>dsddsds</td>
                            <td>23/23/2</td>
                            <td>fafa</td>
                        </tr>
                    </table>
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