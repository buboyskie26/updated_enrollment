<?php

class ScheduleX{

    private $con, $adminLoggedInObj;

    public function __construct($con, $adminLoggedInObj)
    {
        $this->con = $con;
        $this->adminLoggedInObj = $adminLoggedInObj;
    }

    public function createScheduleList(){

        $dateString1 = '2023-03-12 07:30:00';
        $dateString2 = '2023-03-12 07:15:00';

        $dateTime1 = new DateTime($dateString1);
        $dateTime2 = new DateTime($dateString2);

        // $time1 = $dateTime1->format('H:i');
        // $time2 = $dateTime2->format('H:i');

        $time1 =  "16:50";
        $time2 =  "04:10";

        if ($time2 > $time1) {
            echo "The time 2 is greater than time 1.";
        } elseif ($time1 > $time2) {
            echo "The time 1 is greater than time 2.";
        } else {
            echo "The time of datetime 1 is equal to the time of datetime 2.";
        }

       

        $query = $this->con->prepare("SELECT * FROM teacher_schedule");

        $query->execute();

        if($query->rowCount() > 0){
            $table = "
                <div style='text-align: end; margin-right: 46px;'>
                        <button type='button' id='add_schedule_btn' class='btn btn-sm btn-primary'>Add Schedule</button>
                </div>
                <table class='table table-hover' id='scheduleTable'>
                    <thead >
                        <tr class='text-center'>
                            <th>Name</th>
                            <th>Course Subject</th>
                            <th>Room</th>
                            <th>Day</th>
                            <th>Start</th>
                            <th>End</th>
                            <th></th>
                        </tr>
                    </thead>
            ";
            while($row = $query->fetch(PDO::FETCH_ASSOC)){

                $table .= $this->GenerateScheduleBody($row);
            }
            $table .= "
                </table>
            ";
            return $table;
        }
        return null;
    }
    private function GenerateScheduleBody($row){

        $teacher_course_id = $row['teacher_course_id'];
        $teacher_id = $row['teacher_id'];
        $room_number = $row['room_number'];


        $start_time = $row['start_hour'];
        $end_time = $row['end_hour'];
        $day = $row['day'];

        $teacher = new Teacher($this->con, $teacher_id);

        $teacherName = "";


        $sql1 = $this->con->prepare("SELECT * FROM teacher_course as t1
            INNER JOIN course as t2
            ON t1.course_id = t2.course_id
            INNER JOIN subject as t3
            ON t1.subject_id = t3.subject_id

            WHERE teacher_course_id=:teacher_course_id
            LIMIT 1");

        $sql1->bindValue(":teacher_course_id", $teacher_course_id);
        $sql1->execute();

        $course_subject_name = "";

        if($sql1->rowCount() > 0){
            $row = $sql1->fetch(PDO::FETCH_ASSOC);
            $course_name = $row['course_name'];
            $subject_title = $row['subject_title'];
        }

        return "
            <tbody>
                <tr class='text-center'>
                    <td>$teacherName</td>
                    <td>$course_name $subject_title ($teacher_course_id)</td>
                    <td>$room_number</td>
                    <td>$day</td>
                    <td>$start_time</td>
                    <td>$end_time</td>
                </tr>
            </tbody>
        ";
    }


    public function createForm(){

        $createTeacherDropdown = $this->createTeacherDropdown();

        $time = '13:45:30';
        $date = DateTime::createFromFormat('H:i:s', $time);
        echo $date->format('H:i:s');


        return "
            <form action='process-form.php' method='POST'>
                $createTeacherDropdown
            
                <div class='form-group'>
                    <label>Start Date:</label>
                    <input type='date' class='form-control' id='start_date' name='start_date' required>
                </div>

                <div class='form-group'>
                    <label>End Date:</label>
                    <input type='date' class='form-control' id='end_date' name='end_date' required>
                </div>

                <input type='hidden' name='teacher_course_id' value='123'>

                <input type='hidden' name='teacher_id' value='456'>

                <button type='submit' class='btn btn-primary'>Submit</button>
            </form>
        
        ";
    }

    private function createTeacherDropdown(){

        $query = $this->con->prepare("SELECT * FROM teacher");
        $query->execute();

        $html = "<div class='form-group'>
                <select class='form-control' name='teacher_id'>";

        while($row = $query->fetch(PDO::FETCH_ASSOC)){
            $html .= "
                <option value='".$row['teacher_id']."'>".$row['firstname']." ".$row['lastname']."</option>
            ";
        }

        $html .= "
                </select>
            </div>
        ";

        return $html;
    }


    public function ScheduleSubjectsDropdown($school_year_id){

        $query = $this->con->prepare("SELECT 
            t2.subject_title, t2.subject_id
        FROM course as t1

            INNER JOIN subject as t2 ON t2.subject_id = t1.subject_id
            WHERE t1.school_year_id=:school_year_id");

        $query->bindValue(":school_year_id", $school_year_id);
        $query->execute();

        $html = "<div class='form-group'>
            <select class='form-control' name='COURSE'>";
 
        if($query->rowCount() > 0){
            while($row = $query->fetch(PDO::FETCH_ASSOC)){
                $html .= "
                    <option value='".$row['subject_schedule_id']."'>".$row['subject_title']."</option>
                ";
            }
        }
        
        $html .= "</select>
                </div>";

        return $html;
    }
}

?>