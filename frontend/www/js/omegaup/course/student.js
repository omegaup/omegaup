import course_ViewStudent from '../components/course/ViewStudent.vue';
import { OmegaUp } from '../omegaup';
import API from '../api.js';
import * as UI from '../ui';
import T from '../lang';
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
          assignments: payload.course.assignments,
          course: payload.course,
          initialStudent: initialStudent,
          problems: this.problems,
          students: payload.students,
        },
        on: {
          update: function(student, assignment) {
            if (assignment == null) return;
            API.Course.studentProgress({
              course_alias: payload.course.alias,
              assignment_alias: assignment.alias,
              usernameOrEmail: student.username,
            })
              .then(function(data) {
                viewStudent.problems = data.problems;
              })
              .catch(UI.apiError);
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
