import course_ViewStudent from '../components/course/ViewStudent.vue';
import { omegaup, OmegaUp } from '../omegaup';
import { types } from '../api_types';
import * as api from '../api';
import * as UI from '../ui';
import T from '../lang';
import Vue from 'vue';

OmegaUp.on('ready', function() {
  const payload = types.payloadParsers.StudentsProgressPayload();

  let initialStudent: types.CourseStudent | null = null;
  if (payload.students && Object.values(payload.students).length > 0) {
    initialStudent = Object.values(payload.students)[0];
    for (let student of Object.values(payload.students)) {
      if (student.username == payload.student) {
        initialStudent = student;
        break;
      }
    }
  }

  const viewStudent: Vue & { problems: types.CourseProblem[] } = new Vue({
    el: '#main-container',
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
          update: function(
            student: types.CourseStudent,
            assignment: types.CourseAssignment,
          ) {
            if (assignment == null) return;
            api.Course.studentProgress({
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
