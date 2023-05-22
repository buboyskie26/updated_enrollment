<?php 

    require_once('../includes/config.php');
    // require_once('./classes/HomePageEnroll.php');

?>
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DCBT Enrollment Home Page</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css">
    
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="../assets/js/common.js"></script>

</head>

<body>
    <div class="row">
        <div class="col-lg-10 offset-md-1">
            <ul class="nav nav-tabs" role="tablist">
                <!-- <li class="nav-item">
                    <a class="nav-link" id="regular-tab" 
                        data-bs-toggle="tab" 
                        href="#regular" role="tab"
                        aria-controls="regular" aria-selected="true">
                        Regular
                    </a>
                </li> -->

                
                <li class="nav-item">
                    <a class="nav-link active" id="pending-regular-new-tab" 
                        data-bs-toggle="tab" 
                        href="#pending-regular-new" role="tab"
                        aria-controls="pending-regular-new" aria-selected="true">
                        Regular (New)
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" id="pending-regular-new-tab"
                        href="./application/process.php?enrollment_type=1">
                         
                        <button class="btn btn-sm btn-info">
                            Enroll Here
                        </button>
                    </a>
                </li>

                <!-- <li class="nav-item">
                    <a class="nav-link" id="pending-regular-tab" 
                        data-bs-toggle="tab" 
                        href="#pending-regular" role="tab"
                        aria-controls="pending-regular" aria-selected="true">
                        Regular (Pending)
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" id="transferee-pending-tab" 
                        data-bs-toggle="tab" 
                        href="#transferee-pending" role="tab"
                        aria-controls="transferee-pending" aria-selected="true">
                        Transferee (Pending)
                    </a>
                </li> -->

                <!-- <li class="nav-item">
                    <a class="nav-link" id="Old-tab" 
                        data-bs-toggle="tab" 
                        href="#Old" role="tab"
                        aria-controls="Old" aria-selected="true">
                        Old
                    </a>
                </li> -->

                <!-- <?php
                    # TODO: Logic for hiding the Apply Now.
                    echo '
                        <li class="nav-item">
                            <a class="nav-link" id="transferee-tab" 
                                data-bs-toggle="tab" 
                                href="#transferee" role="tab"
                                aria-controls="transferee" aria-selected="true">
                                Transferee
                            </a>
                        </li>
                    ';
                ?> -->
            </ul>

            <div class='tab-content channelContent' id='myTabContent'>

                <div class="tab-pane fade" id="regular"
                    role="tabpanel" aria-labelledby="listOfSubjects-tab">
                   
                    <?php include "regular_form.php"; ?> 
                </div>

                <div class="tab-pane fade show active" id="pending-regular-new"
                    role="tabpanel" aria-labelledby="pending-regular-tab">
                    <?php include "pending_new.php"; ?> 
                </div>

                <div class="tab-pane fade" id="pending-regular"
                    role="tabpanel" aria-labelledby="pending-regular-tab">
                   
                    <?php include "regular_pending_form.php"; ?> 
                </div>


                <div class="tab-pane fade " id="transferee-pending"
                    role="tabpanel" aria-labelledby="transferee-pending-tab">
                   
                    <?php include "transferee_pending_form.php"; ?> 
                 
                </div>

                <div class="tab-pane fade" id="Old" role="tabpanel"
                    aria-labelledby="Old-tab">
                       Old Page
                </div>

                <div class="tab-pane fade" id="transferee" role="tabpanel"
                    aria-labelledby="transferee-tab">
                    <?php include "transferee.php"; ?> 

                </div>
            </div>
            
            <div>
                <?php include "login_enrollment.php"; ?> 
            </div>
        </div>
    </div>
</body>