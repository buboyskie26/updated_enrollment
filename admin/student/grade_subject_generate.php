<?php

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../includes/config.php';

include('../../enrollment/classes/StudentEnroll.php');

use Dompdf\Dompdf;

// Check if the generate_pdf button was clicked
if (isset($_POST['grade_subject_generate'])
    && isset($_POST['student_username'])
    && isset($_POST['student_id'])
    && isset($_POST['enrollment_course_id'])
    && isset($_POST['student_course_level'])
    && isset($_POST['current_school_year_period'])
    
) {

    $FIRST_SEMESTER = "First";
    $GRADE_ELEVEN = 11;

    $userLoggedInId= $_POST['student_id'];
    $username = $_POST['student_username'];
    $enrollment_course_id = $_POST['enrollment_course_id'];
    $student_course_level = $_POST['student_course_level'];
    $current_school_year_period = $_POST['current_school_year_period'];



    $studentEnroll = new StudentEnroll($con);

    $listOfSubjects = $studentEnroll->GetStudentCurriculumBasedOnSemesterSubject(
        $username, $userLoggedInId, $student_course_level, $current_school_year_period);

    $enrollment_school_year = $studentEnroll->GetStudentSectionGradeElevenSchoolYear(
        $username, $userLoggedInId, $student_course_level, $current_school_year_period);

    if($enrollment_school_year !== null){
        $term = $enrollment_school_year['term'];
        $period = $enrollment_school_year['period'];
        $school_year_id = $enrollment_school_year['school_year_id'];
        $enrollment_course_id = $enrollment_school_year['course_id'];
    }

    $enrollment_section_name = $studentEnroll->GetStudentCourseNameByCourseId($enrollment_course_id);
    
    $html = '
        <!DOCTYPE html>
        <html lang="en">

        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Tertiary Course List</title>
            <!-- Bootstrap CSS -->
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css">

            <style>
                *{text-align:center;}
            </style>

        </head>

        <body>
            <h4 style="font-weight: 500;" class="text-primary text-center mb-3">
                Grade 11 '.$enrollment_section_name.' '.$period.' Semester (SY '.$term.')
            </h4>
            <table style="font-size: 12px; border-collapse: collapse;" border="1" cellpadding="10" cellspacing="0" width="100%">
                   <thead>
                        <tr class="text-center"> 
                            <th rowspan="2">Code</th>
                            <th rowspan="2">Type</th>  
                            <th rowspan="2">Description</th>  
                            <th rowspan="2">Unit</th>
                            <th rowspan="2">Prelim</th>
                            <th rowspan="2">Midterm</th>
                            <th rowspan="2">Pre-Final</th>
                            <th rowspan="2">Final</th>
                            <th rowspan="2">Remark</th>
                        </tr>	
                        <tr class="text-center">
                        </tr>
                    </thead>
                <tbody>';

                foreach ($listOfSubjects as $value) {

                    $first = $value['first'];
                    $second = $value['second'];
                    $third = $value['third'];
                    $fourth = $value['fourth'];
                    
                    $grade_remarks = $value['grade_remarks'] != "" ? $value['grade_remarks'] : "Pending";


                    $html .= '
                        <tr class="text-center">
                            <td>'.$value['subject_code'].'</td>
                            <td>'.$value['subject_type'].'</td>
                            <td>'.$value['subject_title'].'</td>
                            <td>'.$value['unit'].'</td>
                            <td>'.$first.'</td>
                            <td>'.$second.'</td>
                            <td>'.$third.'</td>
                            <td>'.$fourth.'</td>
                            <td>'.$grade_remarks.'</td>
                        </tr>';

                }

                $html .= '
                    </tbody>
                </table>

            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
        </body>
        </html>';

    // Create a new Dompdf instance
    $dompdf = new Dompdf();

    // Load the HTML content
    $dompdf->loadHtml($html);

    // (Optional) Set the paper size and orientation
    $dompdf->setPaper('A4', 'portrait');

    // Render the PDF
    $dompdf->render();

    // Output the generated PDF to the browser
    $dompdf->stream('tertiary_course_list.pdf', ['Attachment' => 0]);

    exit; // End the script execution
}
?>
