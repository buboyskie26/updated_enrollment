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

        $teacher_id = $_GET['id'];

        $stmt = $con->prepare("SELECT * FROM teacher WHERE teacher_id = ?");

        $stmt->execute([$teacher_id]);

        $teacher = $stmt->fetch();

        if (!$teacher) {
            // handle error, the 'id' value does not exist in the database
            echo "error id";
            exit();
        }


    if(isset($_POST['create_schedule_enrollment'])
        && isset($_POST['course_id'])
        && isset($_POST['subject_id'])
        && isset($_POST['room'])
        && isset($_POST['schedule_day'])
        && isset($_POST['time_from'])
        && isset($_POST['time_to'])
        && isset($_POST['time_from_am_pm'])
        && isset($_POST['time_to_am_pm'])
        ){
        
        $room = $_POST['room'];

        $schedule_day = $_POST['schedule_day'];

        $time_from = $_POST['time_from'];

        $time_to = $_POST['time_to'];

        $schedule_time = $time_from . "-" . $time_to;

        // $section = $_POST['section'];
        $course_id = $_POST['course_id'];
        $subject_id = $_POST['subject_id'];
        $teacher_id = $teacher_id;

        $time_from_am_pm = $_POST['time_from_am_pm'];
        $time_to_am_pm = $_POST['time_to_am_pm'];

        // $room = null;

        if($course_id != 0){
            // $room_query = $con->prepare("SELECT room FROM course
            //     WHERE course_id=:course_id
            //     LIMIT 1");

            // $room_query->bindValue(":course_id", $course_id);
            // $room_query->execute();

            $section_query = $con->prepare("SELECT program_section, room FROM course
                WHERE course_id=:course_id
                LIMIT 1");
            $section_query->bindValue(":course_id", $course_id);
            $section_query->execute();

        }

            $sql = $con->prepare("INSERT INTO subject_schedule
                (room, schedule_day, time_from, time_to, schedule_time, school_year_id, course_id, subject_id, teacher_id)
                VALUES(:room, :schedule_day, :time_from, :time_to, :schedule_time, :school_year_id, :course_id, :subject_id, :teacher_id)");
            

            $schedule_time = $time_from . ' '. $time_from_am_pm . ' - ' . $time_to. ' ' . $time_to_am_pm;
            $sql->bindValue(":room", $room);
            $sql->bindValue(":schedule_day", $schedule_day);
            $sql->bindValue(":time_from", $time_from);
            $sql->bindValue(":time_to", $time_to);
            $sql->bindValue(":schedule_time", $schedule_time);
            $sql->bindValue(":school_year_id", $current_school_year_id);
            $sql->bindValue(":course_id", $course_id);
            $sql->bindValue(":subject_id", $subject_id);
            $sql->bindValue(":teacher_id", $teacher_id);
            // $sql->execute();

            if($sql->execute()){
            // if(true){
                AdminUser::success("Subject $subject_id has been inserted to the teacher", 'registrar_access_index.php');
                // header("Location: registrar_access_index.php");
            }
        }

        ?>

        <div class='col-md-8 row offset-md-1'>
            <h5 class="text-center">S.Y (<?php echo $current_school_year_term;?>) <?php echo $current_school_year_period;?> Semester</h5>
            <h3 class='text-center mb-3'>Create Schedule of <?php echo $teacher['firstname'] . " " . $teacher['lastname'];?> </h3>
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
                            <label for="schedule_day">Day</label>
                            <select name="schedule_day" id="schedule_day" class="form-control">
                                <option value="">-- Select Day --</option>
                                <option value="M">Monday</option>
                                <option value="T">Tuesday</option>
                                <option value="W">Wednesday</option>
                                <option value="Th">Thursday</option>
                                <option value="F">Friday</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="">Strand Section</label>
                            <!-- <input type="text" placeholder="Section" name="section" id="section" class="form-control" /> -->
                            <select class="form-control" name="course_id" id="course_id">
                                <?php
                                    $query = $con->prepare("SELECT * FROM course
                                        WHERE course_level > :course_level
                                        AND active=:active
                                    ");
                                    $query->bindValue(":course_level", 10);
                                    $query->bindValue(":active", "yes");
                                    $query->execute();

                                    echo "<option value='Course-Section' disabled selected>Select-Section</option>";

                                    while($row = $query->fetch(PDO::FETCH_ASSOC)) {
                                        echo "<option value='" . $row['course_id'] . "'>" . $row['program_section'] . "</option>";
                                    }
                                ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="">Semester</label>
                            <input type="text" readonly value="<?php echo $current_school_year_period?>" placeholder="Semester Period" name="semester" id="semester" class="form-control" />
                        </div>

                        <div class="form-group mb-4">
                            <label for="subject_id">Subject:</label>

                            <select class="form-control" name="subject_id" id="subject_id">
                                <option value="">Pick Subject</option>
                            </select>
                        </div>
                    

                        <!-- <div class="form-group mb-4">
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
                        </div> -->
                    </div>
                    <div class="modal-footer">
                        <button name="create_schedule_enrollment" type="submit" class="btn btn-primary">Save Student</button>
                    </div>
            </form>
        </div>
        <?php
    }
?>

<script>
    
    $('#course_id').on('change', function() {

        var course_id = parseInt($(this).val());

        // var course_id = parseInt($("#course_id").val());
        // console.log(course_id);
        
        if (!course_id) {
            $('#select_semester').html('<option value="">Select Semester</option>');
            return;
        }

        $.ajax({
            url: '../ajax/get_semester.php',
            type: 'POST',
            data: {
                course_id: course_id},
            dataType: 'json',
            success: function(response) {

                // console.log(response)
                var options = '<option value="">Select a Subject</option>';

                $.each(response, function(index, value) {
                    options += '<option value="' + value.subject_id + '">' + value.subject_title +'</option>';
                });
                $('#subject_id').html(options);
            }
        });
    });

    // When the teacher select changes
    // $('#select_semester').on('change', function() {
        
    //     var school_year_id = parseInt($(this).val());

    //     var course_id = parseInt($("#course_id").val());

    //     if (!school_year_id) {
    //         $('#subject_id').html('<option value="">Select Subject</option>');
    //         return;
    //     }

    //     $.ajax({
    //         url: '../ajax/get_semester_subject.php',
    //         type: 'POST',
    //         data: {
    //             school_year_id: school_year_id,
    //             course_id: course_id},

    //         dataType: 'json',
    //         success: function(response) {

    //             console.log(response)

    //             var options = '<option value="">Select a Subject</option>';

    //                 $.each(response, function(index, value) {

    //                 options += '<option value="' + value.subject_id + '">' + value.subject_code + '</option>';

    //                 // console.log(value)
    //             });
    //             $('#subject_id').html(options);


    //         }
    //     });

    // });
</script>
