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

        $get_available_section = $con->prepare("SELECT 
        
                course_tertiary_id, capacity

                FROM course_tertiary

                WHERE program_id=:program_id
                AND active=:active
                AND course_level=:course_level
                AND is_full=:is_full
                AND school_year_term=:school_year_term
                -- ORDER BY course_id DESC
                LIMIT 1");

        // $program_id = 0;
        $get_available_section->bindValue(":program_id", $program_id);
        $get_available_section->bindValue(":course_level", $default_course_level);
        $get_available_section->bindValue(":active", $active_yes);
        $get_available_section->bindValue(":is_full", $not_full);
        $get_available_section->bindValue(":school_year_term", $current_school_year_term);
        $get_available_section->execute();
            
        if($get_available_section->rowCount() > 0){

            // echo "ff";
            $available_section = $get_available_section->fetch(PDO::FETCH_ASSOC);
                
            $default_course_id = $available_section['course_tertiary_id'];

            // echo $default_course_id;

            $capacity = $available_section['capacity'];


            $enrolledStudents = $enroll->CheckNumberOfStudentInSectionTertiary($default_course_id,
                 "First");
            // echo $enrolledStudents;

            $generateStudentUniqueId = $enroll->GenerateUniqueStudentNumber();
            $username = strtolower($lastname) . '.' . $generateStudentUniqueId . '@dcbt.ph';

            // echo $enrolledStudents;
            $FIRST_SEMESTER = "First";
            // $stmt->execute();
            if($capacity > $enrolledStudents){
 
                // echo "hey";

                $sql = "INSERT INTO student (firstname, lastname, middle_name, password, civil_status, nationality, contact_number, birthday, age, guardian_name, guardian_contact_number, sex, student_status,
                            course_tertiary_id, student_unique_id, course_level, username, address, lrn, is_tertiary) 

                        VALUES (:firstname, :lastname, :middle_name, :password, :civil_status, :nationality, :contact_number, :birthday, :age, :guardian_name, :guardian_contact_number, :sex, :student_status,
                            :course_tertiary_id, :student_unique_id, :course_level, :username, :address, :lrn, :is_tertiary)";

                $stmt_insert = $con->prepare($sql);

                $stmt_insert->bindParam(':firstname', $firstname);
                $stmt_insert->bindParam(':lastname', $lastname);
                $stmt_insert->bindParam(':middle_name', $middle_name);
                $stmt_insert->bindParam(':password', $password);
                // $stmt_insert->bindParam(':program_id', $program_id);
                $stmt_insert->bindParam(':civil_status', $civil_status);
                $stmt_insert->bindParam(':nationality', $nationality);
                $stmt_insert->bindParam(':contact_number', $contact_number);
                $stmt_insert->bindParam(':birthday', $birthday);
                $stmt_insert->bindParam(':age', $age);
                $stmt_insert->bindParam(':guardian_name', $guardian_name);
                $stmt_insert->bindParam(':guardian_contact_number', $guardian_contact_number);
                $stmt_insert->bindParam(':sex', $sex);
                $stmt_insert->bindParam(':student_status', $student_status);
                $stmt_insert->bindParam(':course_tertiary_id', $default_course_id);
                $stmt_insert->bindParam(':student_unique_id', $generateStudentUniqueId);
                $stmt_insert->bindParam(':course_level', $default_course_level);
                $stmt_insert->bindValue(':username', $username);
                $stmt_insert->bindValue(':address', $address);
                $stmt_insert->bindValue(':lrn', $lrn);
                $stmt_insert->bindValue(':is_tertiary', 1);

                // if(false){
                if($stmt_insert->execute()){

                    // remove the existing pending table
                    // Add to the enrollment with regostrar evaluated
                    // tentative and aligned to the ccourse id

                    $student_id = $con->lastInsertId();
                    $enrollment_status = "tentative";
                    $is_new_enrollee = 1;
                    $registrar_evaluated = "yes";
                    // $username = "generate";

                    $insert_enrollment = $con->prepare("INSERT INTO enrollment_tertiary
                        (student_id, course_tertiary_id, school_year_id, enrollment_status, is_new_enrollee, registrar_evaluated)
                        VALUES (:student_id, :course_tertiary_id, :school_year_id, :enrollment_status, :is_new_enrollee, :registrar_evaluated)");
                                    
                    $insert_enrollment->bindValue(':student_id', $student_id);
                    $insert_enrollment->bindValue(':course_tertiary_id', $default_course_id);
                    $insert_enrollment->bindValue(':school_year_id', $current_school_year_id);
                    $insert_enrollment->bindValue(':enrollment_status', $enrollment_status);
                    $insert_enrollment->bindValue(':is_new_enrollee', $is_new_enrollee);
                    $insert_enrollment->bindValue(':registrar_evaluated', $registrar_evaluated);

                    if($insert_enrollment->execute()){

                        // Check enrollment course_id number with course_id capacity
                        echo "pending student has been transfered to student table and was inserted in enrollment table.";

                        $date_now = date('Y-m-d H:i:s');
                        $update_pending = $con->prepare("UPDATE pending_enrollees
                            SET student_status=:student_status,
                                date_approved=:date_approved
                            WHERE pending_enrollees_id=:pending_enrollees_id
                            ");
                        $update_pending->bindValue(":student_status", "APPROVED");
                        $update_pending->bindValue(":date_approved", $date_now);
                        $update_pending->bindValue(":pending_enrollees_id", $pending_enrollees_id);
                        $update_pending->execute();
                    }
                }
            }
            else{
                echo "idont";
            }

        }else{
            echo "The current section you`re trying to get is full and there`s no available section after that.";
        }

    // program_section should be the available section to be placed on
    // based on the program_section
    }else{
        echo "something went wrong.";
    }



?>