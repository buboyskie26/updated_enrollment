<?php

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../includes/config.php';

use Dompdf\Dompdf;

// Check if the generate_pdf button was clicked
if (isset($_POST['generate_pdf'])) {
    
    $SHS_Department_ID = 4;

    // Get the query results
    // Modify the query according to your needs
    $query = $con->prepare("SELECT * FROM program 
        WHERE department_id != :department_id");

    $query->bindValue(":department_id", $SHS_Department_ID);
    $query->execute();

    $results = $query->fetchAll(PDO::FETCH_ASSOC);

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
            <h2 class="text-center">Tertiary Course List</h2>
            <table border="1" cellpadding="10" cellspacing="0" width="100%">
                <thead>
                    <tr class="text-center">
                        <th>Strand</th>
                        <th>1st Year Section</th>
                        <th>Students</th>
                        <th>2nd Year Section</th>
                        <th>Students</th>
                    </tr>
                </thead>
                <tbody>';

    foreach ($results as $row) {
        $acronym = $row['program_name'];
        $grade11Sections = 1;
        $grade11Student = 1;
        $grade12Sections = 3;
        $grade12Student = 7;

        $html .= "
            <tr class='text-center'>
                <td>$acronym</td>
                <td>$grade11Student</td>
                <td>$grade11Student</td>
                <td>$grade12Sections</td>
                <td>$grade12Student</td>
            </tr>";
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
