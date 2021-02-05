import { OmegaUp } from '../omegaup';
import { types } from '../api_types';
import * as api from '../api';
import * as ui from '../ui';
import T from '../lang';
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
          course: payload.details,
          name: payload.name,
          description: payload.description,
          needsBasicInformation: payload.needsBasicInformation,
          requestsUserInformation: payload.requestsUserInformation,
          shouldShowAcceptTeacher: payload.shouldShowAcceptTeacher,
          statements: payload.statements,
          userRegistrationRequested: this.userRegistrationRequested,
          userRegistrationAnswered: payload.userRegistrationAnswered,
          userRegistrationAccepted: payload.userRegistrationAccepted,
          loggedIn: headerPayload.isLoggedIn,
          username: headerPayload.currentUsername,
        },
        on: {
          submit: (source: course_Intro) => {
            api.Course.addStudent({
              course_alias: payload.alias,
              usernameOrEmail: headerPayload.currentUsername,
              share_user_information: source.shareUserInformation,
              accept_teacher: source.acceptTeacher,
              privacy_git_object_id: payload.statements.privacy?.gitObjectId,
              accept_teacher_git_object_id:
                payload.statements.acceptTeacher?.gitObjectId,
              statement_type: payload.statements.privacy?.statementType,
            })
              .then(() => {
                window.location.replace(`/course/${payload.alias}/`);
              })
              .catch(ui.apiError);
          },
          'request-access-course': () => {
            api.Course.registerForCourse({ course_alias: payload.alias })
              .then(() => {
                courseIntro.userRegistrationRequested = true;
              })
              .catch(ui.error);
          },
          clone: (alias: string, name: string, startTime: Date) => {
            api.Course.clone({
              course_alias: payload.details?.alias,
              name: name,
              alias: alias,
              start_time: startTime.getTime() / 1000,
            })
              .then(() => {
                ui.success(
                  ui.formatString(T.courseEditCourseClonedSuccessfully, {
                    course_alias: alias,
                  }),
                  /*autoHide=*/ false,
                );
              })
              .catch(ui.apiError);
          },
        },
      });
    },
  });
});
