<?php

    require_once('../teacher_enrollment_header.php');
    require_once('../../enrollment/classes/StudentEnroll.php');
    require_once('../../enrollment/classes/StudentPeriodAssignment.php');
    require_once('../../enrollment/classes/Teacher.php');
    require_once('../../admin/classes/AdminUser.php');
    require_once('../classes/Subject.php');

    if(!AdminUser::IsTeacherAuthenticated()){
        header("Location: /dcbt/teacher_login.php");
        exit();
    }


    if(isset($_GET['id'])
        && isset($_GET['spa_id'])){

        $subject_id = $_GET['id'];
        $subject_period_assignment_id = $_GET['spa_id'];

        $subject = new Subject($con, $teacherLoggedIn, $subject_id);
        $teacher = new Teacher($con, $teacherLoggedIn);


        $studentEnroll = new StudentEnroll($con);

        $school_year_obj = $studentEnroll->GetActiveSchoolYearAndSemester();

        $current_school_year_id = $school_year_obj['school_year_id'];
        $current_school_year_term = $school_year_obj['term'];
        $current_school_year_period = $school_year_obj['period'];

        $studentPeriodAssignment = new StudentPeriodAssignment($con, null);

        $teacher_id = $teacher->GetTeacherId();

        if($subject->InvalidSubject() == false){
            echo "error subject id";
            exit();
        }

        $total = $studentPeriodAssignment->GetAllStudentSubmitted($subject_period_assignment_id);
        $totalStudent = $studentPeriodAssignment->GetAllMyStudent($subject_id,
            $current_school_year_id);
        
        ?>

            <div class="col-md-12 row">

                <div class="card">

                    <div class="card-header">
                        <span>
                            <?php echo $total;?> Student Passed / <?php echo $totalStudent;?> Total Student
                        </span>
                    </div>
                    <div class="card-body">
                        <table class="table table-responsive">
                            <thead>
                                <tr class='bg-dark text-center'>
                                    <th>SUBMITTED BY</th>
                                    <th>Status</th>
                                    <th></th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php 

                                    $student_ass = $con->prepare("SELECT t1.*, t3.*
                                    
                                        -- , t4.*
                    
                                        FROM student_subject as t1

                                        INNER JOIN subject_schedule as t2 ON t2.subject_id = t1.subject_id
                                        INNER JOIN student as t3 ON t3.student_id = t1.student_id

                                        -- LEFT JOIN student_period_assignment as t4 ON t4.student_id = t1.student_id
                                        -- AND student_period_assignment_id=:student_period_assignment_id

                                        WHERE t2.subject_id=:subject_id
                                        AND t2.teacher_id=:teacher_id

                                    ");

                                    $student_ass->bindValue(":subject_id", $subject_id);
                                    $student_ass->bindValue(":teacher_id", $teacher_id);
                                    // $student_ass->bindValue(":student_period_assignment_id", $student_period_assignment_id);
                                    $student_ass->execute();

                                    // $student_ass = $con->prepare("SELECT

                                    //     t1.*, t3.*, t4.file_name, t4.passed_date

                                    //     FROM student_subject AS t1
                                    //     INNER JOIN subject_schedule AS t2 ON t2.subject_id = t1.subject_id
                                    //     INNER JOIN student AS t3 ON t3.student_id = t1.student_id
                                    //     -- 
                                    //     LEFT JOIN student_period_assignment AS t4 ON t4.student_id = t1.student_id
                                    //         AND t4.subject_period_assignment_id = :subject_period_assignment_id

                                    //     WHERE t2.subject_id = :subject_id
                                    //         AND t2.teacher_id = :teacher_id");

                                    // $student_ass->bindValue(":subject_id", $subject_id);
                                    // $student_ass->bindValue(":teacher_id", $teacher_id);
                                    // $student_ass->bindValue(":subject_period_assignment_id", $subject_period_assignment_id);
                                    // $student_ass->execute();


                                    if($student_ass->rowCount() > 0){

                                        while($row_ass = $student_ass->fetch(PDO::FETCH_ASSOC)){


                                            $firstname = $row_ass['firstname'];
                                            $lastname = $row_ass['lastname'];
                                            $student_id = $row_ass['student_id'];

                                            // $file_name = $row_ass['file_name'];
                                            // $passed_date = $row_ass['passed_date'];
        
                                            # 1. GET ALL STUDENTS WHO ARE BELONG TO THAT SUBJECT
                                            # 2. IF STUDENT HAS PASSED the assignment, 
                                            #    it should be viewed ACCORDINGLY.
                                            

                                            $file_name = null;
                                            $passed_date = null;

                                            // echo $subject_period_assignment_id;

                                            // $get_student_ass = $con->prepare("SELECT *
                                            
                                            //     FROM student_period_assignment

                                            //     WHERE subject_period_assignment_id=:subject_period_assignment_id 
                                            //     AND student_id=:student_id

                                            //     LIMIT 1
                                            // ");

                                            $get_student_ass = $con->prepare("SELECT *
                                            
                                                FROM student_assignment_grade

                                                WHERE subject_period_assignment_id=:subject_period_assignment_id 
                                                AND student_id=:student_id

                                                LIMIT 1
                                            ");

                                            $get_student_ass->bindValue(":subject_period_assignment_id", $subject_period_assignment_id);
                                            $get_student_ass->bindValue(":student_id", $student_id);
                                            $get_student_ass->execute();

                                            if($get_student_ass->rowCount() > 0){

                                                // echo "qwe";
                                                $student_row = $get_student_ass->fetch(PDO::FETCH_ASSOC);

                                                // $file_name = $student_row['file_name'];
                                                // $passed_date = $student_row['passed_date'];
                                                $passed_date = $student_row['date_creation'];
                                                // echo $file_name;
                                            }

                                            $status = "";
                                            $url = "";


                                            if($passed_date != null){
                                                $status = "
                                                    <i style='color: green;'class='fas fa-check'></i>
                                                ";

                                                $assignment_url = "assignment_view.php?id=$subject_period_assignment_id&s_id=$student_id";

                                                $url = "
                                                    <a href='$assignment_url'>
                                                        <button class='btn btn-sm btn-primary'>
                                                            <i class='fas fa-eye'></i>
                                                        </button>
                                                    </a>
                                                ";
                                            }else{
                                                $status = "
                                                    <i style='color: orange;'class='fas fa-times'></i>
                                                ";
                                            }


                                            echo "
                                                <tr class='text-center'>
                                                    <td>$firstname $lastname </td>
                                                    <td>$status</td>
                                                    <td>
                                                        $url
                                                    </td>
                                                </tr>
                                            ";
                                        }
                                    }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        <?php
    }
?>