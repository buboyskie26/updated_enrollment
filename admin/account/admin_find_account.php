<?php  
 
    include('../../enrollment/classes/StudentEnroll.php');
    include('../../enrollment/classes/Email.php');
    include('../../includes/classes/Student.php');
    include('../../enrollment/classes/Schedule.php');

    include('../admin_enrollment_header.php');
    // include('../classes/AdminUser.php');

    require "../../vendor/autoload.php";

    use PHPMailer\PHPMailer\PHPMailer;

    // echo "registrar account index";
    if(!AdminUser::IsAuthenticated()){
        header("Location: /dcbt/adminLogin.php");
        exit();
    }

    if(isset($_POST['reset_student_password']) && isset($_POST['student_username'])){

        $student_username = $_POST['student_username'];

        // $enroll = new StudentEnroll($con);

        // $student_username = $enroll->GetStudentUsername($student_id);

        $student = new Student($con, $student_username);

        $email = new Email();
        $student_email = $student->GetEmail();

        $temporaryPassword = $student->ResetPassword($student_username);

        // echo $student_email;

        if(count($temporaryPassword) > 0 && $temporaryPassword[1] == true){

            $isEmailSent = $email->SendTemporaryPassword($student_email,
                $temporaryPassword[0]);

            if($isEmailSent == true){
                echo "Email reset password has been sent to: $student_email";
            }else{
                echo "Sending reset password via email went wrong";
            }

        }else{;
            echo "Password did not reset";
        }
    }

?>

<div class="col-md-10 offset-md-1">
    <div class="table-responsive" style="margin-top:2%;"> 

        <h5 class="text-center">Find Account</h5>

        <table  class="table table-striped table-bordered table-hover "  style="font-size:13px" cellspacing="0"  > 
            <thead>
                <tr class="text-center"> 
                    <th rowspan="2" >Student Id</th>  
                    <th rowspan="2" >Name</th>
                    <th rowspan="2"">Grade Level</th>  
                    <th rowspan="2"">Section</th>  
                    <th rowspan="2"></th>  
                </tr>	
            </thead> 	
            <tbody>
                <?php 
                    $active = 1;
                    // $sectionScheduleGradeElevenFirst = $schedule->GetSectionScheduleGradeElevenFirst($username, $student_id, 11, "First");

                    $query = $con->prepare("SELECT t1.*,
                        t2.program_section 
                        
                        FROM student as t1

                        LEFT JOIN course as t2 ON t1.course_id = t2.course_id
                        WHERE t1.active=:active");

                    $query->bindValue(":active", $active);
                    $query->execute();

                    if($query->rowCount() > 0){

                        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {

                            $name = $row['firstname'] . " " . $row['lastname'];
                            $program_section = $row['program_section'];
                            $student_id = $row['student_id'];
                            $student_username = $row['username'];
                            $course_level = $row['course_level'];

                            echo "<tr class='text-center'>";
                                echo "
                                    <td>$student_id</td>
                                    <td>$name</td>
                                    <td>$course_level</td>
                                    <td>$program_section</td>
                                    <td>
                                        <form method='POST'>
                                            <input name='student_username' type='hidden' value='$student_username'>
                                            <button type='submit' name='reset_student_password' class='btn btn-sm btn-primary'>Reset Password</button>
                                        </form>
                                    </td>
                                ";
                            echo "</tr>";
                        }
                    }
                ?>
            </tbody>
        </table>

        <hr>
        <hr>
    
        <table id="accountTable"
                class="table table-bordered table-hover " 
                style="font-size:15px" cellspacing="0"> 
                <thead>
                    <tr class="text-center">
                        <th>Student Id</th>
                        <th>Name</th>
                        <th>Grade Level</th>
                        <th>Section</th>
                        <th></th>
                    </tr>
                </thead>
        </table>

    </div>
</div>


<script>

    $(document).ready(function(){

        var table = $('#accountTable').DataTable({
            'processing': true,
            'serverSide': true,
            'serverMethod': 'POST',
            'ajax': {
                'url':'accountDataTable.php'
            },
            'columns': [
                { data: 'student_id' },
                { data: 'student_name' },
                { data: 'course_level' },
                { data: 'actions' }
                // { data: 'section' }
            ]
        });
    });

</script>
