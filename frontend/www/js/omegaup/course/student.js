import course_ViewStudent from '../components/course/ViewStudent.vue';
import {API, UI, OmegaUp, T} from '../omegaup.js';
import Vue from 'vue';

OmegaUp.on('ready', function() {
  var payload = JSON.parse(document.getElementById('payload').innerText);

  var initialStudent = null;
  if (payload.students && payload.students.length > 0) {
    initialStudent = payload.students[0];
    for (var student of payload.students) {
      if (student.username == payload.student) {
        initialStudent = student;
        break;
      }
    }
  }

  var viewStudent = new Vue({
    el: '#view-student',
    render: function(createElement) {
      return createElement('omegaup-course-viewstudent', {
        props: {
          T: T,
          assignments: payload.course.assignments,
          course: payload.course,
          initialStudent: initialStudent,
          problems: this.problems,
          students: payload.students,
        },
        on: {
          update: function(student, assignment) {
            API.Course.studentProgress({
                        course_alias: payload.course.alias,
                        assignment: assignment.alias,
                        usernameOrEmail: student.username,
                      })
                .then(function(data) { viewStudent.problems = data.problems; })
                .fail(UI.apiError);
          },
        },
      });
    },
    data: {
      problems: [],
    },
    components: {
      'omegaup-course-viewstudent': course_ViewStudent,
    },
  });
});
