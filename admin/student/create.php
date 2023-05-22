<?php 

    include('../registrar_enrollment_header.php');
    include('../../enrollment/classes/StudentEnroll.php');
    include('../../enrollment/classes/OldEnrollees.php');
    include('../classes/Course.php');


    $enroll = new StudentEnroll($con);
    $old_enroll = new OldEnrollees($con, $enroll);
    $course = new Course($con, $enroll);

	$school_year_obj = $enroll->GetLatestSchoolYearAndSemester();

	$currentYear = $school_year_obj[0];
	$current_semester = $school_year_obj[1];
	$school_year_id = $school_year_obj[2];
	$strandSelection = $enroll->CreateRegisterStrand();

    $generateStudentUniqueId = $enroll->GenerateUniqueStudentNumber();
    $course_dropdown = $course->GetCourseAvailableSelectionForCurrentSY();

    if (isset($_POST['student_admission_create_btn']) && isset($_POST['IDNO'])) {

		echo "create student in admission";
        $stud_unique_id	  =  $_POST['IDNO'];
        $fname 	      =  $_POST['FNAME'];
        $lname  	  =  $_POST['LNAME'];
        $mi           =  $_POST['MI'];
        $address      =  $_POST['PADDRESS'];
        $sex          =  $_POST['optionsRadios'];
        $b_day    = $_POST['BIRTHDATE']; 
        $nationality  =  $_POST['NATIONALITY'];
        $birthplace   =  $_POST['BIRTHPLACE'];
        $religion     =  $_POST['RELIGION'];
        $s_contact      =  $_POST['CONTACT'];
        $civil_status  =  $_POST['CIVILSTATUS'];
        $guardian     =  $_POST['GUARDIAN'];
        $g_contact     =  $_POST['GCONTACT'];
        // $course_id 	  =  $_POST['COURSE'];
        // $program_id 	  =  $_POST['STRAND'];
        $course_id 	  =  $_POST['course_id'];
        
        // $_SESSION['SEMESTER']     =  $_POST['SEMESTER'];  
        // $username    =  $_POST['USER_NAME']; 

        $password    =  $_POST['PASS']; 

        $get_course_level = $course->GetCourseCourseLevel($course_id);
        $course_level    =  $_POST['course_level']; 
        $student_status    =  $_POST['student_status']; 
        $lrn    =  $_POST['lrn']; 


		// $generateStudentUniqueId = $enroll->GenerateUniqueStudentNumber();
        $username = strtolower($lname) . '.' . $stud_unique_id . '@dcbt.ph';

		$wasSuccess = $enroll->RegistrarCreatingStudentFormSubmit($stud_unique_id, $fname, $lname, $mi, $address,
            $sex, $b_day, $nationality, $birthplace,
            $religion, $s_contact, $civil_status, $guardian, $g_contact, 
            
            $course_id,
            
            $username, $password, $get_course_level, $course_level,

			$student_status, $lrn);

        // if(false){
        if($wasSuccess){

			// redirect to the profile page as not enrolled yet.
			// cashier, head and registrar needs to evaluate first.
			// header("Location: enrollment/profile.php");

			// Once registered
			$student_id = $enroll->GetStudentByUniqueId($generateStudentUniqueId);

			if($student_id == 0){
				echo "student id not generated";
				echo $student_id . " ||";
			}

			if($student_id != 0){

				$course_id = $enroll->GetStudentCourseIdById($student_id);
				// TODO: Get student course_id.
				// $course_id = 0;
				if($old_enroll->CheckIfStudentEnrolledWithTheSameSchoolYearId($student_id) == false){
					// Serve as true
					$is_new_enrollee = 1;
					$enrollment_status = "tentative";
					$registrar_evaluted = "yes";

					$sql_enrollment_new = $con->prepare("INSERT INTO enrollment 
						(student_id, course_id, school_year_id, enrollment_status, is_new_enrollee, registrar_evaluated, is_transferee)
						VALUES(:student_id, :course_id, :school_year_id, :enrollment_status, :is_new_enrollee, :registrar_evaluated, :is_transferee)");

					// Regular New Enrollee.
					if($student_status != '' && $student_status === "Regular"){
					
						$sql_enrollment_new->bindValue(":student_id", $student_id);
						$sql_enrollment_new->bindValue(":course_id", $course_id);
						$sql_enrollment_new->bindValue(":school_year_id", $school_year_id);
						$sql_enrollment_new->bindValue(":enrollment_status", $enrollment_status);
						$sql_enrollment_new->bindValue(":is_new_enrollee", $is_new_enrollee);
						$sql_enrollment_new->bindValue(":registrar_evaluated", $registrar_evaluted);
						$sql_enrollment_new->bindValue(":is_transferee", 0, PDO::PARAM_INT);

						if($sql_enrollment_new->execute()){
							echo "success tentative new enrollee (Regular)";
							// Redirect to profile page.
							// header("Location: ");
						}else{
							echo "not success tentative new enrollee (Regular)";
						}
					}

					if($student_status != '' && $student_status === "Transferee"){
					
						$sql_enrollment_new->bindValue(":student_id", $student_id);
						$sql_enrollment_new->bindValue(":course_id", $course_id);
						$sql_enrollment_new->bindValue(":school_year_id", $school_year_id);
						$sql_enrollment_new->bindValue(":enrollment_status", $enrollment_status);
						$sql_enrollment_new->bindValue(":is_new_enrollee", $is_new_enrollee);
						$sql_enrollment_new->bindValue(":registrar_evaluated", $registrar_evaluted);
						$sql_enrollment_new->bindValue(":is_transferee", 1, PDO::PARAM_INT);

						if($sql_enrollment_new->execute()){
							echo "success tentative new enrollee (Transferee)";
							// Redirect to profile page.
							// header("Location: ");
						}else{
							echo "not success tentative new enrollee (Transferee)";
						}
					}
				}else{
					echo "You have already enrolled with the same school year id $school_year_id";
				}
			}
        }
		else{
			echo "Creating Student did not worked.";
		}
    }

?>

<div class="row col-md-12">
<form action="" class="form-horizontal well" method="post" >
<!-- <form action="index.php?q=subject" class="form-horizontal well" method="post" > -->
	<div class="table-responsive">
	<div class="col-md-8"><h2>STUDENT ACCOUNT DATA SHEET</h2></div>
	<div class="col-md-4"><label>Academic Year: <?php echo $currentYear; ?> <span>(<?php echo $current_semester; ?>)</span> </label></div>
		<table class="table">
			<tr>
				<td><label>Id</label></td>
                <td>
                    <input class="form-control input-md" 
                        readonly id="IDNO" name="IDNO" placeholder="Student Id" type="text" value='<?php echo $generateStudentUniqueId?>'>
                </td>
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
						value=''>
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
				<td colspan="5"  >
				<input   class="form-control input-md" id="PADDRESS" name="PADDRESS" placeholder="Permanent Address" type="text" value="<?php echo isset($_SESSION['PADDRESS']) ? $_SESSION['PADDRESS'] : ''; ?>">
				</td> 
			</tr>
			<tr>
				<td ><label>Sex </label></td> 
				<td colspan="2">
					<label>
						<input checked id="optionsRadios1" name="optionsRadios" type="radio" value="Female">Female 
						 <input id="optionsRadios2" name="optionsRadios" type="radio" value="Male"> Male
					</label>
				</td>
				<td ><label>Date of birth</label></td>
				<td colspan="2"> 
				<div class="input-group" >
                  <div class="input-group-addon"> 
                    <i class="fa fa-calendar"></i>
                  </div>
                  <input   name="BIRTHDATE"  id="BIRTHDATE"  type="text" class="form-control input-md"   data-inputmask="'alias': 'mm/dd/yyyy'" data-mask value="<?php echo isset($_SESSION['BIRTHDATE']) ? $_SESSION['BIRTHDATE'] : ''; ?>">
				   </div>             
				</td>
				 
			</tr>
			<tr><td><label>Place of Birth</label></td>
				<td colspan="5">
				<input   class="form-control input-md" id="BIRTHPLACE" name="BIRTHPLACE" placeholder="Place of Birth" type="text" value="<?php echo isset($_SESSION['BIRTHPLACE']) ? $_SESSION['BIRTHPLACE'] : ''; ?>">
			   </td>
			</tr>
			<tr>
				<td><label>Nationality</label></td>
				<td colspan="2"><input   class="form-control input-md" id="NATIONALITY" name="NATIONALITY" placeholder="Nationality" type="text" value="<?php echo isset($_SESSION['CONTACT']) ? $_SESSION['CONTACT'] : ''; ?>">
							</td>
				<td><label>Religion</label></td>
				<td colspan="2"><input  class="form-control input-md" id="RELIGION" name="RELIGION" placeholder="Religion" type="text" value="<?php echo isset($_SESSION['RELIGION']) ? $_SESSION['RELIGION'] : ''; ?>">
				</td>
				
			</tr>
			<tr>
				<td><label>Contact No.</label></td>
					<td colspan="2"><input class="form-control input-md" id="CONTACT" name="CONTACT" placeholder="Contact Number" type="number" maxlength="11" value="<?php echo isset($_SESSION['CONTACT']) ? $_SESSION['CONTACT'] : ''; ?>">
					</td>
				<td><label>Civil Status</label></td>
				<td colspan="2">
					<select class="form-control input-sm" name="CIVILSTATUS">
						<option value="<?php echo isset($_SESSION['CIVILSTATUS']) ? $_SESSION['CIVILSTATUS'] : 'Select'; ?>"><?php echo isset($_SESSION['CIVILSTATUS']) ? $_SESSION['CIVILSTATUS'] : 'Select'; ?></option>
						 <option value="Single">Single</option>
						 <option value="Married">Married</option> 
						 <option value="Widow">Widow</option>
					</select>
				</td>
			<tr>
			<td><label>Strand Section</label></td>
				<td colspan="2">
                    <!-- <select class="form-control input-sm" name="COURSE">
						 <option value="Single">Single</option>
						 <option value="Married">Married</option> 
						 <option value="Widow">Widow</option>
					</select> -->
                    <!-- <?php echo $firstYearCourseSelection; ?> -->
                    <?php echo $course_dropdown; ?>
					
				</td>
				

				<td><label>Student Status</label></td>
				<td colspan="3">
					<select class="form-control input-sm" name="student_status">
						<option value="Regular">New (Regular)</option> 
						<option value="Transferee">Transferee</option>
					</select>
				</td>
			</tr>
			<tr>
				<!-- <td><label>Username</label></td>
				<td colspan="2">
				  <input   class="form-control input-md" id="USER_NAME" name="USER_NAME" placeholder="Username" type="text"value="<?php echo isset($_SESSION['USER_NAME']) ? $_SESSION['USER_NAME'] : ''; ?>">
				</td> -->
				<td><label>Password</label></td>
				<td colspan="2">
						<input value="12345"  class="form-control input-md" id="PASS" name="PASS" placeholder="Password" type="password"value="<?php echo isset($_SESSION['PASS']) ? $_SESSION['PASS'] : ''; ?>">
				</td>
			</tr>
			<tr>
				<td><label>Guardian</label></td>
				<td colspan="2">
					<input   class="form-control input-md" id="GUARDIAN" name="GUARDIAN" placeholder="Parents/Guardian Name" type="text"value="<?php echo isset($_SESSION['GUARDIAN']) ? $_SESSION['GUARDIAN'] : ''; ?>">
				</td>
				<td><label>Contact No.</label></td>
				<td colspan="2"><input   class="form-control input-md" id="GCONTACT" name="GCONTACT" placeholder="Contact Number" type="number" value="<?php echo isset($_SESSION['GCONTACT']) ? $_SESSION['GCONTACT'] : ''; ?>"></td>
			</tr>
			<tr>
			<td>
			
			</td>
				<td colspan="5">	
					<button class="btn btn-success btn-lg" name="student_admission_create_btn" type="submit">Submit</button>
				</td>
			</tr>
		</table>
	</div>
</form>

    <div id="insert_table"></div>

	<!-- <table class="table table-striped table-bordered table-hover "  style="font-size:13px" cellspacing="0"  > 
		<thead>
			<tr class="text-center"> 
				<th rowspan="2">ID</th>
				<th rowspan="2">Code</th>
				<th rowspan="2">Title</th>  
				<th rowspan="2">Unit</th>
			</tr>	
		</thead> 
		<tbody>

		</tbody>
	</table> -->


</div>

<!-- create_student_course_id -->

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
