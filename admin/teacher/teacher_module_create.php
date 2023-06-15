<?php

    include('../teacher_enrollment_header.php');
    include('../../enrollment/classes/StudentEnroll.php');
    require_once('../../admin/classes/AdminUser.php');

    if(!AdminUser::IsTeacherAuthenticated()){
        header("Location: /dcbt/teacher_login.php");
        exit();
    }

    if(isset($_GET['id'])){

        $enroll = new StudentEnroll($con);

        $school_year_obj = $enroll->GetActiveSchoolYearAndSemester();

        $current_school_year_id = $school_year_obj['school_year_id'];
        $current_school_year_term = $school_year_obj['term'];
        $current_school_year_period = $school_year_obj['period'];

        $subject_period_id = $_GET['id'];

        $stmt = $con->prepare("SELECT t1.*, t2.* FROM subject_period as t1

            INNER JOIN subject as t2 ON t2.subject_id = t1.subject_id
            WHERE subject_period_id = ?");

        $stmt->execute([$subject_period_id]);

        $subjectPeriodObj = $stmt->fetch();

        if (!$subjectPeriodObj) {
            // handle error, the 'id' value does not exist in the database
            echo "error id";
            exit();
        }

        $subject_id = $subjectPeriodObj['subject_id'];

        if(isset($_POST['create_subject_period_assignment'])
            // && isset($_POST['description'])    
            // && isset($_POST['assignment_name'])    
            // && isset($_POST['assignment_picture'])    
            // && isset($_POST['due_date'])    
            ){

                $assignment_name = $_POST['assignment_name'];
                $max_score = $_POST['max_score'];

                $assignment_picture = "";

                $description = $_POST['description'];
                $due_date = $_POST['due_date'];

                $image = $_FILES['assignment_picture'] ?? null;


                $imagePath='';

                if (!is_dir('assets')) {
                    mkdir('assets');
                }
                
                if (!is_dir('assets/images')) {
                    mkdir('assets/images');
                }

                if (!is_dir('assets/images/answers')) {
                    mkdir('assets/images/answers');
                }


                if ($image && $image['tmp_name']) {

                    $imagePath = 'assets/images/answers' . '/' . $image['name'];
                    // mkdir(dirname($imagePath));
                    move_uploaded_file($image['tmp_name'], $imagePath);

                }

                $sql = $con->prepare("INSERT INTO subject_period_assignment
                    (subject_period_id, assignment_name, assignment_picture,
                        description, due_date, max_score)
                    VALUES(:subject_period_id, :assignment_name, :assignment_picture,
                        :description, :due_date, :max_score)                
                    ");
            
                $sql->bindValue(":subject_period_id", $subject_period_id);
                $sql->bindValue(":assignment_name", $assignment_name);
                $sql->bindValue(":assignment_picture", $imagePath);
                $sql->bindValue(":description", $description);
                $sql->bindValue(":due_date", $due_date);
                $sql->bindValue(":max_score", $max_score);
                
                if($sql->execute()){
                    AdminUser::success("Assignment: $assignment_name has been added in the module",
                        "teaching_module.php?id=$subject_id");
                }
            
        }

            ?>

            <div class='col-md-10 row offset-md-1'>

                <div class="card">
                    <div class="card-header">
                        <h4 class='mb-3 text-primary'>Teaching Subject: <?php echo $subjectPeriodObj['subject_title']?> </h4>
                        <h5 class="text-muted text-center">S.Y (<?php echo $current_school_year_term;?>) <?php echo $current_school_year_period;?> Semester</h5>
                    </div>

                    <div class="card-body">

                        <form method='POST' enctype="multipart/form-data">
                                <div class="modal-body">

                                    <div class="mb-3">
                                        <label class="mb-1" for="">* Assignment Name</label>
                                        <input type="text" name="assignment_name" 
                                            value="" class="form-control">
                                    </div>

                                    <div class="mb-3">
                                        <label class="mb-1" for="">* Description</label>
                                        <input type="text" name="description" 
                                            value="" class="form-control">
                                    </div>

                                    <div class="mb-3">
                                        <label class="mb-1" for="">* Image</label>
                                        <input type="file" placeholder="Semester Period"
                                            name="assignment_picture" id="assignment_picture" class="form-control" />
                                    </div>

                                    <div class="mb-3">
                                        <label class="mb-1" for="">* Due Date</label>
                                        <input type="date" placeholder="YYYY-MM-DD" name="due_date" id="due_date" class="form-control" />
                                    </div>

                                    <div class="mb-3">
                                        <label class="mb-1" for="">* Max Score</label>
                                        <input type="number" name="max_score" 
                                            value="" class="form-control">
                                    </div>

                                </div>

                                <div class="modal-footer">
                                    <button name="create_subject_period_assignment" type="submit" class="btn btn-primary">Add Assignment</button>
                                </div>
                        </form>

                    </div>
                </div>

            </div>
            <?php
        }
?>