<?php 
    include('../admin_enrollment_header.php');
    include('../../enrollment/classes/StudentEnroll.php');
    include('../../enrollment/classes/Section.php');
    include('../classes/Course.php');

    
    if(!AdminUser::IsAuthenticated()){
        header("location: /dcbt/adminLogin.php");
        exit();
    }

    $studentEnroll = new StudentEnroll($con);

    $school_year_obj = $studentEnroll->GetActiveSchoolYearAndSemester();

    $current_school_year_id = $school_year_obj['school_year_id'];
    $current_school_year_term = $school_year_obj['term'];
    $current_school_year_period = $school_year_obj['period'];

    if(isset($_GET['id'])){
        $program_id = $_GET['id'];

        // echo "qwe";
    }
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
                <table  class="table table-bordered table-hover "  style="font-size:14px" cellspacing="0"  > 
                    <thead>
                        <tr class="text-center"> 
                            <th rowspan="2">Section Id</th>  
                            <th rowspan="2">Section Name</th>  
                            <th rowspan="2">Student</th>  
                            <th rowspan="2">Schedule Subject</th>  
                            <th rowspan="2">School Year</th>  
                            <th rowspan="2">Action</th>  
                        </tr>	
                    </thead> 	
                    <tbody>

                        <?php 
                            // $sectionScheduleGradeElevenFirst = $schedule->GetSectionScheduleGradeElevenFirst($username, $student_id, 11, "First");
                            $query = $con->prepare("SELECT * FROM course
                                WHERE program_id=:program_id

                                ORDER BY school_year_term ASC,
                                program_section
                                ");
// $currentDirectory = basename(__DIR__);
// echo $currentDirectory;
                            $query->bindValue(":program_id", $program_id);
                            $query->execute();

                            if($query->rowCount() > 0){
                                while ($row = $query->fetch(PDO::FETCH_ASSOC)) {


                                    $course_id = $row['course_id'];
                                    $program_section = $row['program_section'];
                                    $capacity = $row['capacity'];
                                    $school_year_term = $row['school_year_term'];

                                    $section = new Section($con, $course_id);
                                    $totalStudent = $section->GetTotalNumberOfStudentInSection($course_id,
                                        $current_school_year_id);

                                    $scheduleSubject = $section->GetSectionTotalScheduleSubjects($course_id);
                                    $totalSectionSubject = $section->GetSectionTotalSubjects($course_id);
                                    $grade12Sections = 3;
                                    $grade12Student = 7;

                                    $relativePath = '../section/strand_show.php?id=' . $course_id;
                                    $url = directoryPath . $relativePath;

                                    $url = directoryPath . "course_show.php?id=$course_id";

                                    echo "<tr class='text-center'>";
                                        echo "<td>
                                            <a style='color:whitesmoke;' href='strand_show.php?id=$program_id'>
                                                $course_id
                                            </a>
                                        </td>";
                                        echo "<td>
                                            <a style='color:whitesmoke;' href='$url'>
                                                $program_section
                                            </a>
                                        </td>";
                                        echo "<td>$totalStudent/$capacity</td>";
                                        echo "<td>$scheduleSubject/$totalSectionSubject</td>";
                                        echo "<td>$school_year_term</td>";
                                        echo "<td></td>";
                                    echo "</tr>";
                                }
                            }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
</div>
 

