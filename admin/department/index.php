<?php 

    include('../admin_enrollment_header.php');
    $createUrl = base_url . "/create.php";
?>

<div class="row col-md-12">
            <h2 class="text-center page-header">List of Department</h2>

        <div class="col-md-10 offset-md-1">
            <div class="table-responsive" style="margin-top:2%;"> 
                <div class="mb-3">
              
                    <a href="<?php echo $createUrl?>">
                        <button class="btn btn-success">Add Department</button>
                    </a>  
                </div>
                <table  class="table table-striped table-bordered table-hover "  style="font-size:13px" cellspacing="0"  > 
                    <thead>
                        <tr class="text-center"> 
                            <th rowspan="2" width="20%">Department</th>  
                            <!-- <th rowspan="2" width="20%">Description</th> -->
                            <!-- <th rowspan="2" width="20%"></th> -->
                        </tr>	
                    </thead> 	
                    <tbody>
                        <?php 
                            $username = "";
                            // $sectionScheduleGradeElevenFirst = $schedule->GetSectionScheduleGradeElevenFirst($username, $student_id, 11, "First");

                            $query = $con->query("SELECT * FROM department
                                ");

                            // $query->bindValue("");
                            $query->execute();

                            if($query->rowCount() > 0){
                                while ($row = $query->fetch(PDO::FETCH_ASSOC)) {

                                    $department_name = $row['department_name'];
                                    $department_id = $row['department_id'];
                                    
                                    $url = directoryPath . "program.php?id=$department_id";

                                    echo "<tr class='text-center'>";
                                        echo "
                                            <td>
                                                <a href='$url'>
                                                    " . $department_name . "    
                                                </a>
                                            </td>
                                        ";
                                    echo "</tr>";
                                }
                            }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
</div>
 