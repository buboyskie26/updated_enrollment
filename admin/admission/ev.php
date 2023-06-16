<?php  
 
    include('../../enrollment/classes/StudentEnroll.php');
    include('../../enrollment/classes/Enrollment.php');
 

    include('../registrar_enrollment_header.php');

    // require_once __DIR__ . '/../../vendor/autoload.php';
    // use Dompdf\Dompdf;
    // use Dompdf\Options;

    if(!AdminUser::IsRegistrarAuthenticated()){

        header("Location: /dcbt/adminLogin.php");
        exit();
    }

    $enroll = new StudentEnroll($con);

    $school_year_obj = $enroll->GetActiveSchoolYearAndSemester();

    $current_school_year_id = $school_year_obj['school_year_id'];
    $current_school_year_term = $school_year_obj['term'];
    $current_school_year_period = $school_year_obj['period'];

    $enrollment = new Enrollment($con, null);
    $pendingEnrollment = $enrollment->PendingEnrollment();
    $ongoingEnrollment = $enrollment->OngoingEnrollment();

    $pendingEnrollment = $enrollment->PendingEnrollment();
    $waitingPaymentEnrollment = $enrollment->WaitingPaymentEnrollment($current_school_year_id);
    $waitingApprovalEnrollment = $enrollment->WaitingApprovalEnrollment($current_school_year_id);
    $enrolledStudentsEnrollment = $enrollment->EnrolledStudentsWithinSYSemester($current_school_year_id);


    $pendingEnrollmentCount = count($pendingEnrollment);
    $waitingPaymentEnrollmentCount = count($waitingPaymentEnrollment);
    $waitingApprovalEnrollmentCount = count($waitingApprovalEnrollment);
    $enrolledStudentsEnrollmentCount = count($enrolledStudentsEnrollment);

?>

    <div class="content">
      <div class="back-menu">
        <button type="button" class="admission-btn" onclick="admission()">
          <i class="bi bi-arrow-left-circle"></i> Admission
        </button>
      </div>

      <div class="head">
        <h3>Enrollment form finder (SHS)</h3>
        <p>Note: Numbers on tabs only count current school year and semester</p>
      </div>

      <div class="choices">
        <div class="evaluation">
          <button
            type="button"
            class="selection-btn"
            id="evaluation"
            onclick="evaluation_btn()"
          >
            Evaluation
            (<?php echo $pendingEnrollmentCount;?>)
          </button>
        </div>
        <div class="waiting-payment">
          <button
            type="button"
            class="selection-btn"
            id="waiting-payment"
            onclick="waiting_payment_btn()"
          >
            Waiting payment
            (<?php echo $waitingPaymentEnrollmentCount;?>)
          </button>
        </div>
        <div class="waiting-approval">
          <button
            type="button"
            class="selection-btn"
            id="waiting-approval"
            onclick="waiting_approval_btn()"
          >
            Waiting approval
            (<?php echo $waitingApprovalEnrollmentCount;?>)
          </button>
        </div>
        <div class="enrolled">
          <button
            type="button"
            class="selection-btn"
            id="enrolled"
            onclick="enrolled_btn()"
          >
            Enrolled
          </button>
        </div>
      </div>

      <?php
        if(count($pendingEnrollment) > 0){
            ?>
            <div class="bg-content">
              <div class="content-box" id="shs-evaluation">
                <table>
                  <tr>
                    <th style="border-right: 2px solid black">Search by</th>
                    <td>Name</td>
                    <th>Email</th>
                    <th>Student ID</th>
                  </tr>
                </table>

                <div class="search-bar">
                  <i class="bi bi-search icon"></i>
                  <input type="text" class="search-field" />
                  <button type="button" class="search-btn">Search</button>
                </div>

                <div class="form-header">
                  <div class="header-content">
                    <h3>Form details</h3>

                    <div class="dropdown">
                      <div class="dropdown-toggle" onclick="toggleDropdown()">
                        <i class="bi bi-three-dots-vertical"></i>
                      </div>
                      <ul class="dropdown-menu">
                        <li>
                          <button
                            type="button"
                            class="action"
                            onclick="delete_form()"
                          >
                            <i class="bi bi-file-x"></i>Delete form
                          </button>
                        </li>
                      </ul>
                    </div>

                    <div class="select-buttons">
                      <button type="button" class="select-all-btn">Select all</button>
                      <button type="button" class="unselect-all-btn">
                        Un-select all
                      </button>
                    </div>
                  </div>

                  <div class="table-content">
                    <table>
                      <tr>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Strand</th>
                        <th>Date Submitted</th>
                        <th>Status</th>
                        <th>Action</th>
                      </tr>
                    </table>

                    <table>
                      <?php
                          $sql = $con->prepare("SELECT t1.*, t2.acronym FROM pending_enrollees as t1
                          
                              LEFT JOIN program as t2 ON t2.program_id = t1.program_id
                              WHERE t1.student_status !='APPROVED'
                              AND t1.is_finished = 1");
                          $sql->execute();

                          if(count($pendingEnrollment) > 0){
                              foreach($pendingEnrollment as $key =>$row){
                                  $fullname = $row['firstname'] . " " . $row['lastname'];
                                  $date_creation = $row['date_creation'];
                                  $acronym = $row['acronym'];
                                  $pending_enrollees_id = $row['pending_enrollees_id'];
                                  $student_unique_id = "N/A";

                                  $type = "";
                                  $url = "";
                                  $status = "Evaluation";
                                  $button_output = "";
                                  $process_url = "process_enrollment.php?step1=true&id=$pending_enrollees_id";

                                  if($row['student_status'] == "Regular"){
                                      $type = "New Regular";
                                      // $url = "../enrollees/view_student_new_enrollment.php?id=$pending_enrollees_id";
                                      $button_output = "
                                          <a href='$process_url'>
                                              <button type='button' class='action-btn' onclick='view_new()' >View</button>
                                          </a>";
                                  } else if($row['student_status'] == "Transferee"){
                                      $type = "New Transferee";
                                      // $url = "../enrollees/view_student_transferee_enrollment.php?id=$pending_enrollees_id";
                                      $url_trans = "transferee_process_enrollment.php?step1=true&id=$pending_enrollees_id";
                                      $button_output = "
                                          <a href='$url_trans'>
                                              <button type='button' class='action-btn' onclick='view_transferee()' >View</button>
                                          </a>";
                                  }

                                  echo "
                                      <tr>
                                          <td>$fullname</td>
                                          <td>$type</td>
                                          <td>$acronym</td>
                                          <td>$date_creation</td>
                                          <td>$status</td>
                                          <td>$button_output</td>
                                      </tr>";
                              }
                          }
                      ?>
                    </table>
                  </div>
                </div>
              </div>
            </div>
        <?php
        }else{
            echo "
                <div class='col-md-12 row'>
                    <h3 class='text-info text-center'>No Data found For Pending Enrollees.</h3>
                    <hr>
                    <hr>
                </div>";
        }

        if(count($ongoingEnrollment) > 0){
            ?>
            <div class="table-content">
                <table>
                    <tr>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Strand</th>
                        <th>Date Submitted</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </table>
                <table>
                    <?php
                        $sql = $con->prepare("SELECT t1.*, t2.acronym FROM pending_enrollees as t1
                            LEFT JOIN program as t2 ON t2.program_id = t1.program_id
                            WHERE t1.student_status !='APPROVED'
                            AND t1.is_finished = 1");
                        $sql->execute();

                        if(count($ongoingEnrollment) > 0){
                            foreach($ongoingEnrollment as $key => $row){
                                $fullname = $row['firstname'] . " " . $row['lastname'];
                                $enrollment_date = $row['enrollment_date'];
                                $student_id = $row['student_id'];
                                $student_course_id = $row['course_id'];

                                $admission_status = $row['admission_status'];
                                $student_statusv2 = $row['student_statusv2'];

                                $username = $row['username'];

                                $acronym = $row['acronym'];
                                // $pending_enrollees_id = $row['pending_enrollees_id'];
                                $student_unique_id = "N/A";

                                $type = "O.S $admission_status (SHS $student_statusv2)";
                                $url = "";
                                $status = "Evaluation";
                                $button_output = "";

                                // $process_url = "process_enrollment.php?step1=true&id=$pending_enrollees_id";
                                $process_url = "";
                                $trans_url = "transferee_process_enrollment.php?step3=true&st_id=$student_id&selected_course_id=$student_course_id";

                                $regular_insertion_url = "../enrollees/subject_insertion.php?username=$username&id=$student_id";

                                $evaluateBtn = "";

                                if($admission_status == "Transferee" && $student_statusv2 == "Regular"){
                                    $evaluationBtn = "
                                        <a href='$regular_insertion_url'>
                                            <button class='btn btn-outline-success btn-sm'>
                                                Evaluate
                                            </button>
                                        </a>";
                                }

                                echo "
                                    <tr>
                                        <td>$fullname</td>
                                        <td>$type</td>
                                        <td>$acronym</td>
                                        <td>$date_creation</td>
                                        <td>$status</td>
                                        <td>$button_output</td>
                                    </tr>";
                            }
                        }
                    ?>
                </table>
            </div>
        <?php
        }else{
            echo "
                <div class='col-md-12 row'>
                    <h3 class='text-info text-center'>No Ongoing Enrollees to be evaluated.</h3>
                </div>";
        }
      ?>
    </div>

    <script>
      function admission() {
        window.location.href = "/DCBT-2/admission-registrar/Admission.html";
      }

      function view_new() {
        window.location.href =
          "/DCBT-2/admission-registrar/admission-new/Admission-new.html";
      }

      function view_old() {
        window.location.href =
          "/DCBT-2/admission-registrar/admission-old/Admission-old.html";
      }

      function view_conflict() {
        window.location.href =
          "/DCBT-2/admission-registrar/admission-conflict/Admission-conflict.html";
      }

      function view_returnee() {
        window.location.href =
          "/DCBT-2/admission-registrar/admission-returnee/Admission-returnee.html";
      }

      function view_transferee() {
        window.location.href =
          "/DCBT-2/admission-registrar/admission-transferee/Admission-transferee.html";
      }
    </script>
    <script>
      function toggleDropdown() {
        var dropdownMenu = document.querySelector(".dropdown-menu");
        dropdownMenu.style.display =
          dropdownMenu.style.display === "block" ? "none" : "block";
      }
    </script>
    <script>
      var evaluation = document.getElementById("evaluation");
      var waitingPayment = document.getElementById("waiting-payment");
      var waitingApproval = document.getElementById("waiting-approval");
      var enrolled = document.getElementById("enrolled");

      var shsEvaluation = document.getElementById("shs-evaluation");
      var shsWaitingP = document.getElementById("shs-waiting-payment");
      var shsWaitingA = document.getElementById("shs-waiting-approval");
      var shsEnrolled = document.getElementById("shs-enrolled");

      function evaluation_btn() {
        evaluation.style.background = "#EFEFEF";
        evaluation.style.color = "black";
        waitingPayment.style.background = "#02001C";
        waitingPayment.style.color = "white";
        waitingApproval.style.background = "#02001C";
        waitingApproval.style.color = "white";
        enrolled.style.background = "#02001C";
        enrolled.style.color = "white";

        shsEvaluation.style.display = "flex";
        shsWaitingP.style.display = "none";
        shsWaitingA.style.display = "none";
        shsEnrolled.style.display = "none";
      }

      function waiting_payment_btn() {
        evaluation.style.background = "#02001C";
        evaluation.style.color = "white";
        waitingPayment.style.background = "#EFEFEF";
        waitingPayment.style.color = "black";
        waitingApproval.style.background = "#02001C";
        waitingApproval.style.color = "white";
        enrolled.style.background = "#02001C";
        enrolled.style.color = "white";

        shsEvaluation.style.display = "none";
        shsWaitingP.style.display = "flex";
        shsWaitingA.style.display = "none";
        shsEnrolled.style.display = "none";
      }

      function waiting_approval_btn() {
        evaluation.style.background = "#02001C";
        evaluation.style.color = "white";
        waitingPayment.style.background = "#02001C";
        waitingPayment.style.color = "white";
        waitingApproval.style.background = "#EFEFEF";
        waitingApproval.style.color = "black";
        enrolled.style.background = "#02001C";
        enrolled.style.color = "white";

        shsEvaluation.style.display = "none";
        shsWaitingP.style.display = "none";
        shsWaitingA.style.display = "flex";
        shsEnrolled.style.display = "none";
      }

      function enrolled_btn() {
        evaluation.style.background = "#02001C";
        evaluation.style.color = "white";
        waitingPayment.style.background = "#02001C";
        waitingPayment.style.color = "white";
        waitingApproval.style.background = "#02001C";
        waitingApproval.style.color = "white";
        enrolled.style.background = "#EFEFEF";
        enrolled.style.color = "black";

        shsEvaluation.style.display = "none";
        shsWaitingP.style.display = "none";
        shsWaitingA.style.display = "none";
        shsEnrolled.style.display = "flex";
      }
    </script>
