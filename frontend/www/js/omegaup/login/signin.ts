import { OmegaUp } from '../omegaup';
import { types } from '../api_types';
import * as api from '../api';
import * as ui from '../ui';
import T from '../lang';
import Vue from 'vue';
import login_Signin from '../components/login/Signin.vue';
import VueRecaptcha from 'vue-recaptcha';

OmegaUp.on('ready', () => {
  function loginAndRedirect(
    usernameOrEmail: string,
    password: string,
    isAccountCreation: boolean,
  ): void {
    api.User.login({
      usernameOrEmail: usernameOrEmail,
      password: password,
    })
      .then(() => {
        redirect(isAccountCreation);
      })
      .catch(ui.apiError);
  }

  function redirect(isAccountCreation: boolean): void {
    const params = new URL(document.location.toString()).searchParams;
    const pathname = params.get('redirect');
    const fromLoginParam = '?fromLogin';
    if (pathname && pathname.indexOf('/') === 0) {
      window.location.href = pathname + fromLoginParam;
      return;
    }
    if (isAccountCreation) {
      window.location.href = '/profile/' + fromLoginParam;
      return;
    }
    window.location.href = '/' + fromLoginParam;
  }

  const payload = types.payloadParsers.LoginDetailsPayload();
  if (payload.statusError) {
    ui.error(payload.statusError);
  }
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
                loginAndRedirect(
                  username,
                  password,
                  /*isAccountCreation=*/ true,
                );
              })
              .catch(ui.apiError);
          },
          login: (usernameOrEmail: string, password: string) => {
            loginAndRedirect(
              usernameOrEmail,
              password,
              /*isAccountCreation=*/ false,
            );
          },
          'google-login': (idToken: string) => {
            // Only log in if the user actually clicked the sign-in button.
            api.Session.googleLogin({ storeToken: idToken })
              .then((data) => {
                redirect(data.isAccountCreation);
              })
              .catch(ui.apiError);
          },
          'google-login-failure': () => {
            ui.error(T.loginFederatedFailed);
          },
        },
      });
    },
  });
});
