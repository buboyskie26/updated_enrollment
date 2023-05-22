$(document).ready(function () {
  $('.navShowHide').click(function () {
    var sideNavBar = $('#sideNavContainer');
    var mainSection = $('#mainSectionContainer');

    if (mainSection.hasClass('leftPadding')) {
      sideNavBar.show();
    } else {
      sideNavBar.hide();
    }
    mainSection.toggleClass('leftPadding');
  });

  // function showTimer(){
  //   alert('click');
  // }

  // alert($time);
});

function markSTudentAssignment(
  button,
  student_period_assignment_id,
  subject_period_assignment_id,
  student_id
) {
  //   var id = $('#id').val();
  var mark_value = $('#mark_value').val();

  $.post('ajax/markStudentGrade.php', {
    mark_value,
    student_period_assignment_id,
    subject_period_assignment_id,
    student_id,
  }).done(function (data) {
    if (data == 'success') {
      location.reload();
    } else {
      alert(`SETTED MAX SCORE IS ONLY ${data}`);
    }
  });
}

function changeSectionValue(button, subject_period_id) {
  var setted_value = $('#section_value').val();

  //  alert(setted_value);

  $.post('ajax/changeSectionValue.php', {
    subject_period_id,
    setted_value,
  }).done(function (data) {
    console.log(data);
  });
}

function markGradebook(
  button,
  student_period_assignment_id,
  subject_period_assignment_id,
  student_id
) {
  // var value = $('#gradebook_input_'.student_period_assignment_id).val();

  if (student_period_assignment_id != 0 || student_period_assignment_id != '') {
    // var value = $('#gradebook_input_'.student_period_assignment_id).val();
  } else {
    alert('student_period_assignment_id is error');
  }

  // console.log(student_period_assignment_id);

  // if (value === '') {
  //   alert('value is empty string');
  // }

  // console.log(`student_period_assignment_id ${student_period_assignment_id}`);
  // console.log(`subject_period_assignment_id ${subject_period_assignment_id}`);
  // console.log(`student_id ${student_id}`);

  // console.log(value)

  var input = $(button).siblings('input');
  var value = input.val();

  $.post('ajax/markGradebook.php', {
    value,
    student_period_assignment_id,
    subject_period_assignment_id,
    student_id,
  }).done(function (data) {
    // Jquery the value
    if (data == 'success') {
      location.reload();
    } else {
      alert(`SETTED MAX SCORE IS ONLY ${data}`);
    }

    // alert(data);
  });
}

// $('#question_type_id').on('change', function (subject_period_quiz_id) {
//   var value = $(this).val();

//   console.log(subject_period_quiz_id);

//   $.ajax({
//     url: 'ajax/questionType.php',
//     type: 'POST',
//     data:
//       'request=' + value + 'subject_period_quiz_id=' + subject_period_quiz_id,
//     success: function (data) {
//       $('.container').html(data);
//     },
//   });
// });

function quizTypeChange(button, subject_period_quiz_id, teacher_id) {
  var value = $('#question_type_id').val();

  // console.log(value);

  $.post('ajax/questionType.php', {
    request: value,
    subject_period_quiz_id,
    teacher_id,
  }).done(function (data) {
    $('.container').html(data);
  });
}

function quizTypeChangeAssignment(button) {
  var value = $('#question_type_id').val();

  console.log(value);

  $.post('ajax/questionTypeAssignment.php', {
    request: value,
  }).done(function (data) {
    $('.container').html(data);
  });
}

function nextQuestionSubmit(
  button,
  subject_period_quiz_question_id,
  student_id,
  subject_period_quiz_id
) {
  // alert(subject_period_quiz_question_id);

  // var value = $(`input[name=${subject_period_quiz_question_id}}]:checked`).val();
  // var val = $("input[type='radio']:checked").val();

  var ele = document.getElementsByName('q-' + subject_period_quiz_question_id);

  var val = '';
  for (i = 0; i < ele.length; i++) {
    if (ele[i].checked) {
      val = ele[i].value;
    }
    // console.log(ele[i].value);
  }

  // console.log(val);

  $.post('ajax/nextQuestionSubmit.php', {
    val,
    subject_period_quiz_question_id,
    student_id,
    subject_period_quiz_id,
  }).done(function (data) {
    // value = $('#samp_' + subject_period_quiz_question_id).val();
    // alert(val);
  });
}

// Quiz v2
function clickRadioSubmitAssQuizTF(
  button,
  subject_period_assignment_quiz_question_id,
  student_id,
  subject_period_assignment_id,
  student_period_assignment_quiz_id
) {
  //
  var ele = document.getElementsByName(
    'q-' + subject_period_assignment_quiz_question_id
  );

  var val = '';

  for (i = 0; i < ele.length; i++) {
    if (ele[i].checked) {
      val = ele[i].value;
      // console.log(val);
    }
  }

  console.log(student_period_assignment_quiz_id);

  $.post('ajax/clickRadioSubmitAssQuizTF.php', {
    val,
    subject_period_assignment_quiz_question_id,
    student_id,
    subject_period_assignment_id,
    student_period_assignment_quiz_id,
  }).done(function (data) {
    console.log(data);

    // value = $('#samp_' + subject_period_quiz_question_id).val();
    // alert(val);
  });
}

function nextQuestionSubmitRadio(
  button,
  subject_period_quiz_question_id,
  student_id,
  subject_period_quiz_id
) {
  var ele = document.getElementsByName('q-' + subject_period_quiz_question_id);

  var val = '';
  for (i = 0; i < ele.length; i++) {
    if (ele[i].checked) {
      val = ele[i].value;
      // console.log(val);
    }
  }

  // console.log(val);

  $.post('ajax/nextQuestionSubmitRadio.php', {
    val,
    subject_period_quiz_question_id,
    student_id,
    subject_period_quiz_id,
  }).done(function (data) {
    // value = $('#samp_' + subject_period_quiz_question_id).val();
    // alert(val);
  });
}

function multipleRadioButtonSubmit(
  button,
  subject_period_quiz_question_id,
  student_id,
  subject_period_quiz_id,
  subject_period_quiz_question_answer_id
) {
  var ele = document.getElementsByName('q-' + subject_period_quiz_question_id);

  var val = '';
  for (i = 0; i < ele.length; i++) {
    if (ele[i].checked) {
      val = ele[i].value;
      // console.log(val);
    }
  }

  $.post('ajax/multipleRadioButtonSubmit.php', {
    val,
    subject_period_quiz_question_id,
    student_id,
    subject_period_quiz_id,
    subject_period_quiz_question_answer_id,
  }).done(function (data) {
    console.log(data);
  });
}

// Multiple Question v2

function multipleRadioButtonSubmitv2(
  button,
  subject_period_assignment_quiz_question_id,
  student_id,
  subject_period_assignment_id,
  subject_period_assignment_quiz_question_answer_id,
  student_period_assignment_quiz_id
) {
  var ele = document.getElementsByName(
    'q-' + subject_period_assignment_quiz_question_id
  );

  var val = '';
  for (i = 0; i < ele.length; i++) {
    if (ele[i].checked) {
      val = ele[i].value;
      // console.log(val);
    }
  }

  // console.log(student_period_assignment_quiz_id);
  $.post('ajax/multipleRadioButtonSubmitv2.php', {
    val,
    subject_period_assignment_quiz_question_id,
    student_id,
    subject_period_assignment_id,
    subject_period_assignment_quiz_question_answer_id,
    student_period_assignment_quiz_id,
  }).done(function (data) {
    console.log(data);
  });
}

function takeQuiz(subject_period_quiz_class_id, student_id) {
  $.post('ajax/takeQuiz.php', {
    subject_period_quiz_class_id,
    student_id,
  }).done(function (data) {
    alert(data);
  });
}

function takeQuizv2(
  subject_period_assignment_quiz_class_id,
  student_id,
  subject_period_assignment_id,
  teacher_course_id
) {
  // alert(subject_period_assignment_quiz_class_id);

  $.post('ajax/takeQuizv2.php', {
    subject_period_assignment_quiz_class_id,
    student_id,
    subject_period_assignment_id,
    teacher_course_id,
  }).done(function (data) {
    alert(data);

    // console.log(data)
  });
}
function notAllowedToSeeSubmission() {
  alert('You`re not allowed to see submission.');
  location.reload();
  return;
}

function clickHandout(subject_period_assignment_handout_id, student_id) {
  $.post('ajax/clickHandout.php', {
    subject_period_assignment_handout_id,
    student_id,
  }).done(function (data) {
    console.log(data);
  });
}

function createGroupChat(teacher_course_id, teacher_id) {
  $.post('ajax/message/createGroupChat.php', {
    teacher_course_id,
    teacher_id,
  }).done(function (data) {
    console.log(data);
  });
}
