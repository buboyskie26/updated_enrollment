<?php 

    class TeacherEnrollmentNavigationMenu{
        
        private $con, $userLoggedInObj;

        public function __construct($con, $userLoggedInObj)
        {
            $this->con = $con;
            $this->userLoggedInObj = $userLoggedInObj;
        }

        public function create(){

            $base_url = 'http://localhost/dcbt/admin';
   
            $my_class_url = $base_url . './teacher_index.php';

            $result = $this->createNavigation("$my_class_url",
                "../../assets/images/icons/home.png", "My Class");
 
            if(AdminUser::IsTeacherAuthenticated()){
                    $result .= $this->createNavigation("../logout.php", 
                "../../assets/images/icons/logout.png", "Logout");
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