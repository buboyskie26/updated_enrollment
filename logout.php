<?php
    session_start();
    session_destroy();
    header("Location: /dcbt/enrollment/index.php");
?>