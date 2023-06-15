<?php 
    include('../registrar_enrollment_header.php');
    include('../../enrollment/classes/StudentEnroll.php');
    include('../../enrollment/classes/Schedule.php');
    include('../classes/Course.php');

    $studentEnroll = new StudentEnroll($con);
    $schedule = new Schedule($con, $studentEnroll);
  
    $course = new Course($con, $studentEnroll);

    $school_year_obj = $studentEnroll->GetActiveSchoolYearAndSemester();

    $current_school_year_id = $school_year_obj['school_year_id'];
    $current_school_year_term = $school_year_obj['term'];
    $current_school_year_period = $school_year_obj['period'];

    $createUrl = base_url . "/create.php";
?>

<script src="../assets/js/common.js"></script>
<div class="row col-md-12">
    <div class="col-lg-12">
        <div class="row">
            <div class="col-lg-12 text-center">
                <!-- <h4 class="page-header">Enrolled Student List (SY: <?php echo $current_school_year_term;?>-<?php echo $current_school_year_period;?> Semester)</h4> -->
                <h4 class="text-center page-header">Grade Module</h4>
            </div>
        </div>
        
    </div>

    <div class="table-responsive" style="margin-top:2%;"> 
        <div class="row">
           <span class="mb-3">Filter Here</span>
        </div>
     
        <div class="row align-items-center">
            <div class="col-md-3">
                <select class="form-select" name="school_year_term" id="school_year_term">

                    <option  value="" selected>School-Year</option>
                    <!-- <option value="2022-2023">2022-2023</option>
                    <option value="2023-2024">2023-2024</option> -->
                    <?php
                        $selected_course_id = 0; 

                        $active_yes = "yes";
                        $get_school_year = $con->prepare("SELECT DISTINCT term FROM school_year");

                        // $get_school_year->bindValue(":school_year_term", $current_term);
                        // $get_school_year->bindValue(":active", $active_yes);
                        $get_school_year->execute();

                        while ($get_sy = $get_school_year->fetch(PDO::FETCH_ASSOC)) {

                            // $selected = ($get_sy['school_year_id'] == $student_course_id) ? 'selected' : '';
                            $selected = "";
                            echo "<option value='{$get_sy['term']}' {$selected}>{$get_sy['term']}</option>";
                        }
                    ?>
                </select>
            </div>

            <div class="col-md-3">
                <select class="form-select" name="course_id" id="course_id">
                    <option  value="" selected>Section</option>
                    
                </select>

                <!-- <select class="form-select" name="course_id" id="course_id">
                    <option  value="" selected>Section</option>
                    <?php
                        $active_yes = "yes";

                        $get_course = $con->prepare("SELECT * FROM course
                            WHERE program_id=3
                            -- WHERE school_year_term=:school_year_term
                            ");

                        // $get_course->bindValue(":school_year_term", $current_school_year_term);
                        $get_course->execute();

                        while ($get_program = $get_course->fetch(PDO::FETCH_ASSOC)) {

                            // $selected = ($get_program['school_year_id'] == $student_course_id) ? 'selected' : '';
                            $selected = "";
                            echo "<option value='{$get_program['course_id']}' {$selected}>{$get_program['program_section']} {$get_program['school_year_term']}</option>";
                        }
                    ?>
                </select> -->
                
            </div>

            <div class="col-md-3">
                <select class="form-select" name="subject_id" id="subject_id">
                    <option value="" selected>Subject</option>
                    <!-- <option value="11">Grade 11</option>
                    <option value="12">Grade 12</option> -->
                </select>
            </div>
        </div>

        <!-- <table  class="table table-striped table-bordered table-hover "  style="font-size:13px" cellspacing="0"  > 
            <thead>
                <tr class="text-center"> 
                    <th rowspan="2">Id</th>
                    <th rowspan="2">Name</th>
                    <th rowspan="2">Title</th>
                    <th rowspan="2">Unit</th>
                    <th rowspan="2">Term</th>
                    <th rowspan="2">Remarks</th>  
                </tr>	
            </thead> 	
            <tbody>
                <?php 
                    $username = "";
                    // $sectionScheduleGradeElevenFirst = $schedule->GetSectionScheduleGradeElevenFirst($username, $student_id, 11, "First");

                    $query = $con->query("SELECT * FROM course
                        WHERE active='yes'");

                    // $query->bindValue("");
                    $query->execute();

                    if($query->rowCount() > 0){
                        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {

                            echo "<tr class='text-center'>";
                                echo "<td></td>";
                            echo "</tr>";
                        }
                    }
                ?>
            </tbody>
        </table> -->
        <div id="insert_table"></div>
 
    </div>
</div>
 
<script>

    $('#school_year_term').on('change', function() {
        var school_year_term = $(this).val();

        
        $.ajax({
            url: '../ajax/get_section_from_sy.php',
            type: 'POST',
            data: {
                school_year_term: school_year_term},
            dataType: 'json',
            success: function(response) {

                // console.log(response)
                var options = '<option value="">Select Now</option>';

                $.each(response, function(index, value) {
                    options += '<option value="' + value.course_id + '">' + value.program_section +' ( '+ value.school_year_term +' )</option>';
                });
                $('#course_id').html(options);
            }
        });
    });  
               
    $('#course_id').on('change', function() {

        var course_id = parseInt($(this).val());

        // console.log(course_id)
        // var course_id = parseInt($("#course_id").val());
        // console.log(course_id);
        
        $.ajax({
            url: '../ajax/get_subject_from_section.php',
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

    $('#subject_id').on('change', function() {

        var subject_id = parseInt($(this).val());
        var course_id = parseInt($("#course_id").val());

        // console.log(course_id)
        // console.log(subject_id)

        // var course_id = parseInt($("#course_id").val());
        // console.log(course_id);
        $.ajax({
            url: '../ajax/get_student_from_section_subject.php',
            type: 'POST',
            data: {
                subject_id: subject_id,
                course_id: course_id
            },
            dataType: 'json',
            success: function(response) {

                $('#insert_table').html("");

                if(response.length > 0){

                    var html = `
                    <table id="res" class="table table-striped table-bordered table-hover "  style="font-size:13px" cellspacing="0"  > 
                        <thead>
                            <tr class='text-center'> 
                                <th rowspan="2">Studeent Id</th>
                                <th rowspan="2">Subject Id</th>
                                <th rowspan="2">Fullname</th>
                                <th rowspan="2">Semester</th>
                                <th rowspan="2">Grade Level</th>
                                <th rowspan="2">Remarks</th>
                            </tr>	
                        </thead>
                    `;
            
                    $.each(response, function(index, value) {

                        var student_id = value.student_id;

                        var url = "../enrollees/student_grade_report.php?id=" + student_id;

                        // var fullname = value.firstname + ' ' + value.lastname;
                        var fullname = value.firstname;
                        var period = value.period;
                        var subject_id = value.subject_id;
                        var course_level = value.course_level;
                        var remarks = value.remarks;

                        html += `
                            <body>
                                <tr class='text-center'>
                                    <td>${student_id}</td>
                                    <td>${subject_id}</td>
                                    <td>${fullname}</td>
                                    <td>${period}</td>
                                    <td>${course_level}</td>
                                    <td>
                                            ${remarks !== 'N/A' ? remarks : `<a href='${url}'>${remarks}</a>`}
                                    </td>
                                </tr>
                            `;
                    });

                    html += `
                        </table>
                    `;
                    $('#insert_table').html(html);
                }else{
                    var nothing = `
                        <div class="alert alert-warning text-center" role="alert">
                            No data found
                        </div>
                    `;
                    $('#insert_table').html(nothing);
                }


            }
        });
    });

    

</script>