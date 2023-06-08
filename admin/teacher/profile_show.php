<?php 

    include('../admin_enrollment_header.php');
    include('../../enrollment/classes/StudentEnroll.php');
    include('../../enrollment/classes/Teacher.php');
    include('../classes/Course.php');

    ?>
        <style>
            .process-status{
                display: flex;
                flex-direction: row;
                justify-content: center;
                font-style: normal;
                font-weight: 700;
                align-items: center;
                margin-top: 80px;
                padding: 0px 53px;
                gap: 1px;
                isolation: isolate;
                width: 100%;
                height: 74px;
                background: #1A0000;
            }

            .process-status .selection{
                margin-top: 5px;
            }

            .process-status .checkDetails{
                color: white;
            }

            .process-status #line-1, #line-2{
                color: #888888;
            }

            .findSection, .subConfirm{
                color: #888888;
            }

            .form-header h2{
                font-style: normal;
                font-weight: 700;
                font-size: 36px;
                line-height: 43px;
                display: flex;
                align-items: center;
                color: #BB4444;
            }   

            .header-content{
                display: flex;
                flex-direction: row;
                justify-content: space-between;
                margin-top: 50px;
                padding: 0px;
                gap: 10px;
                width: 90%;
                height: 43px;
                align-items: center;
            }

            .action{
                border: none;
                background: transparent;
                color: #E85959;
            }

            .action:hover{
                color: #9b3131;
            }

            .student-table{
                display: flex;
                flex-direction: row;
                align-items: flex-start;
                padding: 5px 0px;
                width: 100%;
                height: 58px;
            }

            .inner-student-table{
                table-layout: fixed;
                border-collapse: collapse;
                width: 100%;
                text-align: center;
            }

            /* table{
                table-layout: fixed;
                border-collapse: collapse;
                width: 100%;
                text-align: center;
            }

            tbody{
                font-style: normal;
                font-weight: 400;
                font-size: 17px;
                align-items: center;
            } */

            .choices{
                display: flex;
                flex-direction: row;
                justify-content: center;
                align-items: center;
                /* margin-top: 80px; */
                padding: 20px 53px 0px;
                gap: 1px;
                width: 100%;
                height: 74px;
                background: #1A0000;
                flex: none;
                order: 2;
                align-self: stretch;
                flex-grow: 0;
            }
            .selection-btn{
                display: flex;
                flex-direction: row;
                justify-content: center;
                align-items: center;
                padding: 5px 20px;
                gap: 10px;
                width: 233px;
                height: 54px;
                background: #EFEFEF;
                border: none;
                font-style: normal;
                font-weight: 400;
                font-size: 20px;
            }

            .bg-content{
                display: flex;
                flex-direction: column;
                align-items: center;
                padding: 50px 0px;
                width: 100%;
                height: auto;
                background: #EFEFEF;
            }

            .form-details{
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: flex-start;
                padding: 32px 26px;
                gap: 19px;
                width: 85%;
                height: auto;
                background: #FFFFFF;
                box-shadow: 0px 5px 20px rgba(0, 0, 0, 0.25);
                border-radius: 10px;
                margin-top: 30px;
            }

            .form-details h3{
                display: flex;
                align-items: center;
                font-style: normal;
                font-weight: 700;
                font-size: 36px;
                line-height: 43px;
                color: #BB4444;
            }

            form{
                flex: none;
                order: 1;
                align-self: stretch;
                flex-grow: 0;
            }
            .back-menu{
                display: flex;
                flex: row;
                align-items: center;
                padding: 8px 40px;
                gap: 8px;
                width: 100%;
                height: 46px;
            }
            .admission-btn{
                border: none;
                background: none;
                color: #BB4444;
                font-style: normal;
                font-weight: 700;
                font-size: 16px;
            }
            .admission-btn:hover{
                color: #863131;
            }

        </style>
    <?php

    if(
        isset($_GET['id'])
        && !isset($_GET['teacher_details'])
        && !isset($_GET['subject_load'])
    
    ){

        $teacher_id = $_GET['id'];

        // echo $teacher_id;

        $enroll = new StudentEnroll($con);
        $teacher = new Teacher($con, $teacher_id);
        $fullName = $teacher->GetTeacherFullName();

        $teacher_department =   $teacher->GetDepartmentName();
        $creation =   $teacher->GetCreation();

        $teacher_status = $teacher->GetStatus();
        $teacher_status = ucwords($teacher_status);

        $added_on = "";

        $words = explode(" ", $teacher_department);
        $department_abbreviation = "";

        foreach ($words as $word) {
            $department_abbreviation .= strtoupper($word[0]);
        }

        $school_year_obj = $enroll->GetLatestSchoolYearAndSemester();

        $currentYear = $school_year_obj[0];
        $current_semester = $school_year_obj[1];
        $school_year_id = $school_year_obj[2];

        ?>
            <div class="row col-md-12">

                <div class="content">

                    <div class="mb-3 form-header">
                        <div class="header-content">
                            <h2><?php echo $fullName?></h2>
                        </div>

                        <div class="student-table">
                            <table class="inner-student-table">
                                <tr>
                                    <th>Teacher Id.</th>
                                    <th>Department</th>
                                    <th>Status</th>
                                    <th>Added on:</th>
                                </tr>

                                <tr>
                                    <td><?php echo $teacher_id; ?></td>
                                    <td><?php echo $department_abbreviation; ?></td>
                                    <td><?php echo $teacher_status; ?></td>
                                    <td><?php
                                        $date = new DateTime($creation);
                                        $formattedDate = $date->format('m/d/Y H:i');

                                        echo $formattedDate;
                                    ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                  
                    <div class="choices">
                        <div class="student-details">

                            <a href="profile_show.php?teacher_details=show&id=<?php echo $teacher_id?>">
                                <button style="background-color:palevioletred;"
                                    type="button"
                                    class="selection-btn"
                                    id="student-details">
                                    <i class="bi bi-clipboard-check"></i>Teacher Details
                                </button>
                            </a>
                        </div>

                        <div class="enrolled-subjects">
                               <a href="profile_show.php?subject_load=show&id=<?php echo $teacher_id?>">
                                <button
                                    type="button"
                                    class="selection-btn"
                                    id="enrolled-subjects">
                                    <i class="bi bi-collection"></i>Subject Load
                                </button>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php
  
    }

    if (isset($_GET['teacher_details']) && $_GET['teacher_details'] == "show" && isset($_GET['teacher_details'])) {
        
        $teacher_id = $_GET['id'];

        // echo $teacher_id;

        $enroll = new StudentEnroll($con);
        $teacher = new Teacher($con, $teacher_id);
        $fullName = $teacher->GetTeacherFullName();

        $teacher_department =   $teacher->GetDepartmentName();
        $creation =   $teacher->GetCreation();

        $teacher_status = $teacher->GetStatus();
        $teacher_status = ucwords($teacher_status);

        $added_on = "";

        $words = explode(" ", $teacher_department);
        $department_abbreviation = "";

        foreach ($words as $word) {
            $department_abbreviation .= strtoupper($word[0]);
        }

        $school_year_obj = $enroll->GetLatestSchoolYearAndSemester();

        $currentYear = $school_year_obj[0];
        $current_semester = $school_year_obj[1];
        $school_year_id = $school_year_obj[2];

        ?>

            <div class="row col-md-12">
                <div class="content">

                    <div class="form-header">
                        <div class="header-content">
                            <h2><?php echo $fullName?></h2>
                        </div>

                        <div class="student-table">
                            <table class="inner-student-table">
                                <tr>
                                    <th>Teacher Id.</th>
                                    <th>Department</th>
                                    <th>Status</th>
                                    <th>Added on:</th>
                                </tr>

                                <tr>
                                    <td><?php echo $teacher_id; ?></td>
                                    <td><?php echo $department_abbreviation; ?></td>
                                    <td><?php echo $teacher_status; ?></td>
                                    <td><?php
                                        $date = new DateTime($creation);
                                        $formattedDate = $date->format('m/d/Y H:i');

                                        echo $formattedDate;
                                    ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <div class="choices">
                        <div class="student-details">
                            <a href="profile_show.php?teacher_details=show&id=<?php echo $teacher_id?>">
                                <button style="background-color:palevioletred;"
                                    type="button"
                                    class="selection-btn"
                                    id="student-details">
                                    <i class="bi bi-clipboard-check"></i>Teacher Details
                                </button>
                            </a>
                        </div>

                        <div class="enrolled-subjects">
                            <a href="profile_show.php?subject_load=show&id=<?php echo $teacher_id?>">
                                <button
                                    type="button"
                                    class="selection-btn"
                                    id="enrolled-subjects">
                                    <i class="bi bi-collection"></i>Subject Load
                                </button>
                            </a>
                        </div>
                    </div>
                </div>
                <hr>
                <hr>
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <h4 class="mb-3 text-start text-primary">Student Information</h4>
                            <hr>
                            <table class="table">

                                <tr>
                                    <td><label>Firstname</label></td>
                                    <td>
                                        <input  value='<?php echo ""; ?>' class="form-control input-md" id="FNAME" 
                                        name="firstname" type="text">
                                    </td>
                                    <td><label>Lastname</label></td>
                                    <td colspan="2">
                                        <input value='<?php echo "";?>' class="form-control input-md" name="lastname" 
                                        placeholder="Last Name" type="text">
                                    </td> 
                                    <td><label>Middle</label></td>
                                    <td colspan="2">
                                        <input value='<?php echo ""; ?>' class="form-control input-md"
                                            id="MI" name="mi" placeholder="MI"  maxlength="1" type="text">
                                    </td>
                                </tr>


                                <tr>
                                    <td><label>Status</label></td>
                                    <td>
                                        <input  value='<?php echo ""; ?>' class="form-control input-md" id="FNAME" 
                                        name="status" type="text">
                                    </td>
                                    <td><label>Citizenship</label></td>
                                    <td colspan="2">
                                        <input value='<?php echo "";?>'
                                    class="form-control input-md" id="citizenship" name="LNAME" 
                                        type="text">
                                    </td> 
                                    <td><label>Gender</label></td>

                                    <td colspan="2">
                                        <input value='<?php echo ""; ?>' class="form-control input-md"
                                            id="sex" name="sex"
                                            type="text">
                                    </td>
                                </tr>

                                    <tr>
                                    <td><label>Address</label></td>
                                    <td colspan="5">
                                    <input  value='<?php echo "";?>' class="form-control input-md" id="address" name="address" placeholder="Permanent Address" type="text" >
                                    </td> 
                                </tr>

                                <tr>
                                    <td><label>Contact No.</label></td>
                                    <td colspan="3"
                                        value='<?php echo $row['contact_number']?>'  colspan="2"><input class="form-control input-md" id="contact_num" name="contact_num" placeholder="Contact Number" type="number" maxlength="11" >
                                    </td>
                                    
                                    <td><label>Email</label></td>
                                    <td 
                                        value='<?php echo "";?>'  colspan="2">
                                        <input class="form-control input-md" id="email" name="email" placeholder="Email" type="email">
                                    </td>
                                </tr>

                            
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        <?php
    }


    if (isset($_GET['subject_load']) && $_GET['subject_load'] == "show" 
        && isset($_GET['id'])) {
        
        $teacher_id = $_GET['id'];

        // echo $teacher_id;

        $enroll = new StudentEnroll($con);
        $teacher = new Teacher($con, $teacher_id);
        $fullName = $teacher->GetTeacherFullName();

        $teacher_department =   $teacher->GetDepartmentName();
        $creation =   $teacher->GetCreation();

        $teacher_status = $teacher->GetStatus();
        $teacher_status = ucwords($teacher_status);

        $added_on = "";

        $words = explode(" ", $teacher_department);
        $department_abbreviation = "";

        foreach ($words as $word) {
            $department_abbreviation .= strtoupper($word[0]);
        }

        $school_year_obj = $enroll->GetLatestSchoolYearAndSemester();

        $currentYear = $school_year_obj[0];
        $current_semester = $school_year_obj[1];
        $school_year_id = $school_year_obj[2];

        ?>

            <div class="row col-md-12">
                <div class="content">

                    <div class="form-header">
                        <div class="header-content">
                            <h2><?php echo $fullName?></h2>
                        </div>

                        <div class="student-table">
                            <table class="inner-student-table">
                                <tr>
                                    <th>Teacher Id.</th>
                                    <th>Department</th>
                                    <th>Status</th>
                                    <th>Added on:</th>
                                </tr>

                                <tr>
                                    <td><?php echo $teacher_id; ?></td>
                                    <td><?php echo $department_abbreviation; ?></td>
                                    <td><?php echo $teacher_status; ?></td>
                                    <td><?php
                                        $date = new DateTime($creation);
                                        $formattedDate = $date->format('m/d/Y H:i');

                                        echo $formattedDate;
                                    ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <div class="choices">
                        <div class="student-details">
                            <a href="profile_show.php?teacher_details=show&id=<?php echo $teacher_id?>">
                                <button 
                                    type="button"
                                    class="selection-btn"
                                    id="student-details">
                                    <i class="bi bi-clipboard-check"></i>Teacher Details
                                </button>
                            </a>
                        </div>

                        <div class="enrolled-subjects">
                            <a href="profile_show.php?subject_load=show&id=<?php echo $teacher_id?>">
                                <button style="background-color:palevioletred;"
                                    type="button"
                                    class="selection-btn"
                                    id="enrolled-subjects">
                                    <i class="bi bi-collection"></i>Subject Load
                                </button>
                            </a>
                        </div>
                    </div>
                </div>
                <hr>
                <hr>
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <h4 style="color: #BB4444;" class="mb-3 text-start">Schedule</h4>
                            <hr>

                            <table  class="table table-striped table-bordered table-hover "  style="font-size:13px" cellspacing="0"  > 
                                <thead>
                                    <tr class="text-center"> 
                                        <th rowspan="2">Id</th>
                                        <th rowspan="2">Subject</th>
                                        <th rowspan="2">Code</th>
                                        <th rowspan="2">Section</th>
                                        <th rowspan="2">Day</th>
                                        <th rowspan="2">Time</th>
                                        <th rowspan="2">Room</th>
                                        <th rowspan="2">Hrs/Week</th>
                                    </tr>	
                                </thead> 	 
                                <tbody>
                                    <?php 
                                        $drop_status = "Drop";

            $sql = $con->prepare("SELECT 
                                            t1.*, 
                                            t2.program_section,
                                            t3.subject_title as teacher_subject_title,
                                            t3.subject_code,
                                            t4.firstname,t4.lastname


                                            FROM subject_schedule as t1

                                            LEFT JOIN course as t2 ON t2.course_id = t1.course_id
                                            LEFT JOIN subject as t3 ON t3.subject_id = t1.subject_id
                                            LEFT JOIN teacher as t4 ON t4.teacher_id = t1.teacher_id

                                            WHERE school_year_id=:school_year_id
                                            AND t4.teacher_id=:teacher_id
                                            ");

                                        // $sql->bindValue(":student_status", $drop_status);
                                        $sql->bindValue(":school_year_id", $school_year_id);
                                        $sql->bindValue(":teacher_id", $teacher_id);
                                        $sql->execute();

                                        if($sql->rowCount() > 0){
                                        
                                            while($row = $sql->fetch(PDO::FETCH_ASSOC)){

                                                $subject_schedule_id = $row['subject_schedule_id'];

                                                $fullName = $row['firstname']." ". $row['lastname']; 
                                                $teacher_id = $row['teacher_id'];
                                                $subject_code = $row['subject_code'];
                                                
                                                $program_section = $row['program_section'];
                                                $schedule_day = $row['schedule_day'];
                                                $schedule_day = $row['schedule_day'];
                                                $schedule_time = $row['schedule_time'];
                                                $room = $row['room'];
                                                $teacher_subject_title = $row['teacher_subject_title'];

                                                $gradeUrl = "http://localhost/dcbt/admin/enrollees/student_grade_report.php?id=$teacher_id";

                                                $profile_url = directoryPath . "profile_show.php?id=$teacher_id";
                                                
                                                echo '<tr class="text-center">'; 
                                                        echo '<td>'.$subject_schedule_id.'</td>';
                                                        echo  '<td> '.$teacher_subject_title.'</td>';
                                                        echo '<td>'.$subject_code.'</td>';
                                                        echo '<td>'.$program_section.'</td>';
                                                        echo  '<td> '.$schedule_day.'</td>';
                                                        echo  '<td> '.$schedule_time.'</td>';
                                                        echo  '<td> '.$room.'</td>';
                                                        echo 
                                                        '<td> 
                                                            12.5
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
        <?php
    }


?>