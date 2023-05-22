

//
function deleteMyStudent(button, student_id) {
  $.post('ajax/deleteMyStudent.php?student_id=' + student_id, {
    student_id,
  }).done(function (data) {

     
  });

  // $('#student_id_' + student_id).on('click', function () {
  //   bootbox.confirm('Are you sure', function (result) {
  //     // $.post(
  //     //   'includes/form_handlers/delete_post.php?post_id=<?php echo $post_id ?>',
  //     //   { result: result }
  //     // );

  //     // if (result) {
  //     //   location.reload();
  //     // } else {
  //     //   alert('Alertnerss');
  //     // }
  //   });
  // });
}


function inputGrade(button, student_period_assignment_id) {

  $.post("ajax/inputGrade.php", {student_period_assignment_id}).done(function(data) {

     
  });
}