


<div class="modal fade" id="subjectStrandEditModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Edit Student</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="editSubjectStrand">
                <div class="modal-body">

                    <div id="errorMessageUpdate" class="alert alert-warning d-none"></div>

                    <!-- <div class='form-group mb-2'>
                        <label for="" class="mb-2">Template</label>
                        <select class='form-control' id='edit_subject_template_id'
                            name='edit_subject_template_id'>

                            <option value='11'>Grade 11</option>
                            <option value='12'>Grade 12</option>
                        </select>
                    </div> -->
                    <?php echo $selectSubjectEdit;?>

<!-- 
                    <div class='form-group mb-2 '>
                            <label for="" class="mb-2">Code</label>
                            <input class='form-control' type='text'
                                placeholder='Subject Code' id="edit_subject_code"
                                name='edit_subject_code'>
                    </div> -->

                    <div class='form-group mb-2'>
                        <label for="" class="mb-2">Grade Level</label>
                        <select class='form-control' id='edit_course_level' name='edit_course_level'>
                            <option value='11'>Grade 11</option>
                            <option value='12'>Grade 12</option>
                            <option value='1'>1st Year</option>
                            <option value='2'>2nd Year</option>
                            <option value='3'>3rd Year</option>
                            <option value='4'>4th Year</option>
                        </select>
                    </div>

                    <div class='form-group mb-2'>
                        <label for="" class="mb-2">Semester</label>
                        <select class='form-control' id="edit_semester" name='edit_semester'>
                            <option value='First'>First</option>
                            <option value='Second'>Second</option>
                        </select>
                    </div>

                    <!-- <div class="mb-3">
                        <label for="">Course Name</label>
                        <input type="text" name="course_name" id="course_name" class="form-control" />
                    </div> -->

                </div>

                <div class="modal-footer">
                    <input type="hidden" value="<?php echo ""?>" id="to_subject_program_id" name="to_subject_program_id">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Update Student</button>
                </div>

            </form>
        </div>
    </div>
</div>
<script>

    $(document).on('click', '.editSubjectStrandBtn', function () {

        var subject_program_id = $(this).val();

        // $("#edit_subject_code").val("");
        
        $("#edit_course_level").val("");
        $("#edit_semester").val("");
        $("#edit_subject_template_id").val("");


        $.ajax({
            type: "GET",
            url: "../ajax/subject/strand_subject_edit_modal.php?id=" + subject_program_id,
            // dataType: "json",
            success: function (response) {

                var res = JSON.parse(response)

                // console.log(res)
                // $("#edit_subject_code").val(res.subject_code);
                $("#edit_course_level").val(res.course_level);
                $("#edit_semester").val(res.semester);

                $("#edit_subject_template_id").val(res.subject_template_id);

                $("#to_subject_program_id").val(res.subject_program_id);

                $('#subjectStrandEditModal').modal('show');


                // console.log(response)
                
            }
        });
    });

    $(document).on('submit', '#editSubjectStrand', function (e) {

        e.preventDefault();
        // console.log('click')

        // var formData = new FormData(this);
        // formData.append("save_student", true);

        // var subject_code = $("#edit_subject_code").val();
        var course_level = $("#edit_course_level").val();
        var semester = $("#edit_semester").val();
        var subject_program_id  = $("#to_subject_program_id").val();
        var edit_subject_template_id  = $("#edit_subject_template_id").val();

        $.ajax({
            url: "../ajax/subject/strand_subject_edit_modal.php",
            type: "POST",
            data: {
                // subject_code,
                course_level,
                semester,
                subject_program_id,
                edit_subject_template_id

            },
            // dataType: 'json',
            success: function (response) {
                console.log(response)

                if(response == "success"){

                    $('#strand_subject_view_table').load(location.href + " #strand_subject_view_table");
                    $('#subjectStrandEditModal').modal('hide');
                    $('#editSubjectStrand')[0].reset();
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: `Successfully edited successfully!`,
                    });
                }
            }
        });

    });
</script>