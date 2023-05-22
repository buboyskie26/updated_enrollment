<?php 

    require_once("../../../includes/config.php");

    require_once("../../../enrollment/classes/StudentEnroll.php");

        // echo "yehey";

        // if(isset($_POST['course_id']) &&
        //     isset($_POST['term']) &&
        //     isset($_POST['course_level']) &&
        //     isset($_POST['school_year_id'])
        //     ){
        

        $program_section = $_POST['program_section'];

        // echo $program_section;

        $semester = $_POST['term']; 

        $course_level = $_POST['course_level']; 

        $school_year_term = $_POST['school_year_term']; 


        // $school_year_id = null;

        
        $school_year_ids = array();
        $course_ids = array();
        $courses_level_ids = [];
        $school_year_ids_periods = [];
     
        $return_arr = [];
        if($school_year_term != ""){

            // echo $school_year_term;
            if($program_section != ""){
                $get_program_sections = $con->prepare("SELECT course_id 

                FROM course 
                WHERE program_section LIKE :program_section_prefix");

                $get_program_sections->bindValue(":program_section_prefix", $program_section . "%");
                $get_program_sections->execute();
            }

            $get_sy_id = $con->prepare("SELECT school_year_id FROM school_year 
                WHERE term=:term");

            $get_sy_id->bindValue(":term", $school_year_term);
            $get_sy_id->execute();

            # Supported
            #  SY -> Strand Section -> Grade Level -> Semester
            #  SY -> Strand Section -> Grade Level
            # SY  -> Strand Section 
            # SY  -> Strand Grade Level 
            # SY  -> Strand Semester Level 
            if($get_sy_id->rowCount() > 0){
            
                    // echo "qwee";
                while($row = $get_sy_id->fetch(PDO::FETCH_ASSOC)){
                    $school_year_ids[] = $row['school_year_id'];
                }

                // if(!empty($school_year_ids)){
                //     $sql = 'SELECT
                //         t1.course_id, t1.student_id, 
                //         t2.firstname, t2.lastname, t2.student_unique_id,
                //         t3.course_level,  t3.program_section,
                //         t4.term, t4.period

                //         FROM enrollment as t1

                //         LEFT JOIN student as t2 ON t2.student_id = t1.student_id
                //         LEFT JOIN course as t3 ON t3.course_id = t1.course_id
                //         LEFT JOIN school_year as t4 ON t4.school_year_id = t1.school_year_id

                //         WHERE t1.school_year_id IN ('.implode(',', $school_year_ids).')';

                //     $params = array();

                //     $stmt = $con->prepare($sql);
                //     $stmt->execute($params);

                //     if($stmt->rowCount() > 0){
                //         $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                //         echo json_encode($results);
                //     }
                // }

                //

                if($program_section != "" && $get_program_sections->rowCount() > 0){
                    while($row = $get_program_sections->fetch(PDO::FETCH_ASSOC)){
                        // echo $row['course_id'];
                        // echo "<br>";
                        $course_ids[] = $row['course_id'];
                    }
                }

                $get_cl = $con->prepare("SELECT course_id 
                    FROM course 
                    WHERE course_level = :course_level");

                $get_cl->bindValue(":course_level", $course_level);
                $get_cl->execute();

                // echo $course_level;
                if($course_level != "" && $get_cl->rowCount() > 0){
                    while($row = $get_cl->fetch(PDO::FETCH_ASSOC)){
                        // echo $row['course_id'];
                        // echo "<br>";
                        $courses_level_ids[] = $row['course_id'];
                    }
                }

                $get_sy_from_period = $con->prepare("SELECT school_year_id 

                FROM school_year 
                WHERE period = :period");

                $get_sy_from_period->bindValue(":period", $semester);
                $get_sy_from_period->execute();

                if($semester != "" && $get_sy_from_period->rowCount() > 0){
                    while($row = $get_sy_from_period->fetch(PDO::FETCH_ASSOC)){
                        // echo $row['school_year_id'];
                        // echo "<br>";
                        $school_year_ids_periods[] = $row['school_year_id'];
                    }
                }

                // S.Y Only
                if (!empty($school_year_ids)
                    && empty($course_ids)
                    && empty($school_year_ids_periods)
                    && empty($courses_level_ids)){
                    // echo "empty";

                    // print_r($school_year_ids);
                    $sql = 'SELECT

                        t1.course_id, t1.student_id, 
                        t2.firstname, t2.lastname, t2.student_unique_id,
                        t3.course_level,  t3.program_section,
                        t4.term, t4.period

                        FROM enrollment as t1

                        LEFT JOIN student as t2 ON t2.student_id = t1.student_id
                        LEFT JOIN course as t3 ON t3.course_id = t1.course_id
                        LEFT JOIN school_year as t4 ON t4.school_year_id = t1.school_year_id

                        WHERE t1.school_year_id IN ('.implode(',', $school_year_ids).')';

                    $params = array();
                }

                // 1. S.Y, 2. Section(STEM), 3. Grade_Level, 4. Semester
                else if (!empty($course_ids) 
                    && !empty($school_year_ids) 
                    && !empty($courses_level_ids)
                    && !empty($school_year_ids_periods)
                    ) {

                        // echo "four";
                    $placeholders = implode(',', array_fill(0, count($course_ids), '?'));
                    $placeholders2 = implode(',', array_fill(0, count($courses_level_ids), '?'));
                    $placeholders3 = implode(',', array_fill(0, count($school_year_ids_periods), '?'));

                    // echo "eewe";
                    $sql = "SELECT
                        t1.course_id, t1.student_id, 
                        t2.firstname, t2.lastname, t2.student_unique_id,
                        t3.course_level,  t3.program_section,
                        t4.term, t4.period

                        FROM enrollment as t1
                        LEFT JOIN student as t2 ON t2.student_id = t1.student_id
                        LEFT JOIN course as t3 ON t3.course_id = t1.course_id
                        LEFT JOIN school_year as t4 ON t4.school_year_id = t1.school_year_id
                        WHERE t1.school_year_id IN (" . implode(',', $school_year_ids) . ") 
                        AND t1.course_id IN ($placeholders)
                        AND t3.course_id IN ($placeholders2)
                        AND t1.school_year_id IN ($placeholders3) ";
                        
                    $params = array_merge($course_ids, $courses_level_ids, $school_year_ids_periods);
                }

                // 1. S.Y, 2. Section(STEM), 3. Grade_Level
                else if (!empty($course_ids) 
                    && !empty($school_year_ids) 
                    && !empty($courses_level_ids)
                    && empty($school_year_ids_periods)
                    ) {

                    $placeholders = implode(',', array_fill(0, count($course_ids), '?'));
                    $placeholders2 = implode(',', array_fill(0, count($courses_level_ids), '?'));

                    // echo "eewe";
                    $sql = "SELECT
                        t1.course_id, t1.student_id, 
                        t2.firstname, t2.lastname, t2.student_unique_id,
                        t3.course_level,  t3.program_section,
                        t4.term, t4.period

                        FROM enrollment as t1
                        LEFT JOIN student as t2 ON t2.student_id = t1.student_id
                        LEFT JOIN course as t3 ON t3.course_id = t1.course_id
                        LEFT JOIN school_year as t4 ON t4.school_year_id = t1.school_year_id
                        WHERE t1.school_year_id IN (" . implode(',', $school_year_ids) . ") 
                        AND t1.course_id IN ($placeholders)
                        AND t3.course_id IN ($placeholders2)
                        ";
                    $params = array_merge($course_ids, $courses_level_ids);
                }

                // 1. S.Y, 2. Section(STEM), 3. Semester
                else if (!empty($course_ids) 
                    && !empty($school_year_ids) 
                    && !empty($school_year_ids_periods)
                    && empty($courses_level_ids)
                    ) {

                    $placeholders = implode(',', array_fill(0, count($course_ids), '?'));
                    $placeholders2 = implode(',', array_fill(0, count($school_year_ids_periods), '?'));

                    // echo "eewe";
                    $sql = "SELECT
                        t1.course_id, t1.student_id, 
                        t2.firstname, t2.lastname, t2.student_unique_id,
                        t3.course_level,  t3.program_section,
                        t4.term, t4.period

                        FROM enrollment as t1
                        LEFT JOIN student as t2 ON t2.student_id = t1.student_id
                        LEFT JOIN course as t3 ON t3.course_id = t1.course_id
                        LEFT JOIN school_year as t4 ON t4.school_year_id = t1.school_year_id
                        WHERE t1.school_year_id IN (" . implode(',', $school_year_ids) . ") 
                        AND t1.course_id IN ($placeholders)
                        AND t1.school_year_id IN ($placeholders2)
                        ";
                    $params = array_merge($course_ids, $school_year_ids_periods);
                }
                

                // (SY And 3 filter)
                // # 1 1. S.Y, 2. Section
                elseif (!empty($course_ids)
                    && !empty($school_year_ids) 
                    && empty($courses_level_ids)
                    && empty($school_year_ids_periods)
                    ) {

                    // echo " Section, S.Y Query";
                    $placeholders = implode(',', array_fill(0, count($course_ids), '?'));
                    $sql = "SELECT
                        t1.course_id, t1.student_id, 
                        t2.firstname, t2.lastname, t2.student_unique_id,
                        t3.course_level,  t3.program_section,
                        t4.term, t4.period
                        FROM enrollment as t1
                        LEFT JOIN student as t2 ON t2.student_id = t1.student_id
                        LEFT JOIN course as t3 ON t3.course_id = t1.course_id
                        LEFT JOIN school_year as t4 ON t4.school_year_id = t1.school_year_id
                        WHERE t1.school_year_id IN (" . implode(',', $school_year_ids) . ") 
                        AND t1.course_id IN ($placeholders)";

                    $params = $course_ids;
                }
                  // # 2 1. S.Y, 3. Grade_Level
                elseif (!empty($school_year_ids)
                    && !empty($courses_level_ids) 
                    && empty($school_year_ids_periods) 
                    && empty($course_ids)
                    ) {

                    // echo " SY Grade Level Query";

                    $placeholders = implode(',', array_fill(0, count($courses_level_ids), '?'));

                    $sql = "SELECT
                        t1.course_id, t1.student_id, 
                        t2.firstname, t2.lastname, t2.student_unique_id,
                        t3.course_level,  t3.program_section,
                        t4.term, t4.period
                        FROM enrollment as t1
                        LEFT JOIN student as t2 ON t2.student_id = t1.student_id
                        LEFT JOIN course as t3 ON t3.course_id = t1.course_id
                        LEFT JOIN school_year as t4 ON t4.school_year_id = t1.school_year_id
                        WHERE t1.school_year_id IN (" . implode(',', $school_year_ids) . ") 
                        AND t1.course_id IN ($placeholders)";

                    $params = $courses_level_ids;
                }
                // # 3 1. S.Y, 4. Semester
                elseif (!empty($school_year_ids)
                    && !empty($school_year_ids_periods) 
                    && empty($courses_level_ids)
                    && empty($course_ids)
                    ) {

                    // echo " SY Semester Query";

                    $placeholders = implode(',', array_fill(0, count($school_year_ids_periods), '?'));

                    $sql = "SELECT
                        t1.course_id, t1.student_id, 
                        t2.firstname, t2.lastname, t2.student_unique_id,
                        t3.course_level,  t3.program_section,
                        t4.term, t4.period
                        FROM enrollment as t1
                        LEFT JOIN student as t2 ON t2.student_id = t1.student_id
                        LEFT JOIN course as t3 ON t3.course_id = t1.course_id
                        LEFT JOIN school_year as t4 ON t4.school_year_id = t1.school_year_id
                        WHERE t1.school_year_id IN (" . implode(',', $school_year_ids) . ") 
                        AND t1.school_year_id IN ($placeholders)";

                    $params = $school_year_ids_periods;
                }


                $stmt = $con->prepare($sql);
                $stmt->execute($params);

                if($stmt->rowCount() > 0){
                    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    $return_arr = $results;
                    echo json_encode($return_arr);
                }else{
                    echo json_encode($return_arr);
                }
            }
        }

        // // Section Only
        // if($program_section != ""){

        //     $get_program_sections = $con->prepare("SELECT course_id 

        //     FROM course 
        //     WHERE program_section LIKE :program_section_prefix");

        //     $get_program_sections->bindValue(":program_section_prefix", $program_section . "%");
        //     $get_program_sections->execute();

        //     if($program_section != "" && $get_program_sections->rowCount() > 0){
        //         while($row = $get_program_sections->fetch(PDO::FETCH_ASSOC)){
        //             // echo $row['course_id'];

        //             $course_ids[] = $row['course_id'];
        //         }

        //         $placeholders = implode(',', array_fill(0, count($course_ids), '?'));

        //         $sql = "SELECT

        //             t1.course_id, t1.student_id, 
        //             t2.firstname, t2.lastname, t2.student_unique_id,
        //             t3.course_level,  t3.program_section,
        //             t4.term, t4.period

        //             FROM enrollment as t1

        //             LEFT JOIN student as t2 ON t2.student_id = t1.student_id
        //             LEFT JOIN course as t3 ON t3.course_id = t1.course_id
        //             LEFT JOIN school_year as t4 ON t4.school_year_id = t1.school_year_id

        //             WHERE t1.course_id 
        //             IN (" . implode(',', $course_ids) . ") ";

        //             // AND t1.course_id IN ($placeholders)";

        //             $params = array();

        //             $stmt = $con->prepare($sql);
        //             $stmt->execute($params);

        //             if($stmt->rowCount() > 0){
        //                 $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        //                 echo json_encode($results);
        //             }
        //     }
        // }


        // // Grade LEvel Only
        // if($course_level != ""){

        //     $get_cl = $con->prepare("SELECT course_id 

        //     FROM course 
        //     WHERE course_level = :course_level");

        //     $get_cl->bindValue(":course_level", $course_level);
        //     $get_cl->execute();

        //     if($course_level != "" && $get_cl->rowCount() > 0){
        //         while($row = $get_cl->fetch(PDO::FETCH_ASSOC)){
        //             // echo $row['school_year_id'];
        //             // echo "<br>";
        //             $courses_level_ids[] = $row['course_id'];
        //         }

        //         $placeholders = implode(',', array_fill(0, count($courses_level_ids), '?'));

        //         $sql = "SELECT

        //             t1.course_id, t1.student_id, 
        //             t2.firstname, t2.lastname, t2.student_unique_id,
        //             t3.course_level,  t3.program_section,
        //             t4.term, t4.period

        //             FROM enrollment as t1

        //             LEFT JOIN student as t2 ON t2.student_id = t1.student_id
        //             LEFT JOIN course as t3 ON t3.course_id = t1.course_id
        //             LEFT JOIN school_year as t4 ON t4.school_year_id = t1.school_year_id

        //             WHERE t1.course_id 
        //             IN (" . implode(',', $courses_level_ids) . ") ";

        //             // AND t1.course_id IN ($placeholders)";

        //             $params = array();

        //             $stmt = $con->prepare($sql);
        //             $stmt->execute($params);

        //             if($stmt->rowCount() > 0){
        //                 $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        //                 echo json_encode($results);
        //             }
        //     }
        // }

        // // Semester only
        // if($semester != ""){

        //     $get_sy_from_period = $con->prepare("SELECT school_year_id 

        //     FROM school_year 
        //     WHERE period = :period");

        //     $get_sy_from_period->bindValue(":period", $semester);
        //     $get_sy_from_period->execute();

        //     if($semester != "" && $get_sy_from_period->rowCount() > 0){
        //         while($row = $get_sy_from_period->fetch(PDO::FETCH_ASSOC)){
        //             // echo $row['school_year_id'];
        //             // echo "<br>";
        //             $school_year_ids_periods[] = $row['school_year_id'];
        //         }

        //         $placeholders = implode(',', array_fill(0, count($school_year_ids_periods), '?'));

        //         $sql = "SELECT

        //             t1.course_id, t1.student_id, 
        //             t2.firstname, t2.lastname, t2.student_unique_id,
        //             t3.course_level,  t3.program_section,
        //             t4.term, t4.period

        //             FROM enrollment as t1

        //             LEFT JOIN student as t2 ON t2.student_id = t1.student_id
        //             LEFT JOIN course as t3 ON t3.course_id = t1.course_id
        //             LEFT JOIN school_year as t4 ON t4.school_year_id = t1.school_year_id

        //             WHERE t1.school_year_id 
        //             IN (" . implode(',', $school_year_ids_periods) . ") ";

        //             // AND t1.course_id IN ($placeholders)";

        //             $params = array();

        //             $stmt = $con->prepare($sql);
        //             $stmt->execute($params);

        //             if($stmt->rowCount() > 0){
        //                 $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        //                 echo json_encode($results);
        //             }
        //     }
        // }
        
?>