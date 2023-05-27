<?php 
    include('../admin_enrollment_header.php');
    include('../../enrollment/classes/StudentEnroll.php');
    include('../../enrollment/classes/Schedule.php');
    include('../../enrollment/classes/Section.php');
    include('../classes/Course.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="Section.css">
</head>


<body>
    <div class="row col-md-12">
                <!--shs-strand-->
        <div class="choose-strand" id="shs-choose-strand">
            <h3>Strand Sections</h3>
                <div class="theader">
                    <table>
                        <tr >
                            <th>Strand</th>
                            <th>Grade 11</th>
                            <th>Grade 12</th>
                            <th></th>
                        </tr>
                        <div class="tbody">
                                <tbody>
                                    <?php 

                                        $username = "";
                                        // $sectionScheduleGradeElevenFirst = $schedule->GetSectionScheduleGradeElevenFirst($username, $student_id, 11, "First");
                                        $query = $con->query("SELECT * FROM program
                                            WHERE department_id=4");

                                        if($query->rowCount() > 0){

                                            while($row = $query->fetch(PDO::FETCH_ASSOC)){

                                                $acronym = $row['acronym'];
                                                $program_id = $row['program_id'];

                                                $url = "index_show.php?id=$program_id";
                                                echo "
                                                    <tr >
                                                        <td>$acronym</td>
                                                        <td></td>
                                                        <td></td>
                                                        <td>
                                                            <a href='$url'>
                                                                <button id='shs-button' class='view'>View</button>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                ";
                                            }
                                        }

                                    ?>
                                </tbody>
                        </div>
                    </table>
                </div>
        </div>
    </div>
</body>