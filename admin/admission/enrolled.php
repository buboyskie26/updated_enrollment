<?php 

    include('../registrar_enrollment_header.php');
    include('../../enrollment/classes/StudentEnroll.php');
    include('../../enrollment/classes/OldEnrollees.php');

    $createUrl = base_url . "/create.php";
    $manualCreateUrl = base_url . "/manual_create.php";

    $enroll = new StudentEnroll($con);
    $old_enrollee = new OldEnrollees($con, $enroll);
    
    // echo "im in subject enroll";

    if(!AdminUser::IsRegistrarAuthenticated()){

        header("Location: /dcbt/adminLogin.php");
        exit();
    }

?>

    <div class="row col-md-12">

        <div class="container mb-4">
            <h2 class="text-center text-success">SHS Form Detailst</h2>
            <a href="<?php echo $createUrl?>">
                <button class="btn btn-sm btn-outline-success">Add Student</button>
            </a>
            <a href="<?php echo $manualCreateUrl?>">
                <button class="btn btn-sm btn-success">Add Student</button>
            </a>  
        </div>
        <table  class="table table-striped table-bordered table-hover "  style="font-size:13px" cellspacing="0"  > 
            <thead>
                <tr class="text-center"> 
                    <th rowspan="2">Id</th>
                    <th rowspan="2">Name</th>
                    <th rowspan="2">Status</th>
                    <th rowspan="2">Grade Level</th>
                    <th rowspan="2">Section</th>
                    <th rowspan="2">Action</th>
                </tr>	
            </thead> 	 
            <tbody>
                <?php 
                    $active = 1;

                    $sql = $con->prepare("SELECT 
                        t1.*, t2.program_section, t2.course_id

                    FROM student as t1

                    LEFT JOIN course as t2 ON t2.course_id = t1.course_id

                    WHERE t1.active =:active
                    AND t1.is_tertiary !=:is_tertiary
                    ORDER BY t1.course_level DESC
                    ");

                    $sql->bindValue(":active", $active);
                    $sql->bindValue(":is_tertiary", 1);
                    $sql->execute();

                    if($sql->rowCount() > 0){

                    
                        while($row = $sql->fetch(PDO::FETCH_ASSOC)){

                            $fullName = $row['firstname']." ". $row['lastname']; 
                            $student_id = $row['student_id'];
                            $course_level = $row['course_level'];
                            $course_id = $row['course_id'];
                            $status = $row['student_status'];
                            $program_section = $row['program_section'];

                            $gradeUrl = "http://localhost/dcbt/admin/enrollees/student_grade_report.php?id=$student_id";

                            $view_url = directoryPath . "../student/view_details.php?profile=show&id=$student_id";

                            $trans_url = directoryPath . "../student/shs_view_transferee_details.php?profile=show&id=$student_id";

                            $section_url = "http://localhost/dcbt/admin/section/strand_show.php?id=$course_id";


                            $view_btn = "
                                <a href='$view_url'>
                                    <button class='btn btn-secondary btn-sm'>
                                        View Details
                                    </button>
                                </a>
                            ";

                            if($status == "Transferee"){

                                $view_btn = "
                                    <a href='$trans_url'>
                                        <button class='btn btn-outline-secondary btn-sm'>
                                            View Details
                                        </button>
                                    </a>
                                ";
                            }

                            echo '<tr class="text-center">'; 
                                    echo '<td>'.$student_id.'</td>';
                                    echo '<td>
                                        <a style= "color: whitesmoke;" href="edit.php?id='.$student_id.'">
                                            '.$fullName.'
                                        </a>
                                    </td>';
                                    echo '<td>'.$status.'</td>';
                                    echo '<td>'.$course_level.'</td>';
                                    echo '<td>
                                        <a href="'.$section_url.'">
                                            '.$program_section.'</td>
                                        </a>
                                    ';
                                    echo '
                                        <td> 
                                            <a href="'.$gradeUrl.'">
                                                <button class="btn btn-primary btn-sm">Check Grade</button>
                                            </a>
                                            '.$view_btn.'
                                        </td>
                                    ';
                            echo '</tr>';
                        }
                    }
                ?>
            </tbody>
        </table>
    </div>





