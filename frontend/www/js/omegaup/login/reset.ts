import { OmegaUp } from '../omegaup';
import * as api from '../api';
import * as ui from '../ui';
import Vue from 'vue';
import login_PasswordReset from '../components/login/PasswordReset.vue';

OmegaUp.on('ready', () => {
  const payload = JSON.parse(
    (document.getElementById('payload') as HTMLElement).innerText,
  );

  new Vue({
    el: '#main-container',
    components: {
      'omegaup-login-password-reset': login_PasswordReset,
    },
    render: function (createElement) {
      return createElement('omegaup-login-password-reset', {
        props: {
          email: payload.email,
          resetToken: payload.resetToken,
        },
        on: {
          'reset-password': (
            email: string,
            resetToken: string,
            password: string,
            passwordConfirmation: string,
          ) => {
            api.Reset.update({
              email: email,
              reset_token: resetToken,
              password: password,
              password_confirmation: passwordConfirmation,
            })
              .then((data) => {
                ui.success(data.message ?? '');
              })
              .catch(ui.apiError);
          },
        },
      });
    },
  });
});
