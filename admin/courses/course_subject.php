
<?php 
    include('../admin_enrollment_header.php');
    include('../../enrollment/classes/StudentEnroll.php');
    include('../../enrollment/classes/Schedule.php');
    include('../classes/Course.php');

    
    if(!AdminUser::IsAuthenticated()){
        header("location: /dcbt/adminLogin.php");
        exit();
    }
 
    if(isset($_GET['id'])){

        $program_id = $_GET['id'];

        $sql = $con->prepare("SELECT program_name FROM program
            WHERE program_id=:program_id
            LIMIT 1");

        $sql->bindValue(":program_id", $program_id);
        $sql->execute();
        $sql = $sql->fetch(PDO::FETCH_ASSOC);
        ?>
            <div class="row col-md-12">
            <h4 class="mb-3 text-center"><?php echo $sql['program_name']; ?></h4>

                <h5 class="text-center page-header">Grade 11 First Semester</h5>
                <div class="col-md-10 offset-md-1">
                    <div class="table-responsive" style="margin-top:2%;"> 
                        <table  class="table table-bordered table-hover "  style="font-size:13px" cellspacing="0"  > 
                            <thead>
                                <tr class="text-center"> 
                                    <th rowspan="2">Title</th>  
                                    <th rowspan="2">Unit</th>  
                                    <th rowspan="2">Type</th>  
                                </tr>	
                            </thead> 	
                            <tbody>

                                <?php 
                                    // $sectionScheduleGradeElevenFirst = $schedule->GetSectionScheduleGradeElevenFirst($username, $student_id, 11, "First");
                                    $GRADE_ELEVEN = 11;
                                    $SHS_Department_ID = 4;
                                    $query = $con->prepare("SELECT * FROM subject_program
                                        WHERE program_id=:program_id
                                        AND course_level=:course_level
                                        AND semester=:semester
                                        ");

                                    $query->bindValue(":program_id", $program_id);
                                    $query->bindValue(":course_level", 11);
                                    $query->bindValue(":semester", "First");
                                    $query->execute();

                                    if($query->rowCount() > 0){
                                        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {

                                            $unit = $row['unit'];
                                            $subject_title = $row['subject_title'];
                                            $subject_type = $row['subject_type'];

                                            echo "<tr class='text-center'>";
                                                echo "<td>$subject_title</td>";
                                                echo "<td>$unit</td>";
                                                echo "<td>$subject_type</td>";
                                            echo "</tr>";
                                        }
                                    }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>




                <h5 class="text-center page-header">Grade 11 Second Semester</h5>
                <div class="col-md-10 offset-md-1">
                    <div class="table-responsive" style="margin-top:2%;"> 
                        <table  class="table table-bordered table-hover "  style="font-size:13px" cellspacing="0"  > 
                            <thead>
                                <tr class="text-center"> 
                                    <th rowspan="2">Title</th>  
                                    <th rowspan="2">Unit</th>  
                                    <th rowspan="2">Type</th>  
                                </tr>	
                            </thead> 	
                            <tbody>

                                <?php 
                                    // $sectionScheduleGradeElevenFirst = $schedule->GetSectionScheduleGradeElevenFirst($username, $student_id, 11, "First");
                                    $GRADE_ELEVEN = 11;
                                    $SHS_Department_ID = 4;
                                    $query = $con->prepare("SELECT * FROM subject_program
                                        WHERE program_id=:program_id
                                        AND course_level=:course_level
                                        AND semester=:semester
                                        ");

                                    $query->bindValue(":program_id", $program_id);
                                    $query->bindValue(":course_level", 11);
                                    $query->bindValue(":semester", "Second");
                                    $query->execute();

                                    if($query->rowCount() > 0){
                                        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {

                                            $unit = $row['unit'];
                                            $subject_title = $row['subject_title'];
                                            $subject_type = $row['subject_type'];

                                            echo "<tr class='text-center'>";
                                                echo "<td>$subject_title</td>";
                                                echo "<td>$unit</td>";
                                                echo "<td>$subject_type</td>";
                                            echo "</tr>";
                                        }
                                    }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>


                <h5 class="text-center page-header">Grade 12 First Semester</h5>
                <div class="col-md-10 offset-md-1 mb-2">
                    <div class="table-responsive" style="margin-top:2%;"> 
                        <table  class="table table-bordered table-hover "  style="font-size:13px" cellspacing="0"  > 
                            <thead>
                                <tr class="text-center"> 
                                    <th rowspan="2">Title</th>  
                                    <th rowspan="2">Unit</th>  
                                    <th rowspan="2">Type</th>  
                                </tr>	
                            </thead> 	
                            <tbody>

                                <?php 
                                    // $sectionScheduleGradeElevenFirst = $schedule->GetSectionScheduleGradeElevenFirst($username, $student_id, 11, "First");
                                    $GRADE_ELEVEN = 11;
                                    $SHS_Department_ID = 4;
                                    $query = $con->prepare("SELECT * FROM subject_program
                                        WHERE program_id=:program_id
                                        AND course_level=:course_level
                                        AND semester=:semester
                                        ");

                                    $query->bindValue(":program_id", $program_id);
                                    $query->bindValue(":course_level", 12);
                                    $query->bindValue(":semester", "First");
                                    $query->execute();

                                    if($query->rowCount() > 0){
                                        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {

                                            $unit = $row['unit'];
                                            $subject_title = $row['subject_title'];
                                            $subject_type = $row['subject_type'];

                                            echo "<tr class='text-center'>";
                                                echo "<td>$subject_title</td>";
                                                echo "<td>$unit</td>";
                                                echo "<td>$subject_type</td>";
                                            echo "</tr>";
                                        }
                                    }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <h5 class="text-center page-header">Grade 12 Second Semester</h5>
                <div class="col-md-10 offset-md-1 mb-2">
                    <div class="table-responsive" style="margin-top:2%;"> 
                        <table  class="table table-bordered table-hover "  style="font-size:13px" cellspacing="0"  > 
                            <thead>
                                <tr class="text-center"> 
                                    <th rowspan="2">Title</th>  
                                    <th rowspan="2">Unit</th>  
                                    <th rowspan="2">Type</th>  
                                </tr>	
                            </thead> 	
                            <tbody>

                                <?php 
                                    // $sectionScheduleGradeElevenFirst = $schedule->GetSectionScheduleGradeElevenFirst($username, $student_id, 11, "First");
                                    $GRADE_ELEVEN = 11;
                                    $SHS_Department_ID = 4;
                                    $query = $con->prepare("SELECT * FROM subject_program
                                        WHERE program_id=:program_id
                                        AND course_level=:course_level
                                        AND semester=:semester
                                        ");

                                    $query->bindValue(":program_id", $program_id);
                                    $query->bindValue(":course_level", 12);
                                    $query->bindValue(":semester", "Second");
                                    $query->execute();

                                    if($query->rowCount() > 0){
                                        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {

                                            $unit = $row['unit'];
                                            $subject_title = $row['subject_title'];
                                            $subject_type = $row['subject_type'];

                                            echo "<tr class='text-center'>";
                                                echo "<td>$subject_title</td>";
                                                echo "<td>$unit</td>";
                                                echo "<td>$subject_type</td>";
                                            echo "</tr>";
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

