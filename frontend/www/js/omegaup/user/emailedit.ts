import user_EmailEdit from '../components/user/EmailEdit.vue';
import Vue from 'vue';
import { OmegaUp } from '../omegaup';
import T from '../lang';
import * as api from '../api';
import * as ui from '../ui';
import { types } from '../api_types';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.EmailEditDetailsPayload();

  new Vue({
    el: '#main-container',
    components: {
      'omegaup-user-email-edit': user_EmailEdit,
    },
    render: function (createElement) {
      return createElement('omegaup-user-email-edit', {
        props: {
          email: payload.email,
          profile: payload.profile,
        },
        on: {
          submit: (newEmail: string) => {
            api.User.updateMainEmail({ email: newEmail })
              .then(() => {
                ui.success(T.userEditSuccessfulEmailUpdate);
              })
              .catch(ui.apiError);
          },
        },
      });
    },
  });
});
