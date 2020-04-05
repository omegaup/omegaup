import { OmegaUp } from '../omegaup';
import * as api from '../api_transitional';
import * as UI from '../ui';
import course_Intro from '../components/course/Intro.vue';
import Vue from 'vue';

OmegaUp.on('ready', function() {
  let coursePayload = JSON.parse(
    document.getElementById('course-payload').innerText,
  );
  let courseIntro = new Vue({
    el: '#course-intro',
    render: function(createElement) {
      return createElement('course-intro', {
        props: {
          name: this.name,
          description: this.description,
          needsBasicInformation: this.needsBasicInformation,
          requestsUserInformation: this.requestsUserInformation,
          shouldShowAcceptTeacher: this.shouldShowAcceptTeacher,
          statements: this.statements,
          userRegistrationRequested: this.userRegistrationRequested,
          userRegistrationAnswered: this.userRegistrationAnswered,
          userRegistrationAccepted: this.userRegistrationAccepted,
        },
        on: {
          submit: ev => {
            api.Course.addStudent({
              course_alias: coursePayload.alias,
              usernameOrEmail: coursePayload.currentUsername,
              share_user_information: ev.shareUserInformation,
              accept_teacher: ev.acceptTeacher,
              privacy_git_object_id:
                coursePayload.statements.privacy.gitObjectId,
              accept_teacher_git_object_id:
                coursePayload.statements.acceptTeacher.gitObjectId,
              statement_type: coursePayload.statements.privacy.statementType,
            })
              .then(data => {
                window.location.replace(`/course/${coursePayload.alias}/`);
              })
              .catch(UI.apiError);
          },
          'request-access-course': () => {
            api.Course.registerForCourse({ course_alias: coursePayload.alias })
              .then(() => {
                courseIntro.userRegistrationRequested = true;
              })
              .catch(UI.error);
          },
        },
      });
    },
    data: {
      name: coursePayload.name,
      description: coursePayload.description,
      needsBasicInformation: coursePayload.needsBasicInformation,
      requestsUserInformation: coursePayload.requestsUserInformation,
      shouldShowAcceptTeacher: coursePayload.shouldShowAcceptTeacher,
      statements: coursePayload.statements,
      userRegistrationRequested: coursePayload.userRegistrationRequested,
      userRegistrationAnswered: coursePayload.userRegistrationAnswered,
      userRegistrationAccepted: coursePayload.userRegistrationAccepted,
    },
    components: {
      'course-intro': course_Intro,
    },
  });
});
