import { OmegaUp } from '../omegaup';
import { types } from '../api_types';
import * as api from '../api';
import * as ui from '../ui';
import T from '../lang';
import Vue from 'vue';
import login_Signin from '../components/login/Signin.vue';
import VueRecaptcha from 'vue-recaptcha';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.LoginDetailsPayload();
  new Vue({
    el: '#main-container',
    components: {
      'omegaup-login-signin': login_Signin,
      'vue-recaptcha': VueRecaptcha,
    },
    methods: {
      loginAndredirect: (
        usernameOrEmail: string,
        password: string,
        type: string,
      ): void => {
        api.User.login({
          usernameOrEmail: usernameOrEmail,
          password: password,
        })
          .then(() => {
            const params = new URL(document.location.toString()).searchParams;
            let pathname = params.get('redirect');
            if (
              !pathname ||
              (pathname.indexOf('/') === 0 && type === 'register')
            ) {
              pathname = '/profile/';
            } else if (
              !pathname ||
              (pathname.indexOf('/') === 0 && type === 'login')
            ) {
              pathname = '/';
            }
            window.location.href = pathname;
          })
          .catch(ui.apiError);
      },
    },
    render: function (createElement) {
      return createElement('omegaup-login-signin', {
        props: {
          validateRecaptcha: payload.validateRecaptcha,
          facebookURL: payload.facebookURL,
          linkedinURL: payload.linkedinURL,
        },
        on: {
          'register-and-login': (
            username: string,
            email: string,
            password: string,
            passwordConfirmation: string,
            recaptchaResponse: string,
          ) => {
            if (password != passwordConfirmation) {
              ui.error(T.passwordMismatch);
              return;
            }
            if (password.length < 8) {
              ui.error(T.loginPasswordTooShort);
              return;
            }

            api.User.create({
              username: username,
              email: email,
              password: password,
              recaptcha: recaptchaResponse,
            })
              .then(() => {
                this.loginAndredirect(username, password, 'register');
              })
              .catch(ui.apiError);
          },
          login: (usernameOrEmail: string, password: string) => {
            this.loginAndredirect(usernameOrEmail, password, 'login');
          },
        },
      });
    },
  });
});
