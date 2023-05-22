<?php

    require_once('../includes/config.php');
    
    

    $mytime = 5;

    if(!isset($_SESSION['time'])){
        $_SESSION['time'] = time();
        // echo $_SESSION['time'];
    }else{

        $diff = time() - $_SESSION['time'];

        // echo $_SESSION['time'];
        // echo "<br>";
        // echo time();

        $diff = $mytime - $diff;
        // echo $diff;

        $hours = floor($diff/60);
        $minutes = (int)($diff/60);
        $seconds = $diff%60;
        
        $show = $hours . ":" . $minutes . ":" . $seconds;

        // echo $seconds;

        if($diff == 0 || $diff <= 0){
            echo "Time out";
        }else{
            echo $show;
        }

    }
?>


