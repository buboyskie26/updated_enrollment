<?php 

    require_once("../../includes/config.php");

    require_once("../../enrollment/classes/StudentEnroll.php");

    // echo "yehey";


    if(isset($_POST['school_year_id']) && isset($_POST['course_id'])){

        $school_year_id = $_POST['school_year_id'];
        $course_id = $_POST['course_id'];


        // echo $course_id;

        $sql_course = $con->prepare("SELECT course_level, course_main_id FROM course
            WHERE course_id=:course_id
            LIMIT 1
            ");

        $sql_course->bindValue("course_id", $course_id);
        $sql_course->execute();

        // Default
        $course_level = 0;
        $course_main_id = 0;

        if($sql_course->rowCount() > 0){
            $course_row = $sql_course->fetch(PDO::FETCH_ASSOC);

            // $course_level = $sql_course->fetchColumn();
            $course_level = $course_row['course_level'];
            $course_main_id = $course_row['course_main_id'];

            // echo $course_level;
            // echo "<br>";
            // echo $course_main_id;
        }

        $sql_school_year = $con->prepare("SELECT period FROM school_year
            WHERE school_year_id=:school_year_id
            LIMIT 1
            ");
        // echo $school_year_id;

        $sql_school_year->bindValue("school_year_id", $school_year_id);
        $sql_school_year->execute();

        $arr1 = [];
        $data = [];
        if($sql_school_year->rowCount() > 0){

            $period_name = $sql_school_year->fetchColumn();

            // echo $period_name;

            $sql = $con->prepare("SELECT subject_id, subject_code FROM subject
                WHERE semester=:semester
                AND course_level=:course_level
                AND course_main_id=:course_main_id
                ORDER BY course_level ASC
            ");

            $sql->bindValue("semester", $period_name);
            $sql->bindValue("course_level", $course_level);
            $sql->bindValue("course_main_id", $course_main_id);
            $sql->execute();
            // echo $teacher_id;

            if($sql->rowCount() > 0){

                while($row = $sql->fetch(PDO::FETCH_ASSOC)) {

                    $subject_id = $row['subject_id'];
                    $subject_code = $row['subject_code'];
                   
                    $data[] = array(
                        'subject_id' => $row['subject_id'],
                        'subject_code' => $row['subject_code']
                    );

                    // array_push($arr1, $data);

                }

            }

        }
        $res = [];
        // array_push($res, $arr1);

        // echo json_encode($arr1);

        echo json_encode($data);
 
        // print_r($arr1);
       
    }else{
        echo "something";
    }

?>