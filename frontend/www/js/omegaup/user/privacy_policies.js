import Vue from 'vue';
import user_Privacy_Policies from '../components/user/PrivacyPolicies.vue';
import {OmegaUp, T, API} from '../omegaup.js';
import UI from '../ui.js';

OmegaUp.on('ready', function() {
  const privacy_policies =
      JSON.parse(document.getElementById('payload').innerText);

  let privacyPolicies = new Vue({
    el: '#privacy-policies',
    render: function(createElement) {
      return createElement('omegaup-privacy-policies', {
        props: {policies: this.policies, accepted: this.accepted},
        on: {
          submit: function(ev) {
            API.User.acceptPrivacyPolicies(
                        {git_object_id: privacyPolicies.policies.git_object_id})
                .then(function(data) {
                  UI.info(T.wordsPrivacyPoliciesAccepted);
                  privacyPolicies.accepted = true;
                })
                .fail(UI.apiError);
          }
        }
      });
    },
    data: {policies: privacy_policies, accepted: false},
    components: {
      'omegaup-privacy-policies': user_Privacy_Policies,
    },
  });
});
