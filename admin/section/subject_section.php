<?php

    include('../registrar_enrollment_header.php');
    include('../../enrollment/classes/Section.php');
    include('../../enrollment/classes/StudentEnroll.php');
    include('../classes/Subject.php');

    if(isset($_GET['section_id']) &&
        isset($_GET['subject_id'])){


        $course_id = $_GET['section_id'];
        $subject_id = $_GET['subject_id'];
        $section = new Section($con, $course_id);

       

        $edit_subject_section = $section->createForm($subject_id);
        
        echo "
            <div class='col-md-12 '>
                <div class='col-md-10 offset-md-1'>
                    $edit_subject_section
                </div>
            </div>
        ";
    }

?>