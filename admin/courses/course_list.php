<?php 
    include('../admin_enrollment_header.php');
    include('../../enrollment/classes/StudentEnroll.php');
    include('../../enrollment/classes/Schedule.php');
    include('../classes/Course.php');

    
    if(!AdminUser::IsAuthenticated()){
        header("location: /dcbt/adminLogin.php");
        exit();
    }

    $studentEnroll = new StudentEnroll($con);

    $createUrl = base_url . "/create.php";

    $school_year_obj = $studentEnroll->GetActiveSchoolYearAndSemester();

    $current_school_year_id = $school_year_obj['school_year_id'];
    $current_school_year_term = $school_year_obj['term'];
    $current_school_year_period = $school_year_obj['period'];
?>


<div class="row col-md-12">
            <h2 class="text-center page-header">Strand List</h2>
            <!-- <a href="<?php echo $createUrl?>">
                <button class="btn btn-sm btn-success">Add Schedule</button>
            </a>     -->

        <div class="col-md-10 offset-md-1">
            <div class="table-responsive" style="margin-top:2%;"> 
                <div class="mb-3">
              
                    <a href="<?php echo $createUrl?>">
                        <button class="btn btn-success">Add Strand</button>
                    </a>  
                </div>
                <h5>Grade 11</h5>
                <table  class="table table-bordered table-hover "  style="font-size:13px" cellspacing="0"  > 
                    <thead>
                        <tr class="text-center"> 
                            <th rowspan="2">Strand</th>  
                            <th rowspan="2">Grade 11 Sections</th>  
                            <th rowspan="2">Students</th>  
                            <th rowspan="2">Grade 12 Sections</th>  
                            <th rowspan="2">Students</th>  
                            <th rowspan="2">Action</th>  
                        </tr>	
                    </thead> 	
                    <tbody>

                        <?php 
                            // $sectionScheduleGradeElevenFirst = $schedule->GetSectionScheduleGradeElevenFirst($username, $student_id, 11, "First");
                            $GRADE_ELEVEN = 11;
                            $SHS_Department_ID = 4;
                            $query = $con->prepare("SELECT * FROM program
                                WHERE department_id=:department_id
                                ");

                            $query->bindValue(":department_id", $SHS_Department_ID);
                            $query->execute();

                            if($query->rowCount() > 0){
                                while ($row = $query->fetch(PDO::FETCH_ASSOC)) {

                                    $acronym = $row['program_name'];
                                    $program_id = $row['program_id'];
                                    $grade11Sections = 2;
                                    $grade11Student = 4;
                                    $grade12Sections = 3;
                                    $grade12Student = 7;

                                    $edit_courses = "";

                                    echo "<tr class='text-center'>";
                                        echo "<td>
                                            <a style='color:whitesmoke;' href='course_inner.php?id=$program_id'>
                                                $acronym
                                            </a>
                                        </td>";
                                        echo "<td>$grade11Student</td>";
                                        echo "<td>$grade11Student</td>";
                                        echo "<td>$grade12Sections</td>";
                                        echo "<td>$grade12Student</td>";
                                        echo "<td>
                                            <a href='$edit_courses'>
                                                <button class='btn btn-sm btn-primary'>Edit</button>
                                            </a>
                                            <a href='course_subject.php?id=$program_id'>
                                                <button class='btn btn-sm btn-success'>Subjects</button>
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
</div>
 