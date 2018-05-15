import Vue from 'vue';
import user_Privacy_Policy from '../components/user/PrivacyPolicy.vue';
import {OmegaUp, T, API} from '../omegaup.js';
import UI from '../ui.js';

OmegaUp.on('ready', function() {
  const privacy_policy =
      JSON.parse(document.getElementById('payload').innerText);

  let privacyPolicy = new Vue({
    el: '#privacy-policy',
    render: function(createElement) {
      return createElement('omegaup-privacy-policy', {
        props: {
          policy_markdown: this.policy_markdown,
          accepted: this.accepted,
          git_object_id: this.git_object_id,
        },
        on: {
          submit: function(ev) {
            API.User.acceptPrivacyPolicy(
                        {git_object_id: privacyPolicy.git_object_id})
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
      policy_markdown: privacy_policy.policy_markdown,
      accepted: false,
      git_object_id: privacy_policy.git_object_id
    },
    components: {
      'omegaup-privacy-policy': user_Privacy_Policy,
    },
  });
});
