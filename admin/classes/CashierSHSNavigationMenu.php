<?php 

    class CashierSHSNavigationMenu{
        
        private $con, $userLoggedInObj;

        public function __construct($con, $userLoggedInObj)
        {
            $this->con = $con;
            $this->userLoggedInObj = $userLoggedInObj;
        }

        public function create(){

            // $result = $this->createNavigation("school_year.php",
            //     "../assets/images/icons/home.png", "School Year");
            
            // HARD-CODED

            $base_url = 'http://localhost/dcbt/admin';
            // $enrollees_url = $base_url . '/enrollees/index.php';

            // Set the dynamic part of the URL using a global variable
            $cashier_url = $base_url . '/cashier/index.php';
                        
            // echo $enrollees_url;

            // Search to become dynamic to avoid possible redirect error
            // when it was launch in the internet.

            $current_url = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

            $result = $this->createNavigation("$cashier_url",
                "../assets/images/icons/home.png", "Senior High School");

            // $result .= $this->createNavigation("$cashier_url",
            //     "../assets/images/icons/home.png", "Senior High School");


            if(AdminUser::IsCashierAuthenticated()){
                    $result .= $this->createNavigation("logout.php", 
                "../assets/images/icons/logout.png", "Logout");
                
            }
            
            return "
                <div class='navigationItems'>
                    $result
                </div>
            ";
        }
        
        public function createNavigation($link, $profile, $text){
            return "
                <div class='navigationItem'>
                    <a href='$link'>
                        <img src='$profile'>
                        <span>$text</span>
                    </a>
                </div>
            ";
        }
    }

?>