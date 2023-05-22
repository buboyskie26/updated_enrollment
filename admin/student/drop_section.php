<?php 

    include('../registrar_enrollment_header.php');
    include('../../enrollment/classes/StudentEnroll.php');
    include('../../enrollment/classes/OldEnrollees.php');


    $createUrl = base_url . "/create.php";

    $enrol = new StudentEnroll($con);
    $old_enroll = new OldEnrollees($con, $enrol);

    $school_year_obj = $enrol->GetActiveSchoolYearAndSemester();

    $school_year_id= $school_year_obj['school_year_id'];

    $enrollment_status = "tentative";

    if(isset($_POST['confirmed_tentative_btn']) && isset($_POST['student_id'])){

        $student_id = $_POST['student_id'];
        $registrar_evaluated= "yes";
        // $
        $push_enrollment = $con->prepare("INSERT INTO enrollment
            (student_id, school_year_id, enrollment_status, registrar_evaluated)
            VALUES(:student_id, :school_year_id, :enrollment_status, :registrar_evaluated)");
        
        $push_enrollment->bindValue(":student_id", $student_id);
        $push_enrollment->bindValue(":school_year_id", $school_year_id);
        $push_enrollment->bindValue(":enrollment_status", $enrollment_status);
        $push_enrollment->bindValue(":registrar_evaluated", $registrar_evaluated);
        if($push_enrollment->execute()){

            echo "
                <script>
                    alert('Student push into enrollment');
                </script>
            ";
        }
    }
?>
<div class="row col-md-12">
    <div class="col-lg-12">
        <div class="row">
            <div class="col-lg-6">
                <h1 class="page-header">Drop Student</h1>
            </div>
        </div>
    </div>

    <div class="table-responsive">			
        <table id="dash-table" class="table table-striped table-bordered table-hover table-responsive" style="font-size:12px" cellspacing="0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>
                        <!-- <input type="checkbox" name="chkall" id="chkall" onclick="return checkall('selector[]');">  -->
                        Name</th>
                    <th>Previous Section</th>
                    <th>Registrar Confirmation</th>
                    <th>Cashier Confirmation</th>
                    <th width="14%" >Action</th>
                </tr>	
            </thead> 
            <tbody>
                <?php
                    $student_status = "Drop";

                    $sql = $con->prepare("SELECT * FROM student
                        WHERE student_status = :student_status
                        -- AND student_status=:student_status
                        -- OR student_status=:second_student_status
                        ");   

                    $sql->bindValue(":student_status", $student_status);
                    $sql->execute(); 

                    if($sql->rowCount() > 0){
                        while($row = $sql->fetch(PDO::FETCH_ASSOC)){

                            $student_id = $row['student_id'];
                            $student_name = $row['firstname'] . " " . $row['lastname'];
                            $course_id = $row['course_id'];
                            $program_section = "";
                            $section_term = "";
                            
                            $get_course = $con->prepare("SELECT program_section, school_year_term FROM course
                                WHERE course_id = :course_id
                                LIMIT 1
                                ");   

                            $get_course->bindValue(":course_id", $course_id);
                            $get_course->execute(); 

                            if($get_course->rowCount() > 0 ){
                                $row = $get_course->fetch(PDO::FETCH_ASSOC);

                                $program_section = $row['program_section'];
                                $section_term = $row['school_year_term'];
                            }

                            $registrar_confirmation = "";
                            $cashier_confirmation = "";

                            $url = directoryPath . "drop_show.php?id=$student_id";
                            $button = "
                                <a href='$url'>
                                    <button class='btn btn-sm btn-success'>Evalute</button>
                                </a>
                            ";

                            
                            $checkIfInTheEnrollmentDb = $con->prepare("SELECT enrollment_id FROM enrollment
                                WHERE student_id = :student_id
                                AND school_year_id = :school_year_id
                                LIMIT 1
                                ");   

                            $checkIfInTheEnrollmentDb->bindValue(":student_id", $student_id);
                            $checkIfInTheEnrollmentDb->bindValue(":school_year_id", $school_year_id);
                            $checkIfInTheEnrollmentDb->execute(); 
                            $confirmAsTentativeButton = "";

                            // If that dropped student is not enrolled in the
                            // current school year_id
                            // if($checkIfInTheEnrollmentDb->rowCount() == 0){

                            if($old_enroll->CheckIfDroppedStudentEnrolledInCurrentSY($student_id, $school_year_id) == 0){

                                $confirmAsTentativeButton = "
                                <form method='POST'>
                                        <input type='hidden' name='student_id' value='$student_id'>
                                        <button name='confirmed_tentative_btn' class='btn btn-primary btn-sm'>Confirm</button>
                                    </form>
                                ";
                            }
                        
                            echo "
                                <tr>
                                    <td>$student_id</td>
                                    <td>$student_name</td>
                                    <td>$program_section ($section_term)</td>
                                    <td>X</td>
                                    <td>X</td>
                                    <td>
                                        $button
                                        $confirmAsTentativeButton
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
