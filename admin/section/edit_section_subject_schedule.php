<?php

    include('../registrar_enrollment_header.php');
    include('../../enrollment/classes/StudentEnroll.php');
    require_once('../../admin/classes/AdminUser.php');

    if(!AdminUser::IsRegistrarAuthenticated()){
        header("Location: /dcbt/registrar_login.php");
        exit();
    }

    if(isset($_GET['id'])){

        $enroll = new StudentEnroll($con);

        $school_year_obj = $enroll->GetActiveSchoolYearAndSemester();

        $current_school_year_id = $school_year_obj['school_year_id'];
        $current_school_year_term = $school_year_obj['term'];
        $current_school_year_period = $school_year_obj['period'];

        $subject_schedule = $_GET['id'];

        $stmt = $con->prepare("SELECT subject_schedule.*, t2.* 
        
            FROM subject_schedule 

            INNER JOIN subject as t2 ON t2.subject_id = subject_schedule.subject_id
            WHERE subject_schedule_id = ?
            ");

        $stmt->execute([$subject_schedule]);

        $subjectScheduleObj = $stmt->fetch();

        if (!$subjectScheduleObj) {
            // handle error, the 'id' value does not exist in the database
            echo "error id";
            exit();
        }

        $course_id = $subjectScheduleObj['course_id'];
        $subject_schedule_id = $subjectScheduleObj['subject_schedule_id'];

    if(isset($_POST['edit_section_subject_schedule_btn'])
        // && isset($_POST['course_id'])
        && isset($_POST['teacher_id'])
        && isset($_POST['room'])
        && isset($_POST['schedule_day'])
        && isset($_POST['time_from'])
        && isset($_POST['time_to'])
        && isset($_POST['time_from_am_pm'])
        && isset($_POST['time_to_am_pm'])
        ){
            // echo "qwe";
            
            $room = $_POST['room'];

            $schedule_day = $_POST['schedule_day'];

            $time_from = $_POST['time_from'];

            $time_to = $_POST['time_to'];

            // $course_id = $subjectScheduleObj['course_id'];

            $schedule_time = $time_from . "-" . $time_to;

            $teacher_id = $_POST['teacher_id'];

            // echo $teacher_id;

            $teacher_id = $teacher_id;

            $time_from_am_pm = $_POST['time_from_am_pm'];
            $time_to_am_pm = $_POST['time_to_am_pm'];


            $sql = $con->prepare("UPDATE subject_schedule
                                SET room = :room,
                                    schedule_day = :schedule_day,
                                    time_from = :time_from,
                                    time_to = :time_to,
                                    schedule_time = :schedule_time,
                                    school_year_id = :school_year_id,
                                    -- subject_id = :subject_id,
                                    teacher_id = :teacher_id,
                                    course_id = :course_id
                                WHERE subject_schedule_id = :subject_schedule_id");

            $schedule_time = $time_from . ' ' . $time_from_am_pm . ' - ' . $time_to . ' ' . $time_to_am_pm;

            $sql->bindValue(":room", $room);
            $sql->bindValue(":schedule_day", $schedule_day);
            $sql->bindValue(":time_from", $time_from);
            $sql->bindValue(":time_to", $time_to);
            $sql->bindValue(":schedule_time", $schedule_time);
            $sql->bindValue(":school_year_id", $current_school_year_id);
            // $sql->bindValue(":subject_id", $subject_id);
            $sql->bindValue(":teacher_id", $teacher_id);
            $sql->bindValue(":course_id", $course_id);
            $sql->bindValue(":subject_schedule_id", $subject_schedule_id); // Assuming you have the subject_schedule_id to identify the row to update

            if($sql->execute()){
                AdminUser::success("Subject Schedule: $subject_schedule has been changed",
                    "");
            }else{
                echo "not";
            }
        
        }

        ?>

        <div class='col-md-10 row offset-md-1'>

            <div class="card">
                <div class="card-header">
                    <h4 class='mb-3'>Edit schedule of <?php echo $subjectScheduleObj['subject_title']?> </h4>
                    <h5 class="text-muted text-center">S.Y (<?php echo $current_school_year_term;?>) <?php echo $current_school_year_period;?> Semester</h5>
                </div>

                <div class="card-body">

                    <form method='POST'>
                            <div class="modal-body">

                                <div class="mb-3">
                                    <label for="">Instructor</label>

                                    <select class="form-control" name="teacher_id" id="teacher_id">
                                        <?php
                                            $query = $con->prepare("SELECT * FROM teacher");
                                            $query->execute();
                                            
                                            echo "<option value='' disabled selected>Choose Teacher</option>";

                                            $schedule_teacher_id = $subjectScheduleObj['teacher_id'];
                                            if ($query->rowCount() > 0) {
                                                while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
                                                    $selected = "";  

                                                    // Add condition to check if the option should be selected
                                                    if ($row['teacher_id'] == $schedule_teacher_id) {
                                                        $selected = "selected";
                                                    }

                                                    echo "<option value='" . $row['teacher_id'] . "' $selected>" . $row['firstname'] . " " . $row['lastname'] . "</option>";
                                                }
                                            }
                                        ?>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="">Subject</label>
                                    <input type="text" name="subject_id" readonly value="<?php echo $subjectScheduleObj['subject_title']?>" class="form-control">
                                </div>

                                <div class="mb-3">
                                    <label for="">Room number</label>
                                    <input value="<?php echo $subjectScheduleObj['room'] ?>" type="text" placeholder="(Room: 501)" name="room" id="room" class="form-control" />
                                </div>

                                <div class="mb-3">
                                    <label for="">Time From</label>
                                    <input type="text" value="<?php echo $subjectScheduleObj['time_from'] ?>" placeholder="(7:00)" name="time_from" id="time_from" class="form-control" />
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
                                    <input type="text" value="<?php echo $subjectScheduleObj['time_to'] ?>" placeholder="(7:00)" name="time_to" id="time_to" class="form-control" />
                                </div>

                                <div class="mb-3">
                                    <label for="">Time to AM/PM</label>
                                    <select name="time_to_am_pm" id="time_to_am_pm" class="form-control">
                                        <option value=AM">AM</option>
                                        <option selected value="PM">PM</option>
                                    </select>
                                </div>
        
                                <div class="mb-3">
                                    <label for="schedule_day">Day</label>
                                    <select name="schedule_day" id="schedule_day" class="form-control">
                                        <option value="M" <?php echo ($subjectScheduleObj['schedule_day'] == 'M') ? 'selected' : ''; ?>>Monday</option>
                                        <option value="T" <?php echo ($subjectScheduleObj['schedule_day'] == 'T') ? 'selected' : ''; ?>>Tuesday</option>
                                        <option value="W" <?php echo ($subjectScheduleObj['schedule_day'] == 'W') ? 'selected' : ''; ?>>Wednesday</option>
                                        <option value="Th" <?php echo ($subjectScheduleObj['schedule_day'] == 'Th') ? 'selected' : ''; ?>>Thursday</option>
                                        <option value="F" <?php echo ($subjectScheduleObj['schedule_day'] == 'F') ? 'selected' : ''; ?>>Friday</option>
                                    </select>
                                </div>



                                <div class="mb-3">
                                    <label for="">Semester</label>
                                    <input type="text" readonly value="<?php echo $current_school_year_period?>" placeholder="Semester Period" name="semester" id="semester" class="form-control" />
                                </div>
                            </div>

                            <div class="modal-footer">
                                <button name="edit_section_subject_schedule_btn" type="submit"
                                class="btn btn-primary">Save Schedule</button>
                            </div>
                    </form>

                </div>
            </div>


        </div>
        <?php
    }
?>