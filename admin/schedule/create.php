<?php

    include('../admin_enrollment_header.php');
    include('../../enrollment/classes/StudentEnroll.php');

    // TODO: 
    if(isset($_POST['create_schedule_enrollment'])){
        
        $enroll = new StudentEnroll($con);

        $createUrl = base_url . "/index.php";

        $schedule_day = $_POST['schedule_day'];
        $time_from = $_POST['time_from'];
        $time_to = $_POST['time_to'];

        $schedule_time = $time_from . "-" . $time_to;

        // current school year id in the system.
        $school_year_obj = $enroll->GetActiveSchoolYearAndSemester();

        $school_year_id = $school_year_obj['school_year_id'];

        // $section = $_POST['section'];
        $course_id =$_POST['course_id'];
        $subject_id = $_POST['subject_id'];
        $teacher_id = $_POST['teacher_id'];

        // echo $school_year_id;
 

        // Get room depending the Section room that already been assigned.
        $room = null;

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

        if($section_query->rowCount() > 0){

            $row = $section_query->fetch(PDO::FETCH_ASSOC);

            $section = $row['program_section'];
            $room = $row['room'];
            // echo $section;

            $sql = $con->prepare("INSERT INTO subject_schedule
                (room, schedule_day, time_from, time_to, schedule_time, school_year_id, section, course_id, subject_id, teacher_id)
                VALUES(:room, :schedule_day, :time_from, :time_to, :schedule_time, :school_year_id, :section, :course_id, :subject_id, :teacher_id)");
            
            $sql->bindValue(":room", $room);
            $sql->bindValue(":schedule_day", $schedule_day);
            $sql->bindValue(":time_from", $time_from);
            $sql->bindValue(":time_to", $time_to);
            $sql->bindValue(":schedule_time", $schedule_time);
            $sql->bindValue(":school_year_id", $school_year_id);
            $sql->bindValue(":section", $section);
            $sql->bindValue(":course_id", $course_id);
            $sql->bindValue(":subject_id", $subject_id);
            $sql->bindValue(":teacher_id", $teacher_id);
            $sql->execute();

            // if($sql->execute()){
            //     header("Location: $createUrl");
            // }
        }
        else{
            echo "Went wrong of getting course_section";
            echo $course_id;

            // exit();
        }

    }
?>

<div class='col-md-8 row offset-md-1'>
    <h4 class='text-center mb-3'>Create Subject Schedule</h4>
    <form method='POST'>
            <div class="modal-body">
                <!-- <div class="mb-3">
                    <label for="">Room number</label>
                    <input value='55' type="text" placeholder="(Room: 501)" name="room" id="room" class="form-control" />
                </div> -->

                <div class="mb-3">
                    <label for="">Time From</label>
                    <input type="text" value="8:00" placeholder="(7:00)" name="time_from" id="time_from" class="form-control" />
                </div>

                <div class="mb-3">
                    <label for="">Time to</label>
                    <input type="text" value="9:30" placeholder="(7:00)" name="time_to" id="time_to" class="form-control" />
                </div>

                <div class="mb-3">
                    <label for="">Day</label>
                    <input value='M' type="text" placeholder="(M:Monday & T:Tueesday)" name="schedule_day" id="schedule_day" class="form-control" />
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

                    <!-- <select class="form-control" id="select_semester" name="school_year_id">
                        <?php
                            $query = $con->prepare("SELECT * FROM school_year");
                            $query->execute();

                            echo "<option value='' disabled selected>Select Semester</option>";

                            while($row = $query->fetch(PDO::FETCH_ASSOC)) {
                                echo "<option value='" . $row['school_year_id'] . "'>" . $row['school_year_term'] . " " . $row['period'] . "</option>";
                            }
                        ?>
                    </select> -->

                    <!-- <select class="form-control" name="school_year_id" id="select_semester">
                        <option value="">Select Semester</option>
                    </select> -->

                    <select class="form-control" name="school_year_id" id="select_semester">
                        <option value="" disabled>Select Semester</option>
                        <option value="First">First Semester</option>
                        <option value="Second">Second Semester</option>

                    </select>

                    <!-- <input type="text" placeholder="Semester Period" name="semester" id="semester" class="form-control" /> -->
                </div>

                <div class="form-group mb-4">
                    <label for="subject_id">Subject:</label>

                    <select class="form-control" name="subject_id" id="subject_id">
                        <option value="">Pick Subject</option>
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
                <button name="create_schedule_enrollment" type="submit" class="btn btn-primary">Save Student</button>
            </div>
    </form>
</div>


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