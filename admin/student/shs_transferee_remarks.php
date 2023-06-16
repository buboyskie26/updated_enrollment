

<?php 

    include('../registrar_enrollment_header.php');
    include('../../enrollment/classes/StudentEnroll.php');
    include('../../enrollment/classes/OldEnrollees.php');
    include('../classes/Course.php');



    if(isset($_GET['student_id']) && isset($_GET['s_id']) && isset($_GET['page'])){

        $student_id = $_GET['student_id'];
        $subject_id = $_GET['s_id'];
        $page = $_GET['page'];

        ?>
            <div class="row col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="text-center text-primary">Remarking Section</h4>
                    </div>
                    <div class="card-body">
                        <table style="font-weight: 200;" class="table table-striped table-bordered table-hover "  style="font-size:12px" cellspacing="0"  > 
                            <thead>
                                <tr class="text-center"> 
                                    <th class="text-success" rowspan="2">Subject</th>
                                    <th class="text-success" rowspan="2">Description</th>  
                                    <th class="text-success" rowspan="2">Unit</th>
                                    <th class="text-success" rowspan="2">Pre-Requisite</th>
                                    <th class="text-success" rowspan="2">Type</th>
                                    <th style='width:120px;' class="text-muted" colspan="4">Remark</th> 
                                </tr>	
                            </thead> 
                            <tbody>
                                <?php 

                                    $sql = $con->prepare("SELECT t2.*, t1.student_subject_id
                                    
                                        FROM student_subject as t1

                                        INNER JOIN subject as t2 ON t2.subject_id = t1.subject_id
                                        WHERE t1.subject_id=:subject_id
                                        AND t1.student_id=:student_id
                                        LIMIT 1");

                                    $sql->bindValue(":subject_id", $subject_id);
                                    $sql->bindValue(":student_id", $student_id);
                                    $sql->execute();

                                    $row = $sql->fetch(PDO::FETCH_ASSOC);

                                    $student_subject_id = $row['student_subject_id'];
                                    $subject_id = $row['subject_id'];
                                    $course_id = $row['course_id'];
                                    $subject_code = $row['subject_code'];
                                    $subject_title = $row['subject_title'];
                                    $unit = $row['unit'];
                                    $subject_type = $row['subject_type'];
                                    $pre_requisite = "Blank";




                                    $markAsPassed = "MarkAsPassed($subject_id,
                                        $student_id, \"Passed\",
                                        $student_subject_id, $course_id, \"$subject_title\")";

                                    $markAsFailed = "MarkAsFailed($subject_id,
                                        $student_id, \"Failed\",
                                        $student_subject_id, $course_id, \"$subject_title\")";


                                    echo "
                                        <tr class='text-center'>
                                            <td>$subject_code</td>
                                            <td>$subject_title</td>
                                            <td>$unit</td>
                                            <td>$pre_requisite</td>
                                            <td>$subject_type</td>
                                            <td>
                                                <div class='row'>
                                                    <div class='col-md-6'>
                                                        <i style='color:green; cursor:pointer;' onclick='$markAsPassed' class='fas fa-check'></i>
                                                    </div>
                                                    <div class='col-md-6'>
                                                        <i style='color:orange;' onclick='$markAsFailed' class='fas fa-times'></i>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    ";
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
            <script>


                function MarkAsPassed(subject_id, student_id, remarks,
                    student_subject_id, course_id, subject_title){

                    var page = `
                        <?php echo $page;?>
                    `;

                    page = page.trim();
 

                    $.post('../ajax/shs_transferee_grading.php', {
                        student_id,
                        subject_id,
                        remarks,
                        student_subject_id, 
                        course_id,
                        subject_title

                    }).done(function (data) {

                        Swal.fire({
                            title: 'Subject Passed',
                            // text: '',
                            icon: 'success',
                            showCancelButton: false,
                            confirmButtonText: 'OK',
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = `${page}.php?subject=show&id=${student_id}`;
                            }
                        });
                        // alert(data);

                        // window.location.href = `${page}.php?subject=show&id=${student_id}`;

                    });
                }
                function MarkAsFailed(subject_id, student_id, remarks,
                    student_subject_id, course_id, subject_title){

                    var page = `
                        <?php echo $page;?>
                    `;
                    page = page.trim();
                    
                    if(confirm('Are you sure you want to failed the student?')){
                        $.post('../ajax/shs_transferee_grading.php', {
                            student_id,
                            subject_id,
                            remarks,
                            student_subject_id, 
                            course_id,
                            subject_title

                        }).done(function (data) {

                            Swal.fire({
                                title: 'Subject Failed',
                                // text: '',
                                icon: 'warning',
                                showCancelButton: false,
                                confirmButtonText: 'OK',
                            }).then((result) => {
                                if (result.isConfirmed) {

                                    window.location.href = `${page}.php?subject=show&id=${student_id}`;
                                    // window.location.href = `shs_view_transferee_details.php?subject=show&id=${student_id}`;
                                }
                            });
                            // alert(data);

                        });
                    }
                }
            </script>
        <?php
    }else{
        echo "ewqwe";
    }
?>



