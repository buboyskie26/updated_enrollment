<?php 

    require_once("../../../includes/config.php");

    require_once("../../../enrollment/classes/StudentEnroll.php");
    require_once("../../../enrollment/classes/Section.php");

    // echo "yehey";

    if(isset($_POST['course_id']) && isset($_POST['school_year_id'])){

        $course_id = $_POST['course_id'];
        $school_year_id = $_POST['school_year_id'];

        $enroll = new StudentEnroll($con);

        $section = new Section($con, $course_id);

        $school_year_obj = $enroll->GetActiveSchoolYearAndSemester();
        $current_school_year_id = $school_year_obj['school_year_id'];
        $current_school_year_term = $school_year_obj['term'];
        $current_school_year_period = $school_year_obj['period'];


        $query_school_year = $con->prepare("SELECT period, term, school_year_id

            FROM school_year
            WHERE school_year_id=:school_year_id
            LIMIT 1
            ");
        $query_school_year->bindValue(":school_year_id", $school_year_id);
        $query_school_year->execute();

        // $data[] = [];
        if($query_school_year->rowCount() > 0){

            $select_row = $query_school_year->fetch(PDO::FETCH_ASSOC);
            $select_period = $select_row['period'];
            $select_school_year_id = $select_row['school_year_id'];
            $select_term = $select_row['term'];

            // echo $select_period;

            $section_name = $section->GetSectionName();
            $section_id = $section->GetSectionId();
            $section_s_y = $section->GetSectionSY();
            $section_level = $section->GetSectionGradeLevel();
            $section_advisery = $section->GetSectionAdvisery();

            $getProgramIdBySectionId = $section->GetProgramIdBySectionId($section_id);
            $section_acronym = $section->GetAcronymByProgramId($getProgramIdBySectionId);

            $totalStudent = $section->GetTotalNumberOfStudentInSection($section_id, 
                $select_school_year_id);

                // echo $totalStudent;
            
            $data[] = array(
                'section_name' => $section_name,
                'section_id' => $section_id,
                'current_school_year_term' => $select_term,
                'current_school_year_period' => $select_period,
                'section_level' => $section_level,
                'section_acronym' => $section_acronym,
                'totalStudent' => $totalStudent,
            );
        }

        


        echo json_encode($data);

    }
?>