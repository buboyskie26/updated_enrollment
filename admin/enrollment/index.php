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
                <h4 class="text-center page-header">Enrolled History Student List </h4>
            </div>
        </div>
    </div>

    <div class="table-responsive" style="margin-top:2%;"> 
        <div class="row">
            <a href="<?php echo directoryPath . "create.php"; ?>">
                <button class="mb-3 btn btn-success">Enroll O.S Here</button>
            </a> 
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

            <div class="col-md-2">
                <!-- <select class="form-select" name="course_id" id="course_id"> -->
                <select class="form-select" name="program_section" id="program_section">
                    <option  value="" selected>Strand</option>
                    <option value="STEM">STEM</option>
                    <option value="HUMSS">HUMSS</option>

                    <!-- <?php
                        $active_yes = "yes";
                        $get_school_year = $con->prepare("SELECT * FROM program
                            WHERE department_id=4");

                        // $get_school_year->bindValue(":school_year_term", $current_term);
                        // $get_school_year->bindValue(":active", $active_yes);
                        $get_school_year->execute();

                        while ($get_program = $get_school_year->fetch(PDO::FETCH_ASSOC)) {

                            // $selected = ($get_program['school_year_id'] == $student_course_id) ? 'selected' : '';
                            $selected = "";
                            echo "<option value='{$get_program['program_id']}' {$selected}>{$get_program['acronym']}</option>";
                        }
                    ?> -->
                </select>
                
            </div>

            <div class="col-md-2">
            <select class="form-select" name="course_level" id="course_level">
                <option value="0" selected>Grade</option>
                <option value="11">Grade 11</option>
                <option value="12">Grade 12</option>
            </select>
            </div>
            <div class="col-md-3">
            <select class="form-select" name="term" id="term">
                <option value="" selected>Semester</option>
                <option value="First">First Semester</option>
                <option value="Second">Second Semester</option>
            </select>
            </div>

            <div class="col-md-2">
            <div class="input-group">
                <button id="submit-btn" class="btn btn-primary" type="button">Submit</button>
            </div>
            </div>
        </div>

        <div id="insert_table"></div>

        <!-- <table  class="table table-striped table-bordered table-hover "  style="font-size:13px" cellspacing="0"  > 
            <thead>
                <tr class="text-center"> 
                    <th rowspan="2">Student Id</th>
                    <th rowspan="2">Fullname</th>
                    <th rowspan="2">S.Y</th>
                    <th rowspan="2">Term</th>  
                    <th rowspan="2">Type</th>  
                    <th rowspan="2">Program</th>
                    <th rowspan="2">Section</th>
                </tr>	
            </thead> 	
            <tbody>
                <?php 

                    $enrolled = "enrolled";
                        
                    $query = $con->prepare("SELECT 
                        t1.course_id,

                        t2.firstname, t2.lastname, t2.course_level, t2.student_unique_id,

                        t3.program_section,

                        t4.term, t4.period
                        FROM enrollment as t1

                        LEFT JOIN student as t2 ON t2.student_id = t1.student_id
                        LEFT JOIN course as t3 ON t3.course_id = t1.course_id
                        LEFT JOIN school_year as t4 ON t4.school_year_id = t1.school_year_id

                        WHERE t1.enrollment_status=:enrolled");

                    $query->bindValue(":enrolled", $enrolled);
                    $query->execute();

                    if($query->rowCount() > 0){
                        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {

                            $course_id = $row['course_id'];
                            $firstname = $row['firstname'];
                            $student_unique_id = $row['student_unique_id'];
                            $lastname = $row['lastname'];
                            $fullname = $firstname . " " . $lastname;
                            $program_section = $row['program_section'];
                            $term = $row['term'];
                            $period = $row['period'];
                            
                            $button = "";

                            $url = directoryPath . "strand_show.php?id=$course_id";

                            $section_url = "$program_section";

                             

                            $editUrl = directoryPath . "edit.php?section_id=$course_id";
                            echo "<tr class='text-center'>";
                                echo "<td>$student_unique_id</td>";
                                echo "<td>$fullname</td>";
                                echo "<td>$term</td>";
                                echo "<td>$period</td>";
                                echo "<td>SHS</td>";
                                echo "<td>Program</td>";
                                echo "<td>$program_section</td>";
                                
                                echo "
                                    <td>
                                        $button
                                        <a href='$editUrl'>
                                            <button class='btn btn-primary btn-sm'>Edit</button>
                                        </a>
                                    </td>
                                ";
                            echo "</tr>";

                        }
                    }
                ?>
            </tbody>
        </table> -->

    </div>
</div>
 
<script>
	$(document).ready(function() {

        $('#submit-btn').click(function(e) {
            e.preventDefault(); // prevent the 


            var school_year_term = $('#school_year_term').val();
            var course_level = $('#course_level').val();
            var term = $('#term').val();
            var course_id = $('#course_id').val();
            var program_section = $('#program_section').val();

            // console.log(term)
            $.ajax({
                url: '../ajax/enrollee/filter_enrollment.php',
                type: 'POST',
                data: {
                    course_id,
                    term,
                    course_level,
                    school_year_term,
                    program_section
                },

                dataType: 'json',
                success: function(response) {

                    console.log(response);

                    if (response.length == 0) {
                        // If the array is empty, empty the table
                        $('#insert_table').html('');
                        return;
                    }

                    $('#insert_table').empty();

                    var html = `
					    <table  class="table table-striped table-bordered table-hover "  style="font-size:13px" cellspacing="0"  > 
                        <thead>
                            <tr class='text-center'> 
                                <th rowspan="2">Studeent Id</th>
                                <th rowspan="2">Fullname</th>
                                <th rowspan="2">Semester</th>  
                                <th rowspan="2">Term</th>  
                                <th rowspan="2">Type</th>  
                                <th rowspan="2">Grade Level</th>  
                                <th rowspan="2">Section</th>  
                            </tr>	
                        </thead>
				    `;
                    
                    $.each(response, function(index, value) {

                        var fullname = value.firstname + ' ' + value.lastname;
                        var student_unique_id = value.student_unique_id;
                        var sy = value.term;
                        var period = value.period;
                        var type = 'SHS';
                        var program_section = value.program_section;

                        html += `
                            <body>
                                <tr class='text-center'>
                                    <td>${value.student_unique_id}</td>
                                    <td>${fullname}</td>
                                    <td>${value.period}</td>
                                    <td>${sy}</td>
                                    <td>${type}</td>
                                    <td>${value.course_level}</td>
                                    <td>${value.program_section}</td>
                                </tr>
                            `;
                    });

                    html += `
                        </table>
                    `;
                    $('#insert_table').html(html);
                }
            });
        });
    });

</script>