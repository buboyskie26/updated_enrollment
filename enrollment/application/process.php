
<?php 

    require_once('../classes/StudentEnroll.php');
    require_once('../classes/Section.php');
    require_once('../classes/Schedule.php');
    require_once('../classes/Pending.php');
    require_once('../../includes/config.php');

    $section = new Section($con, null);
    $pending = new Pending($con);

?>

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enrollment Application</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css">
    
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

    <link rel="stylesheet" href="../../assets/css/style.css">
    <script src="../../assets/js/common.js"></script>
</head>

<body>

    <?php 
        if(isset($_GET['enrollment_type'])  && $_GET['enrollment_type'] == 1){

            ?>
                <div class="row col-md-12">
                    <div class="card">
                        <h4>Online application</h4>

                        <div class="card-body">
                            <a href="process.php?new_student=true&step=1">

                                <button class="btn btn-success">New Student</button>

                            </a>

                            <a href="process.php?old_student=true&step=1">
                                <button class="btn btn-primary">Old Student</button>

                            </a>
                        </div>
                    </div>
                </div>
            <?php

        }
    ?>

    <?php 
    
        if(isset($_GET['new_student']) && $_GET['new_student'] == "true"
            && isset($_GET['step']) && $_GET['step'] == 1){

                if(isset($_POST['new_step1_btn'])){

                    $admission_type = $_POST['admission_type'];
                    $student_type = $_POST['student_type'];
                    $program_id = $_POST['STRAND'];

                    $_SESSION['admission_type'] = $_POST['admission_type'];
                    $_SESSION['student_type'] = $_POST['student_type'];
                    $_SESSION['program_id'] = $_POST['STRAND'];

                    // $wasSuccess = $pending->PendingNewStep1($admission_type, $student_type, $program_id);
                    // if($wasSuccess){
                    //     echo "was";
                    // }
                    
                    // header("Location:");
                    // exit();
                }
            ?>
                <div class="row col-md-12">

                    <div class="card">

                        <div class="card-body">
                            <div class="card-header">
                                <h4 class="text-center">STEP 1 ~ Prefered Course</h4>

                                <div class="container mb-4">
                                    <form method="POST">

                                        <div class="row">
                                            <span>Admission Type</span>
                                            <div class="col-md-6">
                                                <label for="">New Student</label>
                                                <input type="radio" name="admission_type"
                                                value="new_student"  >
                                            </div>
                                            <div class="col-md-6">
                                                <label for="">Transferee</label>
                                                <input type="radio" name="admission_type"
                                                value="transferee"  >
                                            </div>
                                        </div>

                                        <div class="row mt-4">
                                            <span>Student Type</span>
                                            <div class="col-md-6">
                                                <label for="">College</label>
                                                <input type="radio" name="student_type"
                                                value="college"  >
                                            </div>
                                            <div class="col-md-6">
                                                <label for="">Senior High</label>
                                                <input type="radio" name="student_type"
                                                value="shs"  >
                                            </div>
                                        </div>

                                        <div class="row mt-4">
                                            <span>Strand</span>
                                            <?php echo $pending->CreateRegisterStrand();?>
                                        </div>

                                        <button type="submit" name="new_step1_btn" class="btn btn-primary">Proceed</button>

                                    </form>


                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php

        }



    ?>


</body>
