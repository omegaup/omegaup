import { OmegaUp } from '../omegaup';
import { types } from '../api_types';
import * as api from '../api';
import * as ui from '../ui';
import Vue from 'vue';
import course_Intro from '../components/course/Intro.vue';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.IntroDetailsPayload();
  const headerPayload = types.payloadParsers.CommonPayload();
  const courseIntro = new Vue({
    el: '#main-container',
    components: {
      'omegaup-course-intro': course_Intro,
    },
    data: () => ({
      userRegistrationRequested: payload.userRegistrationRequested,
    }),
    render: function (createElement) {
      return createElement('omegaup-course-intro', {
        props: {
          course: payload.course,
          needsBasicInformation: payload.needsBasicInformation,
          shouldShowAcceptTeacher: payload.shouldShowAcceptTeacher,
          statements: payload.statements,
          userRegistrationRequested: this.userRegistrationRequested,
          userRegistrationAnswered: payload.userRegistrationAnswered,
          userRegistrationAccepted: payload.userRegistrationAccepted,
          loggedIn: headerPayload.isLoggedIn,
        },
        on: {
          submit: ({
            shareUserInformation,
            acceptTeacher,
          }: {
            shareUserInformation: boolean;
            acceptTeacher: boolean;
          }) => {
            api.Course.addStudent({
              course_alias: payload.course.alias,
              usernameOrEmail: headerPayload.currentUsername,
              share_user_information: shareUserInformation,
              accept_teacher: acceptTeacher,
              privacy_git_object_id: payload.statements.privacy?.gitObjectId,
              accept_teacher_git_object_id:
                payload.statements.acceptTeacher?.gitObjectId,
              statement_type: payload.statements.privacy?.statementType,
            })
              .then(() => {
                window.location.replace(`/course/${payload.course.alias}/`);
              })
              .catch(ui.apiError);
          },
          'request-access-course': ({
            shareUserInformation,
            acceptTeacher,
          }: {
            shareUserInformation: boolean;
            acceptTeacher: boolean;
          }) => {
            api.Course.registerForCourse({
              course_alias: payload.course.alias,
              accept_teacher: acceptTeacher,
              share_user_information: shareUserInformation,
            })
              .then(() => {
                courseIntro.userRegistrationRequested = true;
              })
              .catch(ui.error);
          },
        },
      });
    },
  });
});
