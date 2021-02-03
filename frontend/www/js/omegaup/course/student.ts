import course_ViewStudent from '../components/course/ViewStudent.vue';
import { OmegaUp } from '../omegaup';
import { types } from '../api_types';
import * as api from '../api';
import * as ui from '../ui';
import Vue from 'vue';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.StudentProgressPayload();

  let initialStudent: types.StudentProgress | null = null;
  if (payload.students && payload.students.length > 0) {
    initialStudent = payload.students[0];
    for (const student of payload.students) {
      if (student.username == payload.student) {
        initialStudent = student;
        break;
      }
    }
  }

  const viewStudent = new Vue({
    el: '#main-container',
    components: {
      'omegaup-course-viewstudent': course_ViewStudent,
    },
    data: () => ({
      problems: [] as types.CourseProblem[],
    }),
    render: function (createElement) {
      return createElement('omegaup-course-viewstudent', {
        props: {
          assignments: payload.course.assignments,
          course: payload.course,
          initialStudent: initialStudent,
          problems: this.problems,
          students: payload.students,
        },
        on: {
          update: (student: types.StudentProgress, assignmentAlias: string) => {
            if (assignmentAlias == null) return;
            api.Course.studentProgress({
              course_alias: payload.course.alias,
              assignment_alias: assignmentAlias,
              usernameOrEmail: student.username,
            })
              .then((data) => {
                viewStudent.problems = data.problems;
              })
              .catch(ui.apiError);
          },
        },
      });
    },
  });
});
