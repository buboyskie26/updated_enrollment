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

    $createUrl = base_url . "/create.php";

    $school_year_obj = $studentEnroll->GetActiveSchoolYearAndSemester();

    $current_school_year_id = $school_year_obj['school_year_id'];
    $current_school_year_term = $school_year_obj['term'];
    $current_school_year_period = $school_year_obj['period'];

    if(isset($_GET['id'])){

        $subject_schedule_id  = $_GET['id'];
        // $course_id = $_GET['cid'];

        // $section = new Section($con, $course_id);
        // $program_section = $section->GetSectionName();


        // $sql = $con->prepare("SELECT * FROM subject
        //     WHERE subject_id=:subject_id
        //     LIMIT 1");

        // $sql->bindValue(":subject_id", $subject_id);
        // $sql->execute();
        // $subject_name = "";
        // if($sql->rowCount() > 0){

        //     $row = $sql->fetch(PDO::FETCH_ASSOC);
        //     $subject_name = $row['subject_title'];

        //     if(isset($_POST['edit_subject_schedule_btn'])){

        //         // echo $subject_id;
        //         // echo "<br>";
        //         // echo $course_id;

        //         $room = $_POST['room'];
        //         $time_from = $_POST['time_from'];
        //         $time_to = $_POST['time_to'];
        //         $schedule_day = $_POST['schedule_day'];

        //         // $schedule_time = "";
        //         // $schedule_time = $time_from . "-" . $time_to;

        //         $time_from_am_pm = $_POST['time_from_am_pm'];
        //         $time_to_am_pm = $_POST['time_to_am_pm'];
        
                
        //         $teacher_id = intval($_POST['teacher_id']);

        //         $sql = $con->prepare("INSERT INTO subject_schedule
        //             (room, schedule_day, time_from, time_to, schedule_time, school_year_id, course_id, subject_id, teacher_id)
        //             VALUES(:room, :schedule_day, :time_from, :time_to, :schedule_time, :school_year_id, :course_id, :subject_id, :teacher_id)");
                
        //         $schedule_time = $time_from . ' '. $time_from_am_pm . ' - ' . $time_to. ' ' . $time_to_am_pm;

        //         // echo $schedule_time;
        //         $sql->bindValue(":room", $room);
        //         $sql->bindValue(":schedule_day", $schedule_day);
        //         $sql->bindValue(":time_from", $time_from);
        //         $sql->bindValue(":time_to", $time_to);
        //         $sql->bindValue(":schedule_time", $schedule_time);
        //         $sql->bindValue(":school_year_id", $current_school_year_id);
        //         $sql->bindValue(":course_id", $course_id);
        //         $sql->bindValue(":subject_id", $subject_id);
        //         $sql->bindValue(":teacher_id", $teacher_id);

        //         if($sql->execute()){
        //             header("Location: course_view_subject.php?id=$course_id");
        //         }

        //     }

        // }

        $get_schedule = $con->prepare("SELECT * FROM subject_schedule
            WHERE subject_schedule_id=:subject_schedule_id");

        $get_schedule->bindValue(":subject_schedule_id", $subject_schedule_id);
        $get_schedule->execute();

        if($get_schedule->rowCount() > 0){

            // echo $subject_id;
            $row = $get_schedule->fetch(PDO::FETCH_ASSOC);
            $teacher_id = $row['teacher_id'];
            $schedule_day = $row['schedule_day'];
            $course_id = $row['course_id'];


            if(isset($_POST['edit_subject_schedule_btn'])){

                $room = $row['room'];

                $teacher_id_v2 = $_POST['teacher_id'];
                $schedule_day_v2 = $_POST['schedule_day'];

                $time_from = $_POST['time_from'];
                $time_to = $_POST['time_to'];

                $time_from_am_pm  = $_POST['time_from_am_pm'];
                $time_to_am_pm  = $_POST['time_to_am_pm'];
            

                $sql = $con->prepare("UPDATE subject_schedule       
                    SET room = :room,
                        schedule_day = :schedule_day,
                        time_from = :time_from,
                        time_to = :time_to,
                        schedule_time = :schedule_time,
                        teacher_id = :teacher_id
                    WHERE subject_schedule_id = :subject_schedule_id");

                $schedule_time = $time_from . ' ' . $time_from_am_pm . ' - ' . $time_to . ' ' . $time_to_am_pm;

                $sql->bindValue(":room", $room);
                $sql->bindValue(":schedule_day", $schedule_day_v2);
                $sql->bindValue(":time_from", $time_from);
                $sql->bindValue(":time_to", $time_to);
                $sql->bindValue(":schedule_time", $schedule_time);
                $sql->bindValue(":teacher_id", $teacher_id_v2);
                $sql->bindValue(":subject_schedule_id", $subject_schedule_id);

                if($sql->execute()){
                    header("Location: course_view_subject.php?id=$course_id");
                }
            }

            ?>
            <div class='col-md-8 row offset-md-1'>

                <h5 class="text-center">S.Y (<?php echo $current_school_year_term;?>) <?php echo $current_school_year_period;?> Semester</h5>
                <div class="row justify-content-center">
                    <div class="col-sm-6">
                        <!-- <h4 class="text-center">Section: <?php echo $program_section;?></h4> 
                        <p class="text-center">Edit Schedule on <span><?php echo $subject_name?></span></p> -->
                    </div>
                </div>
                <form method='POST'>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="">Room number</label>
                                <input value='<?php echo $row['room'];?>' type="text" placeholder="(Room: 501)" name="room" id="room" class="form-control" />
                            </div>

                            <div class="mb-3">
                                <label for="">Time From</label>
                                <input type="text" value='<?php echo $row['time_from'];?>' placeholder="(7:00)" name="time_from" id="time_from" class="form-control" />
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
                                <input type="text" value='<?php echo $row['time_to'];?>' placeholder="(7:00)" name="time_to" id="time_to" class="form-control" />
                            </div>

                            <div class="mb-3">
                                <label for="">Time to AM/PM</label>
                                <select name="time_to_am_pm" id="time_to_am_pm" class="form-control">
                                    <option value="AM">AM</option>
                                    <option selected value="PM">PM</option>
                                </select>
                            </div>
    
                            <div class="mb-3">
                                <label class="mb-1" for="schedule_day">Day</label>
                               <select name="schedule_day" id="schedule_day" class="form-control">
                                    <option value="">-- Select Day --</option>
                                    <option value="M"<?php if($schedule_day == 'M') echo ' selected'; ?>>Monday</option>
                                    <option value="T"<?php if($schedule_day == 'T') echo ' selected'; ?>>Tuesday</option>
                                    <option value="W"<?php if($schedule_day == 'W') echo ' selected'; ?>>Wednesday</option>
                                    <option value="Th"<?php if($schedule_day == 'Th') echo ' selected'; ?>>Thursday</option>
                                    <option value="F"<?php if($schedule_day == 'F') echo ' selected'; ?>>Friday</option>
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
                                            $selected = "";
                                            if($row['teacher_id'] == $teacher_id) {
                                                $selected = "selected";
                                            }
                                            echo "<option $selected  value='" . $row['teacher_id'] . "'>" . $row['firstname'] . " " . $row['lastname'] . "</option>";
                                        }

                                    ?>
                                </select>
                            </div>
                        
                        </div>
                        <div class="modal-footer">
                            <button name="edit_subject_schedule_btn" type="submit" class="btn btn-primary">Save Student</button>
                        </div>
                </form>
            </div>
            <?php
        }

    }
?>

