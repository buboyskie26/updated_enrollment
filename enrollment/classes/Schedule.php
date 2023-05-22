<?php

    class Schedule{

        private $con, $userLoggedIn, $sqlData, $studentEnroll;


        public function __construct($con, $studentEnroll){
            $this->con = $con;
            $this->studentEnroll = $studentEnroll;
        }

        public function GetDayFullName($dayAcronym) {
            switch($dayAcronym) {
                case 'M':
                    return 'Monday';
                case 'T':
                    return 'Tuesday';
                case 'W':
                    return 'Wednesday';
                case 'TH':
                    return 'Thursday';
                case 'F':
                    return 'Friday';
                case 'SA':
                    return 'Saturday';
                case 'SU':
                    return 'Sunday';
                default:
                    return 'TBA';
            }
        }

        public function GetSectionScheduleGradeElevenFirst($username, $student_id,
            $GRADE_ELEVEN, $SEMESTER){

            $course_id = $this->studentEnroll->GetStudentCourseId($username);
            $program_id = $this->studentEnroll->GetStudentProgramId($course_id);
            $arr = [];

            echo $student_id;
            echo "<br>";
            // TODO: GRADE 11 HUMSS-101 1st Semester (Done)
            // TODO: Subject -> SChedule
    
            // $first_sem = "First";

            $query = $this->con->prepare("SELECT 
                e.course_id, sy.school_year_id, sy.period 
                FROM enrollment e
                INNER JOIN school_year sy ON e.school_year_id = sy.school_year_id
                INNER JOIN course c ON c.course_id = e.course_id

                WHERE e.student_id = :student_id
                AND e.enrollment_status=:enrollment_status
                AND sy.period =:first_sem
                -- AND c.course_id =:course_id
                ");

            $query->bindValue("student_id", $student_id); 
            $query->bindValue("first_sem", $SEMESTER); 
            $query->bindValue("enrollment_status", "enrolled"); 
            // $query->bindValue("course_id", 20); 
            $query->execute(); 

            // $course_id = 0;

            if($query->rowCount() > 0){
                $result = $query->fetch(PDO::FETCH_ASSOC);
                // $course_id = $result['course_id'];
                // print_r($result);
                echo "<br>";
            }


            $subject_query = $this->con->prepare("SELECT 
                t1.subject_id as t1Subject_id,
                t1.subject_code,
                t1.subject_title,
                t2.*
                FROM subject as t1

                LEFT JOIN subject_schedule as t2 ON t1.subject_id=t2.subject_id

                WHERE t1.course_id=:course_id
                AND t1.semester=:semester
                AND t1.program_id=:program_id
                AND t1.course_level=:course_level
                ");
            
            $subject_query->bindValue("course_id", $course_id); 
            $subject_query->bindValue("semester", $SEMESTER); 
            $subject_query->bindValue("program_id", $program_id); 
            $subject_query->bindValue("course_level", $GRADE_ELEVEN); 
            $subject_query->execute();

            // print_r($row_sub);
            if($subject_query->rowCount() > 0){
                $row_sub = $subject_query->fetchAll(PDO::FETCH_ASSOC);

                return $row_sub;
            }

            return null;
        }

        public function GetSectionScheduleByCourseId($course_id,$SEMESTER){

            // $course_id = $this->studentEnroll->GetStudentCourseId($username);
            // $program_id = $this->studentEnroll->GetStudentProgramId($course_id);


            $subject_query = $this->con->prepare("SELECT 
                t1.subject_id as t1Subject_id,
                t1.subject_code,
                t1.subject_title,
                t2.*
                FROM subject as t1

                LEFT JOIN subject_schedule as t2 ON t1.subject_id=t2.subject_id

                WHERE t1.course_id=:course_id
                AND t1.semester=:semester
                -- AND t1.program_id=:program_id
                -- AND t1.course_level=:course_level
                ");
            
            $subject_query->bindValue("course_id", $course_id); 
            $subject_query->bindValue("semester", $SEMESTER); 
            // $subject_query->bindValue("program_id", $program_id); 
            // $subject_query->bindValue("course_level", $GRADE_TWELVE); 
            $subject_query->execute();

            // print_r($row_sub);
            if($subject_query->rowCount() > 0){
                $row_sub = $subject_query->fetchAll(PDO::FETCH_ASSOC);
                // print_r($row_sub);
                return $row_sub;
            }

            return null;
        }
        public function GetSectionScheduleGradeTwelveFirst($username, $student_id,
            $GRADE_TWELVE, $SEMESTER){

            $course_id = $this->studentEnroll->GetStudentCourseId($username);
            $program_id = $this->studentEnroll->GetStudentProgramId($course_id);

            $subject_query = $this->con->prepare("SELECT 
                t1.subject_id as t1Subject_id,
                t1.subject_code,
                t1.subject_title,
                t2.*
                FROM subject as t1

                LEFT JOIN subject_schedule as t2 ON t1.subject_id=t2.subject_id

                WHERE t1.course_id=:course_id
                AND t1.semester=:semester
                AND t1.program_id=:program_id
                AND t1.course_level=:course_level
                ");
            
            $subject_query->bindValue("course_id", $course_id); 
            $subject_query->bindValue("semester", $SEMESTER); 
            $subject_query->bindValue("program_id", $program_id); 
            $subject_query->bindValue("course_level", $GRADE_TWELVE); 
            $subject_query->execute();

            // print_r($row_sub);
            if($subject_query->rowCount() > 0){
                $row_sub = $subject_query->fetchAll(PDO::FETCH_ASSOC);
                // print_r($row_sub);
                return $row_sub;
            }

            return null;
        }
        public function GetSectionScheduleGradeElevenFirstByCourseId(
            $course_id, $semester){

            $query = $this->con->prepare("SELECT course_level
                FROM course 
                WHERE course_id=:course_id
                AND active=:active

                ");

            $query->bindValue(":course_id", $course_id);
            $query->bindValue(":active", "yes");
            $query->execute();

            if($query->rowCount() > 0){
                $course_level = $query->fetchColumn();

                if($course_level == 11){

                    $query = "SELECT * FROM mytable ORDER BY CASE schedule_day
                        WHEN 'M' THEN 1
                        WHEN 'T' THEN 2
                        ELSE 3
                        END";
                    // 1st sem
                    $query_subject = $this->con->prepare("SELECT 

                        t1.subject_id,
                        t1.subject_code,
                        t1.subject_title,
                        t2.room,
                        t2.schedule_day,
                        t2.schedule_time

                        FROM subject as t1

                        LEFT JOIN subject_schedule as t2 ON t1.subject_id = t2.subject_id

                        WHERE t1.course_id=:course_id
                        AND t1.course_level=:course_level
                        AND t1.semester=:first_semester

                        -- ORDER BY DAYOFWEEK, M as 1, T as 2
                        ORDER BY CASE t2.schedule_day
                        WHEN 'M' THEN 1
                        WHEN 'T' THEN 2
                        WHEN 'W' THEN 3
                        WHEN 'TH' THEN 3
                        WHEN 'F' THEN 3
                        END
                        -- OR t1.semester=:second_semester
                        -- AND t1.course_id=:course_id
                        -- AND t1.course_level=:course_level

                        ");

                    $query_subject->bindValue(":course_id", $course_id);
                    $query_subject->bindValue(":course_level", 11);
                    $query_subject->bindValue(":first_semester", $semester);
                    // $query_subject->bindValue(":second_semester", "Second");
                    $query_subject->execute();
                    
                    if($query_subject->rowCount() > 0){

                        $subject_result = $query_subject->fetchAll(PDO::FETCH_ASSOC);
                        // print_r($subject_result);

                        return $subject_result;
                    }
                
                }
                else if($course_level == 112){
                    // 1st sem

                    // 2nd sem
                }
            }
            return null;
        }

        public function GetSectionSchoolYearByCourseId($course_id, $period){

            $query_course = $this->con->prepare("SELECT school_year_term
                FROM course 
                WHERE course_id=:course_id
                LIMIT 1");

            $query_course->bindValue(":course_id", $course_id);
            $query_course->execute();

            if($query_course->rowCount() > 0){

                $school_year_term = $query_course->fetchColumn();

                $query = $this->con->prepare("SELECT 
                    school_year_id, term, period
                    FROM school_year 
                    WHERE term=:term
                    AND period=:period
                    
                    LIMIT 1");

                $query->bindValue(":term", $school_year_term);
                $query->bindValue(":period", $period);
                $query->execute();

                if($query->rowCount() > 0){
                    $obj = $query->fetch(PDO::FETCH_ASSOC);
                    return $obj;
                }
            }
            return null;
        }


    public function GetStudentSectionGradeTwelveSchoolYear($course_id,
        // $student_id,
        $GRADE_TWELVE, $SEMESTER){

        // $course_id = $this->studentEnroll->GetStudentCourseId($username);
        // $program_id = $this->studentEnroll->GetStudentProgramId($course_id);
    
                // echo "wee";

        // TODO: GRADE 11 HUMSS-101 1st Semester (Done)
        // TODO: Subject -> Schedule 
 
        $first_sem = "First";
        $enrollment_status = "enrolled";

        $query = $this->con->prepare("SELECT e.enrollment_id,
            e.student_id, e.course_id, sy.school_year_id, sy.period, sy.term
            FROM enrollment e

            INNER JOIN school_year sy ON e.school_year_id = sy.school_year_id
            INNER JOIN course c ON c.course_id = e.course_id
            
            WHERE e.course_id = :course_id
            AND e.enrollment_status=:enrollment_status
            AND sy.period =:first_sem

            AND c.course_id IN (
                SELECT course_id FROM course WHERE course_level = $GRADE_TWELVE
            )
            LIMIT 1
        ");

        $query->bindValue("course_id", $course_id); 
        $query->bindValue("first_sem", $SEMESTER); 
        $query->bindValue("enrollment_status", $enrollment_status); 
        $query->execute(); 

        if($query->rowCount() > 0){
            $result = $query->fetch(PDO::FETCH_ASSOC);
            // print_r($result);
            return $result;
        }

        return null;
    }

    public function GetTeacherSubjectLoad($teacher_id){

        $sql = $this->con->prepare("SELECT subject_schedule_id
                FROM subject_schedule  
                INNER JOIN teacher ON teacher.teacher_id = subject_schedule.teacher_id
                WHERE subject_schedule.teacher_id=:teeacher_id
        ");
        $sql->bindValue(":teeacher_id", $teacher_id);
        $sql->execute();

        return $sql->rowCount();
    }

    public function InsertSubjectSchedule($room,$schedule_day, 
        $time_from, $time_to, $schedule_time, $school_year_id,
        $course_id, $subject_id, $teacher_id){

        $isExecuted = false;

        $sql = $this->con->prepare("INSERT INTO subject_schedule
            (room, schedule_day, time_from, time_to, schedule_time, 
                school_year_id, course_id, subject_id, teacher_id)

            VALUES(:room, :schedule_day, :time_from, :time_to, :schedule_time,
                :school_year_id, :course_id, :subject_id, :teacher_id)");
        
        // $schedule_time = $time_from . ' '. $time_from_am_pm . ' - ' . $time_to. ' ' . $time_to_am_pm;

        $sql->bindValue(":room", $room);
        $sql->bindValue(":schedule_day", $schedule_day);
        $sql->bindValue(":time_from", $time_from);
        $sql->bindValue(":time_to", $time_to);
        $sql->bindValue(":schedule_time", $schedule_time);
        $sql->bindValue(":school_year_id", $school_year_id);
        $sql->bindValue(":course_id", $course_id);
        $sql->bindValue(":subject_id", $subject_id);
        $sql->bindValue(":teacher_id", $teacher_id); 

        if($sql->execute() && $sql->rowCount() > 0){
           $isExecuted = true;
        }

        return $isExecuted;;
    }


    public function CheckConflictedSchedule($schedule_day, $time_from,
        $time_to, $course_id){


        # Check confliction of subject time within the day

        # MONDAY

        // First Schedule

        #1. 8:00 - 10:00 
        // Second Schedule, but this should be conflicted
        #2. 10:00 - 11:00 
            
        $isConflicted = false; // Variable name updated for clarity


        // $sql = $this->con->prepare("SELECT * FROM subject_schedule

        //     WHERE schedule_day = :schedule_day
        //     AND (
        //         (time_from <= :time_from AND time_to >= :time_from)
        //         OR
        //         (time_from >= :time_from AND time_from <= :time_to)
        //         OR
        //         (time_from <= :time_to AND time_to >= :time_to)
        //     )
        // ");

        # DATA.

        # 8:00 - 9:30
        # 12:00 - 12:30	

        # 7:58 - 7:59 INSERTED 
        # 8:30 - 9:00 CORRECT CONFLICT.

        # 7:00 - 13:40 FAILED

        // $sql = $this->con->prepare("SELECT time_to, time_from FROM subject_schedule
        //     WHERE schedule_day = :schedule_day
        //     AND (
        //         (time_from <= :time_from AND time_to >= :time_from)
        //         OR
        //         (time_from >= :time_from AND time_from <= :time_to)
        //         -- OR
        //         -- (time_from <= :time_to AND time_to >= :time_to)
        //         -- OR
        //         -- (time_from <= :time_from AND time_to >= :time_to)
        //         -- OR
        //         -- (time_from >= :time_from AND time_to <= :time_to)
        //     )
        // ");

        // if($time_to > $time_from){
        //     echo "time_to > time_from";

        // }

        // echo $time_from;
        // echo "<br>";
        // echo $time_to;
        $time_from_minutes = strtotime($time_from);
        $time_to_minutes = strtotime($time_to);

        if ($time_from_minutes > $time_to_minutes) {
            // $time_from is greater than $time_to
            echo "Invalid time range";
            $isConflicted = true;
        } else {
            // $time_from is less than or equal to $time_to
            // echo "Valid time range";
        }

        $sql = $this->con->prepare("SELECT * FROM subject_schedule

            WHERE schedule_day = :schedule_day
            AND course_id = :course_id
            AND (
                (time_from <= :time_from AND time_to >= :time_from)
                OR
                (time_from >= :time_from AND time_from <= :time_to)
                OR
                (time_from <= :time_to AND time_to >= :time_to)
            )
        ");

        $sql->bindValue(":schedule_day", $schedule_day);
        $sql->bindValue(":course_id", $course_id);

        $sql->bindValue(":time_from", $time_from);
        $sql->bindValue(":time_to", $time_to);
        $sql->execute();

        if ($sql->rowCount() > 0) {

            $row = $sql->fetch(PDO::FETCH_ASSOC);

            $row_time_from = $row['time_from'];
            $row_time_to = $row['time_to'];

            // Conflicts exist for the provided schedule
            echo "There is a schedule conflict between $row_time_from - $row_time_to.";

            $isConflicted = true;
        } else {
            // No conflicts, the schedule can be added
            echo "No conflicts found. The schedule can be added.";
            $isConflicted = false;
        }
        return $isConflicted;
    }
    public function RemoveSchedule($subject_schedule_id){

        $delete = $this->con->prepare("DELETE FROM subject_schedule
            WHERE subject_schedule_id=:subject_schedule_id");
        
        $delete->bindValue(":subject_schedule_id", $subject_schedule_id);
        return $delete->execute();
    }
}
?>