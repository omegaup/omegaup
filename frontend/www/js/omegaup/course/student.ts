import course_ViewStudent from '../components/course/ViewStudent.vue';
import { OmegaUp } from '../omegaup';
import { types } from '../api_types';
import * as api from '../api';
import * as ui from '../ui';
import Vue from 'vue';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.StudentProgressPayload();

  const match = /#(?<alias>[^/]+)?/g.exec(window.location.hash);
  const selectedProblem = match?.groups?.alias;

  const initialStudent = payload.students.find(
    (student) => payload.student === student.username,
  );

  const initialAssignment = payload.course.assignments.find(
    (assignment) => payload.assignment === assignment.alias,
  );

  const initialProblem = payload.problems.find(
    (problem) => selectedProblem === problem.alias,
  );

  const problems: types.CourseProblem[] = payload.problems;

  const viewStudent = new Vue({
    el: '#main-container',
    components: {
      'omegaup-course-viewstudent': course_ViewStudent,
    },
    data: () => ({
      problems,
    }),
    render: function (createElement) {
      return createElement('omegaup-course-viewstudent', {
        props: {
          assignments: payload.course.assignments,
          course: payload.course,
          initialStudent,
          initialAssignment,
          initialProblem,
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
