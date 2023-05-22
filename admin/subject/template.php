<?php

    include('../registrar_enrollment_header.php');
    include('../../enrollment/classes/StudentEnroll.php');
    include('../classes/Subject.php');


    $subject = new Subject($con, $registrarLoggedIn);

    $form = $subject->createFormModified();

    echo "
        <div class='col-md-10 row offset-md-1'>
            $form
        </div>
    ";

?>