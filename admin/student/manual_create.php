<?php 

    include('../registrar_enrollment_header.php');
    include('../../enrollment/classes/StudentEnroll.php');
    include('../../enrollment/classes/Pending.php');
    include('../../enrollment/classes/OldEnrollees.php');
    include('../classes/Course.php');

    if(!AdminUser::IsRegistrarAuthenticated()){

        header("Location: /dcbt/adminLogin.php");
        exit();
    }

    $enroll = new StudentEnroll($con);
    $pending = new Pending($con);
    $old_enroll = new OldEnrollees($con, $enroll);
    $course = new Course($con, $enroll);

	$school_year_obj = $enroll->GetLatestSchoolYearAndSemester();

	$currentYear = $school_year_obj[0];
	$current_semester = $school_year_obj[1];
	$school_year_id = $school_year_obj[2];
	$strandSelection = $enroll->CreateRegisterStrand();

    $generateStudentUniqueId = $enroll->GenerateUniqueStudentNumber();
    $course_dropdown = $course->GetCourseAvailableSelectionForCurrentSY();



    if (isset($_POST['manual_creation_btn'])
        && isset($_POST['FNAME'])
        && isset($_POST['LNAME'])
        && isset($_POST['MI'])
        && isset($_POST['PADDRESS'])
        && isset($_POST['optionsRadios'])
        && isset($_POST['lrn'])
        && isset($_POST['course_level'])
        && isset($_POST['STRAND'])
        && isset($_POST['student_status'])
        ) {

        $fname 	      =  $_POST['FNAME'];
        $lname  	  =  $_POST['LNAME'];
        $mi           =  $_POST['MI'];
        $address      =  $_POST['PADDRESS'];
        $sex          =  $_POST['optionsRadios'];
        $lrn    =  $_POST['lrn']; 
        $course_level    =  $_POST['course_level']; 
        $program_id    =  $_POST['STRAND']; 
        $student_status    =  $_POST['student_status']; 

        // echo "asd";

        $insert = $con->prepare("INSERT INTO pending_enrollees
            (firstname,lastname, middle_name, lrn, sex, program_id, student_status)
            VALUES(:firstname,:lastname, :middle_name, :lrn, :sex, :program_id, :student_status)");
        
        $insert->bindValue(":firstname", $fname);
        $insert->bindValue(":lastname", $lname);
        $insert->bindValue(":middle_name", $mi);
        $insert->bindValue(":lrn", $lrn);
        $insert->bindValue(":sex", $sex);
        $insert->bindValue(":program_id", $program_id);
        $insert->bindValue(":student_status", $student_status);
        
        if($insert->execute()){

            $pending_enrollees_id = $con->lastInsertId();

            $url = "../admission/process_enrollment.php?step1=true&id=$pending_enrollees_id";
            AdminUser::success("Successfully Created", "$url");
            exit();
        }
    }

?>

<div class="row col-md-12">
    <div class="card">
        <div class="card-header">
            <div class="col-md-8"><h2>Manual Student Sheet</h2></div>
        </div>
        <div class="card-header">
            <div class="col-md-4"><label>Academic Year: <?php echo $currentYear; ?> <span>(<?php echo $current_semester; ?>)</span> </label></div>
        </div>
        <div class="card-body">
            <form  method="post" >
                <div class="table-responsive">
                        <table class="table">
                            <tr>
                                <!-- <td><label>Id</label></td>
                                <td>
                                    <input class="form-control input-md" 
                                        readonly id="IDNO" name="IDNO" placeholder="Student Id" type="text" value='<?php echo $generateStudentUniqueId?>'>
                                </td> -->
                                <td ><label>Grade Level</label></td> 
                                <td colspan="1">

                                    <select class="form-control input-sm" name="course_level">
                                        <option>Select</option>
                                        <option value="11">Grade 11</option>
                                        <option value="12">Grade 12</option> 
                                    </select>
                                </td>
                                <td ><label>LRN</label></td> 
                                    <td colspan="1">
                                    <input class="form-control input-md" 
                                            name="lrn" placeholder="LRN:136-746-XXX" type="text" 
                                        value='137-257-232'>
                                </td>
                                <td ><label>Strand</label></td> 
                                    <td colspan="1">
                                    <?php echo $pending->CreateRegisterStrand(0);?>
                                </td>

                            </tr>
                            
                            <tr>
                                <td><label>Firstname</label></td>
                                <td>
                                    <input  class="form-control input-md" id="FNAME" name="FNAME" placeholder="First Name" type="text" value="<?php echo isset($_SESSION['FNAME']) ? $_SESSION['FNAME'] : ''; ?>">
                                </td>
                                <td><label>Lastname</label></td>
                                <td colspan="2">
                                    <input   class="form-control input-md" id="LNAME" name="LNAME" placeholder="Last Name" type="text" value="<?php echo isset($_SESSION['LNAME']) ? $_SESSION['LNAME'] : ''; ?>">
                                </td> 
                                <td>
                                    <input class="form-control input-md" id="MI" name="MI" placeholder="MI"  maxlength="1" type="text" value="<?php echo isset($_SESSION['MI']) ? $_SESSION['MI'] : ''; ?>">
                                </td>
                            </tr>
                            <tr>
                                <td><label>Address</label></td>
                                <td colspan="3"  >
                                <input   class="form-control input-md" id="PADDRESS" name="PADDRESS" placeholder="Permanent Address" type="text" value="<?php echo isset($_SESSION['PADDRESS']) ? $_SESSION['PADDRESS'] : ''; ?>">
                                </td> 

                                <td ><label>Sex </label></td> 
                                <td colspan="2">
                                    <label>
                                        <input checked id="optionsRadios1" name="optionsRadios" type="radio" value="Female">Female 
                                        <input id="optionsRadios2" name="optionsRadios" type="radio" value="Male"> Male
                                    </label>
                                </td>
                               </td>
                            </tr>
                            <tr>
                                <td><label>Student Type</label></td>
                                <td colspan="3"  >
                                <input class="form-control input-md"
                                    name="student_status" placeholder="New"
                                    type="text">
                                </td> 
                            </tr> 


                                <td colspan="5">	
                                    <button class="btn btn-success btn-lg" name="manual_creation_btn" type="submit">Submit & Proceed</button>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </form>
        </div>
    </div>


</div>

<script>
	$(document).ready(function() {

        $('#create_student_course_id').on('change', function() {

			var course_id = $("#create_student_course_id").val();
			
			// if(!$course_id){
            //     // $('#insert_table').html("");
			// }
			$.ajax({
            url: '../ajax/section/get_subject_section.php',
            type: 'POST',
            data: {
                course_id
            },
            dataType: 'json',
            success: function(response) {

				var html = `
					<table  class="table table-striped table-bordered table-hover "  style="font-size:13px" cellspacing="0"  > 
                        <thead>
                            <tr class='text-center'> 
                                <th rowspan="2">Subject Id</th>
                                <th rowspan="2">Code</th>
                                <th rowspan="2">Title</th>  
                                <th rowspan="2">Unit</th>  
                                <th rowspan="2">Semester</th>  
                            </tr>	
                        </thead>
				`;
                $.each(response, function(index, value) {

					html += `
                        <body>
                            <tr class='text-center'>
                                <td>${value.subject_id}</td>
								<td>${value.subject_code}</td>
                                <td>${value.subject_title}</td>
                                <td>${value.unit}</td>
                                <td>${value.semester}</td>
                            </tr>
					`;
				});

				html += `
                    </table>
                `;
                $('#insert_table').html(html);
			}
			});
		})

	});
</script>
