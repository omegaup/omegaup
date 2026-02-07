import { OmegaUp } from '../omegaup';
import { types } from '../api_types';
import * as api from '../api';
import * as ui from '../ui';
import T from '../lang';
import Vue from 'vue';
import login_Signin, { AvailableTabs } from '../components/login/Signin.vue';
import { EventBus } from '../components/common/Navbar.vue';
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
    const params = new URLSearchParams(window.location.search);
    const pathname = params.get('redirect');
    const fromLoginParam = '?fromLogin';

    if (isAccountCreation) {
      window.location.href = `/profile/${fromLoginParam}`;
      return;
    }

    if (pathname && pathname.indexOf('/') === 0) {
      window.location.href = pathname + '?fromLogin';
      return;
    }

    if (pathname && pathname.indexOf(document.location.origin) === 0) {
      window.location.href = pathname;
      return;
    }

    window.location.href = `/${fromLoginParam}`;
  }

  const payload = types.payloadParsers.LoginDetailsPayload();
  const urlParams = new URLSearchParams(window.location.search);
  const useSignupFormWithBirthDate =
    urlParams.get('useSignupFormWithBirthDate') === 'true';
  const googleClientId = document
    .querySelector('meta[name="google-signin-client_id"]')
    ?.getAttribute('content');
  const githubClientId = payload.githubClientId;
  const githubState = payload.githubState;
  if (payload.statusError) {
    ui.warning(payload.statusError);
  } else if (payload.verifyEmailSuccessfully) {
    ui.success(payload.verifyEmailSuccessfully);
  }

  const locationHash = window.location.hash.substring(1);
  let initialActiveTab: AvailableTabs = AvailableTabs.Login;
  if (locationHash === AvailableTabs.Signup) {
    initialActiveTab = AvailableTabs.Signup;
  }

  const userSignin = new Vue({
    el: '#main-container',
    components: {
      'omegaup-login-signin': login_Signin,
      'vue-recaptcha': VueRecaptcha,
    },
    data: () => ({
      initialActiveTab,
    }),
    render: function (createElement) {
      return createElement('omegaup-login-signin', {
        props: {
          validateRecaptcha: payload.validateRecaptcha,
          facebookUrl: payload.facebookUrl,
          githubClientId,
          githubState,
          googleClientId,
          hasVisitedSection: payload.hasVisitedSection,
          useSignupFormWithBirthDate,
          initialActiveTab: this.initialActiveTab,
        },
        on: {
          'register-and-login': ({
            over13Checked,
            username,
            email,
            dateOfBirth,
            parentEmail,
            password,
            passwordConfirmation,
            recaptchaResponse,
            termsAndPolicies,
          }: {
            over13Checked: boolean;
            username: string;
            email: string;
            dateOfBirth: Date;
            parentEmail: string;
            password: string;
            passwordConfirmation: string;
            recaptchaResponse: string;
            termsAndPolicies: boolean;
          }) => {
            if (!termsAndPolicies) {
              ui.error(T.privacyPolicyNotAccepted);
              return;
            }
            if (password != passwordConfirmation) {
              ui.error(T.passwordMismatch);
              return;
            }
            if (password.length < 8) {
              ui.error(T.loginPasswordTooShort);
              return;
            }
            if (!useSignupFormWithBirthDate) {
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
              return;
            }
            const request: {
              username: string;
              email?: string;
              birth_date: Date;
              parent_email?: string;
              password: string;
              recaptcha: string;
            } = {
              username,
              birth_date: new Date(dateOfBirth),
              password,
              recaptcha: recaptchaResponse,
            };
            if (over13Checked) {
              request.email = email;
            } else {
              request.parent_email = parentEmail;
            }

            api.User.create(request)
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

  const onActiveTab = (tab: AvailableTabs): void => {
    userSignin.initialActiveTab = tab;
    window.location.hash = `#${tab}`;
  };
  EventBus.$on('update:activeTab', onActiveTab);
});
