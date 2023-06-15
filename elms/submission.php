<?php  
    // include('../classes/Subject.php');

    include('../admin/classes/Subject.php');
    include('../enrollment/classes/StudentEnroll.php');
    include('../enrollment/classes/SubjectPeriodAssignment.php');
    // include('../admin/assets/images/answers');
    include('../enrollment/classes/StudentPeriodAssignment.php');
 
    require_once('../includes/studentHeader.php');


    if(isset($_GET['spa_id'])){


        $subject_period_assignment_id = $_GET['spa_id'];

        $spa = new SubjectPeriodAssignment($con, $subject_period_assignment_id);

        $subject_id = $spa->GetSubjectId();

        // echo $subject_id;

        $studentEnroll = new StudentEnroll($con);

        $student = new Student($con, $studentLoggedIn);

        $student_id = $student->GetId();

        $school_year_obj = $studentEnroll->GetActiveSchoolYearAndSemester();

        $current_school_year_id = $school_year_obj['school_year_id'];
        $current_school_year_term = $school_year_obj['term'];
        $current_school_year_period = $school_year_obj['period'];

        $student_period_ass = new StudentPeriodAssignment($con, null);

        $checkIfHasSubmission = $student_period_ass->CheckIfSubmitted($subject_period_assignment_id,
            $student_id);
           
        $hasGraded = $student_period_ass->CheckIfGraded($subject_period_assignment_id,
            $student_id);

        if(isset($_POST['submit_student_dropbox_btn'])
            && isset($_FILES['assignment_file'])    
            
            ){

                $student_id = $student->GetId();

                // $image = $_FILES['assignment_file'] ?? null;

                $imageArray = $_FILES['assignment_file']['name'] ?? null;

                // var_dump($imageArray);
                $imagePath='';

                if (!is_dir('../admin/assets')) {
                    mkdir('../admin/assets');
                }
                
                if (!is_dir('../admin/assets/images')) {
                    mkdir('../admin/assets/images');
                }

                if (!is_dir('../admin/assets/images/answers')) {
                    mkdir('../admin/assets/images/answers');
                }


                $file_grade = $con->prepare("INSERT INTO student_assignment_grade
                    (student_id, subject_period_assignment_id)
                    VALUES(:student_id, :subject_period_assignment_id)                
                    ");

                $file_grade->bindValue(":student_id", $student_id);
                $file_grade->bindValue(":subject_period_assignment_id", $subject_period_assignment_id);

                // if(false){
                if($file_grade->execute()){


                    $student_assignment_grade_id = $con->lastInsertId();

                    if($student_assignment_grade_id != 0 && 
                        sizeof($imageArray) > 0){

                        $total_count = sizeof($imageArray);

                        for( $i=0 ; $i < $total_count ; $i++ ) {

                            //The temp file path is obtained
                            $tmpFilePath = $_FILES['assignment_file']['tmp_name'][$i];
                            $imagePath= '';

                            if ($tmpFilePath != ""){

                                // ORIGINAL FILE PATH ../admin/assets/etc
                                $imagePath = '../admin/assets/images/answers' . '/' 
                                    . $_FILES['assignment_file']['name'][$i];
                                    
                                // mkdir(dirname($imagePath));
                                move_uploaded_file($tmpFilePath, $imagePath);
                            }
                            $imagePath = substr($imagePath, 3);

                            $sql = $con->prepare("INSERT INTO student_period_assignment
                                (student_id, subject_period_assignment_id, 
                                    student_assignment_grade_id, file_path)
                                VALUES(:student_id, :subject_period_assignment_id, 
                                    :student_assignment_grade_id, :file_path)                
                                ");
                        
                            $sql->bindValue(":student_id", $student_id);
                            $sql->bindValue(":subject_period_assignment_id", $subject_period_assignment_id);
                            $sql->bindValue(":student_assignment_grade_id", $student_assignment_grade_id);
                            $sql->bindValue(":file_path", $imagePath);
                            
                            if($sql->execute()){
                                // echo "success";
                                AdminUser::success("Success",
                                    "student_class.php?id=$subject_id");
                            }
                        }
                    }
                    

                }

                
            
        }

        ?>
            <div class='col-md-10 row offset-md-1'>
                <div class="card">
                    <div class="card-header">
                        <h4 class='mb-3 text-primary'>Preparing Answer</h4>

                            <?php
                                if($checkIfHasSubmission){
                                    ?>
                                        <a href="student_assignment_view.php?id=<?php echo $subject_period_assignment_id;?>">
                                            <button class="btn btn-sm btn-outline-primary">View Submission</button>
                                        </a>
                                    <?php
                                }
                            ?>
                            
                        <h5 class="text-muted text-center">S.Y (<?php echo $current_school_year_term;?>) <?php echo $current_school_year_period;?> Semester</h5>
                    </div>

                    <?php
                        if($hasGraded == false){
                            ?>
                                <div class="card-body">
                                            
                                    <form method='POST' enctype="multipart/form-data">
                                            <div class="modal-body">

                                                <!-- <div class="mb-3">
                                                    <label class="mb-1" for="">* File Name</label>
                                                    <input type="text" name="file_path" 
                                                        value="" class="form-control">
                                                </div> -->

                                                <div class="mb-3">
                                                    <label class="mb-1" for="">* Choose File(s)</label>
                                                    <input type="file" placeholder="Choose" multiple='multiple'
                                                        name="assignment_file[]" id="assignment_file" class="form-control" />
                                                </div>

                                            </div>

                                            <div class="modal-footer">
                                                <button name="submit_student_dropbox_btn" type="submit" class="btn btn-primary">Add Assignment</button>
                                            </div>
                                    </form>

                                </div>
                            <?php
                        }
                    ?>

                </div>

            </div>

        <?php

    }


?>

