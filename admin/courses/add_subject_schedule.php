<?php 
    include('../registrar_enrollment_header.php');
    include('../../enrollment/classes/StudentEnroll.php');
    include('../../enrollment/classes/Schedule.php');
    include('../../enrollment/classes/Section.php');
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

    if(isset($_GET['id'])
    && isset($_GET['cid'])
    ){

        $subject_id = $_GET['id'];
        $course_id = $_GET['cid'];

        $section = new Section($con, $course_id);
        $program_section = $section->GetSectionName();



        $sql = $con->prepare("SELECT subject_title FROM subject
            WHERE subject_id=:subject_id");

        $sql->bindValue(":subject_id", $subject_id);
        $sql->execute();
        $subject_name = "";
        if($sql->rowCount() > 0){
            $subject_name = $sql->fetchColumn();
        }

        if(isset($_POST['add_subject_schedule_btn'])){

            // echo $subject_id;
            // echo "<br>";
            // echo $course_id;

            $room = $_POST['room'];
            $time_from = $_POST['time_from'];
            $time_to = $_POST['time_to'];
            $schedule_day = $_POST['schedule_day'];

            // $schedule_time = "";
            // $schedule_time = $time_from . "-" . $time_to;

            $time_from_am_pm = $_POST['time_from_am_pm'];
            $time_to_am_pm = $_POST['time_to_am_pm'];
            
            $teacher_id = intval($_POST['teacher_id']);
            $schedule_time = $time_from . ' '. $time_from_am_pm . ' - ' . $time_to. ' ' . $time_to_am_pm;

            // $sql = $con->prepare("INSERT INTO subject_schedule
            //     (room, schedule_day, time_from, time_to, schedule_time, school_year_id, course_id, subject_id, teacher_id)
            //     VALUES(:room, :schedule_day, :time_from, :time_to, :schedule_time, :school_year_id, :course_id, :subject_id, :teacher_id)");
            

            // $sql->bindValue(":room", $room);
            // $sql->bindValue(":schedule_day", $schedule_day);
            // $sql->bindValue(":time_from", $time_from);
            // $sql->bindValue(":time_to", $time_to);
            // $sql->bindValue(":schedule_time", $schedule_time);
            // $sql->bindValue(":school_year_id", $current_school_year_id);
            // $sql->bindValue(":course_id", $course_id);
            // $sql->bindValue(":subject_id", $subject_id);
            // $sql->bindValue(":teacher_id", $teacher_id);

            // if($sql->execute()){
            //     header("Location: course_view_subject.php?id=$course_id");
            // }

            $check = $schedule->CheckConflictedSchedule($schedule_day,
                $time_from, $time_to, $course_id);
            if($check == false){
                $wasSuccess = $schedule->InsertSubjectSchedule(
                    $room, $schedule_day, $time_from,
                    $time_to, $schedule_time, $current_school_year_id,
                    $course_id, $subject_id, $teacher_id);
                
                if($wasSuccess){
                    echo "success";
                    header("Location: course_view_subject.php?id=$course_id");
                }
            }

        }
        ?>

        <div class='col-md-8 row offset-md-1'>

            <h5 class="text-center">S.Y (<?php echo $current_school_year_term;?>) <?php echo $current_school_year_period;?> Semester</h5>
            <div class="row justify-content-center">
                <div class="col-sm-6">
                    <h4 class="text-center">Section: <?php echo $program_section;?></h4> 
                    <p class="text-center">Schedule on <span><?php echo $subject_name?></span></p>
                </div>
            </div>
            <form method='POST'>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="">Room number</label>
                            <input value='55' type="text" placeholder="(Room: 501)" name="room" id="room" class="form-control" />
                        </div>

                        <div class="mb-3">
                            <label for="">Time From</label>
                            <input type="text" value="8:00" placeholder="(7:00)" name="time_from" id="time_from" class="form-control" />
                        </div>

                        <div class="mb-3">
                            <label for="">Time From AM/PM</label>
                            <select name="time_from_am_pm" id="time_from_am_pm" class="form-control">
                                <option value="AM">AM</option>
                                <option value="PM">PM</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="">Time to</label>
                            <input type="text" value="9:30" placeholder="(7:00)" name="time_to" id="time_to" class="form-control" />
                        </div>

                        <div class="mb-3">
                            <label for="">Time to AM/PM</label>
                            <select name="time_to_am_pm" id="time_to_am_pm" class="form-control">
                                <option value=AM">AM</option>
                                <option selected value="PM">PM</option>
                            </select>
                        </div>
 
                        <div class="mb-3">
                            <label class="mb-1" for="schedule_day">Day</label>
                            <select name="schedule_day" id="schedule_day" class="form-control">
                                <option value="">-- Select Day --</option>
                                <option value="M">Monday</option>
                                <option value="T">Tuesday</option>
                                <option value="W">Wednesday</option>
                                <option value="TH">Thursday</option>
                                <option value="F">Friday</option>
                            </select>
                        </div>

                        <div class="form-group mb-4">
                            <label for="teacher">Select Teacher:</label>
                            <select class="form-control" name="teacher_id">
                                <?php
                                    $query = $con->prepare("SELECT * FROM teacher");
                                    $query->execute();

                                    echo "<option value='' disabled selected>Select Teacher</option>";

                                    while($row = $query->fetch(PDO::FETCH_ASSOC)) {
                                        echo "<option value='" . $row['teacher_id'] . "'>" . $row['firstname'] . " " . $row['lastname'] . "</option>";
                                    }
                                ?>
                            </select>
                        </div>
                     
                    </div>
                    <div class="modal-footer">
                        <button name="add_subject_schedule_btn" type="submit" class="btn btn-primary">Save Student</button>
                    </div>
            </form>
        </div>
        <?php
    }
?>

