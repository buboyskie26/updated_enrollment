<?php 

    include('../admin_enrollment_header.php');
    include('../../enrollment/classes/StudentEnroll.php');

?>
    <div class="row col-md-12">
        <h4 class="text-center">SHS Graduating Student</h4>
        <div class="table-responsive">			
            <table id="dash-table" class="table table-striped table-bordered table-hover table-responsive" style="font-size:12px" cellspacing="0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>STRAND</th>
                        <th>Course/Section</th>
                
                    </tr>	
                </thead> 
                <tbody>
                    <?php

                        $enrol = new StudentEnroll($con);
                        $school_year_id = $enrol->GetCurrentYearId();

                        // localhost
                        $host = $_SERVER['HTTP_HOST'];
                        // elms/admin/enrollees/index.php
                        $uri =  $_SERVER['REQUEST_URI'];

                        // Get the tentative student in the enrollment.
                        $enrollment_status = "tentative";
                        $tentative_shs_student_array = [];

                        $sql = $con->prepare("SELECT student_id FROM enrollment
                            WHERE enrollment_status = :enrollment_status
                            -- AND student_status=:student_status
                            -- OR student_status=:second_student_status
                            ");   

                        $sql->bindValue(":enrollment_status", $enrollment_status);
                        $sql->execute(); 

                        if($sql->rowCount() > 0){
                            while($row = $sql->fetch(PDO::FETCH_ASSOC)){
                                array_push($tentative_shs_student_array,$row['student_id']);
                            }
                        }
 

                        foreach ($tentative_shs_student_array as $key => $student_id) {
                            # code...
                            $row = $con->prepare("SELECT * FROM student
                                WHERE student_id=:student_id
                            ");

                            $row->bindValue(":student_id", $student_id);
                            $row->execute();

                            $row = $row->fetch(PDO::FETCH_ASSOC);

                            $course_id = $row['course_id'];
                            $course_level = $row['course_level'];
                            // $school_year_id = $row['school_year_id'];
                            $student_id = $row['student_id'];
                            // $student_unique_id = $row['stud_unique_id'];
                            $student_username = $row['username'];

                            $confirmStudent = "enrolledStudent(\"$student_username\", $student_id)";

                            $createUrl = base_url . "/create.php?username=$student_username&id=$student_id";

                            $student_unique_id = $row['student_unique_id'];
                            $fullName = $row['firstname'] . $row['lastname'];
                            $sex = $row['sex'];
                            $age = $row['age'];
                            $address = $row['address'];
                            $contact_number = $row['contact_number'];
                            $student_status = $row['student_status'];

                            $section = "BSCS-101";

                            $sql_course = $con->prepare("SELECT program_section FROM course
                                WHERE course_id=:course_id
                                LIMIT 1");

                            $sql_course->bindValue(":course_id", $course_id);
                            $sql_course->execute();

                            $courseName = $sql_course->rowCount() > 0 ? $sql_course->fetchColumn() : "";

                            $course_level_status = $course_level >= 11 && $course_level <= 12? "Grade $course_level" : " $course_level Year";

                            $latest_student_obj = $enrol->GetSchoolYearOfStudent($school_year_id);
                            $latest_student_semester = $latest_student_obj['period'];

                            echo "
                                <tr>
                                    <td>1000015</td>
                                    <td>JUSTINE SIRIOS</td>
                                    <td>STEM</td>
                                    <td>STEM-101</td>
                                </tr>
                            ";
                        }
                    ?>
                </tbody>
            </table>
        </div>
    </div>









