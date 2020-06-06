import { OmegaUp } from '../omegaup';
import { types } from '../api_types';
import * as api from '../api';
import * as UI from '../ui';
import T from '../lang';
import Vue from 'vue';
import login_SignIn from '../components/login/SignIn.vue';
import VueRecaptcha from 'vue-recaptcha';

OmegaUp.on('ready', () => {
  const payload = JSON.parse(
    (<HTMLElement>document.getElementById('payload')).innerText,
  );

  let signIn = new Vue({
    el: '#login-sign-in',
    render: function(createElement) {
      return createElement('omegaup-login-sign-in', {
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
              UI.error(T.passwordMismatch);
              return;
            }
            if (password.length < 8) {
              UI.error(T.loginPasswordTooShort);
              return;
            }

            api.User.create({
              username: username,
              email: email,
              password: password,
              recaptcha: recaptchaResponse,
            })
              .then(data => {
                loginAndredirect(username, password, 'register');
              })
              .catch(UI.apiError);
          },
          login: (usernameOrEmail: string, password: string) => {
            loginAndredirect(usernameOrEmail, password, 'login');
          },
          loginGoogle: (url: string) => {
            //const googleUser = this.$gAuth.signIn();
          },
        },
      });
    },
    components: {
      'omegaup-login-sign-in': login_SignIn,
      'vue-recaptcha': VueRecaptcha,
    },
  });

  function loginAndredirect(
    usernameOrEmail: string,
    password: string,
    type: string,
  ) {
    api.User.login({
      usernameOrEmail: usernameOrEmail,
      password: password,
    })
      .then(data => {
        const params = new URL(document.location.toString()).searchParams;
        let pathname = params.get('redirect');
        if (!pathname || (pathname.indexOf('/') == 0 && type == 'register')) {
          pathname = '/profile/';
        } else if (
          !pathname ||
          (pathname.indexOf('/') == 0 && type == 'login')
        ) {
          pathname = '/';
        }
        window.location.href = pathname;
      })
      .catch(UI.apiError);
  }
});
