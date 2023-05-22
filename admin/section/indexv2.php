<?php 
    include('../registrar_enrollment_header.php');
    include('../../enrollment/classes/StudentEnroll.php');
    include('../../enrollment/classes/Schedule.php');
    include('../classes/Course.php');
 
?>

<script src="../assets/js/common.js"></script>
 

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- DataTables -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs4/dt-1.11.3/datatables.min.css"/>
    <script type="text/javascript" src="https://cdn.datatables.net/v/bs4/dt-1.11.3/datatables.min.js"></script>

</head>
 
<body>

    <div class="row col-md-12">
        <div class="col-lg-12">
            <div class="row">
                <div class="col-lg-12 text-center">
                    <h4>Active Enrollment</h4>
                </div>
            </div>
            
        </div>

        <div class="table-responsive" style="margin-top:2%;"> 
            <div class="mb-3">
                <a href="<?php echo directoryPath . "create.php"; ?>">
                    <button class=" btn btn-success">Add Section</button>
                </a> 
            </div>
            <table id="courseTable" class="table table-striped table-bordered table-hover "  style="font-size:13px" cellspacing="0"  > 
                <thead>
                    <tr class="text-center"> 
                        <th rowspan="2">Course Id</th>
                        <th rowspan="2">Program Section</th>
                        <th rowspan="2">Capacity</th>
                        <th rowspan="2">School Year Term</th>  
                    </tr>	
                </thead> 	
                 <tbody></tbody>
            </table>
        </div>
    </div>
</body>
</html>

<script>
$(document).ready(function() {
    $('#courseTable').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "fetchData.php",
            "type": "POST"
        },
        "columns": [
            { "data": "course_id", "title": "Course Id" },
            { "data": "program_section", "title": "Program Section" },
            { "data": "capacity", "title": "Capacity" },
            { "data": "school_year_term", "title": "School Year Term" }
        ]
    });
});

</script>

<script>
  // select all populate buttons
  const populateBtns = document.querySelectorAll('.populate-btn');

  // loop through buttons and trigger click event
  populateBtns.forEach(btn => {
    btn.click();
  });
</script>
