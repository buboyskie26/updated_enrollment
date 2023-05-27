<?php 
    include('../registrar_enrollment_header.php');
    include('../../enrollment/classes/StudentEnroll.php');
    include('../../enrollment/classes/Schedule.php');
    include('../classes/Course.php');

    $studentEnroll = new StudentEnroll($con);
    $schedule = new Schedule($con, $studentEnroll);
  
    $course = new Course($con, $studentEnroll);


    if(isset($_GET['course_id']) && isset($_GET['sy_id'])){
        $course_id = $_GET['course_id'];
        $school_year_id = $_GET['sy_id'];

        // echo $course_id;
        // echo "<br>";
        // echo $school_year_id;

        ?>
            <div class="row col-md-10">
                <div class="offset-md-1">
                <table  class="table table-striped table-bordered table-hover "  style="font-size:13px" cellspacing="0"  > 
                    <thead>
                        <tr class="text-center"> 
                            <th rowspan="2">Id</th>
                            <th rowspan="2">Name</th>
                            <th rowspan="2">Status</th>
                            <th rowspan="2"></th>
                        </tr>	
                    </thead> 	 
                    <tbody>
                        <?php 

                            $enrollment_status = "enrolled";
                            $sql = $con->prepare("SELECT 
                            
                                    t3.program_id, t2.student_id,
                                    t2.student_status,t2.firstname, t2.lastname 
                                    
                                    FROM enrollment as t1
                            
                                    LEFT JOIN student as t2 ON t2.student_id=t1.student_id
                                    LEFT JOIN course as t3 ON t3.course_id=t1.course_id


                                    WHERE t1.course_id=:course_id
                                    AND t1.school_year_id=:school_year_id
                                    AND t1.enrollment_status=:enrollment_status
                            ");

                            $sql->bindValue(":course_id", $course_id);
                            $sql->bindValue(":school_year_id", $school_year_id);
                            $sql->bindValue(":enrollment_status", $enrollment_status);
                            $sql->execute();
                            if($sql->rowCount() > 0){

                              
                                while($row = $sql->fetch(PDO::FETCH_ASSOC)){

                                    $fullName = $row['firstname']." ". $row['lastname']; 
                                    $student_id = $row['student_id'];
                                    $status = $row['student_status'];
                                    $program_id = $row['program_id'];

                                    $url = directoryPath . "section_shifting.php?id=$student_id&pid=$program_id";
                                    echo '<tr class="text-center">'; 
                                            echo '<td>'.$student_id.'</td>';
                                            echo '<td>'.$fullName.'</td>';
                                            echo '<td>'.$status.'</td>';
                                            echo '<td>
                                                <a href="'.$url.'">
                                                    <button  class="btn btn-sm btn-primary">Move</button>
                                                </a>
                                            </td>';
                                    echo '</tr>';
                                }
                            }
                        ?>
                    </tbody>
                </table>
                </div>

            </div>
        <?php

    }

?>