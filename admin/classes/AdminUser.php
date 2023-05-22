<?php

class AdminUser{

    private $con, $sqlData;

    public function __construct($con, $input)
    {
        $this->con = $con;
        $this->sqlData = $input;

        // echo "hey";
        // print_r($input);
        if(!is_array($input)){
            $query = $this->con->prepare("SELECT * FROM users
            WHERE username=:username");

            $query->bindValue(":username", $input);
            $query->execute();

            $this->sqlData = $query->fetch(PDO::FETCH_ASSOC);
        }
    }

    public static function success($text, $redirectUrl) {
        echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: '$text'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '$redirectUrl';
                }
            });
        </script>";
    }
    public static function error($text, $redirectUrl) {
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Oh no!',
                text: '$text'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '$redirectUrl';
                }
            });
        </script>";
    }
    public static function remove($text, $redirectUrl) {
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Removal!',
                text: '$text'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '$redirectUrl';
                }
            });
        </script>";
    }

    public static function confirm($text, $redirectUrl) {
        echo "
            <script>
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: '$text',
                    showCancelButton: true,
                    confirmButtonText: 'OK'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = '$redirectUrl';
                    }
                });
            </script>
        ";

    }
    

    public function GetId() {
        return isset($this->sqlData['user_id']) ? $this->sqlData["user_id"] : 0; 
    }
    public function GetUsername() {
        return isset($this->sqlData['username']) ? $this->sqlData["username"] : ""; 
    }
    public function GetFirstName() {
        return isset($this->sqlData['firstName']) ? $this->sqlData["firstName"] : ""; 
    }
    public function GetName() {
        return $this->sqlData["firstName"] . " " . $this->sqlData["lastName"];
    }
    public function GetLastName() {
        return isset($this->sqlData['lastName']) ? $this->sqlData["lastName"] : ""; 
    }

    public static function IsAuthenticated(){
        return isset($_SESSION['adminLoggedIn']);
    }
      public static function IsCashierAuthenticated(){
        return isset($_SESSION['cashierLoggedIn']);
    }

    public static function IsRegistrarAuthenticated(){
        return isset($_SESSION['registrarLoggedIn']);
    }


    public static function IsStudentEnrolledAuthenticated(){
        return isset($_SESSION['username']) 
            && isset($_SESSION['status']) && $_SESSION['status'] == "enrolled";
    }

    public static function IsStudentPendingAuthenticated(){
        return isset($_SESSION['username']) 
            && isset($_SESSION['status']) && $_SESSION['status'] == "pending";
    }

    public function createForm(){
        $createCourseCategory = $this->createCourseCategory();
        return "
                <form action='student.php' method='POST'>
                    <div class='form-group'>
                    
                        $createCourseCategory
                        <input class='form-control' type='text' 
                            placeholder='ID Number' name='username'>
                        <input class='form-control' type='text' 
                            placeholder='First Name' name='firstname'>
                        <input class='form-control' type='text' 
                            placeholder='Last Name' name='lastname'>
                    </div>

                    <button type='submit' class='btn btn-primary' name='submit_student'>Save</button>
                </form>
            ";
    }

    public function insertStudent($course_id, $username, $firstname, $lastname){
            
        // Check if the subject already entered.

        $query = $this->con->prepare("INSERT INTO student(course_id, username,firstname,lastname)
            VALUES(:course_id, :username,:firstname,:lastname)");
        
        $query->bindValue(":course_id", $course_id);
        $query->bindValue(":username", $username);
        $query->bindValue(":firstname", $firstname);
        $query->bindValue(":lastname", $lastname);

        return $query->execute();
    }
    private function createCourseCategory(){

        $query = $this->con->prepare("SELECT * FROM course");
        $query->execute();

            $html = "<div class='form-group'>
                    <select class='form-control' name='course_id'>";

            while($row = $query->fetch(PDO::FETCH_ASSOC)){
                $html .= "
                    <option value='".$row['course_id']."'>".$row['course_name']."</option>
                ";
            }
            $html .= "</select>
                    </div>";
            return $html;
    }
    
    public function createTable(){

        $enroll = new StudentEnroll($this->con);
        $school_year_obj = $enroll->GetActiveSchoolYearAndSemester();

        $school_year_id = null;

        if($school_year_obj == null){
            echo "school_year_obj is null";
            exit();
        }
        
        $school_year_id = $school_year_obj['school_year_id'];
        $current_school_period = $school_year_obj['period'];
        $current_school_term = $school_year_obj['term'];
        $current_school_year_id = $school_year_obj['school_year_id'];

        if(isset($_POST['set_year_semester']) 
            && isset( $_POST['school_year_id_btn']) ){

            $school_year_id_btn = $_POST['school_year_id_btn'];

            // echo $school_year_id_btn . " school_year_id_btn";
            // echo $school_year_id;
            $status_active = "Active";
            $current_status = "InActive";

            // Get the prev school_yerm term before the changing of new school_year term.
            # We have this function already.

            $current_school_year_term = null;
            $get_current_school_year_term = $this->con->prepare("SELECT term, period FROM school_year
                    WHERE statuses=:statuses
                    LIMIT 1");

            $get_current_school_year_term->bindValue(":statuses", $status_active);
            $get_current_school_year_term->execute();

            if($get_current_school_year_term->rowCount() > 0){
                $current_term_row = $get_current_school_year_term->fetch(PDO::FETCH_ASSOC);

                $current_school_year_term = $current_term_row['term'];
                $current_school_semester = $current_term_row['period'];
            }

            // echo $current_school_year_term;
            // echo $current_school_semester;

            // The previous active becomes inactive
            $update_normalized = $this->con->prepare("UPDATE school_year
                    SET statuses=:statuses
                    WHERE statuses=:current_status
                    AND school_year_id=:school_year_id");

            $update_normalized->bindValue(":statuses", "InActive");
            $update_normalized->bindValue(":current_status", "Active");
            $update_normalized->bindValue(":school_year_id", $school_year_id);
            $update_normalized->execute();

            // (Setting a new school_year term) the click inactive become active.
            $update_year = $this->con->prepare("UPDATE school_year
                    SET statuses=:statuses
                    WHERE statuses=:current_status
                    AND school_year_id=:school_year_id");

            $update_year->bindValue(":statuses", $status_active);
            $update_year->bindValue(":current_status", $current_status);
            $update_year->bindValue(":school_year_id", $school_year_id_btn);
            $update_year->execute();

            if($update_year->execute() && $current_school_year_term != null){

                $select_recently_term = $this->con->prepare("SELECT term FROM school_year
                        WHERE statuses=:statuses");
                $select_recently_term->bindValue(":statuses", $status_active);
                $select_recently_term->execute();

                // comes from update_year execution
                $new_school_year_term = $select_recently_term->rowCount() > 0 ? $select_recently_term->fetchColumn() : null;

                // echo "new_school_year_term=" . $new_school_year_term;
                // echo "School year have changed ";
                // echo "<br>";

                $course_level = 11;
                $active = "yes";

                // Will execute if changing FROM SECOND to FIRST Semester.
                $get_course_level_eleven = $this->con->prepare("SELECT * FROM course
                    WHERE course_level=:course_level
                    AND school_year_term=:school_year_term
                    AND active=:active");

                $get_course_level_eleven->bindValue(":course_level", $course_level);
                $get_course_level_eleven->bindValue(":school_year_term", $current_school_year_term);
                $get_course_level_eleven->bindValue(":active", $active);
                $get_course_level_eleven->execute();

                // echo " $current_school_year_term --";
                // echo "<br>";
                $get_course_tertiary_section = $this->con->prepare("SELECT * FROM course_tertiary
                    WHERE school_year_term=:school_year_term
                    AND active=:active");

                $get_course_tertiary_section->bindValue(":school_year_term", $current_school_year_term);
                $get_course_tertiary_section->bindValue(":active", $active);
                $get_course_tertiary_section->execute();


                # Algorithm for Moving Up the Current Tertiary Section Based on the Current Term (2021-2022)
                # As registrar selects new School Year From Second Sem(S.Y2021) To (S.Y2022)First Sem

                # 1. Select the current Section(s) based in todays S.Y
                # 2. Loop each section. Update Section active='yes' column into active='no'
                # 3. For every update of no.2, Create a newly tertiary section that will move-up the program_section (from ABE-1A to ABE-2A)
                # 4. Get the newly created course_tertiary_id
                # 5. Get the course_tertiary_id, course_level, program_id column on that newly created section
                # 6. Get All Subject_Program Table referencing the program_id and course_level
                # 7. Insert the subject_tertiary table that referenced the necessary column of Subject_Program Table (subject_title, subject_code etc)
                # 8. In just changing the S.Y from 2nd sem to 1st sem. We created individual newly section based on the previous active tertiary_course section
                # Which we have included its appropriate subject.
                

                # From Second Semester to First Semester. (Time of Moving-Up Section)
                if($get_course_tertiary_section->rowCount() > 0 && $current_school_semester == "Second"){

                    $getTertiaryCoursesForMovingUp = $get_course_tertiary_section->fetchAll(PDO::FETCH_ASSOC);
                    
                    $update_tertiary = $this->con->prepare("UPDATE course_tertiary
                        SET active=:active
                        -- WHERE course_level=:course_level
                        WHERE course_tertiary_id=:course_tertiary_id");
 
                    $moveUpTertiarySection = $this->con->prepare("INSERT INTO course_tertiary
                            (program_section, program_id, course_level, capacity, school_year_term, school_year_id, active, is_full, prev_course_tertiary_id)
                            VALUES(:program_section, :program_id, :course_level, :capacity, :school_year_term, :school_year_id, :active, :is_full, :prev_course_tertiary_id)");

                    $moveUpTertiarySectionId = null;

                    $new_program_section = "";


                    $insert_section_subject = $this->con->prepare("INSERT INTO subject_tertiary
                        (subject_title, description, subject_program_id, unit, semester, program_id, course_level, course_tertiary_id, subject_type, subject_code)
                        VALUES(:subject_title, :description, :subject_program_id, :unit, :semester, :program_id, :course_level, :course_tertiary_id, :subject_type, :subject_code)");


                    $insert_section_subject_v2 = $this->con->prepare("INSERT INTO subject_tertiary
                        (subject_title, description, subject_program_id, unit, semester, program_id, course_level, course_tertiary_id, subject_type, subject_code)
                        VALUES(:subject_title, :description, :subject_program_id, :unit, :semester, :program_id, :course_level, :course_tertiary_id, :subject_type, :subject_code)");

                    $createDefaultTertiaryCourse = $this->con->prepare("INSERT INTO 
                        course_tertiary
                        (program_section, program_id, course_level, capacity, school_year_term, active, is_full)
                        VALUES(:program_section, :program_id, :course_level, :capacity, :school_year_term, :active, :is_full)");

                    foreach ($getTertiaryCoursesForMovingUp as $key => $value) {
                        # code...

                        $tertiary_program_id = $value['program_id'];
                        $tertiary_course_level = $value['course_level'];
                        $course_tertiary_id = $value['course_tertiary_id'];

                        $previous_course_tertiary_id = $value['course_tertiary_id'];
                        
                        // $new_program_section = preg_replace('/(?<=HUMMS)\d+/', '12', $program_section);
                        // $new_program_section = str_replace('1', '2', $tertiary_program_section);
 
                        $tertiary_program_section = $value['program_section'];
                        $substring = substr($tertiary_program_section, 0, strpos($tertiary_program_section, "-") + 1);
                        preg_match('/(\d+)([A-Z])/', $tertiary_program_section, $matches);
                        $number = $matches[1] + 1;
                        $letter = $matches[2];

                        $new_program_section = $substring . $number . $letter;

                        $update_tertiary->bindValue(":active", "no");
                        $update_tertiary->bindValue(":course_tertiary_id", $course_tertiary_id);

                        if($update_tertiary->execute()){
                            // echo "Tertiary Section $tertiary_program_section is de-activated";
                            // echo "<br>";

                            $moveUpTertiarySection->bindValue(":program_section", $new_program_section);
                            $moveUpTertiarySection->bindValue(":program_id", $tertiary_program_id);
                            $moveUpTertiarySection->bindValue(":course_level", $tertiary_course_level + 1);
                            $moveUpTertiarySection->bindValue(":capacity", "2");
                            $moveUpTertiarySection->bindValue(":school_year_term", $new_school_year_term);
                            $moveUpTertiarySection->bindValue(":school_year_id", 0);
                            $moveUpTertiarySection->bindValue(":active", "yes");
                            $moveUpTertiarySection->bindValue(":is_full", "no");
                            $moveUpTertiarySection->bindValue(":prev_course_tertiary_id", $previous_course_tertiary_id);

                            
                            // Check and handle duplication entry of
                            // same program_section and school_year_term
                            if($moveUpTertiarySection->execute()){
                                // echo "New Tertiary $new_program_section section has been established at new $new_school_year_term";
                                // echo "<br>";
                                
                                $moveUpTertiarySectionId = $this->con->lastInsertId();

                                # Code for automatic population of section`s appropriate subject
                                # for course_tertiary table
                                if($moveUpTertiarySectionId != null){

                                    $newly_created_tertiary_program = $this->con-> prepare("SELECT 
                                            course_tertiary_id, course_level, program_id
                                            
                                            FROM course_tertiary

                                            WHERE course_tertiary_id=:course_tertiary_id
                                            LIMIT 1
                                        ");
                                    $newly_created_tertiary_program->bindValue(":course_tertiary_id", $moveUpTertiarySectionId);
                                    $newly_created_tertiary_program->execute();

                                    if($newly_created_tertiary_program->rowCount() > 0){

                                        $newly_tertiary_section_row = $newly_created_tertiary_program->fetch(PDO::FETCH_ASSOC);

                                        $newly_created_tertiary_program_id = $newly_tertiary_section_row['program_id'];
                                        $newly_created_tertiary_course_level = $newly_tertiary_section_row['course_level'];

                                        $get_subject_program = $this->con->prepare("SELECT * FROM subject_program
                                            WHERE program_id=:program_id
                                            AND course_level=:course_level
                                            ");

                                        $get_subject_program->bindValue(":program_id", $newly_created_tertiary_program_id);
                                        $get_subject_program->bindValue(":course_level", $newly_created_tertiary_course_level);
                                        $get_subject_program->execute();

                                        if($get_subject_program->rowCount() > 0){
                                            
                                            $isSubjectCreated = false;


                                            while($row = $get_subject_program->fetch(PDO::FETCH_ASSOC)){

                                                $program_program_id = $row['subject_program_id'];
                                                $program_course_level = $row['course_level'];
                                                $program_semester = $row['semester'];
                                                $program_subject_type = $row['subject_type'];
                                                $program_subject_title = $row['subject_title'];
                                                $program_subject_description = $row['description'];
                                                $program_subject_unit = $row['unit'];

                                                // $program_section = "";
                                                // $course_tertiary_id = 0;

                                                // $program_subject_code = $row['subject_code'] . $new_program_section; 
                                                $program_subject_code = $row['subject_code'];

                                                $insert_section_subject->bindValue(":subject_title", $program_subject_title);
                                                $insert_section_subject->bindValue(":description", $program_subject_description);
                                                $insert_section_subject->bindValue(":subject_program_id", $program_program_id);
                                                $insert_section_subject->bindValue(":unit", $program_subject_unit);
                                                $insert_section_subject->bindValue(":semester", $program_semester);
                                                $insert_section_subject->bindValue(":program_id", $newly_created_tertiary_program_id);
                                                $insert_section_subject->bindValue(":course_level", $program_course_level);
                                                $insert_section_subject->bindValue(":course_tertiary_id", $moveUpTertiarySectionId);
                                                $insert_section_subject->bindValue(":subject_type", $program_subject_type);
                                                $insert_section_subject->bindValue(":subject_code", $program_subject_code);

                                                // $insert_section_subject->execute();
                                                if($insert_section_subject->execute()){
                                                    $isSubjectCreated = true;
                                                }
                                            }
                                        }
                                    }
                                }

                                # Create new 1st Year Section.
                                if(true){

                                    // $createDefaultTertiaryCourse = $this->con->prepare("INSERT INTO 
                                    //     course_tertiary
                                    //     (program_section, program_id, course_level, capacity, school_year_term, active, is_full)
                                    //     VALUES(:program_section, :program_id, :course_level, :capacity, :school_year_term, :active, :is_full)");

                                    $createDefaultTertiaryCourse->bindValue(":program_section", $tertiary_program_section);
                                    $createDefaultTertiaryCourse->bindValue(":program_id", $tertiary_program_id, PDO::PARAM_INT);
                                    $createDefaultTertiaryCourse->bindValue(":course_level", $tertiary_course_level, PDO::PARAM_INT);
                                    $createDefaultTertiaryCourse->bindValue(":capacity", 2);
                                    $createDefaultTertiaryCourse->bindValue(":school_year_term", $new_school_year_term);
                                    $createDefaultTertiaryCourse->bindValue(":active", "yes");
                                    $createDefaultTertiaryCourse->bindValue(":is_full", "no");

                                    // Check and handle duplication entry of
                                    // same program_section and school_year_term
                                    if($createDefaultTertiaryCourse->execute()){

                                        $newlyCreatedTertiarySectionId = $this->con->lastInsertId();

                                        // echo "<br>";
                                        // echo "New Course $tertiary_program_section section is established at new $new_school_year_term";
                                        // echo "<br>";

                                        if($newlyCreatedTertiarySectionId != null){
                                            
                                            $newly_created_tertiary_program_v2 = $this->con-> prepare("SELECT 

                                                course_tertiary_id, course_level, program_id
                                                
                                                FROM course_tertiary

                                                WHERE course_tertiary_id=:course_tertiary_id
                                                LIMIT 1");

                                            $newly_created_tertiary_program_v2->bindValue(":course_tertiary_id", $newlyCreatedTertiarySectionId);
                                            $newly_created_tertiary_program_v2->execute();

                                            if($newly_created_tertiary_program_v2->rowCount() > 0){


                                                $newly_tertiary_section_row_v2 = $newly_created_tertiary_program_v2->fetch(PDO::FETCH_ASSOC);

                                                $newly_created_tertiary_program_id_v2 = $newly_tertiary_section_row_v2['program_id'];
                                                $newly_created_tertiary_course_level_v2 = $newly_tertiary_section_row_v2['course_level'];

                                                $get_subject_program_v2 = $this->con->prepare("SELECT * FROM subject_program
                                                    WHERE program_id=:program_id
                                                    AND course_level=:course_level
                                                    ");

                                                $get_subject_program_v2->bindValue(":program_id", $newly_created_tertiary_program_id_v2);
                                                $get_subject_program_v2->bindValue(":course_level", $newly_created_tertiary_course_level_v2);
                                                $get_subject_program_v2->execute();

                                                if($get_subject_program_v2->rowCount() > 0){
                                                    
                                                    $isSubjectCreated = false;

                                                    // $insert_section_subject_v2 = $this->con->prepare("INSERT INTO subject_tertiary
                                                    //     (subject_title, description, subject_program_id, unit, semester, program_id, course_level, course_tertiary_id, subject_type, subject_code)
                                                    //     VALUES(:subject_title, :description, :subject_program_id, :unit, :semester, :program_id, :course_level, :course_tertiary_id, :subject_type, :subject_code)");

                                                    while($row_v2 = $get_subject_program_v2->fetch(PDO::FETCH_ASSOC)){

                                                        $program_program_id = $row_v2['subject_program_id'];
                                                        $program_course_level = $row_v2['course_level'];
                                                        $program_semester = $row_v2['semester'];
                                                        $program_subject_type = $row_v2['subject_type'];
                                                        $program_subject_title = $row_v2['subject_title'];
                                                        $program_subject_description = $row_v2['description'];
                                                        $program_subject_unit = $row_v2['unit'];

                                                        // $program_section = "";
                                                        // $course_tertiary_id = 0;

                                                        // $program_subject_code = $row_v2['subject_code'] . $new_program_section; 
                                                        $program_subject_code = $row_v2['subject_code'];

                                                        $insert_section_subject_v2->bindValue(":subject_title", $program_subject_title);
                                                        $insert_section_subject_v2->bindValue(":description", $program_subject_description);
                                                        $insert_section_subject_v2->bindValue(":subject_program_id", $program_program_id);
                                                        $insert_section_subject_v2->bindValue(":unit", $program_subject_unit);
                                                        $insert_section_subject_v2->bindValue(":semester", $program_semester);
                                                        $insert_section_subject_v2->bindValue(":program_id", $newly_created_tertiary_program_id);
                                                        $insert_section_subject_v2->bindValue(":course_level", $program_course_level);
                                                        $insert_section_subject_v2->bindValue(":course_tertiary_id", $newlyCreatedTertiarySectionId);
                                                        $insert_section_subject_v2->bindValue(":subject_type", $program_subject_type);
                                                        $insert_section_subject_v2->bindValue(":subject_code", $program_subject_code);

                                                        // $insert_section_subject_v2->execute();
                                                        if($insert_section_subject_v2->execute()){
                                                            $isSubjectCreated = true;
                                                        }
                                                    }
                                                }
                                            }
                                        }

                                    }
                                }

                            }


                        }

                        // # Code for automatic population of section`s appropriate subject
                        // # for course_tertiary table
                        // if($moveUpTertiarySectionId != null){

                        //     $newly_created_tertiary_program = $this->con-> prepare("SELECT 
                        //             course_tertiary_id, course_level, program_id
                                    
                        //             FROM course_tertiary

                        //             WHERE course_tertiary_id=:course_tertiary_id
                        //             LIMIT 1
                        //         ");
                        //     $newly_created_tertiary_program->bindValue(":course_tertiary_id", $moveUpTertiarySectionId);
                        //     $newly_created_tertiary_program->execute();

                        //     if($newly_created_tertiary_program->rowCount() > 0){

                        //         $newly_tertiary_section_row = $newly_created_tertiary_program->fetch(PDO::FETCH_ASSOC);

                        //         $newly_created_tertiary_program_id = $newly_tertiary_section_row['program_id'];
                        //         $newly_created_tertiary_course_level = $newly_tertiary_section_row['course_level'];

                        //         $get_subject_program = $this->con->prepare("SELECT * FROM subject_program
                        //             WHERE program_id=:program_id
                        //             AND course_level=:course_level
                        //             ");

                        //         $get_subject_program->bindValue(":program_id", $newly_created_tertiary_program_id);
                        //         $get_subject_program->bindValue(":course_level", $newly_created_tertiary_course_level);
                        //         $get_subject_program->execute();

                        //         if($get_subject_program->rowCount() > 0){
                                    
                        //             $isSubjectCreated = false;

                        //             $insert_section_subject = $this->con->prepare("INSERT INTO subject_tertiary
                        //                 (subject_title, description, subject_program_id, unit, semester, program_id, course_level, course_tertiary_id, subject_type, subject_code)
                        //                 VALUES(:subject_title, :description, :subject_program_id, :unit, :semester, :program_id, :course_level, :course_tertiary_id, :subject_type, :subject_code)");

                        //             while($row = $get_subject_program->fetch(PDO::FETCH_ASSOC)){

                        //                 $program_program_id = $row['subject_program_id'];
                        //                 $program_course_level = $row['course_level'];
                        //                 $program_semester = $row['semester'];
                        //                 $program_subject_type = $row['subject_type'];
                        //                 $program_subject_title = $row['subject_title'];
                        //                 $program_subject_description = $row['description'];
                        //                 $program_subject_unit = $row['unit'];

                        //                 // $program_section = "";
                        //                 // $course_tertiary_id = 0;

                        //                 $program_subject_code = $row['subject_code'] . $new_program_section; 

                        //                 $insert_section_subject->bindValue(":subject_title", $program_subject_title);
                        //                 $insert_section_subject->bindValue(":description", $program_subject_description);
                        //                 $insert_section_subject->bindValue(":subject_program_id", $program_program_id);
                        //                 $insert_section_subject->bindValue(":unit", $program_subject_unit);
                        //                 $insert_section_subject->bindValue(":semester", $program_semester);
                        //                 $insert_section_subject->bindValue(":program_id", $newly_created_tertiary_program_id);
                        //                 $insert_section_subject->bindValue(":course_level", $program_course_level);
                        //                 $insert_section_subject->bindValue(":course_tertiary_id", $moveUpTertiarySectionId);
                        //                 $insert_section_subject->bindValue(":subject_type", $program_subject_type);
                        //                 $insert_section_subject->bindValue(":subject_code", $program_subject_code);

                        //                 // $insert_section_subject->execute();
                        //                 if($insert_section_subject->execute()){
                        //                     $isSubjectCreated = true;
                        //                 }
                        //             }
                        //         }
                        //     }
                        // }
                    }
 
                }

                $active_update = "no";

                # For SHS automatically created move_up course section
                # TODO. Automatically populating course subject is NOT SUPPORTED YET
                // if(false){
                // If second Sem 
                // Creates new section from (HUMMSS11-A, STEM11-A) to (HUMMSS12-A, STEM12-A)
                if($get_course_level_eleven->rowCount() > 0 && $current_school_semester == "Second"){

                    $gradeElevenCourses = $get_course_level_eleven->fetchAll(PDO::FETCH_ASSOC);

                    $moveUpCourseLevel = 12;
                    $capacity = 2;
                    $total_student = 0;
                    // Must be the new set school_year term.
                    $school_year_term = 0;
                    $active = "yes";
                    $is_full = "no";

                    $new_number = $moveUpCourseLevel;
                    

                    $update = $this->con->prepare("UPDATE course
                        SET active=:active
                        WHERE course_level=:course_level
                        AND course_id=:course_id");

                    $moveUpGradeSection = $this->con->prepare("INSERT INTO course
                            (program_section, program_id, course_level, capacity, school_year_term, active, is_full, previous_course_id)
                            VALUES(:program_section, :program_id, :course_level, :capacity, :school_year_term, :active, :is_full, :previous_course_id)");

                    $last_inserted_id = null;

                    $insert_section_subject = $this->con->prepare("INSERT INTO subject
                        (subject_title, description, subject_program_id, unit, semester, program_id, course_level, course_id, subject_type, subject_code)
                        VALUES(:subject_title, :description, :subject_program_id, :unit, :semester, :program_id, :course_level, :course_id, :subject_type, :subject_code)");

                    foreach ($gradeElevenCourses as $key => $value) {
                        
                        $program_section = $value['program_section'];
                        $program_id = $value['program_id'];
                        $course_id = $value['course_id'];
                        $previous_course_id = $value['course_id'];
                        
                        // $new_program_section = preg_replace('/(?<=HUMMS)\d+/', '12', $program_section);
                        $new_program_section = str_replace('11', $new_number, $program_section);


                        $update->bindValue(":active", $active_update);
                        $update->bindValue(":course_level", $course_level);
                        $update->bindValue(":course_id", $course_id);

                        if($update->execute()){
                            // echo "Grade 11 Section $program_section is de-activated";
                            // echo "<br>";
 
                            $moveUpGradeSection->bindValue(":program_section", $new_program_section);
                            $moveUpGradeSection->bindValue(":program_id", $program_id);
                            $moveUpGradeSection->bindValue(":course_level", $moveUpCourseLevel);
                            $moveUpGradeSection->bindValue(":capacity", $capacity);
                            $moveUpGradeSection->bindValue(":school_year_term", $new_school_year_term);
                            $moveUpGradeSection->bindValue(":active", "yes");
                            $moveUpGradeSection->bindValue(":is_full", "no");
                            $moveUpGradeSection->bindValue(":previous_course_id", $previous_course_id);
                            // Check and handle duplication entry of
                            // same program_section and school_year_term
                            if($moveUpGradeSection->execute()){
                                // echo "New Grade $moveUpCourseLevel $new_program_section  section is established at new $new_school_year_term";
                                // echo "<br>";
                                
                                // All Updated Id needs to insert an subjects.
                                $last_inserted_id = $this->con->lastInsertId();

                            }
                        }
                    }
                    
                    // Must insert the client strand offered, adjustable.
                    // (HUMMS,ABM,GAS,TVL)
                    $stem_program_id = 3;
                    $stem_program_section = "STEM11-A";
                    $humms_program_id = 4;
                    $humms_program_section = "HUMSS11-A";

                    if(true){
                        $defaultGrade11StemStrand = $this->con->prepare("INSERT INTO course
                            (program_section, program_id, course_level, capacity, school_year_term, active, is_full)
                            VALUES(:program_section, :program_id, :course_level, :capacity, :school_year_term, :active, :is_full)");

                        $defaultGrade11StemStrand->bindValue(":program_section", $stem_program_section);
                        $defaultGrade11StemStrand->bindValue(":program_id", $stem_program_id, PDO::PARAM_INT);
                        $defaultGrade11StemStrand->bindValue(":course_level", $course_level, PDO::PARAM_INT);
                        $defaultGrade11StemStrand->bindValue(":capacity", $capacity);
                        $defaultGrade11StemStrand->bindValue(":school_year_term", $new_school_year_term);
                        $defaultGrade11StemStrand->bindValue(":active", $active);
                        $defaultGrade11StemStrand->bindValue(":is_full", $is_full);

                        // Check and handle duplication entry of
                        // same program_section and school_year_term
                        if($defaultGrade11StemStrand->execute()){
                            // echo "<br>";
                            // echo "New Grade $course_level $stem_program_section section is established at new $new_school_year_term";
                            // echo "<br>";
                        }
                    }

                    if(true){
                        $defaultGrade11HummsStrand = $this->con->prepare("INSERT INTO course
                            (program_section, program_id, course_level, capacity, school_year_term, active, is_full)
                            VALUES(:program_section, :program_id, :course_level, :capacity, :school_year_term, :active, :is_full)");

                        $defaultGrade11HummsStrand->bindValue(":program_section", $humms_program_section);
                        $defaultGrade11HummsStrand->bindValue(":program_id", $humms_program_id, PDO::PARAM_INT);
                        $defaultGrade11HummsStrand->bindValue(":course_level", $course_level, PDO::PARAM_INT);
                        $defaultGrade11HummsStrand->bindValue(":capacity", $capacity);
                        $defaultGrade11HummsStrand->bindValue(":school_year_term", $new_school_year_term);
                        $defaultGrade11HummsStrand->bindValue(":active", $active);
                        $defaultGrade11HummsStrand->bindValue(":is_full", $is_full);

                        // Check and handle duplication entry of
                        // same program_section and school_year_term
                        if($defaultGrade11HummsStrand->execute()){
                            // echo "<br>";
                            // echo "New Grade $course_level $humms_program_section section is established at new $new_school_year_term";
                            // echo "<br>";
                        }
                    }

                    # TODO: It should inactive only if the second semester changed
                    # into first semester.
                    $GRADE_TWELVE = 12;
                    // Get all Grade 12 Sections and Mark them as Inactive.

                    $update_old_section = $this->con->prepare("UPDATE course
                        SET active=:active
                        WHERE course_level=:course_level
                        AND school_year_term=:school_year_term
                        AND active=:current_status ");

                    $update_old_section->bindValue(":active", $active_update);
                    $update_old_section->bindValue(":course_level", $GRADE_TWELVE);
                    $update_old_section->bindValue(":school_year_term", $current_school_year_term);
                    $update_old_section->bindValue(":current_status", $active);
                    $update_old_section->execute();

                }


            }
        }
        

        if(isset($_POST['end_enrollment_btn'])){

            $student_id = "";
            // All student enrolled in last semester will be deactived.

            // Current semester is Second
            // Check student in the first semester who have enrolled
            // and did not enrolled in this Current Semester.

            // if($current_school_period == "Second"){

            //     $asd = " SELECT e.*
            //         FROM enrollment e
            //         JOIN school_year s ON e.school_year_id = s.school_year_id
            //         WHERE s.period = 'First'
            //         AND e.school_year_id.period = 'First'
            //         ";

            //     $ah = $this->con->prepare("SELECT e.student_id
            //         FROM enrollment e

            //         WHERE e.school_year_id IN (
            //         SELECT s.school_year_id
            //         FROM school_year s
            //         WHERE s.period = 'First'
            //         AND s.term=:term
            //         -- AND e.student_id !=:student_id
            //         )");
                
            //     $ah->bindValue(":term", $current_school_term);
            //     // $ah->bindValue(":student_id", $student_id);
            //     $ah->execute();

            //     $row1 = $ah->fetchAll(PDO::FETCH_COLUMN);

            //     print_r($row1);
            //     echo "<br>";

            //     // Get all second semester enrolled
            //     $get_student_current_sy = $this->con->prepare("SELECT student_id FROM enrollment
            //         -- student_id=:student_id
            //         WHERE school_year_id=:school_year_id
            //         AND enrollment_status=:enrollment_status
            //         ");
            //     $get_student_current_sy->bindValue(":school_year_id", $school_year_id);
            //     $get_student_current_sy->bindValue(":enrollment_status", "tentative");
            //     $get_student_current_sy->execute();

            //     $row2 = $get_student_current_sy->fetchAll(PDO::FETCH_COLUMN);

            //     echo "<br>";
            //     print_r($row2);

            //     echo "<br>";
            //     echo "<br>";

            //     $not_enrolled_second_sem = array_diff($row1, $row2);

            //     print_r($not_enrolled_second_sem);
                
            //     $status_drop = "Drop";
            //     $regular_status = "Regular";
            //     $transferee_status = "Transferee";
            //     $dropped_status = "Drop";

            //     $update_dropped_student = $this->con->prepare("UPDATE student
            //         SET student_status=:update_status

            //         WHERE student_id=:student_id
            //         AND student_status=:regular_status
            //         OR student_id=:student_id
            //         AND student_status=:transferee_status
            //         ");

            //       // Get all second semester enrolled
            //     $check_already_dropped = $this->con->prepare("SELECT student_id FROM student
            //         WHERE student_id=:student_id
            //         AND student_status != :dropped_status
            //         ");


            //     // Enrolled for 1st semester
            //     // Did not enroll in this current second semester
            //     foreach ($not_enrolled_second_sem as $key => $value) {
            //         # code...
            //         // echo "not enrolled $value";

            //         $check_already_dropped->bindValue(":student_id", $value);
            //         $check_already_dropped->bindValue(":dropped_status", $dropped_status);
            //         $check_already_dropped->execute();

            //         if($check_already_dropped->rowCount() > 0){

            //             // echo "thats student $value is not dropped and neede to be dropped";
            //             // echo "<br>";
            //             $update_dropped_student->bindValue(":update_status", $status_drop);
            //             $update_dropped_student->bindValue(":student_id", $value);
            //             $update_dropped_student->bindValue(":regular_status", $regular_status);
            //             $update_dropped_student->bindValue(":transferee_status", $transferee_status);

            //             if($update_dropped_student->execute()){
            //                 echo "Student $value becomes drop";
            //                 echo "<br>";
            //             }
            //         }else{
            //             echo "no active student had drop";
            //         }
            //     }
            // }else{
            //     // echo "The system semester is not Second Semester";
            // }
            
            
            # From 1st Semester to Start of Second Semester
            # Grade 11 1st sem to Grade 11 2nd sem.
            # Grade 12 1st sem to Grade 12 2nd sem.
            
            // if($current_school_period == "Second"){

            //     $previos_sy_id = $this->con->prepare("SELECT school_year_id FROM school_year 
            //         WHERE school_year_id < (SELECT school_year_id FROM school_year WHERE statuses = 'Active') ORDER BY school_year_id DESC LIMIT 1
            //     ");

            //     $previos_sy_id->execute();

            //     $enrolled_prev_student_arr = [];
            //     $enrolled_current_student_arr = [];

            //     if($previos_sy_id->rowCount() > 0 && $current_school_period == "First"){
            //         $previous_school_year_id = $previos_sy_id->fetchColumn();

            //         // echo $previous_school_year_id;

            //         $enrollment_status = "enrolled";

            //         $enrollment_previous_sy_id = $this->con->prepare("SELECT student_id FROM enrollment 
            //             WHERE enrollment_status=:enrollment_status
            //             AND school_year_id=:school_year_id
            //         ");

            //         $enrollment_previous_sy_id->bindValue(":enrollment_status", $enrollment_status);
            //         $enrollment_previous_sy_id->bindValue(":school_year_id", $previous_school_year_id);
            //         $enrollment_previous_sy_id->execute();

            //         if($enrollment_previous_sy_id->rowCount() > 0){
            //             while($row = $enrollment_previous_sy_id->fetch(PDO::FETCH_ASSOC)){
            //                 array_push($enrolled_prev_student_arr, $row['student_id']);
            //             }
            //         }

            //         $enrollment_current_sy_id = $this->con->prepare("SELECT student_id FROM enrollment 
            //             WHERE enrollment_status=:enrollment_status
            //             AND school_year_id=:school_year_id
            //         ");

            //         $enrollment_current_sy_id->bindValue(":enrollment_status", $enrollment_status);
            //         $enrollment_current_sy_id->bindValue(":school_year_id", $current_school_year_id);
            //         $enrollment_current_sy_id->execute();

            //         if($enrollment_current_sy_id->rowCount() > 0){
            //             while($row = $enrollment_current_sy_id->fetch(PDO::FETCH_ASSOC)){
            //                 array_push($enrolled_current_student_arr, $row['student_id']);
            //             }
            //         }

            //         $student_did_not_enrolled_now_sy = array_diff($enrolled_prev_student_arr, $enrolled_current_student_arr);
                
            //         $active_status = 1;
            //         $non_active = 0;
            //         $update_dropped_student = $this->con->prepare("UPDATE student

            //             SET active=:update_status

            //             WHERE student_id=:student_id
            //             AND student_status=:regular_status
            //             AND active=:active_status

            //             OR student_id=:student_id
            //             AND student_status=:transferee_status
            //             AND active=:active_status

            //             ");

            //         $stopped_status = "Stopped";
            //         $in_active_status = "no";
            //         $regular_status = "Regular";
            //         $transferee_status = "Transferee";

            //         $reason = "Student Had Reached the Enrollment Data";
            //         $description = "If you want to enroll, Please walk in to registrar.";
                    
            //         // Enrolled for 1st semester
            //         // Did not enroll in this current second semester.

            //         foreach ($student_did_not_enrolled_now_sy as $key => $value) {

            //             $update_dropped_student->bindValue(":update_status", $non_active);
            //             $update_dropped_student->bindValue(":student_id", $value);

            //             $update_dropped_student->bindValue(":regular_status", $regular_status);
            //             $update_dropped_student->bindValue(":transferee_status", $transferee_status);
            //             $update_dropped_student->bindValue(":active_status", $active_status);

            //             if($update_dropped_student->execute()){
            //                 echo "Student $value is set to stopped";
            //                 echo "<br>";
            //                 // Put that information to the provided table for reference.

            //                 $insert = $this->con->prepare("INSERT INTO student_inactive_reason
            //                     (student_id, reason_title, description)
            //                     -- student_status, current_course_id, current_course_level
            //                     VALUES(:student_id, :reason_title, :description)");
            //                     // :student_status, :current_course_id,:current_course_level
                            
            //                 $insert->bindValue(":student_id", $value);
            //                 $insert->bindValue(":reason_title", $reason);
            //                 $insert->bindValue(":description", $description);
            //                 // $insert->bindValue(":student_status", $description);
            //                 // $insert->bindValue(":current_course_id", $description);
            //                 // $insert->bindValue(":current_course_level", $description);
            //                 $insert->execute();
            //             }

            //         }
            //     } 

            // }


            # Before system reached the end of enrollment
            # registrar should enrolled all tentative enrollees.
            # Dropping Logic.
            if($current_school_period == "First"){

                $previos_sy_id = $this->con->prepare("SELECT school_year_id FROM school_year 
                    WHERE school_year_id < (SELECT school_year_id FROM school_year WHERE statuses = 'Active') ORDER BY school_year_id DESC LIMIT 1
                ");

                $previos_sy_id->execute();

                $enrolled_prev_student_arr = [];
                $enrolled_current_student_arr = [];

                if($previos_sy_id->rowCount() > 0 && $current_school_period == "First"){

                    $previous_school_year_id = $previos_sy_id->fetchColumn();

                    if($previous_school_year_id != false){
                    // echo $previous_school_year_id;
                        $enrollment_status = "enrolled";

                        $enrollment_previous_sy_id = $this->con->prepare("SELECT student_id FROM enrollment 
                            WHERE enrollment_status=:enrollment_status
                            AND school_year_id=:school_year_id
                        ");

                        $enrollment_previous_sy_id->bindValue(":enrollment_status", $enrollment_status);
                        $enrollment_previous_sy_id->bindValue(":school_year_id", $previous_school_year_id);
                        $enrollment_previous_sy_id->execute();

                        if($enrollment_previous_sy_id->rowCount() > 0){
                            while($row = $enrollment_previous_sy_id->fetch(PDO::FETCH_ASSOC)){
                                array_push($enrolled_prev_student_arr, $row['student_id']);
                            }
                        }

                        $enrollment_current_sy_id = $this->con->prepare("SELECT student_id FROM enrollment 
                            WHERE enrollment_status=:enrollment_status
                            AND school_year_id=:school_year_id
                        ");

                        $enrollment_current_sy_id->bindValue(":enrollment_status", $enrollment_status);
                        $enrollment_current_sy_id->bindValue(":school_year_id", $current_school_year_id);
                        $enrollment_current_sy_id->execute();

                        if($enrollment_current_sy_id->rowCount() > 0){
                            while($row = $enrollment_current_sy_id->fetch(PDO::FETCH_ASSOC)){
                                array_push($enrolled_current_student_arr, $row['student_id']);
                            }
                        }

                        $student_did_not_enrolled_now_sy = array_diff($enrolled_prev_student_arr, $enrolled_current_student_arr);
                    
                        $active_status = 1;
                        $non_active = 0;
                        $update_dropped_student = $this->con->prepare("UPDATE student

                            SET active=:update_status

                            WHERE student_id=:student_id
                            AND student_status=:regular_status
                            AND active=:active_status

                            OR student_id=:student_id
                            AND student_status=:transferee_status
                            AND active=:active_status

                            ");

                        $stopped_status = "Stopped";
                        $in_active_status = "no";
                        $regular_status = "Regular";
                        $transferee_status = "Transferee";

                        $reason = "Student Had Reached the Enrollment Data";
                        $description = "If you want to enroll, Please walk in to registrar.";
                        
                        // Enrolled for 1st semester
                        // Did not enroll in this current second semester.

                        foreach ($student_did_not_enrolled_now_sy as $key => $value) {

                            $update_dropped_student->bindValue(":update_status", $non_active);
                            $update_dropped_student->bindValue(":student_id", $value);

                            $update_dropped_student->bindValue(":regular_status", $regular_status);
                            $update_dropped_student->bindValue(":transferee_status", $transferee_status);
                            $update_dropped_student->bindValue(":active_status", $active_status);

                            if(false){
                            // if($update_dropped_student->execute()){
                                echo "Student $value is set to stopped";
                                echo "<br>";
                                // Put that information to the provided table for reference.

                                $insert = $this->con->prepare("INSERT INTO student_inactive_reason
                                    (student_id, reason_title, description)
                                    -- student_status, current_course_id, current_course_level
                                    VALUES(:student_id, :reason_title, :description)");
                                    // :student_status, :current_course_id,:current_course_level
                                
                                $insert->bindValue(":student_id", $value);
                                $insert->bindValue(":reason_title", $reason);
                                $insert->bindValue(":description", $description);
                                // $insert->bindValue(":student_status", $description);
                                // $insert->bindValue(":current_course_id", $description);
                                // $insert->bindValue(":current_course_level", $description);
                                $insert->execute();
                            }

                        }
                    }else{
                        var_dump($previous_school_year_id);
                    }
                } 
            }

            if($current_school_period == "Second"){

                $previos_sy_id = $this->con->prepare("SELECT school_year_id FROM school_year 
                    WHERE school_year_id < (SELECT school_year_id FROM school_year WHERE statuses = 'Active') ORDER BY school_year_id DESC LIMIT 1
                ");

                $previos_sy_id->execute();

                $enrolled_prev_student_arr = [];
                $enrolled_current_student_arr = [];

                if($previos_sy_id->rowCount() > 0 && $current_school_period == "Second"){

                    $previous_school_year_id = $previos_sy_id->fetchColumn();

                    // echo $previous_school_year_id;

                    // echo $previous_school_year_id;

                    $enrollment_status = "enrolled";

                    // Get all enrolled & tentative student_id based on the previous_school_year_id
                    $enrollment_previous_sy_id = $this->con->prepare("SELECT student_id FROM enrollment 
                        WHERE enrollment_status=:enrollment_status
                        AND school_year_id=:school_year_id
                    ");

                    $enrollment_previous_sy_id->bindValue(":enrollment_status", $enrollment_status);
                    $enrollment_previous_sy_id->bindValue(":school_year_id", $previous_school_year_id);
                    $enrollment_previous_sy_id->execute();

                    if($enrollment_previous_sy_id->rowCount() > 0){
                        while($row = $enrollment_previous_sy_id->fetch(PDO::FETCH_ASSOC)){
                            array_push($enrolled_prev_student_arr, $row['student_id']);
                        }
                    }

                    // Get all enrolled student_id based on the current_school_year_id
                    $enrollment_current_sy_id = $this->con->prepare("SELECT student_id FROM enrollment 
                        WHERE enrollment_status=:tentative_enrollment_status
                        AND school_year_id=:school_year_id

                        OR enrollment_status=:enrolled_enrollment_status
                        AND school_year_id=:school_year_id
                    ");

                    $enrollment_current_sy_id->bindValue(":tentative_enrollment_status", "tentative");
                    $enrollment_current_sy_id->bindValue(":enrolled_enrollment_status", $enrollment_status);
                    $enrollment_current_sy_id->bindValue(":school_year_id", $current_school_year_id);
                    $enrollment_current_sy_id->execute();

                    if($enrollment_current_sy_id->rowCount() > 0){
                        while($row = $enrollment_current_sy_id->fetch(PDO::FETCH_ASSOC)){
                            array_push($enrolled_current_student_arr, $row['student_id']);
                        }
                    }

                    $student_did_not_enrolled_now_sy = array_diff($enrolled_prev_student_arr, $enrolled_current_student_arr);
                
                    print_r($student_did_not_enrolled_now_sy);
                    
                    $active_status = 1;
                    $non_active = 0;
                    $update_dropped_student = $this->con->prepare("UPDATE student

                        SET active=:update_status

                        WHERE student_id=:student_id
                        AND student_status=:regular_status
                        AND active=:active_status

                        OR student_id=:student_id
                        AND student_status=:transferee_status
                        AND active=:active_status

                        ");

                    $stopped_status = "Stopped";
                    $in_active_status = "no";
                    $regular_status = "Regular";
                    $transferee_status = "Transferee";

                    $reason = "Student Had Reached the Enrollment Data";
                    $description = "If you want to enroll, Please walk in to registrar.";
                    
                    // Enrolled for 1st semester
                    // Did not enroll in this current second semester.

                    foreach ($student_did_not_enrolled_now_sy as $key => $value) {

                        $update_dropped_student->bindValue(":update_status", $non_active);
                        $update_dropped_student->bindValue(":student_id", $value);

                        $update_dropped_student->bindValue(":regular_status", $regular_status);
                        $update_dropped_student->bindValue(":transferee_status", $transferee_status);
                        $update_dropped_student->bindValue(":active_status", $active_status);

                        // if(false){
                        if($update_dropped_student->execute()){
                            echo "Student $value is set to stopped";
                            echo "<br>";
                            // Put that information to the provided table for reference.

                            $insert = $this->con->prepare("INSERT INTO student_inactive_reason
                                (student_id, reason_title, description)
                                -- student_status, current_course_id, current_course_level
                                VALUES(:student_id, :reason_title, :description)");
                                // :student_status, :current_course_id,:current_course_level
                            
                            $insert->bindValue(":student_id", $value);
                            $insert->bindValue(":reason_title", $reason);
                            $insert->bindValue(":description", $description);
                            // $insert->bindValue(":student_status", $description);
                            // $insert->bindValue(":current_course_id", $description);
                            // $insert->bindValue(":current_course_level", $description);
                            $insert->execute();
                        }

                    }
                } 

            }

        }
        
        $table = "
            <h3 class='text-center'>Set the Current School Year</h3>
                <form method='POST'>
                    <button name='end_enrollment_btn' type='submit'class='btn btn-sm btn-success'>End Enrollment</button>
                </form>
                <table class='table table-hover'>
                    <thead >
                        <tr class='text-center'>
                            <th>Year</th>
                            <th>Semester</th>
                            <th>Action</th>
                        </tr>
                    </thead>
        ";

        $get_school_year = $this->con->prepare("SELECT * FROM school_year
            -- WHERE school_year_id >= :school_year_id
        ");

        // $get_school_year->bindValue(":school_year_id", $school_year_id);
        $get_school_year->execute();

        if($get_school_year->rowCount() > 0){
            while($row = $get_school_year->fetch(PDO::FETCH_ASSOC)){
                $table .= $this->GenerateTableBody($row);
            }
        }
        $table .= "
            </table>

        ";
        return $table;
    }

    private function GenerateTableBody($row){
      
        $school_year_id = $row['school_year_id'];
        $period = $row['period'];
        $school_year_term = $row['term'];
        $isActive = $row['statuses'];

        // Todo for changing the school year semester.
        $button = "
            <form method='POST' name='set_year_semester'>

                <button name='set_year_semester'   type='submit' class='btn btn-sm btn-primary'>
                    Set
                </button>
                <input type='hidden' name='school_year_id_btn' value='".$row['school_year_id']."'>
            </form >

        ";
        if($isActive == "Active"){
            $button = "
                <form method='POST' name='set_year_semester'>
                    <button name='set_year_semester' type='submit' class='btn btn-sm btn-success'>
                        Active
                    </button>
                    <input type='hidden' name='school_year_id_btn' value='".$row['school_year_id']."'>
                </form >
            ";
        }

        return "
            <tbody>
                <tr class='text-center'>
                    <td>$school_year_term</td>
                    <td>$period</td>
                    <td>
                        $button
                    </td>
                </tr>
            </tbody>
        ";
    }
}

?>