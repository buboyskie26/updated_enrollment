<?php 

    require_once('../includes/studentHeader.php');
    require_once('./classes/StudentEnroll.php');
    require_once('./classes/Section.php');
    require_once('./classes/SectionTertiary.php');
    require_once('../includes/classes/Student.php');
    
    // echo $_SESSION['username'];
    // echo $_SESSION['status'];
    if(isset($_SESSION['username'])
        && isset($_SESSION['status']) 
        && $_SESSION['status'] == 'enrolled'
        && $_SESSION['status'] != 'pending'
        ){

        $username = $_SESSION['username'];
        $enroll = new StudentEnroll($con);

        $student = new Student($con, $username);

        $school_year_obj = $enroll->GetActiveSchoolYearAndSemester();
        $course_id = $enroll->GetStudentCourseId($username);
        $course_tertiary_id = $enroll->GetStudentCourseTertiaryId($username);

        

        $section = new Section($con, $course_id);
        $sectionTertiary = new SectionTertiary($con, $course_tertiary_id);

        $school_year_id = $school_year_obj['school_year_id'];
        $current_semester = $school_year_obj['period'];

        // $student_year_id = $enroll->GetStudentCurrentYearId($username);

        $sql = $con->prepare("SELECT * FROM student
            WHERE username=:username
            -- WHERE firstname=:firstname
            LIMIT 1");

        $sql->bindValue(":username", $username);
        $sql->execute();
                
        // SHS OR TERTIARY
        $sectionName = "";

        $sectionName = $section->GetSectionName();
        $sectionName = $sectionTertiary->GetSectionName();

        ?>
            <div class="row col-md-12">
                <div class="col-lg-4">
                    <div class="card mb-4">
                        <div style="height: 100vh;" class="card-body text-center">
                            <img src="https://mdbcdn.b-cdn.net/img/Photos/new-templates/bootstrap-chat/ava3.webp" alt="avatar" class="rounded-circle img-fluid" style="width: 150px;">

                            <h5 class="my-3"><?php echo $student->GetName();?></h5>
                            <p class="text-muted mb-1"><?php echo $student->GetUsername();?></p>
                           
                            <!-- <div class="row">
                                <div class="col-sm-6 text-left"><p class="mb-0"><strong>Course:</strong></p></div>
                                <div class="col-sm-6 text-left"><p class="text-muted mb-0">CE</p></div>
                            </div> -->
                            <hr>
                            <div class="row">
                                <div class="col-sm-6 text-left"><p class="mb-0"><strong>Section:</strong></p></div>
                                <div class="col-sm-6 text-left"><p class="text-muted mb-0"><?php echo $sectionName;?></p></div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-sm-6 text-left"><p class="mb-0"><strong>Semester:</strong></p></div>
                                <div class="col-sm-6 text-left"><p class="text-muted mb-0"><?php echo $current_semester;?></p></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-8">
                    <ul class="nav nav-tabs" role="tablist">
                        <li style="margin-right: 7px;" class="nav-item">
                            <a class="nav-link active" id="basic-info-tab" 
                                data-bs-toggle="tab" 
                                href="#basic-info" role="tab"
                                aria-controls="basic-info" aria-selected="true">
                                Basic Info
                            </a>
                        </li>

                        <li style="margin-right: 7px;" class="nav-item">
                            <a class="nav-link" id="credentials-tab" 
                                data-bs-toggle="tab" 
                                href="#credentials" role="tab"
                                aria-controls="credentials" aria-selected="true">
                                Credentials
                            </a>
                        </li>

                        <li style="margin-right: 7px;" class="nav-item">
                            <a class="nav-link" id="guardian-tab" 
                                data-bs-toggle="tab" 
                                href="#guardian" role="tab"
                                aria-controls="guardian" aria-selected="true">
                                Guardian
                            </a>
                        </li>
                        <li style="margin-right: 7px;" class="nav-item">
                            <a class="nav-link" id="education-tab" 
                                data-bs-toggle="tab" 
                                href="#education" role="tab"
                                aria-controls="education" aria-selected="true">
                                Education
                            </a>
                        </li>
                        <div class='tab-content channelContent' id='myTabContent'>
                                <div class="col-md-12 tab-pane fade show active" id="basic-info"
                                    role="tabpanel" aria-labelledby="basic-info-tab">
                                    <?php include "basic_info.php" ?> 
                                </div>  
                                <div class="tab-pane fade" id="credentials" role="tabpanel"
                                    aria-labelledby="credentials-tab">
                                    <?php include "credentials.php" ?> 
                                </div>
                                <div class="tab-pane fade" id="guardian" role="tabpanel"
                                    aria-labelledby="guardian-tab">
                                    <?php include "guardian.php" ?> 
                                </div>
                                <div class="tab-pane fade" id="education" role="tabpanel"
                                    aria-labelledby="education-tab">
                                    <?php include "education.php" ?> 
                                </div>
                        </div>
                </div>
        <?php
    } 

    if(isset($_SESSION['username'])
        && isset($_SESSION['status']) 
        && $_SESSION['status'] == 'pending'
        && $_SESSION['status'] != 'enrolled'
        ){
        $username = $_SESSION['username'];

        // echo $username;
        $enroll = new StudentEnroll($con);

        $school_year_obj = $enroll->GetActiveSchoolYearAndSemester();
        // $course_id = $enroll->GetStudentCourseId($username);

        // $section = new Section($con, $course_id);

        $school_year_id = $school_year_obj['school_year_id'];
        $current_semester = $school_year_obj['period'];

        // $student_year_id = $enroll->GetStudentCurrentYearId($username);

        $sql = $con->prepare("SELECT * FROM student
            WHERE username=:username
            -- WHERE firstname=:firstname
            LIMIT 1");

        $sql->bindValue(":username", $username);
        $sql->execute();
                
        // $sectionName = $section->GetSectionName();

        $sql = $con->prepare("SELECT * FROM pending_enrollees
            WHERE firstname=:firstname");
        
        $sql->bindValue(":firstname", $username);
        $sql->execute();
        $row = $sql->fetch(PDO::FETCH_ASSOC);

        ?>
            <div class="row col-md-12">
                <div class="col-lg-4">
                    <div class="card mb-4">
                        <div style="height: 100vh;" class="card-body text-center">
                            <img src="https://mdbcdn.b-cdn.net/img/Photos/new-templates/bootstrap-chat/ava3.webp" alt="avatar" class="rounded-circle img-fluid" style="width: 150px;">

                            <h5 class="my-3"><?php echo $row['firstname'] . " " .$row['lastname'];?> </h5>
                            <p class="text-muted mb-1"><?php echo $row['email'];?></p>
                           
                            <div class="row">
                                <div class="col-sm-6 text-left"><p class="mb-0"><strong>Course:</strong></p></div>
                                <div class="col-sm-6 text-left"><p class="text-muted mb-0">N/A</p></div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-sm-6 text-left"><p class="mb-0"><strong>Section:</strong></p></div>
                                <div class="col-sm-6 text-left"><p class="text-muted mb-0">N/A</p></div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-sm-6 text-left"><p class="mb-0"><strong>Semester:</strong></p></div>
                                <div class="col-sm-6 text-left"><p class="text-muted mb-0"><?php echo $current_semester;?></p></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-8">
                    <ul class="nav nav-tabs" role="tablist">
                        <li style="margin-right: 7px;" class="nav-item">
                            <a class="nav-link active" id="basic-info-tab" 
                                data-bs-toggle="tab" 
                                href="#basic-info" role="tab"
                                aria-controls="basic-info" aria-selected="true">
                                Basic Info
                            </a>
                        </li>

                        <li style="margin-right: 7px;" class="nav-item">
                            <a class="nav-link" id="credentials-tab" 
                                data-bs-toggle="tab" 
                                href="#credentials" role="tab"
                                aria-controls="credentials" aria-selected="true">
                                Credentials
                            </a>
                        </li>

                        <li style="margin-right: 7px;" class="nav-item">
                            <a class="nav-link" id="guardian-tab" 
                                data-bs-toggle="tab" 
                                href="#guardian" role="tab"
                                aria-controls="guardian" aria-selected="true">
                                Guardian
                            </a>
                        </li>
                        <li style="margin-right: 7px;" class="nav-item">
                            <a class="nav-link" id="education-tab" 
                                data-bs-toggle="tab" 
                                href="#education" role="tab"
                                aria-controls="education" aria-selected="true">
                                Education
                            </a>
                        </li>
                        <div class='tab-content channelContent' id='myTabContent'>
                                <div class="col-md-12 tab-pane fade show active" id="basic-info"
                                    role="tabpanel" aria-labelledby="basic-info-tab">
                                    <?php include "basic_info.php" ?> 
                                </div>  
                                <div class="tab-pane fade" id="credentials" role="tabpanel"
                                    aria-labelledby="credentials-tab">
                                    <?php include "credentials.php" ?> 
                                </div>
                                <div class="tab-pane fade" id="guardian" role="tabpanel"
                                    aria-labelledby="guardian-tab">
                                    <?php include "guardian.php" ?> 
                                </div>
                                <div class="tab-pane fade" id="education" role="tabpanel"
                                    aria-labelledby="education-tab">
                                    <?php include "education.php" ?> 
                                </div>
                        </div>
                </div>
        <?php
    } 
?>


<?php  include('../includes/footer.php');?>
