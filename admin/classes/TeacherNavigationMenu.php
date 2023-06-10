<?php 

    class TeacherNavigationMenu{
        
        private $con, $userLoggedInObj;

        public function __construct($con, $userLoggedInObj)
        {
            $this->con = $con;
            $this->userLoggedInObj = $userLoggedInObj;
        }

        public function create(){

      
            $base_url = 'http://localhost/dcbt/admin';
       
            $admission_url = $base_url . '/admission/index.php';

            $current_url = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

            $result = $this->createNavigation("$admission_url",
                "../assets/images/icons/home.png", "Admission");
            
            // $result .= $this->createNavigation("$student_creation_url",
            //     "../assets/images/icons/home.png", "Students");

         
            if(AdminUser::IsTeacherAuthenticated()){
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