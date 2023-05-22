<?php 

    require_once("../../../includes/config.php");

    require_once("../../../enrollment/classes/StudentEnroll.php");

   if (isset($_POST['firstname']) && isset($_POST['lastname']) 
        && isset($_POST['middle_name']) && isset($_POST['password']) 
        && isset($_POST['program_id']) && isset($_POST['civil_status']) && isset($_POST['nationality']) && isset($_POST['contact_number']) && isset($_POST['birthday']) && isset($_POST['age']) && isset($_POST['guardian_name']) && isset($_POST['guardian_contact_number']) && isset($_POST['sex']) && isset($_POST['student_status'])
        && isset($_POST['program_section']) 
        && isset($_POST['pending_enrollees_id'])
        && isset($_POST['address'])
        && isset($_POST['lrn'])) {
            
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
        $default_course_level = 11;

        // Get the available section based on the program_id.
        // Insert that student to the available section.

        $get_available_section = $con->prepare("SELECT course_id, capacity

                FROM course

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
                
            $default_course_id = $available_section['course_id'];

            $capacity = $available_section['capacity'];

            $enrolledStudents = $enroll->CheckNumberOfStudentInSection($default_course_id,
                 "First");
            // echo $enrolledStudents;

            $generateStudentUniqueId = $enroll->GenerateUniqueStudentNumber();
            $username = strtolower($lastname) . '.' . $generateStudentUniqueId . '@dcbt.ph';

            // echo $enrolledStudents;
            $FIRST_SEMESTER = "First";
            // $stmt->execute();
            if($capacity > $enrolledStudents){
 
                $sql = "INSERT INTO student (firstname, lastname, middle_name, password, civil_status, nationality, contact_number, birthday, age, guardian_name, guardian_contact_number, sex, student_status,
                            course_id, student_unique_id, course_level, username, address, lrn) 
                        VALUES (:firstname, :lastname, :middle_name, :password, :civil_status, :nationality, :contact_number, :birthday, :age, :guardian_name, :guardian_contact_number, :sex, :student_status,
                            :course_id, :student_unique_id, :course_level, :username, :address, :lrn)";

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
                $stmt_insert->bindParam(':course_id', $default_course_id);
                $stmt_insert->bindParam(':student_unique_id', $generateStudentUniqueId);
                $stmt_insert->bindParam(':course_level', $default_course_level);
                $stmt_insert->bindValue(':username', $username);
                $stmt_insert->bindValue(':address', $address);
                $stmt_insert->bindValue(':lrn', $lrn);

                if($stmt_insert->execute()){

                    // remove the existing pending table
                    // Add to the enrollment with regostrar evaluated
                    // tentative and aligned to the ccourse id

                    $student_id = $con->lastInsertId();
                    $enrollment_status = "tentative";
                    $is_new_enrollee = 1;
                    $registrar_evaluated = "yes";
                    // $username = "generate";

                    $insert_enrollment = $con->prepare("INSERT INTO enrollment
                        (student_id, course_id, school_year_id, enrollment_status, is_new_enrollee, registrar_evaluated)
                        VALUES (:student_id, :course_id, :school_year_id, :enrollment_status, :is_new_enrollee, :registrar_evaluated)");
                                    
                    $insert_enrollment->bindValue(':student_id', $student_id);
                    $insert_enrollment->bindValue(':course_id', $default_course_id);
                    $insert_enrollment->bindValue(':school_year_id', $current_school_year_id);
                    $insert_enrollment->bindValue(':enrollment_status', $enrollment_status);
                    $insert_enrollment->bindValue(':is_new_enrollee', $is_new_enrollee);
                    $insert_enrollment->bindValue(':registrar_evaluated', $registrar_evaluated);

                    if($insert_enrollment->execute()){

                        // Check enrollment course_id number with course_id capacity

                        // echo "pending student transfered to student table and insert in enrollment.";
                        echo "Pre-Enrollment successfully confirmed.";

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

            // echo $enrolledStudents;
            else if($capacity === $enrolledStudents && $current_period === $FIRST_SEMESTER){
                    echo "section $default_course_id is now full. ";

                    $update_course = $con->prepare("UPDATE course
                        SET is_full=:is_full

                        WHERE course_id=:course_id
                        AND active=:current_active_status
                        AND is_full=:current_is_ful
                        AND school_year_term=:school_year_term
                        ");

                    $update_course->bindValue(":is_full", $full_yes);
                    $update_course->bindValue(":course_id", $default_course_id);
                    $update_course->bindValue(":current_active_status", $active_yes);
                    $update_course->bindValue(":current_is_ful", $not_full);
                    $update_course->bindValue(":school_year_term", $current_school_year_term);

                    if($update_course->execute()){
                    // if(false){

                        $get_prev_default_section = $con->prepare("SELECT *
                            
                            FROM course
                            WHERE program_id=:program_id
                            AND active=:active
                            AND is_full=:is_full
                            -- AND program_section='HUMMS11-A'
                            ORDER BY course_id DESC
                            LIMIT 1");

                        // $program_id = 0;
                        $get_prev_default_section->bindValue(":program_id", $program_id);
                        $get_prev_default_section->bindValue(":active", $active_yes);
                        $get_prev_default_section->bindValue(":is_full", $full_yes);
                        $get_prev_default_section->execute();

                        if($get_prev_default_section->rowCount() > 0){
                            
                            $prev_row = $get_prev_default_section->fetch(PDO::FETCH_ASSOC);

                            // If newly_created course id is HUMMS11-E
                            // then prev_course_id must be HUMMS11-D course_id
                            $prev_course_id = $prev_row['course_id'];
                            $prev_school_year_term = $prev_row['school_year_term'];
                            $prev_capacity = $prev_row['capacity'];
                            $prev_program_section = $prev_row['program_section'];

                            $generateNextStrandSection = $enroll->GetNextProgramSection($prev_program_section);

                            $checkIfSectionExists = $con->prepare("SELECT course_id FROM course
                                WHERE program_section=:program_section

                                AND active='yes'
                                LIMIT 1");

                            $checkIfSectionExists->bindValue(":program_section", $generateNextStrandSection);
                            $checkIfSectionExists->execute();

                            if($checkIfSectionExists->rowCount() > 0){

                                // The student registered will be stuck at the moment
                                // but the moment they refresh and fill-up again, it will be fine.

                                # If STEM11-B already exists
                                # Because registrar manually created the section even the STEM11-A
                                # is not yet full
                                echo  $generateNextStrandSection . " already exists";

                                // If already exists because it was made by the registrar
                                # Then, that made section should be the section of student course_id.

                                
                                return;
                            }

                            else if($checkIfSectionExists->rowCount() == 0){
                                // echo  $generateNextStrandSection . " is new";

                                # Bug: What if 2 sections went full at the same time.
                                // System will only cater one. so the other will need to refresh the page.

                                // create a new one with  HUMMS11-B as the next program_section.
                                $create_new_section = $con->prepare("INSERT INTO course
                                    (program_section, program_id, course_level, capacity, school_year_term, active, is_full)
                                    VALUES(:program_section, :program_id, :course_level, :capacity, :school_year_term, :active, :is_full)");

                                $create_new_section->bindValue(":program_section", $generateNextStrandSection);

                                $create_new_section->bindValue(":program_id", $program_id);
                                $create_new_section->bindValue(":course_level", $default_course_level);
                                $create_new_section->bindValue(":capacity", $prev_capacity);
                                // Should be placed if student were officially enrolled by registrar.
                                // $create_new_section->bindValue(":total_student", 1);
                                $create_new_section->bindValue(":school_year_term", $prev_school_year_term);
                                $create_new_section->bindValue(":active", "yes");
                                $create_new_section->bindValue(":is_full", "no");

                                if($create_new_section->execute() && $create_new_section->rowCount() == 1){

                                    # BUG: If the total_student is only -1 left to make the capacity becomes full, 
                                    # and two students clicked at the same time, 
                                    echo "Created new section: $generateNextStrandSection ";

                                        // echo "created $last_inserted_course_id";
                                        $course_level_eleven = 11;

                                        // course_id of newly created course (HUMMS11-B)
                                        $getNewlyCreatedCourse = $con->prepare("SELECT course_id, program_section 
                                        
                                            FROM course
                                            WHERE program_section=:program_section
                                            AND course_level=:course_level
                                            AND active='yes'
                                            LIMIT 1");
                                        $getNewlyCreatedCourse->bindValue(":program_section", $generateNextStrandSection);
                                        $getNewlyCreatedCourse->bindValue(":course_level", $course_level_eleven);
                                        $getNewlyCreatedCourse->execute();

                                        $second_strand_course_id = null;

                                        if($getNewlyCreatedCourse->rowCount() > 0){

                                            // newly created course id
                                            $get_newly_created_course_row = $getNewlyCreatedCourse->fetch(PDO::FETCH_ASSOC);
                                            $get_newly_created_course_id = $get_newly_created_course_row['course_id'];
                                            $get_newly_created_course_program_section = $get_newly_created_course_row['program_section'];

                                            $active = "yes";



                                            // Even you`re at the (STRAND-COURSE_LEVEL)-E, Get the -A Grade 11 strand.
                                            // Move it up in logical place.
                                            $get_A_section = $con->prepare("SELECT course_id FROM course

                                                WHERE program_id=:program_id
                                                AND course_level=:course_level
                                                AND active=:active
                                                AND school_year_term=:school_year_term
                                                ORDER BY course_id ASC
                                                LIMIT 1");
                                                
                                            $get_A_section->bindValue(":program_id", $program_id);
                                            $get_A_section->bindValue(":course_level", $default_course_level);
                                            $get_A_section->bindValue(":active", $active);
                                            $get_A_section->bindValue(":school_year_term", $current_school_year_term);
                                            $get_A_section->execute();

                                            if($get_A_section->rowCount() == 0){
                                                echo "Cant get the Strand11-A section hard coded";
                                                return;
                                            }


                                            # REFACTOR.
                                            $get_subject_program = $con->prepare("SELECT * FROM subject_program
                                                WHERE program_id=:program_id
                                                AND course_level=:course_level
                                                ");

                                            $get_subject_program->bindValue(":program_id", $program_id);
                                            $get_subject_program->bindValue(":course_level", 11);
                                            $get_subject_program->execute();

                                            if($get_subject_program->rowCount() > 0){

                                                $isSubjectCreated = false;

                                                $insert_section_subject = $con->prepare("INSERT INTO subject
                                                    (subject_title, description, subject_program_id, unit, semester, program_id, course_level, course_id, subject_type, subject_code)
                                                    VALUES(:subject_title, :description, :subject_program_id, :unit, :semester, :program_id, :course_level, :course_id, :subject_type, :subject_code)");

                                                while($row = $get_subject_program->fetch(PDO::FETCH_ASSOC)){

                                                    $program_program_id = $row['subject_program_id'];
                                                    $program_course_level = $row['course_level'];
                                                    $program_semester = $row['semester'];
                                                    $program_subject_type = $row['subject_type'];
                                                    $program_subject_title = $row['subject_title'];
                                                    $program_subject_description = $row['description'];
                                                    $program_subject_unit = $row['unit'];

                                                    $program_subject_code = $row['subject_code'] . $get_newly_created_course_program_section; 

                                                    $insert_section_subject->bindValue(":subject_title", $program_subject_title);
                                                    $insert_section_subject->bindValue(":description", $program_subject_description);
                                                    $insert_section_subject->bindValue(":subject_program_id", $program_program_id);
                                                    $insert_section_subject->bindValue(":unit", $program_subject_unit);
                                                    $insert_section_subject->bindValue(":semester", $program_semester);
                                                    $insert_section_subject->bindValue(":program_id", $program_id);
                                                    $insert_section_subject->bindValue(":course_level", $program_course_level);
                                                    $insert_section_subject->bindValue(":course_id", $get_newly_created_course_id);
                                                    $insert_section_subject->bindValue(":subject_type", $program_subject_type);
                                                    $insert_section_subject->bindValue(":subject_code", $program_subject_code);

                                                    // $insert_section_subject->execute();
                                                    if($insert_section_subject->execute()){
                                                        $isSubjectCreated = true;
                                                    }
                                                }
                                            }

                                            $a_section_course_id = $get_A_section->fetchColumn();

                                            // echo "prev_course_id " . $prev_course_id;
                                            // echo "a_section_course_id=" . $a_section_course_id . " ";

                                            // Make sure the it will create ONLY ONCE the subject course referencing
                                            // to the course_id 

                                            ## REPLACED BY $get_subject_program

                                            // $subjectQuery =  "SELECT * FROM subject
                                            //     WHERE course_id=:course_id
                                            //     AND course_level=:course_level";
                                            
                                            // $subjectQueryState = $con->prepare("SELECT * FROM subject

                                            //     WHERE course_id=:course_id
                                            //     AND course_level=:course_level");

                                            // $subjectQueryState->bindParam(":course_id", $a_section_course_id);
                                            // $subjectQueryState->bindParam(":course_level", $course_level_eleven);
                                            // $subjectQueryState->execute();

                                            
                                            // $subject_copy_create = "INSERT INTO subject
                                            //         (subject_title, pre_subject_id, unit, semester, program_id, course_level, subject_type, course_id, subject_code, subject_program_id)
                                            //         VALUES(:subject_title, :pre_subject_id, :unit, :semester, :program_id, :course_level, :subject_type, :course_id, :subject_code, :subject_program_id)";

                                            // $subject_copy_create = $con->prepare($subject_copy_create);

                                            // // // Make sure the subject creation is perfectly reference
                                            // // // to new created course_id (HUMMSS11-B)

                                            // if($subjectQueryState->rowCount() == 0){
                                            //     echo "subjectQuery variable is zero. (it did not reference correctly the course_id)";
                                            //     echo "<br>";
                                            //     return;
                                            // }

                                            // if($get_newly_created_course_id != null && $subjectQueryState->rowCount() > 0){

                                            //     // subject_code must be depend on
                                            //     // registrar subject convetion.
                                            //     foreach ($subjectQueryState as $subject_row) {

                                            //         // Auto Create of Subjects for the Auto create of course_section
                                            //         // If HUMMS11-A has 3 subjects eventually becomes full.
                                            //         // It creates HUMMSS11-B with all the subjects of HUMMSS11-A
                                            //         // Registrar will just insert a schedule, subject_code and pre_subject_id (pre_Requisite) on the subject_table,
                                            //         #
                                            //         $subject_copy_create->execute([
                                            //             ':subject_title' => $subject_row['subject_title'],
                                            //             ':pre_subject_id' => $subject_row['pre_subject_id'],
                                            //             ':unit' => $subject_row['unit'],
                                            //             ':semester' => $subject_row['semester'],
                                            //             ':program_id' => $subject_row['program_id'],
                                            //             ':course_level' => $subject_row['course_level'],
                                            //             ':subject_type' => $subject_row['subject_type'],
                                            //             ':course_id' => $get_newly_created_course_id,
                                            //             ':subject_code' => $generateNextStrandSection,
                                            //             ':subject_program_id' => $subject_row['subject_program_id']
                                            //         ]);
                                            //     }
                                            // }

                                            if($isSubjectCreated == true){

                                                $sql = "INSERT INTO student (firstname, lastname, middle_name, password, civil_status, nationality, contact_number, birthday, age, guardian_name, guardian_contact_number, sex, student_status, course_id, student_unique_id, course_level, username, lrn, address) 
                                                    VALUES (:firstname, :lastname, :middle_name, :password, :civil_status, :nationality, :contact_number, :birthday, :age, :guardian_name, :guardian_contact_number, :sex, :student_status, :course_id, :student_unique_id, :course_level, :username, :lrn, :address)";

                                                $stmt = $con->prepare($sql);

                                                $stmt->bindParam(':firstname', $firstname);
                                                $stmt->bindParam(':lastname', $lastname);
                                                $stmt->bindParam(':middle_name', $middle_name);
                                                $stmt->bindParam(':password', $password);
                                                // $stmt->bindParam(':program_id', $program_id);
                                                $stmt->bindParam(':civil_status', $civil_status);
                                                $stmt->bindParam(':nationality', $nationality);
                                                $stmt->bindParam(':contact_number', $contact_number);
                                                $stmt->bindParam(':birthday', $birthday);
                                                $stmt->bindParam(':age', $age);
                                                $stmt->bindParam(':guardian_name', $guardian_name);
                                                $stmt->bindParam(':guardian_contact_number', $guardian_contact_number);
                                                $stmt->bindParam(':sex', $sex);
                                                $stmt->bindParam(':student_status', $student_status);
                                                $stmt->bindParam(':course_id', $get_newly_created_course_id);
                                                $stmt->bindParam(':student_unique_id', $generateStudentUniqueId);
                                                $stmt->bindParam(':course_level', $default_course_level);
                                                $stmt->bindValue(':username', $username);
                                                $stmt->bindValue(':address', $address);
                                                $stmt->bindValue(':lrn', $lrn);

                                                // if(false){
                                                if($stmt->execute()){

                                                    // echo "successfully created an vacant  course_id ";

                                                    $student_id = $con->lastInsertId();
                                                    $enrollment_status = "tentative";
                                                    $is_new_enrollee = 1;
                                                    $registrar_evaluated = "yes";
                                                    // $username = "generate";

                                                    $insert_enrollment = $con->prepare("INSERT INTO enrollment
                                                        (student_id, course_id, school_year_id, enrollment_status, is_new_enrollee, registrar_evaluated)
                                                        VALUES (:student_id, :course_id, :school_year_id, :enrollment_status, :is_new_enrollee, :registrar_evaluated)");
                                                                    
                                                    $insert_enrollment->bindValue(':student_id', $student_id);
                                                    $insert_enrollment->bindValue(':course_id', $get_newly_created_course_id);
                                                    $insert_enrollment->bindValue(':school_year_id', $current_school_year_id);
                                                    $insert_enrollment->bindValue(':enrollment_status', $enrollment_status);
                                                    $insert_enrollment->bindValue(':is_new_enrollee', $is_new_enrollee);
                                                    $insert_enrollment->bindValue(':registrar_evaluated', $registrar_evaluated);

                                                    if($insert_enrollment->execute()){
                                                        echo "successfully created an vacant  course_id and pending student transfered to student table and insert in enrollment.";

                                                        //
                                                        $datetime_now = date("Y-m-d H:i:s");
                                                        $update_pending = $con->prepare("UPDATE pending_enrollees
                                                            SET student_status=:student_status,
                                                                date_approved=:date_approved
                                                            WHERE pending_enrollees_id=:pending_enrollees_id
                                                            ");
                                                        
                                                        $update_pending->bindValue(":student_status", "APPROVED");
                                                        $update_pending->bindValue(":pending_enrollees_id", $pending_enrollees_id);
                                                        $update_pending->bindValue(":date_approved", $datetime_now);
                                                        $update_pending->execute();
                                                    }
                                                }
                                            }
                                        }
                                }
                            }
                        }
                    }
            }else{
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