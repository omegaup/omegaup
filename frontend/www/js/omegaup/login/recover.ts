import { OmegaUp } from '../omegaup';
import { types } from '../api_types';
import * as api from '../api';
import * as UI from '../ui';
import T from '../lang';
import Vue from 'vue';
import recover from '../components/login/Recover.vue';

OmegaUp.on('ready', () => {
  let loginPaswwordRecover = new Vue({
    el: '#main-container',
    render: function(createElement) {
      return createElement('omegaup-login-password-recover', {
        on: {
          'forgot-password': function(email: string) {
            api.Reset.create({
              email: email,
            })
              .then(function(data) {
                UI.success(data.message ?? '');
              })
              .catch(UI.apiError);
          },
        },
      });
    },
    components: {
      'omegaup-login-password-recover': recover,
    },
  });
});
