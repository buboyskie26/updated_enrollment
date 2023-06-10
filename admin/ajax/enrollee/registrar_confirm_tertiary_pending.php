<?php 

    require_once("../../../includes/config.php");

    require_once("../../../enrollment/classes/StudentEnroll.php");

   if (isset($_POST['firstname']) && isset($_POST['lastname']) 
        && isset($_POST['middle_name']) && isset($_POST['password']) 
        && isset($_POST['program_id']) && isset($_POST['civil_status']) && isset($_POST['nationality']) && isset($_POST['contact_number']) && isset($_POST['birthday']) && isset($_POST['age']) && isset($_POST['guardian_name']) && isset($_POST['guardian_contact_number']) && isset($_POST['sex']) && isset($_POST['student_status'])
        && isset($_POST['program_section']) 
        && isset($_POST['pending_enrollees_id'])
        && isset($_POST['address'])
        && isset($_POST['lrn'])
        && isset($_POST['type'])

     ) {
        // echo "hey";
        $firstname = $_POST['firstname'];
        $lastname = $_POST['lastname'];
        $middle_name = $_POST['middle_name'];
        $password = $_POST['password'];
        $program_id = $_POST['program_id'];
        $civil_status = $_POST['civil_status'];
        $nationality = $_POST['nationality'];
        $contact_number = $_POST['contact_number'];
        $birthday = $_POST['birthday'];
        $age = $_POST['age'];
        $guardian_name = $_POST['guardian_name'];
        $guardian_contact_number = $_POST['guardian_contact_number'];
        $sex = $_POST['sex'];
        $student_status = $_POST['student_status'];
        $program_section = $_POST['program_section'];
        $pending_enrollees_id = $_POST['pending_enrollees_id']; 
        $address = $_POST['address']; 
        $lrn = $_POST['lrn']; 
        $type = $_POST['type']; 
        
        // All Pending Table will be transfered to student table.
        // With a student_unique_id generated, new_enrollee = 1, course_level = 11.

        $enroll = new StudentEnroll($con);
        $school_year_obj = $enroll->GetActiveSchoolYearAndSemester();

        $current_school_year_term = $school_year_obj['term'];
        $current_period = $school_year_obj['period'];
        $current_school_year_id = $school_year_obj['school_year_id'];

        $not_full = "no";
        $full_yes = "yes";
        $active_yes = "yes";
        $default_course_level = 1;

        // Get the available section based on the program_id.
        // Insert that student to the available section.
         

    // program_section should be the available section to be placed on
    // based on the program_section
    }else{
        echo "something went wrong.";
    }



?>