import { OmegaUp } from '../omegaup';
import * as api from '../api';
import * as ui from '../ui';
import Vue from 'vue';
import login_PasswordRecover from '../components/login/PasswordRecover.vue';

OmegaUp.on('ready', () => {
  new Vue({
    el: '#main-container',
    components: {
      'omegaup-login-password-recover': login_PasswordRecover,
    },
    render: function (createElement: Function) {
      return createElement('omegaup-login-password-recover', {
        on: {
          'forgot-password': function (email: string) {
            api.Reset.create({
              email: email,
            })
              .then(function (data) {
                ui.success(data.message ?? '');
              })
              .catch(ui.apiError);
          },
        },
      });
    },
  });
});
