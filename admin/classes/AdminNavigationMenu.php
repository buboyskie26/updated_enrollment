<?php 

    class AdminNavigationMenu{
        
        private $con, $userLoggedInObj;

        public function __construct($con, $userLoggedInObj)
        {
            $this->con = $con;
            $this->userLoggedInObj = $userLoggedInObj;
        }

        public function create(){

            $result = $this->createNavigation("school_year.php",
                "../assets/images/icons/home.png", "School Year");
            
            // HARD-CODED
            $base_url = 'http://localhost/dcbt/admin';

            // Set the dynamic part of the URL using a global variable
            $admin_find_account = $base_url . '/account/admin_find_account.php';
            $schedule_url = $base_url . '/schedule/index.php';

            $teacher_url = $base_url . '/teacher/index.php';
            $section_url = $base_url . '/section/admin_strand_sections.php';   
            $courses_url = $base_url . '/courses/course_list.php';   
            $enrollees_subject = $base_url . '/subject/index.php';


            // Search to become dynamic to avoid possible redirect error
            // when it was launch in the internet.

            $current_url = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            // header("Location: " . $current_url . "profile.php");


            $enrollees_url=  "";
            $result .= $this->createNavigation("$teacher_url",
                "../assets/images/icons/home.png", "Teacher");
                
            $result .= $this->createNavigation("$courses_url",
                "../assets/images/icons/home.png", "Courses");

            $result .= $this->createNavigation("$enrollees_subject",
                "../assets/images/icons/home.png", "Subjects");

            $result .= $this->createNavigation("$section_url",
                "../assets/images/icons/home.png", "Sections");

            $result .= $this->createNavigation("$admin_find_account",
                "../assets/images/icons/home.png", "Accounts");
            
            $result .= $this->createNavigation("$schedule_url",
                "../assets/images/icons/home.png", "Schedule");

            $result .= $this->createNavigation("$enrollees_url",
                "../assets/images/icons/home.png", "Senior High School");

            if(AdminUser::IsAuthenticated()){
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