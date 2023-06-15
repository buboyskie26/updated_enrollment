<?php 

    require_once('../enrollment/classes/StudentEnroll.php');
    // require_once('../includes/config.php');
    require_once('../includes/studentHeader.php');

    if(!AdminUser::IsStudentAuthenticated()){
        header("location: /dcbt/enrollment/index.php");
        exit();
    }


    
    $studentEnroll = new StudentEnroll($con);

    $school_year_obj = $studentEnroll->GetActiveSchoolYearAndSemester();

    $current_school_year_id = $school_year_obj['school_year_id'];
    $current_school_year_term = $school_year_obj['term'];
    $current_school_year_period = $school_year_obj['period'];


    $student_username = $studentLoggedIn;

?>
<div class="row col-md-12">
    <div class="card">
        <div class="card-hea">
            <h3 class="text-center text-muted">My Subjects</h3>
        </div>

        <div class="card-body">
            <table class="table table-responsive">
                <thead>
                    <tr class="text-center">
                        <th>Subject Id</th>
                        <th>Subject Name</th>
                        <th></th>
                    </tr>
                </thead>

                <tbody>
                    <?php 
                    
                        $sql = $con->prepare("SELECT t1.*, t2.*, t3.*
                        
                            FROM student_subject as t1
                            INNER JOIN student as t2 ON t2.student_id = t1.student_id
                            INNER JOIN subject as t3 ON t3.subject_id = t1.subject_id

                            WHERE t2.username=:username");

                        $sql->bindValue(":username", $student_username);
                        $sql->execute();

                        if($sql->rowCount() > 0){

                            while($row = $sql->fetch(PDO::FETCH_ASSOC)){


                                $subject_title = $row['subject_title'];
                                $subject_id = $row['subject_id'];


                                $subject_module_url = "./student_class.php?id=$subject_id";

                                echo "
                                    <tr class='text-center'>
                                    
                                        <td>$subject_id</td>
                                        <td>$subject_title</td>
                                      
                                        <td>
                                            <a href='$subject_module_url'>
                                                <button class='btn btn-sm btn-primary'>
                                                    <i class='fas fa-eye'></i>
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



