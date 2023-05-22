<?php 

    require_once('./classes/StudentEnroll.php');
    require_once('./classes/Schedule.php');
    // require_once('../includes/config.php');
    require_once('../includes/studentHeader.php');

    if(isset($_SESSION['username'])){
        $username = $_SESSION['username'];

        $isValid = false;

        $sql = $con->prepare("SELECT * FROM pending_enrollees
            WHERE firstname=:firstname
            AND activated=:activated");

        $sql->bindValue(":firstname", $username);
        $sql->bindValue(":activated", 1);
        $sql->execute();

        if($sql->rowCount() > 0){
            # What if student becomes enrolled 
            # and pending_enrollees table was deleted
            ?>
                <!-- <div style="  margin-left: -50px;    margin-top: 100px;" class="table-responsive" style="margin-top:15%;">  -->
                <div "> 
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="basic_info-tab" 
                                data-bs-toggle="tab" 
                                href="#basic_info" role="tab"
                                aria-controls="basic_info" aria-selected="true">
                                Basic Info
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="credentials-tab" 
                                data-bs-toggle="tab" 
                                href="#credentials" role="tab"
                                aria-controls="credentials" aria-selected="true">
                                Credentials
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" id="address-tab" 
                                data-bs-toggle="tab" 
                                href="#address" role="tab"
                                aria-controls="address" aria-selected="true">
                                Address
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" id="guardian-tab" 
                                data-bs-toggle="tab" 
                                href="#guardian" role="tab"
                                aria-controls="guardian" aria-selected="true">
                                Guardian
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" id="education-tab" 
                                data-bs-toggle="tab" 
                                href="#education" role="tab"
                                aria-controls="education" aria-selected="true">
                                Education
                            </a>
                        </li>

                        <div>
                        <!-- <div style="height: 100vh;width: 100%;" class='tab-content channelContent' id='myTabContent'> -->
                            <div 
                                class="tab-pane fade show active" id="basic_info"
                                role="tabpanel" aria-labelledby="basic_info-tab">
                                    <?php include "basic_info.php" ?> 
                            </div>

                            <div 
                                class="tab-pane fade" id="credentials"
                                role="tabpanel" aria-labelledby="credentials-tab">
                                    <?php include "credentials.php" ?> 
                            </div>

                            <div 
                                class="tab-pane fade" id="address"
                                role="tabpanel" aria-labelledby="address-tab">
                                    <?php include "address.php" ?> 
                            </div>

                            <div 
                                class="tab-pane fade" id="guardian"
                                role="tabpanel" aria-labelledby="guardian-tab">
                                    <?php include "guardian.php" ?> 
                            </div>
                            <div 
                                class="tab-pane fade" id="education"
                                role="tabpanel" aria-labelledby="education-tab">
                                    <?php include "education.php" ?> 
                            </div>
                        </div>

                    </ul>
                </div>
            <?php
        }else{
            echo "not authenticated";
        }


    }else{
        echo "You are not authenticated";
        exit();
    }
 

?>