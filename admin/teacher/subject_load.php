<?php 

    include('../admin_enrollment_header.php');
    include('../../enrollment/classes/StudentEnroll.php');
    include('../../enrollment/classes/Teacher.php');
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

            <div class="none_active" id="teacher-list-div">
                <a href="index.php" id="teacher-list-a">Teacher List

                </a>
            </div>
            <div class="active" id="subject-load-div">
                <a href="subject_load.php" id="subject-load-a">Subject Load
                </a>
            </div>
        </div>


        <section class="teacher-container" id="teacher">
            <!--SHS Teacher List Page-->
            <div class="teach" id="shs-teacher">
                <h1>Subject Load</h1>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Section</th>
                            <th>Level</th>
                            <th>Day</th>
                            <th>Time</th>
                            <th>Hrs/Week</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php 
                            $teacher_status = "active";

                            $sql = $con->prepare("SELECT 
                                t1.*, 
                                t2.program_section,
                                t3.subject_title as teacher_subject_title,
                                t3.subject_code,
                                t3.subject_id,
                                t3.course_level,
                                t4.firstname,t4.lastname


                                FROM subject_schedule as t1

                                LEFT JOIN course as t2 ON t2.course_id = t1.course_id
                                LEFT JOIN subject as t3 ON t3.subject_id = t1.subject_id
                                LEFT JOIN teacher as t4 ON t4.teacher_id = t1.teacher_id

                                WHERE school_year_id=:school_year_id
                                ");

                            // $sql->bindValue(":student_status", $drop_status);
                            $sql->bindValue(":school_year_id", $school_year_id);
                            $sql->execute();

                            if($sql->rowCount() > 0){
                            
                                while($row = $sql->fetch(PDO::FETCH_ASSOC)){

                                    $subject_schedule_id = $row['subject_schedule_id'];

                                    $fullName = $row['firstname']." ". $row['lastname']; 
                                    $teacher_id = $row['teacher_id'];
                                    $subject_code = $row['subject_code'];
                                    $course_level = $row['course_level'];
                                    $subject_id = $row['subject_id'];
                                    
                                    $program_section = $row['program_section'];
                                    $schedule_day = $row['schedule_day'];
                                    $schedule_time = $row['schedule_time'];
                                    $room = $row['room'];
                                    $teacher_subject_title = $row['teacher_subject_title'];

                                    $gradeUrl = "http://localhost/dcbt/admin/enrollees/student_grade_report.php?id=$teacher_id";

                                    $profile_url = directoryPath . "profile_show.php?id=$teacher_id";
                                        $edit_url = directoryPath . "edit.php?id=$teacher_id";
                                    
                                    echo '<tr class="text-center">'; 
                                            echo '<td>'.$subject_id.'</td>';
                                            echo '<td>'.$fullName.'</td>';
                                            echo '<td>'.$program_section.'</td>';
                                            echo '<td>'.$course_level.'</td>';
                                            echo '<td>'.$schedule_day.'</td>';
                                            echo '<td>'.$schedule_time.'</td>';
                                            echo 
                                            '<td> 
                                                12.5
                                            </td>';
                                            echo '<td>
                                                <a href="'.$edit_url.'">
                                                    <button class="btn btn-sm btn-primary">
                                                        <i class="fas fa-edit"></i>
                                                    </button>             
                                                </a>

                                            </td>';


                                        
                                    echo '</tr>';
                                }
                            }
                        ?>
                    </tbody>   

                </table>
 
            </div>
        
        </section>


    </div>

</div>