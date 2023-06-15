<?php

    // require_once('../classes/Subject.php');

    require_once('../enrollment/classes/StudentEnroll.php');
    require_once('../includes/studentHeader.php');
    require_once('../enrollment/classes/StudentPeriodAssignment.php');

    if(!AdminUser::IsStudentAuthenticated()){
        header("Location: /dcbt/teacher_login.php");
        exit();
    }


    if(isset($_GET['id'])){



        $subject_period_assignment_id = $_GET['id'];
        // $student_id = $_GET['s_id'];

        $student = new Student($con, $studentLoggedIn);

        $student_id = $student->GetId();

        $student_period_ass = new StudentPeriodAssignment($con, null);

        $checkIfHasSubmission = $student_period_ass->CheckIfSubmitted($subject_period_assignment_id,
            $student_id);

        if(!$checkIfHasSubmission){
            echo "You dont have any submission";
            exit();
        }

        $student = new Student($con, $studentLoggedIn);

        $student_id = $student->GetId();

        ?>

        <div class="col-md-12 row">

            <div class="card">
                <div class="card-header">
                    <h3 class="text-center text-muted">List of Submission(s)</h3>
                </div>
                <?php 

                    $submission = $con->prepare("SELECT 
                        t1.*
                        FROM student_assignment_grade as t1
                        
                        WHERE t1.subject_period_assignment_id=:subject_period_assignment_id
                        AND t1.student_id=:student_id

                        ORDER BY t1.date_creation DESC
                    ");

                    $submission->bindValue(":subject_period_assignment_id", $subject_period_assignment_id);
                    $submission->bindValue(":student_id", $student_id);
                    
                    $submission->execute();

                    if($submission->rowCount() > 0){

                        $i = 0;

                        while($row = $submission->fetch(PDO::FETCH_ASSOC)){

                            $i++;

                            $text = "";

                            if($i == 1){
                                $text = "(Latest)";
                            }

                            $date_creation = $row['date_creation'];
                            $student_assignment_grade_id = $row['student_assignment_grade_id'];

                            $btn = "inputGrade()";

                            $grade = $row['grade'];
                            $output = "";

                            if($grade != 0){
                                $output = "Grade: $grade";
                            }else{
                                $output = "To be graded.";
                            }
                            ?>
                                    <div style='border: 2px solid green;' class='card'>
                                        <div class='card-body'>
                                            <div class='card-block'>
                                                <h4 class='card-title'>Attempt: <?php echo "$i $text";?></h4>
                                                <h6 class='card-subtitle text-muted'>Submission Date: <?php echo $date_creation;?></h6>
                                                <div class='row'>
                                                    <?php echo $output;?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <table class="table table-responsive">
                                        <thead>
                                            <tr class='bg-dark text-center'>
                                                <th>File</th>
                                                <th>Passed Date</th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            <?php 
                                            
                                                $student_ass_file = $con->prepare("SELECT 
                                                    t1.*
                                                    FROM student_period_assignment as t1
                                                    
                                                    WHERE t1.student_assignment_grade_id=:student_assignment_grade_id
                                                    -- AND t1.student_id=:student_id

                                                    ORDER BY t1.passed_date DESC
                                                ");

                                                $student_ass_file->bindValue(":student_assignment_grade_id", $student_assignment_grade_id);
                                                
                                                $student_ass_file->execute();

                                                if($student_ass_file->rowCount() > 0){
                                                    while($ass_row = $student_ass_file->fetch(PDO::FETCH_ASSOC)){

                                                        $file_path = "../../dcbt/" . $ass_row['file_path'];
                                                        $passed_date = $ass_row['passed_date'];
                                                        // require_once('../../admin/assets/images/answers/');

                                                        echo "
                                                            <tr class='text-center'>
                                                                <td class='text-start'>
                                                                    <img style='width: 280px;' src='$file_path' alt='image'>
                                                                </td>
                                                                <td>$passed_date</td>
                                                            </tr>
                                                        ";
                                                    }
                                                }
                                            ?>
                                        </tbody>

                                    </table>


                            <?php

                        }
                    }

                ?>

            </div>



            
        </div>

        <?php
    }

?>