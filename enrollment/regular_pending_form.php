
<?php 

    require_once('./classes/Pending.php');
    // require_once('../admin/classes/Course.php');
    require_once('../includes/config.php');


    $pending = new Pending($con);
    $enroll = new StudentEnroll($con);
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
    if (isset($_POST['pending_form_btn'])) {

        $fname 	      =  $_POST['FNAME'];
        $lname  	  =  $_POST['LNAME'];
        $mi           =  $_POST['MI'];
        $sex          =  $_POST['optionsRadios'];
        $b_day    = $_POST['BIRTHDATE']; 
        $nationality  =  $_POST['NATIONALITY'];
        $s_contact      =  $_POST['CONTACT'];
        $civil_status  =  $_POST['CIVILSTATUS'];
        $guardian     =  $_POST['GUARDIAN'];
        $g_contact     =  $_POST['GCONTACT'];
        $program_id 	  =  $_POST['STRAND'];
        $password    =  $_POST['PASS'];


        $wasSuccess = $pending->PendingRegularFormSubmit(
            $fname, $lname, $mi, $sex, $b_day, $nationality,
            $school_year_id, $civil_status, $guardian, $g_contact, $password, $program_id
        );

        if($wasSuccess == true){
            echo "successfully transfer you request to pending table ";
        }else{
            echo "submit went wrong. ";
        }
    }
?>


<form action="" class="form-horizontal well" method="post" >
<!-- <form action="index.php?q=subject" class="form-horizontal well" method="post" > -->
	<div class="table-responsive">
	<div class="col-md-8"><h2>Pre-Registration Pending Form (Regular)</h2></div>
	<div class="col-md-4"><label>Academic Year: <?php echo $currentYear; ?> <span>(<?php echo $current_semester; ?>)</span> </label></div>
		<table class="table">
			
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
                  <input name="BIRTHDATE"  id="BIRTHDATE"  type="text" class="form-control input-md"   data-inputmask="'alias': 'mm/dd/yyyy'" data-mask value="<?php echo isset($_SESSION['BIRTHDATE']) ? $_SESSION['BIRTHDATE'] : ''; ?>">
				   </div>             
				</td>
				 
			</tr>
			 
			<tr>
				<td><label>Nationality</label></td>
				<td colspan="2">
                    <input class="form-control input-md" id="NATIONALITY" name="NATIONALITY" placeholder="Nationality" type="text" value="<?php echo isset($_SESSION['CONTACT']) ? $_SESSION['CONTACT'] : ''; ?>">
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
					<button class="btn btn-success btn-lg" name="pending_form_btn" type="submit">Submit</button>
				</td>
			</tr>
		</table>
	</div>
</form>


