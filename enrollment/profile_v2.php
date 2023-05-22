<?php 

    require_once('../includes/studentHeader.php');
    require_once('./classes/StudentEnroll.php');
    
    if(isset($_SESSION['username'])){

        $username = $_SESSION['username'];
        $enroll = new StudentEnroll($con);

        $school_year_obj = $enroll->GetActiveSchoolYearAndSemester();
        $school_year_id = $school_year_obj['school_year_id'];

        // $student_year_id = $enroll->GetStudentCurrentYearId($username);

        $sql = $con->prepare("SELECT * FROM student
            WHERE username=:username
            -- WHERE firstname=:firstname
            LIMIT 1");

        $sql->bindValue(":username", $username);
        $sql->execute();

        ?>
            <div class="row">
                <div class="col-lg-10 offset-md-1">
                    <ul class="nav nav-tabs" role="tablist">
                        
                    <?php 
                        // Student Table
                        if($sql->rowCount() > 0){
                        ?>
                            <li class="nav-item">
                                <a class="nav-link active" id="listOfSubjects-tab" 
                                    data-bs-toggle="tab" 
                                    href="#listOfSubjects" role="tab"
                                    aria-controls="listOfSubjects" aria-selected="true">
                                    Schedule
                                </a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link" id="grades-tab" 
                                    data-bs-toggle="tab" 
                                    href="#grades" role="tab"
                                    aria-controls="grades" aria-selected="true">
                                    Grades
                                </a>
                            </li>
                            <?php
                                # TODO: Logic for hiding the Apply Now.
                                echo '
                                    <li class="nav-item">
                                        <a class="nav-link" id="next-sem-tab" 
                                            data-bs-toggle="tab" 
                                            href="#next-sem" role="tab"
                                            aria-controls="next-sem" aria-selected="true">
                                            Apply now
                                        </a>
                                    </li>
                                ';
                            ?>
                            
                            <div class='tab-content channelContent' id='myTabContent'>
                                <div class="tab-pane fade show active" id="listOfSubjects"
                                    role="tabpanel" aria-labelledby="listOfSubjects-tab">
                                    <?php include "list_subjects.php" ?> 
                                </div>
                                <div class="tab-pane fade" id="grades" role="tabpanel"
                                    aria-labelledby="grades-tab">
                                        <?php include "student_grade_list.php" ?> 
                                </div>
                                <div class="tab-pane fade" id="next-sem" role="tabpanel"
                                    aria-labelledby="next-sem-tab">
                                        <?php include "current_semester_subject.php" ?> 
                                </div>
                            </div>
                            
                        <?php
                        
                        }else if($sql->rowCount() == 0){
                            ?>

                            <li class="nav-item">
                                <a class="nav-link active" id="pending_profile-tab" 
                                    data-bs-toggle="tab" 
                                    href="#pending_profile" role="tab"
                                    aria-controls="pending_profile" aria-selected="true">
                                    Profile
                                </a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link" id="pre_registration_pending-tab" 
                                    data-bs-toggle="tab" 
                                    href="#pre_registration_pending" role="tab"
                                    aria-controls="pre_registration_pending" aria-selected="true">
                                    Pre Registration
                                </a>
                            </li>

                            <div class='tab-content'>
                                <div class="tab-pane fade show active" id="pending_profile"
                                        role="tabpanel" aria-labelledby="pending_profile-tab">
                                        <?php include "pending_profile.php" ?> 
                                </div>
                                <div class="tab-pane fade" id="pre_registration_pending"
                                        role="tabpanel" aria-labelledby="pre_registration_pending-tab">
                                    <?php include "pre_registration_pending.php" ?> 
                                </div>
                            </div>

                            <?php
                        }
                    ?>
                       
                    </ul>
                </div>
            </div>
        <?php

    }else{
        $username = $_SESSION['username'];
        echo $username;
        echo "username session not establish";
    }


?>


<?php  include('../includes/footer.php');?>
