<?php 

    include('../admin_enrollment_header.php');
    $createUrl = directoryPath . "create.php";
    $templateUrl = directoryPath . "template.php";

    // echo "im in subject enroll";

    if(!AdminUser::IsAuthenticated()){
        header("Location: /dcbt/adminLogin.php");
        exit();
    }
?>


<!-- <div class="row col-md-12">
        <h2 class="text-center page-header">Subject List</h2>
        <div class="row justify-content-end">
            <a class="mb-2" href="<?php echo $createUrl?>">
                <button class="btn btn-sm btn-success">Add Subject Program</button>
            </a>    
        </div>
        <div class="col-md-10 offset-md-1">
            <table  class="table table-striped table-bordered table-hover "  style="font-size:13px" cellspacing="0"  > 
                <thead>
                    <tr class="text-center"> 
                        <th rowspan="2">Program Name</th>
                        <th rowspan="2">Title</th>
                        <th rowspan="2">Grade Level</th>
                        <th rowspan="2">Semester</th>  
                        <th rowspan="2">Unit</th>
                        <th rowspan="2">Type</th>
                        <th rowspan="2"></th>
                    </tr>	
                </thead> 	
                <tbody>
                    <?php 
                        $username = "";
                        // $sectionScheduleGradeElevenFirst = $schedule->GetSectionScheduleGradeElevenFirst($username, $student_id, 11, "First");

                        $query = $con->query("SELECT * FROM subject_program

                            LEFT JOIN program ON program.program_id = subject_program.program_id
                            ORDER BY program.program_name,
                            subject_program.course_level,
                            subject_program.semester
                            ");

                        // $query->bindValue("");
                        $query->execute();

                        if($query->rowCount() > 0){
                        
                            while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
                                $program_name = $row['program_name'];
                                $subject_title = $row['subject_title'];
                                $course_level = $row['course_level'];
                                $semester = $row['semester'];
                                $unit = $row['unit'];
                                $subject_type = $row['subject_type'];
                                $subject_program_id = $row['subject_program_id'];

                                $editUrl = directoryPath . "edit.php?id=$subject_program_id";


                                echo "<tr class='text-center'>";
                                echo "<td>" . $program_name . "</td>";
                                echo "<td>" . $subject_title . "</td>";
                                echo "<td>" . $course_level . "</td>";
                                echo "<td>" . $semester . "</td>";
                                echo "<td>" . $unit . "</td>";
                                echo "<td>" . $subject_type . "</td>";
                                echo "<td>
                                    <a href='$editUrl'>
                                    
                                        <button class='btn btn-sm btn-primary'>Edit</button>
                                    </a>
                                </td>";
                                echo "</tr>";

                            }
                        }
                    ?>
                </tbody>
            </table>
        </div>
</div>
  -->

<div class="row col-md-12">

    <h4 class="text-center">Menu</h4>
    <div class="col-md-4">
        <a href="list.php">
            <button class="button btn btn-success">View Subjects</button>
        </a>
    </div>
    <div class="col-md-4">
        <a href="strand.php">
            <button class="button btn btn-primary">View Strand subjects</button>
        </a>

    </div>
</div>

