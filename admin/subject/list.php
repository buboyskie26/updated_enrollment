<?php 

    include('../registrar_enrollment_header.php');
    $createUrl = directoryPath . "create.php";
    $templateUrl = directoryPath . "template.php";

    // echo "im in subject enroll";
?>


<div class="row col-md-12">
            <div class="col-md-10 offset-md-1">
            <h3 class="text-center">SHS Subjects</h3>
            <div class="row justify-content-end">
                <a class="mb-2" href="<?php echo $createUrl?>">
                    <button class="btn btn btn-success">Add New</button>
                </a>    
            </div>
            <table  class="table table-striped table-bordered table-hover "  style="font-size:13px" cellspacing="0"  > 
                <thead>
                    <tr class="text-center"> 
                        <th rowspan="2">Id</th>
                        <th rowspan="2">Code</th>
                        <th rowspan="2">Pre Requisite</th>
                        <th rowspan="2">Type</th>  
                        <th rowspan="2">Unit</th>
                        <th rowspan="2">Action</th>
                    </tr>	
                </thead> 	
                <tbody>
                    <?php 
                    

                        // $query = $con->query("SELECT * FROM subject_program

                        //     LEFT JOIN program ON program.program_id = subject_program.program_id
                        //     ORDER BY program.program_name,
                        //     subject_program.course_level,
                        //     subject_program.semester
                        //     ");

                        // // $query->bindValue("");
                        // $query->execute();

                        // if($query->rowCount() > 0){
                        
                        //     while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
                        //         $program_name = $row['program_name'];
                        //     }
                        // }
                    ?>
                </tbody>
            </table>
        </div>
</div>