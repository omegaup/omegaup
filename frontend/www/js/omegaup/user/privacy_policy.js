import Vue from 'vue';
import user_Privacy_Policy from '../components/user/PrivacyPolicy.vue';
import { OmegaUp } from '../omegaup-legacy';
import T from '../lang';
import * as api from '../api';
import * as ui from '../ui';

OmegaUp.on('ready', function () {
  const payload = JSON.parse(document.getElementById('payload').innerText);

  let privacyPolicy = new Vue({
    el: '#privacy-policy',
    render: function (createElement) {
      return createElement('omegaup-privacy-policy', {
        props: {
          policyMarkdown: this.policyMarkdown,
          saved: this.saved,
        },
        on: {
          submit: function (ev) {
            api.User.acceptPrivacyPolicy({
              privacy_git_object_id: payload.git_object_id,
              statement_type: payload.statement_type,
            })
              .then(function (data) {
                ui.info(T.wordsPrivacyPolicyAccepted);
                privacyPolicy.saved = true;
              })
              .catch(ui.apiError);
          },
        },
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
