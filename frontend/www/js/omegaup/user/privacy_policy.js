import Vue from 'vue';
import user_Privacy_Policy from '../components/user/PrivacyPolicy.vue';
import {OmegaUp, T, API} from '../omegaup.js';
import UI from '../ui.js';

OmegaUp.on('ready', function() {
  const payload = JSON.parse(document.getElementById('payload').innerText);

  let privacyPolicy = new Vue({
    el: '#privacy-policy',
    render: function(createElement) {
      return createElement('omegaup-privacy-policy', {
        props: {
          policyMarkdown: this.policyMarkdown,
          saved: this.saved,
        },
        on: {
          submit: function(ev) {
            API.User.acceptPrivacyPolicy({
                      privacy_git_object_id: payload.git_object_id,
                      statement_type: payload.statement_type
                    })
                .then(function(data) {
                  UI.info(T.wordsPrivacyPolicyAccepted);
                  privacyPolicy.saved = true;
                })
                .fail(UI.apiError);
          }
        }
      });
    },
    data: {
      policyMarkdown: payload.policy_markdown,
      saved: payload.has_accepted,
    },
    components: {
      'omegaup-privacy-policy': user_Privacy_Policy,
    },
  });
});
