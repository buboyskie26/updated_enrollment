<?php

    include('../registrar_enrollment_header.php');
    include('../../enrollment/classes/StudentEnroll.php');
    include('../classes/Subject.php');


    $subject = new Subject($con, $registrarLoggedIn);

    $form = $subject->createForm();
    $createProgramSelection = $subject->createProgramSelection();
    $selectSubjectTitle = $subject->SelectSubjectTitle();

    // echo "
    //     <div class='col-md-10 row offset-md-1'>
           
    //     </div>
    // ";

?>
    <div class='col-md-10 row offset-md-1'>
       <h4 class='text-center mb-3'>Create Subject Program</h4>
        <form method='POST'>

            <?php echo $createProgramSelection;?>

            <div class='form-group mb-2'>
                <input class='form-control' type='text' placeholder='Subject Code' name='subject_code'>
            </div>

            <div class="form-group mb-2">

                <select class="form-control" name="subject_template_id" id="subject_template_id">
                    <option value="">Pick Subject</option>
                </select>
            </div>
           

            <div class='form-group mb-2'>
                <input class='form-control' type='text' placeholder='Pre-Requisite' name='pre_subject_id'>
            </div>

            <div class='form-group mb-2'>
                <select class='form-control' name='course_level'>
                    <option value='11'>Grade 11</option>
                    <option value='12'>Grade 12</option>
                </select>
            </div>
            
            <div class='form-group mb-2'>
                <select class='form-control' name='semester'>
                    <option value='First'>First</option>
                    <option value='Second'>Second</option>
                </select>
            </div>

            <div class='form-group mb-2'>
                <select class='form-control' name='subject_type'>
                    <option value='CORE SUBJECTS'>CORE SUBJECTS</option>
                    <option value='APPLIED SUBJECTS'>APPLIED SUBJECTS</option>
                    <option value='SPECIALIZED_SUBJECTS'>SPECIALIZED_SUBJECTS</option>
                </select>
            </div>

            <button type='submit' class='btn btn-primary' name='create_subject_program'>Save</button>
        </form>
    </div>


<script>
    // alert('qwe');
    $('#program_id').on('change', function() {

        var program_id = parseInt($(this).val());

           $.ajax({
            url: '../ajax/subject/populateSubject.php',
            type: 'POST',
            data: {
                program_id: program_id},
            dataType: 'json',
            success: function(response) {

                console.log(response)
                var options = '<option value="">Select Subject</option>';

                $.each(response, function(index, value) {
                    options += '<option value="' + value.subject_template_id + '">' + value.subject_title +'   (' + value.subject_type +')</option>';
                });
                $('#subject_template_id').html(options);
            }
        });
         
    });

</script>

<!-- <form method='POST'>
        <div class="modal-body">
 
            <div class="mb-3">
                <label for="">Time From</label>
                <input type="text" value="8:00" placeholder="(7:00)" name="time_from" id="time_from" class="form-control" />
            </div>

          
        </div>
        <div class="modal-footer">
            <button name="create_subject_enrollment" type="submit" class="btn btn-primary">Save Student</button>
        </div>
</form> -->