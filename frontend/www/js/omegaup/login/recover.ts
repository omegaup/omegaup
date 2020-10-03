import { OmegaUp } from '../omegaup';
import { types } from '../api_types';
import * as api from '../api';
import * as ui from '../ui';
import T from '../lang';
import Vue from 'vue';
import login_PasswordRecover from '../components/login/PasswordRecover.vue';

OmegaUp.on('ready', () => {
  const loginPaswwordRecover = new Vue({
    el: '#main-container',
    render: function (createElement) {
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
    components: {
      'omegaup-login-password-recover': login_PasswordRecover,
    },
  });
});
