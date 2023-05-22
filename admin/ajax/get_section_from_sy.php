<?php 

    require_once("../../includes/config.php");

    require_once("../../enrollment/classes/StudentEnroll.php");

    // echo "yehey";

    if(isset($_POST['school_year_term'])){

        $school_year_term = $_POST['school_year_term'];

        $query = $con->prepare("SELECT program_section, course_id, school_year_term 
        
            FROM course
            WHERE school_year_term=:school_year_term
            ");
        $query->bindValue(":school_year_term", $school_year_term);
        $query->execute();

        if($query->rowCount() > 0){
            while($row = $query->fetch(PDO::FETCH_ASSOC)) {
                $data[] = array(
                    'course_id' => $row['course_id'],
                    'program_section' => $row['program_section'],
                    'school_year_term' => $row['school_year_term']
                );
            }
        }
        echo json_encode($data);
    }


?>