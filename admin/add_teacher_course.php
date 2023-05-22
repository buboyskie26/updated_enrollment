<?php  
 
    include('../includes/classes/Teacher.php');
    include('./classes/Department.php');
    include('./adminHeader.php');


    if(isset($_GET['teacher_id'])){

        $teacher = new Teacher($con, $adminLoggedIn);

        $teacher_id = $_GET['teacher_id'];

        $createFormTeacherCourse = $teacher->createTeacherCourse($teacher_id);

        echo "
            <div style='width: 80%'; class='column'>
                $createFormTeacherCourse
            </div>
        ";

        if(isset($_POST['submit_teacher_course_admin'])){

            $wasSuccess = $teacher->insertTeacherCourse(
                $_POST['course_id'],
                $_POST['teacher_id'],
                $_POST['subject_id'],
                $_POST['school_year_id'],
                $_POST['thumbnail']);

            if($wasSuccess){
                header("Location: teacher.php");
            }
             

            // echo $_POST['course_id'];
            // echo "<br>";
            // echo $_POST['subject_id'];
            // echo "<br>";
            // echo $_POST['school_year_id'];
            // echo "<br>";
        }
    }

?>