<?php

include('../admin_enrollment_header.php');
include('../../enrollment/classes/StudentEnroll.php');
require_once('../../admin/classes/AdminUser.php');

if(!AdminUser::IsAuthenticated()){
    header("Location: /dcbt/adminLoggedIn.php");
    exit();
}

if(isset($_POST['update_teacher_btn'])){
    $enroll = new StudentEnroll($con);
    echo "wee";
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $department_id = $_POST['department_id'];
    $teacher_id = $_POST['teacher_id'];

    $sql = $con->prepare("UPDATE teacher SET 
    firstname=:firstname, lastname=:lastname, department_id=:department_id WHERE teacher_id=:teacher_id");

    $sql->bindValue(":firstname", $firstname);
    $sql->bindValue(":lastname", $lastname);
    $sql->bindValue(":department_id", $department_id);
    $sql->bindValue(":teacher_id", $teacher_id);
    $sql->execute();
    
    header("Location: /dcbt/admin/teacher/index.php");
    exit();
}

// Get teacher data to prepopulate form
if(isset($_GET['id'])){

    $teacher_id = $_GET['id'];

    $sql = $con->prepare("SELECT * FROM teacher WHERE teacher_id=:teacher_id");
    $sql->bindValue(":teacher_id", $teacher_id);
    $sql->execute();

    $teacher = $sql->fetch(PDO::FETCH_ASSOC);

    // echo $teacher['department_id'];
}
?>
<div class='col-md-8 row offset-md-1'>
    <form method='POST'>
        <div class="card">
            <div class="card-header">
                <h4 class='text-center mb-3'>Edit Teacher</h4>

            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label for="firstname">Firstname</label>
                    </div>
                    <div class="col-md-9">
                        <input type="text" name="firstname" class="form-control" value="<?php echo $teacher['firstname']; ?>">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-3">
                        <label for="lastname">Lastname</label>
                    </div>
                <div class="col-md-9">
                    <input type="text" name="lastname" class="form-control" value="<?php echo $teacher['lastname']; ?>">
                </div>

            </div>
        </div>


        <div class="row mb-3">
            <div class="col-md-3">
                <label for="department_id">Department</label>
            </div>
            <div class="col-md-9">
                <select style="width: 349px; margin-left:45px;" class="form-select" name="department_id" aria-label="Select department name">
                    <option <?php if($teacher['department_id'] == 0) echo "selected"; ?> value="0">Choose Department</option>
                    <option <?php if($teacher['department_id'] == 4) echo "selected"; ?> value="4">Senior High School</option>
                </select>
            </div>
        </div>
    </div>

    <div class="modal-footer">
        <button name="update_teacher_btn" type="submit" class="btn btn-primary">Save</button>
    </div>

    <input type="hidden" name="teacher_id" value="<?php echo $teacher_id; ?>">
</form>
