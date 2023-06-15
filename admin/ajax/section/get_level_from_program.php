<?php 

    require_once("../../../includes/config.php");

    require_once("../../../enrollment/classes/StudentEnroll.php");
    require_once("../../../enrollment/classes/Section.php");

    // echo "yehey";

    if(isset($_POST['program_id'])){


        $program_id = $_POST['program_id'];

        
        $get_program = $con->prepare("SELECT * FROM program
            WHERE program_id=:program_id
            LIMIT 1
            ");
        $get_program->bindValue(":program_id", $program_id);
        $get_program->execute();


        if($get_program->rowCount() > 0){

            $row = $get_program->fetch(PDO::FETCH_ASSOC);

            if($row['department_id'] != 4){

                $data = array(
                    array(
                        'level' => "1"
                    ),
                    array(
                        'level' => "2"
                    ),
                    array(
                        'level' => "3"
                    ),
                    array(
                        'level' => "4"
                    )
                );
            }
            else if($row['department_id'] == 4){

               $data = array(
                    array(
                        'level' => "11"
                    ),
                    array(
                        'level' => "12"
                    )
                );


            }

            echo json_encode($data);
        }
    }
?>