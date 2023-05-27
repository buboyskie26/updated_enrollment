<?php  
 
    include('../classes/Department.php');
    include('../../enrollment/classes/StudentEnroll.php');
    include('../../enrollment/classes/Schedule.php');
    include('../classes/Course.php');

    include('../registrar_enrollment_header.php');


    if(isset($_GET['id'])){

        $school_year_id = $_GET['id'];

        $enroll = new StudentEnroll($con);

        $school_year_obj = $enroll->GetActiveSchoolYearAndSemester();

        $current_school_year_term = $school_year_obj['term'];

        $current_school_year_period = $school_year_obj['period'];

        $currentDateTime = date('Y-m-d H:i');

        if(isset($_POST['create_sy_maintenance_btn'])){

            $start_enrollment_date = $_POST['start_enrollment_date'];
            $end_enrollment_date = $_POST['end_enrollment_date'];

            $start_period = $_POST['start_period'];
            $end_period = $_POST['end_period'];

            
            $sql = $con->prepare("UPDATE school_year
                                SET start_enrollment_date = :start_enrollment_date,
                                    end_enrollment_date = :end_enrollment_date,
                                    start_period = :start_period,
                                    end_period = :end_period
                                
                                WHERE school_year_id=:school_year_id");

            $sql->bindValue(":start_enrollment_date", $start_enrollment_date);
            $sql->bindValue(":end_enrollment_date", $end_enrollment_date);
            $sql->bindValue(":start_period", $start_period);
            $sql->bindValue(":end_period", $end_period);
            $sql->bindValue(":school_year_id", $school_year_id);

            if ($sql->execute()) {
                AdminUser::success("Maintenance Updated", "index.php");
                exit();
            }


            // $sql = $con->prepare("INSERT INTO school_year
            //     (start_enrollment_date, end_enrollment_date, start_period, end_period)
            //     VALUES(:start_enrollment_date, :end_enrollment_date, :start_period, :end_period)");

            // $sql->bindValue(":start_enrollment_date", $start_enrollment_date);
            // $sql->bindValue(":end_enrollment_date", $end_enrollment_date);
            // $sql->bindValue(":start_period", $start_period);
            // $sql->bindValue(":end_period", $end_period);
            // if($sql->execute()){

            //     AdminUser::success("Maintenance Added","index.php");
            //     exit();
            // }
        }

        ?>
            <div class='col-md-10 row offset-md-1'>

                <div class="card">
                    <div class="card-header">
                        <h4 class='text-center mb-3'>Maintenance (S.Y <?php echo $current_school_year_term; ?> <?php echo $current_school_year_period; ?> Semester)</h4>
                    </div>
                    <div class="card-body">
                        <form method='POST'>
                            
                            <div class='form-group mb-2'>
                                <label class="mb-3" for="">Start Enrollment</label>
                                 <input class='form-control'
                                    value='<?php echo $currentDateTime; ?>'
                                 
                                    type='datetime-local' placeholder='Enrollment Starts'
                                    name='start_enrollment_date'>
                            </div>

                            <div class='form-group mb-2'>
                                <label class="mb-3" for="">End Enrollment</label>
                                <input class='form-control' 
                                    value='<?php echo $currentDateTime; ?>'
                                    type='datetime-local' 
                                    placeholder='Enrollment Ends'
                                    name='end_enrollment_date'>
                            </div>

 
                            <div class='form-group mb-2'>
                                <label class="mb-3" for="">Start Period</label>
                                <input class='form-control' 
                                    value='<?php echo $currentDateTime; ?>'
                                    type='datetime-local'  placeholder='Semester Starts'
                                    name='start_period'>
                            </div>

                            <div class='form-group mb-2'>
                                <label class="mb-3" for="">End Period</label>
                                <input class='form-control' 
                                    value='<?php echo $currentDateTime; ?>'
                                    type='datetime-local'  placeholder='Semester Ends'
                                    name='end_period'>
                            </div>
                        

                            <button type='submit' class='btn btn-primary' name='create_sy_maintenance_btn'>Save</button>
                        </form>
                    </div>
                </div>
            </div>
        <?php
    }
?>