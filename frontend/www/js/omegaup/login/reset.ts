import { OmegaUp } from '../omegaup';
import { types } from '../api_types';
import * as api from '../api';
import * as ui from '../ui';
import T from '../lang';
import Vue from 'vue';
import login_PasswordReset from '../components/login/PasswordReset.vue';

OmegaUp.on('ready', () => {
  const payload = JSON.parse(
    (<HTMLElement>document.getElementById('payload')).innerText,
  );

  let loginPaswwordRecover = new Vue({
    el: '#main-container',
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
    components: {
      'omegaup-login-password-reset': login_PasswordReset,
    },
  });
});
