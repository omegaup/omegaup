import {API, UI, OmegaUp} from '../omegaup.js';
import course_Intro from '../components/course/Intro.vue';
import Vue from 'vue';

OmegaUp.on('ready', function() {
  let coursePayload =
      JSON.parse(document.getElementById('course-payload').innerText);

  let courseIntro = new Vue({
    el: '#course-intro',
    render: function(createElement) {
      return createElement('course-intro', {
        props: {
          name: coursePayload.name,
          description: coursePayload.description,
          needsBasicInformation: coursePayload.needsBasicInformation,
          requestsUserInformation: coursePayload.requestsUserInformation
        },
        on: {
          submit: function(ev) {
            API.Course.addStudent({
                        'course_alias': coursePayload.alias,
                        'usernameOrEmail': coursePayload.currentUsername,
                        'share_user_information': ev.shareUserInformation
                      })
                .then(function(data) {
                  window.location.replace('/course/' + coursePayload.alias);
                })
                .fail(UI.apiError);
          }
        }
      });
    },
    components: {
      'course-intro': course_Intro,
    }
  });
});
