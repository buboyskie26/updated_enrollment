<?php 

    include('../admin_enrollment_header.php');
    $createUrl = base_url . "/create.php";


    if(isset($_GET['id'])){
        $department_id = $_GET['id'];

        ?>
        <div class="row col-md-12">
                <h2 class="text-center page-header">SHS Department</h2>

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
                                    <th rowspan="2" width="20%">Track</th>  
                                    <th rowspan="2" width="20%">Strand</th>  
                                    <th rowspan="2" width="20%">Total student</th>  
                                    <th rowspan="2" width="20%">Action</th>  
                                </tr>	
                            </thead> 	
                            <tbody>
                                <?php 

                                    $query = $con->prepare("SELECT * FROM program
                                        WHERE department_id=:department_id
                                    ");

                                    $query->bindValue(":department_id", $department_id);
                                    $query->execute();
                                 
                                    if($query->rowCount() > 0){
                                        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {

                                            $program_name = $row['program_name'];
                                            $track = $row['track'];
                                            

                                            echo "<tr class='text-center'>";
                                                echo "
                                                    <td>
                                                        $track
                                                    </td>
                                                ";
                                                echo "<td>$program_name</td>";
                                                echo "<td>5</td>";
                                                echo "
                                                    <td>

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
        <?php
    }
?>

