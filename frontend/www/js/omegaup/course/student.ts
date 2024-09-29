import course_ViewStudent from '../components/course/ViewStudent.vue';
import { OmegaUp } from '../omegaup';
import { types } from '../api_types';
import * as api from '../api';
import * as ui from '../ui';
import T from '../lang';
import Vue from 'vue';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.StudentProgressPayload();

  const student = payload.students.find(
    (student) => payload.student === student.username,
  );

  const viewStudent = new Vue({
    el: '#main-container',
    components: {
      'omegaup-course-viewstudent': course_ViewStudent,
    },
    data: () => ({
      problems: [] as types.CourseProblem[],
      problem: null as null | types.CourseProblem,
    }),
    methods: {
      refreshStudentProgress: (
        student: string,
        assignmentAlias: string,
      ): void => {
        if (assignmentAlias == null) return;
        api.Course.studentProgress({
          course_alias: payload.course.alias,
          assignment_alias: assignmentAlias,
          usernameOrEmail: student,
        })
          .then((data) => {
            viewStudent.problems = data.problems;
          })
          .catch(ui.apiError);
      },
    },
    render: function (createElement) {
      return createElement('omegaup-course-viewstudent', {
        props: {
          assignments: payload.course.assignments,
          course: payload.course,
          student,
          problems: this.problems,
          problem: this.problem,
          students: payload.students,
        },
        on: {
          'set-feedback': ({
            guid,
            feedback,
            isUpdate,
            assignmentAlias,
            studentUsername,
          }: {
            guid: string;
            feedback: string;
            isUpdate: boolean;
            assignmentAlias: string;
            studentUsername: string;
          }) => {
            api.Submission.setFeedback({
              guid,
              course_alias: payload.course.alias,
              assignment_alias: assignmentAlias,
              feedback,
            })
              .then(() => {
                ui.success(
                  isUpdate
                    ? T.feedbackSuccessfullyUpdated
                    : T.feedbackSuccessfullyAdded,
                );
                viewStudent.refreshStudentProgress(
                  studentUsername,
                  assignmentAlias,
                );
                api.Course.studentProgress({
                  course_alias: payload.course.alias,
                  assignment_alias: assignmentAlias,
                  usernameOrEmail: studentUsername,
                })
                  .then((data) => {
                    viewStudent.problems = data.problems;
                  })
                  .catch(ui.apiError);
              })
              .catch(ui.error);
          },
          update: ({
            student,
            assignmentAlias,
          }: {
            student: string;
            assignmentAlias: string;
          }) => {
            viewStudent.refreshStudentProgress(student, assignmentAlias);
          },
          'push-state': (student: string, title: string, url: string) => {
            window.history.pushState(student, title, url);
          },
        },
      });
    },
  });
});
