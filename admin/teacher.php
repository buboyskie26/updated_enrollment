<?php  
 
    include('../includes/classes/Teacher.php');
    include('./classes/Department.php');
    include('./adminHeader.php');

    $teacher = new Teacher($con, $adminLoggedIn);

    $table =  $teacher->createTable();

    $createForm = $teacher->createForm();

    echo "
        <div style='width:100%;' class='teacher_section'>
            $table
        </div>
       
    ";

    if(isset($_POST['submit_teacher'])){
        $wasSuccessful = $teacher->insertTeacher($_POST['department_id'],
            $_POST['username'], $_POST['firstname'], $_POST['lastname']);
    }

    if(isset($_GET['teacher_id'])){
        $teacher_id = $_GET['teacher_id'];

        $createFormTeacherCourse = $teacher->createTeacherCourse($teacher_id);

        echo $teacher_id;
    }

?>


<?php  include('../includes/footer.php');?>