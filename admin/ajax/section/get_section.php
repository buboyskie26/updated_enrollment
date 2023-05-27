<?php 

    require_once("../../../includes/config.php");

    require_once("../../../enrollment/classes/StudentEnroll.php");
    require_once("../../../enrollment/classes/Section.php");

    // echo "yehey";

    if(isset($_POST['course_id']) && isset($_POST['school_year_id'])){


        $course_id = $_POST['course_id'];
        $school_year_id = $_POST['school_year_id'];

        $section = new Section($con, $course_id);

        // echo $course_id;
        // echo "<br>";
        // echo $school_year_id;

        $query_school_year = $con->prepare("SELECT period, term FROM school_year
            WHERE school_year_id=:school_year_id
            LIMIT 1
            ");
        $query_school_year->bindValue(":school_year_id", $school_year_id);
        $query_school_year->execute();

        // $data[] = [];
        if($query_school_year->rowCount() > 0){

            $row = $query_school_year->fetch(PDO::FETCH_ASSOC);
            $semester_period = $row['period'];
            $current_term = $row['term'];

            $subject_query = $con->prepare("SELECT 
                t1.subject_id as t1Subject_id,
                t1.subject_code,
                t1.subject_title,
                t1.subject_type,
                t2.*,
                t3.firstname as teacherFirstName,
                t3.lastname as teacherLastName

                FROM subject as t1

                LEFT JOIN subject_schedule as t2 ON t1.subject_id= t2.subject_id
                LEFT JOIN teacher as t3 ON t2.teacher_id=t3.teacher_id

                WHERE t1.course_id=:course_id
                AND t1.semester=:semester
                -- AND t1.program_id=:program_id
                -- AND t1.course_level=:course_level
                ");
        
            $subject_query->bindValue("course_id", $course_id); 
            $subject_query->bindValue("semester", $semester_period); 
            // $subject_query->bindValue("program_id", $program_id); 
            // $subject_query->bindValue("course_level", $GRADE_TWELVE); 
            $subject_query->execute();

            // print_r($row_sub);
            if($subject_query->rowCount() > 0){
                while($row = $subject_query->fetch(PDO::FETCH_ASSOC)) {

                    $totalStudents = $section->GetTotalNumberOfStudentInSection($course_id, $school_year_id);

                    $data[] = array(
                        'current_semester_period' => $semester_period,
                        'current_term' => $current_term,
                        'subject_id' => $row['t1Subject_id'],
                        'subject_title' => $row['subject_title'],
                        'subject_code' => $row['subject_code'],
                        'subject_type' => $row['subject_type'],
                        'total_students' => $totalStudents,
                        
                        'time_from' => $row['time_from'],
                        'time_to' => $row['time_to'],
                        'schedule_day' => $row['schedule_day'],
                        'schedule_time' => $row['schedule_time'],
                        'room' => $row['room'],
                        'teacherFirstName' => $row['teacherFirstName'],
                        'teacherLastName' => $row['teacherLastName']
                    );
                    // $data = array(
                    //     $semester_period,
                    //     $current_term,
                    //     $row['t1Subject_id'],
                    //     $row['subject_title'],
                    //     $row['subject_code'],
                    //     $row['time_from'],
                    //     $row['time_to'],
                    //     $row['schedule_day'],
                    //     $row['schedule_time'],
                    //     $row['room'],
                    //     $row['teacherFirstName'],
                    //     $row['teacherLastName']
                    // );
                }
            }else{
                $data = [];
            }

            echo json_encode($data);
            // echo $data;

        }
    }
?>