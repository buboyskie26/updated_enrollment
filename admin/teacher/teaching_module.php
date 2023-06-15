<?php  
    include('../../enrollment/classes/Teacher.php');
    include('../teacher_enrollment_header.php');
    include('../../enrollment/classes/StudentEnroll.php');
    include('../classes/Subject.php');
    include('../classes/Course.php');

    if(!AdminUser::IsTeacherAuthenticated()){
        header("location: /dcbt/teacher_login.php");
        exit();
    }

    $teacher = new Teacher($con, $teacherLoggedIn);
    
    $teacher_id = $teacher->GetTeacherId();

    $studentEnroll = new StudentEnroll($con);

    $school_year_obj = $studentEnroll->GetActiveSchoolYearAndSemester();

    $current_school_year_id = $school_year_obj['school_year_id'];
    $current_school_year_term = $school_year_obj['term'];
    $current_school_year_period = $school_year_obj['period'];

    echo $current_school_year_id;

    if(isset($_GET['id'])){

        $subject_id = $_GET['id'];

        $subject = new Subject($con, $teacherLoggedIn, $subject_id);

        $subject_title = $subject->GetSubjectTitle($subject_id);

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
                                                            <a href="teacher_module_create.php?id=<?php echo $subject_period_id;?>">
                                                                <button class='btn btn-primary btn-sm'>Add Assignment</button>
                                                            </a>
                                                        </div>
                                                        <div class='col-md-3'>
                                                            <!-- Add content for the second column if needed -->
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <table class='table table-responsive mt-2'>
                                            <thead>
                                                <tr class='bg-success text-center'>
                                                    <th>Assignment</th>
                                                    <th>Image</th>
                                                    <th>Max Score</th>
                                                    <th>Due</th>
                                                    <th></th>
                                                </tr>
                                            </thead>

                                            <tbody>
                                                <?php

                                                    $assignment = $con->prepare("SELECT *
                                    
                                                        FROM subject_period_assignment
                                                        WHERE subject_period_id=:subject_period_id
                                                    ");

                                                    $assignment->bindValue(":subject_period_id", $subject_period_id);
                                                    $assignment->execute();

                                                    if($sql->rowCount() > 0){

                                                        while($row_ass = $assignment->fetch(PDO::FETCH_ASSOC)){
                                                            $assignment_name = $row_ass['assignment_name'];
                                                            $subject_period_assignment_id = $row_ass['subject_period_assignment_id'];
                                                            $due_date = $row_ass['due_date'];
                                                            $assignment_picture = $row_ass['assignment_picture'];
                                                            $max_score = $row_ass['max_score'];


                                                            $my_students_submission = "students_assignments_status.php?id=$subject_id&spa_id=$subject_period_assignment_id";

                                                            echo "
                                                                <tr class='text-center'>
                                                                    <td>
                                                                        <a href='$my_students_submission'>
                                                                            $assignment_name
                                                                        </a>
                                                                    </td>
                                                                    <td>
                                                                        <img style='width:100px;' src='$assignment_picture' alt=''>
                                                                    </td>

                                                                    <td>$max_score</td>
                                                                    <td>$due_date</td>
                                                                    <td>
                                                                        <a href=''>
                                                                            <button class='btn btn-outline-primary'>
                                                                                <i class='fas fa-edit'></i>
                                                                            </button>
                                                                        </a>

                                                                    </td>
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
                        }else{
                            echo "not";
                        }
                    ?>

                </div>
            </div>
        <?php
    }
?>

