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
          policy_markdown: this.policy_markdown,
          accepted: this.accepted,
        },
        on: {
          submit: function(ev) {
            API.User.acceptPrivacyPolicy({})
                .then(function(data) {
                  UI.info(T.wordsPrivacyPolicyAccepted);
                  privacyPolicy.accepted = true;
                })
                .fail(UI.apiError);
          }
        }
      });
    },
    data: {
      policy_markdown: payload.policy_markdown,
      accepted: payload.has_accepted,
    },
    components: {
      'omegaup-privacy-policy': user_Privacy_Policy,
    },
  });
});
