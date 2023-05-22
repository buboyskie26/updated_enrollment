<?php

    class SectionTertiary{

        private $con, $course_tertiary_id, $sqlData;


        public function __construct($con, $course_tertiary_id){
            $this->con = $con;
            $this->course_tertiary_id = $course_tertiary_id;

            $query = $this->con->prepare("SELECT * FROM course_tertiary
                 WHERE course_tertiary_id=:course_tertiary_id");

            $query->bindValue(":course_tertiary_id", $course_tertiary_id);
            $query->execute();

            $this->sqlData = $query->fetch(PDO::FETCH_ASSOC);
        }

        public function GetSectionName() {
            return isset($this->sqlData['program_section']) ? $this->sqlData["program_section"] : ""; 
        }
        public function GetSectionGradeLevel() {
            return isset($this->sqlData['course_level']) ? $this->sqlData["course_level"] : ""; 
        }
        public function GetCourseTertiaryTerm() {
            return isset($this->sqlData['school_year_term']) ? $this->sqlData["school_year_term"] : ""; 
        }
        public function GetCourseTertiaryId() {
            return isset($this->sqlData['course_tertiary_id']) ? $this->sqlData["course_tertiary_id"] : null; 
        }

        public function GetMoveUpTertiarySection($course_tertiary_id) {


            $query = $this->con->prepare("SELECT course_tertiary_id, course_level FROM course_tertiary
                 WHERE prev_course_tertiary_id=:prev_course_tertiary_id");

            $query->bindValue(":prev_course_tertiary_id", $course_tertiary_id);
            $query->execute();
            if($query->rowCount() > 0){
                // $row = $query->fetch(PDO::FETCH_ASSOC);

                return $query->fetch(PDO::FETCH_ASSOC);
            }
            return null;
        }

        public function IrregularStudentSectionList($student_id, $student_program_id){

            $sql = $this->con->prepare("SELECT 
            
                t1.program_section, t1.course_tertiary_id,
                t1.course_level,
                t1.school_year_term,
                t2.course_tertiary_id as studentCourseTertiaryId
                
                FROM course_tertiary as t1

                LEFT JOIN student as t2 ON t2.course_tertiary_id = t1.course_tertiary_id
                AND t2.student_id=:student_id
                WHERE t1.program_id=:program_id
                AND t1.active='yes'
                -- AND t1.active='yes'

                ");
            
            $sql->bindValue(":program_id", $student_program_id);
            $sql->bindValue(":student_id", $student_id);
            $sql->execute();

            if($sql->rowCount() > 0){

                $html = "<div class='form-group'>
                    <label style='font-weight: bold;' class='text-center mb-2'>Select Section</label>
                    <select style='width: 35%;' class='mb-3 form-control' name='course_tertiary_id' id='create_course_tertiary_id'>
                    ";

                while($row = $sql->fetch(PDO::FETCH_ASSOC)){

                    $studentCourseTertiaryId = $row['studentCourseTertiaryId'];

                    $selected = ($studentCourseTertiaryId == $row['course_tertiary_id']) ? "selected" : "";

                    $html .= "
                            <option value='".$row['course_tertiary_id']."' $selected>".$row['program_section']." (".$row['course_level']." Year S.Y ".$row['school_year_term'].")</option>
                        ";
                }

                $html .= "</select>
                        <input type='hidden' id='student_id_dropdown'name='student_id_dropdown' value='".$student_id."' />
                        </div>";

                return $html;
            }
            return "";

        }


        // public function StudentTertiaryStatusDropdown($student_id, $student_program_id){

        //     $sql = $this->con->prepare("SELECT 
            
        //         t1.program_section, t1.course_tertiary_id,
        //         t1.course_level,
        //         t1.school_year_term,
        //         t2.course_tertiary_id as studentCourseTertiaryId
                
        //         FROM course_tertiary as t1

        //         LEFT JOIN student as t2 ON t2.course_tertiary_id = t1.course_tertiary_id
        //         AND t2.student_id=:student_id
        //         WHERE t1.program_id=:program_id
        //         AND t1.active='yes'
        //         -- AND t1.active='yes'

        //         ");
            
        //     $sql->bindValue(":program_id", $student_program_id);
        //     $sql->bindValue(":student_id", $student_id);
        //     $sql->execute();

        //     if($sql->rowCount() > 0){

        //         $html = "<div class='form-group'>
        //             <label style='font-weight: bold;' class='text-center mb-2'>Select Section</label>
        //             <select style='width: 35%;' class='mb-3 form-control' name='course_tertiary_id' id='create_course_tertiary_id'>
        //             ";

        //         while($row = $sql->fetch(PDO::FETCH_ASSOC)){

        //             $studentCourseTertiaryId = $row['studentCourseTertiaryId'];

        //             $selected = ($studentCourseTertiaryId == $row['course_tertiary_id']) ? "selected" : "";

        //             $html .= "
        //                     <option value='".$row['course_tertiary_id']."' $selected>".$row['program_section']." (".$row['course_level']." Year S.Y ".$row['school_year_term'].")</option>
        //                 ";
        //         }

        //         $html .= "</select>
        //                 <input type='hidden' id='student_id_dropdown'name='student_id_dropdown' value='".$student_id."' />
        //                 </div>";

        //         return $html;
        //     }
        //     return "";

        // }

    }

?>