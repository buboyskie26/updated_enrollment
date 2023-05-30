<?php 
    include('../registrar_enrollment_header.php');
    include('../../enrollment/classes/StudentEnroll.php');
    include('../../enrollment/classes/Schedule.php');
    include('../classes/Course.php');


    if(!AdminUser::IsRegistrarAuthenticated()){
        header("location: /dcbt/registrarLogin.php");
        exit();
    }

    $studentEnroll = new StudentEnroll($con);

    $createUrl = base_url . "/create.php";

    $school_year_obj = $studentEnroll->GetActiveSchoolYearAndSemester();

    $current_school_year_id = $school_year_obj['school_year_id'];
    $current_school_year_term = $school_year_obj['term'];
    $current_school_year_period = $school_year_obj['period'];
?>

<body>
    <div class="row col-md-12">
        <h2 class="text-center page-header">Tertiary Course List</h2>
        <div class="col-md-10 offset-md-1">
            <div class="table-responsive" style="margin-top:2%;"> 

                <form action="generate_pdf.php" method="POST">

                    <button class="btn btn-sm btn-primary" name="generate_pdf">Click me to Generate Pdf</button>
                    <table  class="table table-bordered table-hover "  style="font-size:13px" cellspacing="0"> 
                        <thead>
                            <tr class="text-center"> 
                                <th rowspan="2">Strand</th>  
                                <th rowspan="2">1st Year Section</th>  
                                <th rowspan="2">Students</th>  
                                <th rowspan="2">2nd Year Section</th>  
                                <th rowspan="2">Students</th>  
                            </tr>	
                        </thead> 	
                        <tbody>

                            <?php 
                                // $sectionScheduleGradeElevenFirst = $schedule->GetSectionScheduleGradeElevenFirst($username, $student_id, 11, "First");
                                $GRADE_ELEVEN = 11;
                                $SHS_Department_ID = 4;
                                $query = $con->prepare("SELECT * FROM program
                                    WHERE department_id !=:department_id
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

                                        echo "<tr class='text-center'>";
                                            echo "<td>
                                                <a style='color:whitesmoke;' href='registrar_course_inner_tertiary.php?id=$program_id'>
                                                    $acronym
                                                </a>
                                            </td>";
                                            echo "<td>$grade11Student</td>";
                                            echo "<td>$grade11Student</td>";
                                            echo "<td>$grade12Sections</td>";
                                            echo "<td>$grade12Student</td>";
                                        echo "</tr>";
                                    }
                                }
                            ?>
                        </tbody>
                    </table>
                </form>
               
            </div>
        </div>
    </div>
</body>
</html>
<script>
    // $(document).ready(function(){
    //     var table = $('#empTable').DataTable({
    //         'processing': true,
    //         'serverSide': true,
    //         'serverMethod': 'POST',
    //         'ajax': {
    //             'url':'courseListDataTable.php'
    //         },
    //         'columns': [
    //             { data: 'program_name' },
    //             { data: 'department_id' },
    //             { data: 'dean' },
    //             { data: 'actions' }
    //         ]
    //     });
    // });
</script>