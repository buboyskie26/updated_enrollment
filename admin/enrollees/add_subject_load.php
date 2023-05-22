  <?php

    include('../registrar_enrollment_header.php');
    include('../../enrollment/classes/Section.php');
    include('../../enrollment/classes/Enrollment.php');
    include('../../enrollment/classes/StudentEnroll.php');
    include('../classes/Subject.php');
    // include('../classes/AdminUser.php');


    if(!AdminUser::IsRegistrarAuthenticated()){
        header("Location: dcbt/adminLogin.php");
        exit();
    }

    if(isset($_GET['id'])){

        $student_id = $_GET['id'];

        // echo $student_id;

        $studentEnroll = new StudentEnroll($con);

        $enrollment = new Enrollment($con, $studentEnroll);

        $student_username = $studentEnroll->GetStudentUsername($student_id);
        // $student_course_id = $studentEnroll->GetStudentCourseId($student_username);

        $student_obj = $studentEnroll->GetStudentCourseLevelYearIdCourseId($student_username);

        $student_course_id = $student_obj['course_id'];
        $student_course_level = $student_obj['course_level'];

        $school_year_obj = $studentEnroll->GetActiveSchoolYearAndSemester();
        $current_semester = $school_year_obj['period'];
        $student_program_id = $studentEnroll->GetStudentProgramId($student_course_id);
        $current_school_year_id = $school_year_obj['school_year_id'];

        // echo $student_course_id;

        if(isset($_POST['insert_subject_load'])){

            $subject_id = $_POST['subject_id'];

            $insert = $con->prepare("INSERT INTO student_subject
                (student_id, subject_id, course_id, course_level, school_year_id)
                VALUES(:student_id, :subject_id, :course_id, :course_level, :school_year_id)");
            
            $insert->bindValue(":student_id", $student_id);
            $insert->bindValue(":subject_id", $subject_id);
            $insert->bindValue(":course_id", $student_course_id);
            $insert->bindValue(":course_level", $student_course_level);
            $insert->bindValue(":school_year_id", $current_school_year_id);
            if($insert->execute()){

                header("Location: add_subject_load.php?id=$student_id");

            }

        }
        if(isset($_POST['remove_subject_load'])){
            $subject_id = $_POST['subject_id'];

            // echo "remove $subject_id";

            $remove = $con->prepare("DELETE FROM student_subject
                WHERE subject_id=:subject_id");
            $remove->bindValue(":subject_id", $subject_id);
            // $remove->execute();

            if($remove->execute()){

                header("Location: add_subject_load.php?id=$student_id");

            }
        }
        ?>
            <div class="row col-md-12">
                <div class="table-responsive" style="margin-top:5%;"> 
                    <h4 class="text-success mb-3 text-center">Subject List For <?php echo $current_semester;?> Semester</h4>
                    <form action="" method="POST">
                        <table class="table table-striped table-bordered table-hover "  style="font-size:13px" cellspacing="0"  > 
                            <thead>
                                <tr class="text-center"> 
                                    <th rowspan="2">ID</th>
                                    <th rowspan="2">Code</th>
                                    <th rowspan="2">Unit</th>  
                                    <th rowspan="2">Type</th>
                                </tr>	
                            </thead> 
                                <tbody>
                                    <?php
                                            
                                    //    $get = $enrollment->CheckStudentEnrolledSubject($student_username);

                                        $sql = $con->prepare("SELECT 
                                                    
                                            subject_id,
                                            subject_title, subject_code,
                                            unit, subject_type 
                                        
                                            FROM subject
                                            WHERE course_id=:course_id
                                            AND course_level=:course_level
                                            AND semester=:semester
                                            AND program_id=:program_id
                                            ");

                                        $arr = [];

                                        $sql->bindValue(":course_id", $student_course_id);
                                        $sql->bindValue(":course_level", $student_course_level);
                                        $sql->bindValue(":semester", $current_semester);
                                        $sql->bindValue(":program_id", $student_program_id);
                                        $sql->execute();
                                        if($sql->rowCount() > 0){

                                            while($row = $sql->fetch(PDO::FETCH_ASSOC)){

                                                $subject_id = $row['subject_id'];
                                                $subject_title = $row['subject_title'];
                                                $subject_code = $row['subject_code'];
                                                $unit = $row['unit'];
                                                $subject_type = $row['subject_type'];

                                                echo "
                                                    <tr class='text-center'>
                                                        <td>$subject_code</td>
                                                        <td>$subject_title</td>
                                                        <td>$unit</td>
                                                        <td>$subject_type</td>
                                                        <td>
                                                            <form method='POST'>

                                                                <input name='subject_id' type='hidden' value='$subject_id'>
                                                                <button name='insert_subject_load' type='submit' class='btn btn-success btn-sm'>
                                                                    Insert
                                                                </button>

                                                                <button name='remove_subject_load' type='submit' class='btn btn-danger btn-sm'>
                                                                    Remove
                                                                </button>
                                                            </form>

                                                        </td>
                                                    </tr>
                                                ";
                                            }
                                        }
                                    ?>
                                </tbody> 
                        </table>

                    </form>
                </div>


                <div class="table-responsive" style="margin-top:5%;"> 
                    <h4 class="text-primary mb-3 text-center">Student Enrolled Subject</h4>
                    <form action="" method="POST">
                        <table class="table table-striped table-bordered table-hover "  style="font-size:13px" cellspacing="0"  > 
                            <thead>
                                <tr class="text-center"> 
                                    <th rowspan="2">ID</th>
                                    <th rowspan="2">Code</th>
                                    <th rowspan="2">Unit</th>
                                    <th rowspan="2">Type</th>
                                    <th rowspan="2">Remarks</th>
                                </tr>	
                            </thead> 
                                <tbody>
                                    <?php
                                            
                                    //    $get = $enrollment->CheckStudentEnrolledSubject($student_username);

                                        $student_subject = $con->prepare("SELECT 

                                            t2.subject_title, t2.subject_code,
                                            t2.unit, t2.subject_type,

                                            t3.remarks
                                        
                                            FROM student_subject as t1

                                            INNER JOIN subject as t2 ON t1.subject_id = t2.subject_id
                                            LEFT JOIN student_subject_grade as t3 ON t3.student_subject_id = t1.student_subject_id
                                            WHERE t1.student_id=:student_id
                                            AND t2.semester=:semester
                                            AND t2.course_level=:course_level
                                             
                                            ");

                                        $student_subject->bindValue(":student_id", $student_id);
                                        $student_subject->bindValue(":semester", $current_semester);
                                        $student_subject->bindValue(":course_level", $student_course_level);
                                        $student_subject->execute();
                                        if($student_subject->rowCount() > 0){

                                            while($row = $student_subject->fetch(PDO::FETCH_ASSOC)){

                                                $subject_title = $row['subject_title'];
                                                $subject_code = $row['subject_code'];
                                                $unit = $row['unit'];
                                                $subject_type = $row['subject_type'];
                                                $remarks = $row['remarks'] != "" ? $row['remarks'] : "N/A";


                                                echo "
                                                    <tr class='text-center'>
                                                        <td>$subject_code</td>
                                                        <td>$subject_title</td>
                                                        <td>$unit</td>
                                                        <td>$subject_type</td>
                                                        <td>$remarks</td>
                                                    </tr>
                                                ";
                                            }
                                        }
                                    ?>
                                </tbody> 
                        </table>

                    </form>
                </div>
            </div>
        <?php
    }

?>

