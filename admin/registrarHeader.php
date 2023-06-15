
<?php 
    require_once('../includes/config.php');
    // require_once('../includes/classes/User.php');
    require_once('../admin/classes/RegistrarNavigationMenu.php');
    require_once('../admin/classes/ButtonProvider.php');
    require_once('../admin/classes/AdminUser.php');
    require_once('../admin/classes/Alert.php');
   
    $adminLoggedIn = isset($_SESSION["registrarLoggedIn"]) 
        ? $_SESSION["registrarLoggedIn"] : "";
    

    $adminLoggedInObj = new AdminUser($con, $adminLoggedIn);

    # Some pages of registrar were accessbile by admin
    // if (!isset($_SESSION['registrarLoggedIn']) || $_SESSION['registrarLoggedIn'] == '') {
    //     header("location: /dcbt/registrar_login.php");
    //     exit();
    // }
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enrollment</title>

  
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>


    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

    <link rel="stylesheet" href="../assets/css/style.css">

    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.js"></script>
    
	<script src="../assets/js/bootbox.min.js"></script>
    <script src="../assets/js/common.js"></script>

    <link rel="stylesheet" href="../assets/css/bootstrap-datetimepicker.css">
    <script src="../assets/js/bootstrap-datetimepicker.js"></script>

  	<script src="https://cdn.jsdelivr.net/gh/guillaumepotier/Parsley.js@2.9.1/dist/parsley.js"></script>
    <link rel="stylesheet" href="//cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/alertify.min.css"/>

    <!-- DataTables -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs4/dt-1.11.3/datatables.min.css"/>
    <script type="text/javascript" src="https://cdn.datatables.net/v/bs4/dt-1.11.3/datatables.min.js"></script>

</head>
<body>
    
    <div id="pageContainer">
        <div id="mastHeadContainer">

            <button class="navShowHide">
                <img src="../assets/images/icons/menu.png">
            </button>

            <a class="logoContainer" href="index.php">
            </a>

            <div class="searchBarContainer">
                <form action="search.php" method="GET">
                    <input type="text" class="searchBar" 
                        name="term" placeholder="Search...">

                    <button class="searchButton">
                        <img src="../assets/images/icons/search.png">
                    </button>
                </form>
            </div>

            <div class="rightIcons">
                <a href="upload.php">
                    <img class="upload" src="../assets/images/icons/upload.png">
                </a>
                <!-- <?php
                    echo ButtonProvider::createAdminProfileNavigationButton($con, $adminLoggedIn);
                ?> -->
            </div>
        </div>

        <div id="sideNavContainer" style="display: block;">
            <?php
                $nav = new RegistrarNavigationMenu($con, $adminLoggedInObj);
                echo $nav->create();
            ?>
        </div>

        <div id="mainSectionContainer">
            <div id="mainContentContainer">
                
            