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
    if (pathname && pathname.indexOf('/') === 0) {
      const url = new URL(document.location.origin + pathname);
      url.searchParams.set('fromLogin', '');
      window.location.href = url.toString();
      return;
    }
    if (pathname && pathname.indexOf(document.location.origin) === 0) {
      window.location.href = pathname;
      return;
    }
    const fromLoginParam = '?fromLogin';
    if (isAccountCreation) {
      window.location.href = `/profile/${fromLoginParam}`;
      return;
    }
    window.location.href = `/${fromLoginParam}`;
  }

  const payload = types.payloadParsers.LoginDetailsPayload();
  const googleClientId = document
    .querySelector('meta[name="google-signin-client_id"]')
    ?.getAttribute('content');
  if (payload.statusError) {
    ui.warning(payload.statusError);
  } else if (payload.verifyEmailSuccessfully) {
    ui.success(payload.verifyEmailSuccessfully);
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
          googleClientId,
          hasVisitedSection: payload.hasVisitedSection,
        },
        on: {
          'register-and-login': (
            username: string,
            email: string,
            password: string,
            passwordConfirmation: string,
            recaptchaResponse: string,
            termsAndPolicies: boolean,
          ) => {
            console.log('termsAndPolicies is ', termsAndPolicies);
            if (password != passwordConfirmation) {
              ui.error(T.passwordMismatch);
              return;
            }
            if (password.length < 8) {
              ui.error(T.loginPasswordTooShort);
              return;
            }
            if (termsAndPolicies != true) {
              ui.error(T.privacyPolicyNotAccepted);
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
        },
      });
    },
  });
});
