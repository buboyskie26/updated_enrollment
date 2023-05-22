<?php  
    include('./adminHeader.php');
    include('classes/Schedule.php');
    include('../includes/classes/Teacher.php');

    $schedule = new Schedule($con, $adminLoggedInObj);

   
?>

<div class="column">

    <?php
        echo $schedule->createScheduleList();

    // require_once(__DIR__ . '../form_handlers/schedule/create_schedule.php');
    require_once('../form_handlers/schedule/create_schedule.php');

    ?>
</div>

<script src="//cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/alertify.min.js"></script>

<script>

    $(document).ready(function() {
        
        $("#add_schedule_btn").click(function(){

            $("#createScheduleModal").modal('show');
        });

        var date = new Date();
	    date.setDate(date.getDate());

        // $('#start_date').datetimepicker({
        //     startDate :date,
        //     format: 'yyyy-mm-dd hh:ii',
        //     autoclose:true
        // });

        // $('#end_date').datetimepicker({
        //     startDate :date,
        //     format: 'yyyy-mm-dd hh:ii',
        //     // format: 'hh:ii',

        //     autoclose:true
        // });
        
        // $('#start_hour').datetimepicker({
        //     format: 'hh:ii',
        //     autoclose:true

        //     // dateFormat: 'HH:mm',
        //     // timeFormat: 'HH:mm',
        //     // controlType: 'select',
        //     // oneLine: true,
        // });

        // $('#end_hour').datetimepicker({
        //     format: 'hh:ii',
        //     autoclose:true
        // });
        
        
        $(document).on('submit', '#addScheduleForm', function (e) {

            e.preventDefault();
            

            var teacher_id = parseInt($("#teacher_id").val());

            // var start_date = $("#start_date").val();
            // var end_date = $("#end_date").val();
            // var hours = $("#hours").val();

            var teacher_course_id = parseInt($("#teacher_course").val());

            var room_number = $("#room_number").val();
            
            var start_hour = $("#start_hour").val();
            var end_hour = $("#end_hour").val();
            var day = $("#day").val();

            console.log(teacher_course_id)

            var formData = new FormData(this);
            formData.append("save_ScheduleForm", true);

            $.ajax({
            method: "POST",
            url:"../ajax/schedule/add_teacher_schedule.php",
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {

                console.log(response)

                // var res = jQuery.parseJSON(response);

                // // console.log(response);

                // if(res.status === 422){
                //     $('#errorMessageForSchedule').removeClass('d-none');
                //     $('#errorMessageForSchedule').text(res.message);
                // }
                // else if(res.status == 200){

                //     $('#errorMessageForSchedule').addClass('d-none');
                //     $("#createScheduleModal").modal('hide');
                //     $('#addScheduleForm')[0].reset();

                //     alertify.set('notifier','position', 'top-right');
                //     alertify.success(res.message);

                //     $('#scheduleTable').load(location.href + " #scheduleTable");

                // }
                // else if(res.status == 500) {
                //     alert(res.message);
                // }
            }
            });

        });

    });

</script>