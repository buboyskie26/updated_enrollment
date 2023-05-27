<?php

    class SchoolYear{

    private $con, $userLoggedIn, $sqlData;
   
    public function __construct($con){
        $this->con = $con;
    }


    public function GetStartEnrollment($school_year_id){

        $sql = $this->con->prepare("SELECT start_enrollment_date FROM school_year
            WHERE school_year_id=:school_year_id");

        $sql->bindValue(":school_year_id", $school_year_id);

        $sql->execute();

        return $sql->fetchColumn();
    }

    public function GetSYEnrollmentStatus($school_year_id){

        $sql = $this->con->prepare("SELECT enrollment_status FROM school_year
            WHERE school_year_id=:school_year_id");

        $sql->bindValue(":school_year_id", $school_year_id);
        $sql->execute();

        return $sql->fetchColumn();
    }


    public function DoesEndPeriodIsOver($school_year_id){

        $sql = $this->con->prepare("SELECT end_period FROM school_year
            WHERE school_year_id=:school_year_id");

        $sql->bindValue(":school_year_id", $school_year_id);
        $sql->execute();

        // echo "qweqwe";
        if($sql->rowCount()){

            $end_period = $sql->fetchColumn();

            $currentTimestamp = time();  // Current timestamp
            $currentDateTime = new DateTime();
            $current_time = $currentDateTime->format('Y-m-d H:i:s');

            // $currentTimestamp = time();
            // echo date('Y-m-d H:i:s', $currentTimestamp);

            echo $current_time;
            echo "<br>";
            echo "<br>";
            echo $end_period;

            if ($end_period < $current_time) {
                // $end_period is less than the current date and time

                # Change The Semester Into Next Row.
                echo $end_period;
            } else if ($end_period > $current_time){
                // $end_period is greater than or equal to the current date and time
                // echo "greater";

            }


            // $endPeriodTimestamp = strtotime($end_period);

            // if ($endPeriodTimestamp < $currentTimestamp) {
            //     // $end_period is less than the current date and time
            //     echo "The end period is less than";
            // } else if ($endPeriodTimestamp > $currentTimestamp){
            //     // $end_period is greater than or equal to the current date and time
            //     echo "end_period is greater than to the current";
            // }
        }
        
    }

  
}
?>