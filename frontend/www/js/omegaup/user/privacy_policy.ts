import Vue from 'vue';
import user_PrivacyPolicy from '../components/user/PrivacyPolicy.vue';
import { OmegaUp } from '../omegaup';
import T from '../lang';
import * as api from '../api';
import * as ui from '../ui';
import { types } from '../api_types';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.PrivacyPolicyDetailsPayload();

  const privacyPolicy = new Vue({
    el: '#main-container',
    components: {
      'omegaup-privacy-policy': user_PrivacyPolicy,
    },
    data: () => {
      return {
        saved: payload.has_accepted,
      };
    },
    render: function (createElement) {
      return createElement('omegaup-privacy-policy', {
        props: {
          policyMarkdown: payload.policy_markdown,
          saved: this.saved,
        },
        on: {
          submit: () => {
            api.User.acceptPrivacyPolicy({
              privacy_git_object_id: payload.git_object_id,
              statement_type: payload.statement_type,
            })
              .then(() => {
                ui.info(T.wordsPrivacyPolicyAccepted);
                privacyPolicy.saved = true;
              })
              .catch(ui.apiError);
          },
        },
      });
    },
  });
});
