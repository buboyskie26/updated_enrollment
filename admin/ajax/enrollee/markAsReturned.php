<?php 

    require_once("../../../includes/config.php");

    require_once("../../../enrollment/classes/StudentEnroll.php");

     
    if(isset($_POST['username'])){

        echo "qwe";
        $enrol = new StudentEnroll($con);

        $school_year_obj = $enrol->GetActiveSchoolYearAndSemester();
        $current_school_year_id = $school_year_obj['school_year_id'];

        
    }

?>