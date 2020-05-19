import { OmegaUp } from '../omegaup';
import { types } from '../api_types';
import * as api from '../api';
import * as UI from '../ui';
import T from '../lang';
import Vue from 'vue';
import login_SignIn from '../components/login/SignIn.vue';

OmegaUp.on('ready', () => {
  let loginPaswwordRecover = new Vue({
    el: '#login-sign-in',
    render: function(createElement) {
      return createElement('omegaup-login-sign-in', {
        on: {

        },
      });
    },
    components: {
      'omegaup-login-sign-in': login_SignIn,
    },
  });
});