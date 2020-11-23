import { OmegaUp } from '../omegaup';
import { types } from '../api_types';
import * as api from '../api';
import * as ui from '../ui';
import T from '../lang';
import Vue from 'vue';
import login_Signin from '../components/login/Signin.vue';
import VueRecaptcha from 'vue-recaptcha';

function loginAndredirect(
  usernameOrEmail: string,
  password: string,
  isAccountCreation: boolean,
): void {
  api.User.login({
    usernameOrEmail: usernameOrEmail,
    password: password,
  })
    .then(() => {
      const params = new URL(document.location.toString()).searchParams;
      const pathname = params.get('redirect');
      if (pathname && pathname.indexOf('/') !== 0) {
        window.location.href = pathname;
        return;
      }
      if (isAccountCreation) {
        window.location.href = '/profile/';
        return;
      }
      window.location.href = '/';
    })
    .catch(ui.apiError);
}

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.LoginDetailsPayload();
  new Vue({
    el: '#main-container',
    components: {
      'omegaup-login-signin': login_Signin,
      'vue-recaptcha': VueRecaptcha,
    },
    render: function (createElement) {
      return createElement('omegaup-login-signin', {
        props: {
          validateRecaptcha: payload.validateRecaptcha,
          facebookUrl: payload.facebookUrl,
          linkedinUrl: payload.linkedinUrl,
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
                loginAndredirect(
                  username,
                  password,
                  /*isAccountCreation*/ true,
                );
              })
              .catch(ui.apiError);
          },
          login: (usernameOrEmail: string, password: string) => {
            loginAndredirect(
              usernameOrEmail,
              password,
              /*isAccountCreation*/ false,
            );
          },
        },
      });
    },
  });
});
