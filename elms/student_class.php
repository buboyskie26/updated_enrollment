<?php  
    // include('../classes/Subject.php');

    include('../admin/classes/Subject.php');
    include('../enrollment/classes/StudentEnroll.php');
    include('../enrollment/classes/StudentPeriodAssignment.php');
 
    require_once('../includes/studentHeader.php');

    if(!AdminUser::IsStudentAuthenticated()){
        header("location: /dcbt/teacher_login.php");
        exit();
    }

    // $teacher = new Teacher($con, $teacherLoggedIn);
    
    // $teacher_id = $teacher->GetTeacherId();

    $studentEnroll = new StudentEnroll($con);

    $school_year_obj = $studentEnroll->GetActiveSchoolYearAndSemester();

    $current_school_year_id = $school_year_obj['school_year_id'];
    $current_school_year_term = $school_year_obj['term'];
    $current_school_year_period = $school_year_obj['period'];

    $student = new Student($con, $studentLoggedIn);

    $student_id = $student->GetId();


    // echo $current_school_year_id;

    if(isset($_GET['id'])){

        $subject_id = $_GET['id'];


        $subject = new Subject($con, $studentLoggedIn, $subject_id);

        $subject_title = $subject->GetSubjectName();

        ?>
            <div class="col-md-12 row">

                <div class="card">
                    <div class="card-header">
                        <h3 class="text-center text-muted"><?php echo $subject_title;?></h3>
                    </div>
                                    
                    <?php
                        $sql = $con->prepare("SELECT t1.*, t2.*
                        
                            FROM subject_period as t1 

                            INNER JOIN subject as t2 ON t2.subject_id = t1.subject_id
                            WHERE t1.subject_id=:subject_id
                            AND t1.school_year_id=:school_year_id
                            ");

                        $sql->bindValue(":subject_id", $subject_id);
                        $sql->bindValue(":school_year_id", $current_school_year_id);
                        $sql->execute();

                        if($sql->rowCount() > 0){

                            while($row = $sql->fetch(PDO::FETCH_ASSOC)){

                                $subject_period_id = $row['subject_period_id'];
                                $term = $row['term'];
                                $title = $row['title'];
                                $description = $row['description'];
                                $subject_title = $row['subject_title'];

                                ?>
                                   <div class='col-md-12 mb-3'>
                                        <div style='border: 2px solid green;' class='card'>
                                            <div class='card-body'>
                                                <div class='card-block'>
                                                    <h4 class='card-title'><?php echo $title;?></h4>
                                                    <h6 class='card-subtitle text-muted'><?php echo $description;?></h6>
                                                    <p class='card-text p-y-1'>Some quick example text to build on the card title.</p>
                                                    <div class='row'>
                                                         
                                                        <div class='col-md-3'>
                                                            <!-- Add content for the second column if needed -->
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>


                                        <table class='table table-responsive mt-2'>
                                            <thead>
                                                <tr class='bg-dark text-center'>
                                                    <th>Section</th>
                                                    <th>Submitted</th>
                                                    <th>Score</th>
                                                    <th>Due</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>

                                            <tbody>
                                                <?php

                                                    # All student enrolled in the subject
                                                    # Answered/Unaswered, All subject_period_assignment should be shown.

                                                    $assignment = $con->prepare("SELECT t1.*, t2.grade
                                    
                                                        FROM subject_period_assignment as t1

                                                        LEFT JOIN student_assignment_grade as t2 
                                                            ON t2.subject_period_assignment_id = t1.subject_period_assignment_id

                                                        AND t2.is_final=1
                                                        AND t2.student_id=:student_id

                                                        WHERE t1.subject_period_id=:subject_period_id
                                                        -- AND t2.student_id=:student_id

                                                    ");

                                                    $assignment->bindValue(":subject_period_id", $subject_period_id);
                                                    $assignment->bindValue(":student_id", $student_id);
                                                    $assignment->execute();

                                                    if($sql->rowCount() > 0){

                                                        while($row_ass = $assignment->fetch(PDO::FETCH_ASSOC)){

                                                            $assignment_name = $row_ass['assignment_name'];
                                                            $subject_period_assignment_id = $row_ass['subject_period_assignment_id'];
                                                            $due_date = $row_ass['due_date'];
                                                            $assignment_picture = $row_ass['assignment_picture'];
                                                            $max_score = $row_ass['max_score'];

                                                            $grade = $row_ass['grade'];

                                                            $my_students_submission = "students_assignments_status.php?id=$subject_id&spa_id=$subject_period_assignment_id";

                                                            $url = directoryPath . "submission.php?spa_id=$subject_period_assignment_id";

                                                            $status = "";

                                                            $student_period_ass = new StudentPeriodAssignment($con, $subject_period_assignment_id);

                                                            $passedAndNotChecked = $student_period_ass->CheckIfSubmitted($subject_period_assignment_id,
                                                                $student_id);

                                                            $passedAndChecked = $student_period_ass->CheckIfGraded($subject_period_assignment_id,
                                                                $student_id);

                                                            $score = "$grade/$max_score";

                                                            $date = strtotime($due_date);
                                                            $month = date("F", $date);
                                                            $day = date("d", $date);
                                                            
                                                            

                                                            if($grade == 0){
                                                                // $status = "~";
                                                                $score = "~";

                                                            }

                                                            if($passedAndNotChecked == true && !$passedAndChecked){
                                                                $status = "
                                                                    <i class='fas fa-check'></i>
                                                                ";
                                                                $score = "?/$max_score";
                                                                
                                                            }

                                                            if($passedAndChecked == true){
                                                                $status = "
                                                                    <i class='fas fa-check'></i>
                                                                ";
                                                                $score = "$grade/$max_score";
                                                                // $url  = "";
                                                            }

                                                            echo "
                                                                <tr class='text-center'>
                                                                    <td>
                                                                        <a href='$url'>
                                                                            $assignment_name
                                                                        </a>
                                                                    </td>
                                                                    <td>$status</td>
                                                                    <td>$score</td>

                                                                    <td>$month $day</td>
                                                                </tr>
                                                            ";
                                                        }
                                                    }
                                                                    
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php
                            }
                        } 
                    ?>
                </div>
            </div>
        <?php
    }
?>

