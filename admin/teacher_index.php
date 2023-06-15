<?php  
    require_once('../admin/teacherHeader.php');
    include('../enrollment/classes/Teacher.php');
    
    if(!AdminUser::IsTeacherAuthenticated()){
        header("location: /dcbt/teacher_login.php");
        exit();
    }

    $teacher = new Teacher($con, $teacherLoggedIn);
    
    $teacher_id = $teacher->GetTeacherId();
?>

<div class="col-md-12 col">

    <!-- <h4>DASHBOARD</h4> -->
    <!-- <?php
        echo "This is the teacher main section of ". $_SESSION['teacherLoggedIn'];
    ?> -->


    <div class="card">
        <div class="card-hea">
            <h3 class="text-center text-muted">My Class</h3>
        </div>

        <div class="card-body">
            <table class="table table-responsive">
                <thead>
                    <tr class="text-center">
                        <th>Subject Id</th>
                        <th>Teching Section</th>
                        <th>Handled Subject</th>
                    </tr>
                </thead>

                <tbody>
                    <?php 
                    
                        $sql = $con->prepare("SELECT t1.*, t2.* , t3.*
                        
                            FROM subject_schedule as t1

                            INNER JOIN subject as t2 ON t2.subject_id = t1.subject_id
                            LEFT JOIN course as t3 ON t3.course_id = t2.course_id

                            WHERE t1.teacher_id=:teacher_id");

                        $sql->bindValue(":teacher_id", $teacher_id);
                        $sql->execute();

                        if($sql->rowCount() > 0){

                            while($row = $sql->fetch(PDO::FETCH_ASSOC)){


                                $program_section = $row['program_section'];
                                $subject_title = $row['subject_title'];
                                $subject_id = $row['subject_id'];


                                $subject_module_url = "./teacher/teaching_module.php?id=$subject_id";

                                echo "
                                    <tr class='text-center'>
                                    
                                        <td>$subject_id</td>
                                        <td>$program_section</td>
                                        <td>$subject_title</td>
                                        <td>
                                            <a href='$subject_module_url'>
                                                <button class='btn btn-sm btn-primary'>
                                                    View
                                                </button>
                                            </a>
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

</div>

<?php  include('../includes/footer.php');?>