function enrolledStudent(student_username, student_id) {
  //
  $.post('../ajax/enrolledStudent.php', {
    student_username,
    student_id,
  }).done(function (data) {
    // data = JSON.parse(data);

    console.log(data);
  });
}

function enrollOldStudent(student_username, student_id) {
  //

  $.post('../ajax/enrollOldStudent.php', {
    student_username,
    student_id,
  }).done(function (data) {
    // data = JSON.parse(data);

    // console.log(data);
    if (data === 'success') {
      alert(
        'Student ' + student_username + 'has been added to enrolled_student db'
      );
    } else {
      console.log(data);
    }
  });
}

function applyForNextSemBtn(student_username) {
  // $.post('../ajax/applyForNextSemBtn.php', {
  //   student_username,
  // }).done(function (data) {
  //   console.log(data);
  // });
  // alert('qwe')
}

function insertGrade(
  student_id,
  subject_id,
  student_subject_id,
  subject_title,
  course_id
) {
  //
  // console.log(subject_title);

  var remark_input = $('#remark_input').val();

  // alert('You Applied for the semester');

  // alert(remark_input);
  $.post('../ajax/insertGrade.php', {
    student_id,
    subject_id,
    remark_input,
    student_subject_id,
    subject_title,
    course_id,
  }).done(function (data) {
    if (data == 'Invalid Input') {
      alert('Invalid Input');
    }

    console.log(data);

    if (data == 'success') {
      alert('Insertion of grades success');
    }
    if (data == 'Irregular') {
      alert('Set student into Irregular. Failed the subject');
    }

    $('#grade_report_insertion').load(
      location.href + ' #grade_report_insertion'
    );
  });
}

function tertiaryInsertGrade(
  student_id,
  subjectId,
  student_subject_tertiary_id
) {
  //
  // console.log(subject_title);

  var remark_input = $('#tertiary_remark_input').val();

  // console.log(student_id);
  // console.log(subjectId);
  // console.log(student_subject_tertiary_id);
  // console.log(remark_input);

  // alert('You Applied for the semester');

  // alert(remark_input);

  $.post('../ajax/tertiaryInsertGrade.php', {
    student_id,
    subjectId,
    remark_input,
    student_subject_tertiary_id,
  }).done(function (data) {
    //
    if (data == 'Invalid Input') {
      alert('Invalid Input');
    }

    console.log(data);

    // if (data == 'success') {
    //   alert('Insertion of grades success');
    // }
    // if (data == 'Irregular') {
    //   alert('Set student into Irregular. Failed the subject');
    // }

    $('#tertiary_grade_report_insertion').load(
      location.href + ' #tertiary_grade_report_insertion'
    );
  });
}

function moveUpAction(student_username) {


  $.post('../ajax/student/move_up_student.php', {
    student_username
  }).done(function (data) {
    //

    alert(data);
    
  });
}
