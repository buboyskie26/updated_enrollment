<?php

    require_once('../teacher_enrollment_header.php');
    require_once('../../enrollment/classes/StudentEnroll.php');
    require_once('../../enrollment/classes/SubjectPeriodAssignment.php');
    require_once('../../enrollment/classes/Teacher.php');
    require_once('../classes/Subject.php');


    if(!AdminUser::IsTeacherAuthenticated()){
        header("Location: /dcbt/teacher_login.php");
        exit();
    }


    if(isset($_GET['id']) && isset($_GET['s_id'])){

        $subject_period_assignment_id = $_GET['id'];
        $student_id = $_GET['s_id'];

        $spa = new SubjectPeriodAssignment($con, $subject_period_assignment_id);

        $subject_id = $spa->GetSubjectId();

        if(isset($_POST['grade_remark_btn'])
            && isset($_POST['grade_input'])
            && isset($_POST['student_assignment_grade_id'])
        ){

            
            $grade_input = $_POST['grade_input'];
            $student_assignment_grade_id = $_POST['student_assignment_grade_id'];

            

            $insert = $con->prepare("UPDATE student_assignment_grade

                SET grade=:grade
                WHERE student_assignment_grade_id=:student_assignment_grade_id
                AND subject_period_assignment_id=:subject_period_assignment_id");

            $insert->bindValue(":grade", $grade_input);
            $insert->bindValue(":student_assignment_grade_id", $student_assignment_grade_id);
            $insert->bindValue(":subject_period_assignment_id", $subject_period_assignment_id);
            if($insert->execute()){

                Alert::success("Marked Graded", "teaching_module.php?id=$subject_id");
            }
        }
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
                                $output = "
                                <form style='margin-top:7px;' method='POST'>
                                    <div class='row col-md-12'>
                                    
                                        <div class='col-md-2'>

                                            <button name='grade_remark_btn' type='submit' class='btn btn-primary'>Remark</button>
                                        </div>
                                        <div class='col-md-2'>
                                            <input type='hidden' name='student_assignment_grade_id' value='$student_assignment_grade_id'>
                                            <input style='width: 100px;' 
                                                type='text' name='grade_input' class='form-control'>
                                        </div>
                                    </div>
                                </form>
                                ";
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

                                                        $file_path = "../../" . $ass_row['file_path'];
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