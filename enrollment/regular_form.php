
<?php 

    require_once('./classes/StudentEnroll.php');
    require_once('./classes/OldEnrollees.php');
    // require_once('../admin/classes/Course.php');
    require_once('../includes/config.php');

    $enroll = new StudentEnroll($con);
    $old_enroll = new OldEnrollees($con, $enroll);
    // $course = new Course($con);

    $firstYearCourseSelection = $enroll->createFirstYearCourseSelection();
	$strandSelection = $enroll->CreateRegisterStrand();
	$current_year = date('Y');
	$next_year = date('Y', strtotime('+1 year'));

    // $_SESSION['SY'] = "2023-2024";
    // $_SESSION['SY'] = $current_year . "-" . $next_year;
    // $_SESSION['SY'] = $enroll->GetLatestSchoolYearAndSemester();

	$school_year_obj = $enroll->GetLatestSchoolYearAndSemester();

	$currentYear = $school_year_obj[0];
	$current_semester = $school_year_obj[1];
	$school_year_id = $school_year_obj[2];

    $generateStudentUniqueId = $enroll->GenerateUniqueStudentNumber();

	// echo $generateStudentUniqueId;
    if (isset($_POST['regsubmit']) && isset($_POST['IDNO'])) {

		echo "posts";
        $stud_unique_id	  =  $_POST['IDNO'];
        $fname 	      =  $_POST['FNAME'];
        $lname  	  =  $_POST['LNAME'];
        $mi           =  $_POST['MI'];
        $address      =  $_POST['PADDRESS'];
        $sex          =  $_POST['optionsRadios'];
        // $_SESSION['BIRTHDATE']    = date_format(date_create($_POST['BIRTHDATE']),'Y-m-d'); 
        $b_day    = $_POST['BIRTHDATE']; 
        $nationality  =  $_POST['NATIONALITY'];
        $birthplace   =  $_POST['BIRTHPLACE'];
        $religion     =  $_POST['RELIGION'];
        $s_contact      =  $_POST['CONTACT'];
        $civil_status  =  $_POST['CIVILSTATUS'];
        $guardian     =  $_POST['GUARDIAN'];
        $g_contact     =  $_POST['GCONTACT'];
        // $course_id 	  =  $_POST['COURSE'];
        $program_id 	  =  $_POST['STRAND'];
        // $_SESSION['SEMESTER']     =  $_POST['SEMESTER'];  
        $username    =  $_POST['USER_NAME']; 
        $password    =  $_POST['PASS']; 
 
		$wasSuccess = $enroll->RegularStudentFormSubmit($stud_unique_id, $fname, $lname, $mi, $address,
				$sex, $b_day, $nationality, $birthplace,
				$religion, $s_contact, $civil_status, $guardian, $g_contact, 
				
				$program_id,
				
				$username, $password, $school_year_id);

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

					$sql_enrollment_new = $con->prepare("INSERT INTO enrollment 
						(student_id, course_id, school_year_id, enrollment_status, is_new_enrollee)
						VALUES(:student_id, :course_id, :school_year_id, :enrollment_status, :is_new_enrollee)");

					$sql_enrollment_new->bindValue(":student_id", $student_id);
					$sql_enrollment_new->bindValue(":course_id", $course_id);
					$sql_enrollment_new->bindValue(":school_year_id", $school_year_id);
					$sql_enrollment_new->bindValue(":enrollment_status", $enrollment_status);
					$sql_enrollment_new->bindValue(":is_new_enrollee", $is_new_enrollee);

					if($sql_enrollment_new->execute()){
						echo "success tentative new enrollee";
						// Redirect to profile page.
						// header("Location: ");
					}else{
						echo "not success tentative new enrollee";
					}
				}else{
					echo "You have already enrolled with the same school year id $school_year_id";
				}
			}
        }
		else{
			echo "registering regular student went wrong";
		}
    }
?>


<form action="" class="form-horizontal well" method="post" >
<!-- <form action="index.php?q=subject" class="form-horizontal well" method="post" > -->
	<div class="table-responsive">
	<div class="col-md-8"><h2>Pre-Registration Form</h2></div>
	<div class="col-md-4"><label>Academic Year: <?php echo $currentYear; ?> <span>(<?php echo $current_semester; ?>)</span> </label></div>
		<table class="table">
			<tr>
				<td><label>Id</label></td>
				<!-- <td >
					<input class="form-control input-md" readonly id="IDNO" name="IDNO" placeholder="Student Id" type="text" value="<?php echo isset($_SESSION['STUDID']) ? $_SESSION['STUDID'] : $autonum->AUTO; ?>">
				</td> -->
                <td>
                    <input class="form-control input-md" 
                        readonly id="IDNO" name="IDNO" placeholder="Student Id" type="text" value='<?php echo $generateStudentUniqueId?>'>
                </td>
				<td colspan="4"></td>

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
				<td colspan="6"><input class="form-control input-md" id="CONTACT" name="CONTACT" placeholder="Contact Number" type="number" maxlength="11" value="<?php echo isset($_SESSION['CONTACT']) ? $_SESSION['CONTACT'] : ''; ?>">
				</td>
			</tr>
			<tr>
			<td><label>Strand Section</label></td>
				<td colspan="2">
                    <!-- <select class="form-control input-sm" name="COURSE">
						 <option value="Single">Single</option>
						 <option value="Married">Married</option> 
						 <option value="Widow">Widow</option>
					</select> -->
                    <!-- <?php echo $firstYearCourseSelection; ?> -->
                    <?php echo $strandSelection; ?>
					
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
			</tr>
			<tr>
				<td><label>Username</label></td>
				<td colspan="2">
				  <input   class="form-control input-md" id="USER_NAME" name="USER_NAME" placeholder="Username" type="text"value="<?php echo isset($_SESSION['USER_NAME']) ? $_SESSION['USER_NAME'] : ''; ?>">
				</td>
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
			<td></td>
				<td colspan="5">	
					<button class="btn btn-success btn-lg" name="regsubmit" type="submit">Submit</button>
				</td>
			</tr>
		</table>
	</div>
</form>

