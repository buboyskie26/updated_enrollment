<?php 

    include('../admin_enrollment_header.php');
    $createUrl = directoryPath . "create.php";
    $templateUrl = directoryPath . "template.php";

    // echo "im in subject enroll";

    if(!AdminUser::IsAuthenticated()){
        header("Location: /dcbt/adminLogin.php");
        exit();
    }
?>


<div class="row col-md-12">
    <div class="col-md-10 offset-md-1">
            <table  class="table table-striped table-bordered table-hover "  style="font-size:13px" cellspacing="0"  > 
                <thead>
                    <tr class="text-center"> 
                        <th rowspan="2">Track</th>
                        <th rowspan="2">Strand</th>
                        <th rowspan="2">Total Unit</th>
                        <th rowspan="2"></th>
                    </tr>	
                </thead> 	
                <tbody>
                    <?php 
                        $query = $con->prepare("SELECT * FROM program
                            WHERE department_id=:department_id
                            ");

                        $query->bindValue(":department_id", 4);
                        $query->execute();



                        if($query->rowCount() > 0){
                        
                            $track = "";
                            while ($row = $query->fetch(PDO::FETCH_ASSOC)) {


                                $program_id = $row['program_id'];
                                $program_name = $row['program_name'];
                                $acronym = $row['acronym'];

                                if($acronym == "HUMMS" ||$acronym == "ABM" || $acronym == "STEM" )
                                    $track = "Academic";
                                
                                $strand_url = "strand_subject_view.php?id=$program_id";
                                echo "
                                    <tr class='text-center'>
                                        <td>$track</td>
                                        <td>
                                            <a href='$strand_url'>
                                                $acronym
                                            </a>
                                        </td>
                                    </tr>
                                ";
                            }
                        }
                    ?>
                </tbody>
            </table>
    </div>
</div>