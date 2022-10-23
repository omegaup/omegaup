import { OmegaUp } from '../omegaup';
import { types } from '../api_types';
import * as api from '../api';
import * as ui from '../ui';
import T from '../lang';
import Vue from 'vue';
import login_Signup from '../components/login/Signup.vue';

OmegaUp.on('ready', () => {
  function loginAndRedirect(usernameOrEmail: string, password: string): void {
    api.User.login({ usernameOrEmail, password })
      .then(() => {
        redirect();
      })
      .catch(ui.apiError);
  }

  function redirect(): void {
    const params = new URL(document.location.toString()).searchParams;
    const pathname = params.get('redirect');
    if (pathname && pathname.indexOf('/') === 0) {
      const url = new URL(document.location.origin + pathname);
      url.searchParams.set('fromLogin', '');
      window.location.href = url.toString();
      return;
    }
    const fromLoginParam = '?fromLogin';
    window.location.href = `/profile/${fromLoginParam}`;
  }

  const payload = types.payloadParsers.RegisterDetailsPayload();

  new Vue({
    el: '#main-container',
    components: {
      'omegaup-login-signup': login_Signup,
    },
    render: function (createElement) {
      return createElement('omegaup-login-signup', {
        props: {
          username: atob(payload.username),
          email: atob(payload.email),
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
                loginAndRedirect(username, password);
              })
              .catch(ui.apiError);
          },
        },
      });
    },
  });
});
