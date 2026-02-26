<template>
  <div class="card">
    <div class="card-header">
      <h2 class="card-title">{{ T.loginHeader }}</h2>
    </div>
    <div class="card-body">
      <div class="row justify-content-md-center">
        <div class="col-md-5 mx-2 login-section">
        <div class="col-md-4 col-md-offset-2 introjs-federated">
          <h4>{{ T.loginFederated }}</h4>
          <div class="row">
            <div class="col-xs-12 text-left py-2 pl-3">
              <!-- id-lint off -->
              <div
                id="g_id_onload"
                :data-client_id="googleClientId"
                :data-login_uri="loginUri"
                data-auto_prompt="false"
              ></div>
              <div
                class="g_id_signin introjs-google"
                data-type="standard"
                data-size="large"
                data-theme="outline"
                data-text="signin_with"
                data-shape="rectangular"
                data-logo_alignment="left"
              ></div>
              <!-- id-lint on -->
              <button
                data-login-github
                class="btn btn-block btn-outline-secondary mt-3 github-login-btn introjs-github"
                type="button"
                :disabled="!githubClientId"
                :aria-label="T.loginGithub"
                @click.prevent="loginWithGithub"
              >
                <img
                  src="https://github.githubassets.com/images/modules/logos_page/GitHub-Mark.png"
                  alt="GitHub"
                  class="github-icon"
                  height="20"
                  width="20"
                />
                {{ T.loginGithub }}
              </button>
            </div>
          </div>
        </div>

        <div class="col-md-5 mx-2 login-section">
        <div class="col-md-4 col-md-offset-2 introjs-native">
          <h4>{{ T.loginNative }}</h4>
          <form class="form-horizontal">
            <div class="form-group">
              <label for="user">{{ T.loginEmailUsername }}</label>
              <input
                v-model="usernameOrEmail"
                data-login-username
                name="login_username"
                type="text"
                class="form-control"
                tabindex="1"
                autocomplete="username"
              />
            </div>

            <div class="form-group">
              <label for="pass"
                >{{ T.loginPassword }} (<a
                  href="/login/password/recover/"
                  data-login-recover
                  >{{ T.loginRecover }}</a
                >)</label
              >
              <omegaup-password-input
                v-model="password"
                data-login-password
                name="login_password"
                :tabindex="2"
                autocomplete="current-password"
              />
            </div>

            <div class="form-group">
              <button
                data-login-submit
                class="btn btn-primary form-control"
                name="login"
                @click.prevent="$emit('login', usernameOrEmail, password)"
              >
                {{ T.loginLogIn }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';
import T from '../../lang';
import omegaup_PasswordInput from '../common/PasswordInput.vue';
import 'intro.js/introjs.css';
import introJs from 'intro.js';
import VueCookies from 'vue-cookies';

Vue.use(VueCookies, { expire: -1 });

@Component({
  components: {
    'omegaup-password-input': omegaup_PasswordInput,
  },
})
export default class Login extends Vue {
  @Prop() facebookUrl!: string;
  @Prop({ default: '' }) githubClientId!: string;
  @Prop({ default: null }) githubState!: string | null;
  @Prop() googleClientId!: string;
  @Prop({ default: 'login' }) activeTab!: string;

  usernameOrEmail: string = '';
  password: string = '';
  T = T;
  githubCsrfState: string | null = null;
  introStarted: boolean = false;

  mounted() {
    // The reason for loading the script here instead of the `template.tpl` file
    // is that sometimes the script runs after the DOM is ready, and the element
    // may not exist yet
    const script = document.createElement('script');
    script.src = 'https://accounts.google.com/gsi/client';
    document.body.appendChild(script);

    this.initializeGithubCsrfToken();
    this.maybeStartIntro();
  }

  @Watch('activeTab')
  onActiveTabChanged(newValue: string): void {
    if (newValue === 'login') {
      this.maybeStartIntro();
    }
  }

  maybeStartIntro(): void {
    if (this.introStarted || this.$cookies.get('has-visited-login')) {
      return;
    }
    if (this.activeTab !== 'login') {
      return;
    }

    this.$nextTick(() => {
      if (this.introStarted || this.$cookies.get('has-visited-login')) {
        return;
      }
      const title = T.loginFormInteractiveGuideTitle;
      const steps: Array<{
        title: string;
        intro: string;
        element?: Element;
      }> = [
        {
          title,
          intro: T.loginFormInteractiveGuideWelcome,
        },
      ];
      const addStep = (selector: string, intro: string): void => {
        const element = document.querySelector(selector);
        if (!element) {
          return;
        }
        steps.push({
          element,
          title,
          intro,
        });
      };

      addStep('.introjs-federated', T.loginFormInteractiveGuideFederated);
      addStep('.introjs-google', T.loginFormInteractiveGuideGoogle);
      addStep('.introjs-github', T.loginFormInteractiveGuideGithub);
      addStep('.introjs-native', T.loginFormInteractiveGuideNative);
      addStep('[data-login-username]', T.loginFormInteractiveGuideUsername);
      addStep('[data-login-password]', T.loginFormInteractiveGuidePassword);
      addStep('[data-login-recover]', T.loginFormInteractiveGuideRecover);
      addStep('[data-login-submit]', T.loginFormInteractiveGuideSubmit);

      if (steps.length <= 1) {
        return;
      }
      this.introStarted = true;
      introJs()
        .setOptions({
          nextLabel: T.interactiveGuideNextButton,
          prevLabel: T.interactiveGuidePreviousButton,
          doneLabel: T.interactiveGuideDoneButton,
          steps,
        })
        .start();
      this.$cookies.set('has-visited-login', true, -1);
    });
  }

  get loginUri(): string {
    return document.location.href;
  }

  initializeGithubCsrfToken(): void {
    const storedState = sessionStorage.getItem('github_oauth_state');
    if (storedState) {
      this.githubCsrfState = storedState;
    } else if (this.githubState) {
      this.githubCsrfState = this.githubState;
      sessionStorage.setItem('github_oauth_state', this.githubState);
    } else {
      const generatedState = this.generateSecureRandomString();
      this.githubCsrfState = generatedState;
      sessionStorage.setItem('github_oauth_state', generatedState);
    }

    if (this.githubCsrfState) {
      document.cookie = `github_oauth_state=${this.githubCsrfState};path=/;SameSite=Lax`;
    }
  }

  loginWithGithub(): void {
    if (!this.githubClientId) {
      return;
    }

    const state =
      sessionStorage.getItem('github_oauth_state') ||
      this.githubCsrfState ||
      this.generateSecureRandomString();
    sessionStorage.setItem('github_oauth_state', state);
    document.cookie = `github_oauth_state=${state};path=/;SameSite=Lax`;

    const redirectUri = new URL('/login', window.location.origin);
    redirectUri.searchParams.set('third_party_login', 'github');

    const currentParams = new URLSearchParams(window.location.search);
    const redirectParam = currentParams.get('redirect');
    if (redirectParam) {
      redirectUri.searchParams.set('redirect', redirectParam);
    }

    const params = new URLSearchParams({
      client_id: this.githubClientId,
      redirect_uri: redirectUri.toString(),
      scope: 'read:user user:email',
      state,
      allow_signup: 'true',
    });

    window.location.href = `https://github.com/login/oauth/authorize?${params.toString()}`;
  }

  generateSecureRandomString(): string {
    const validChars =
      'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    const len = 16;

    if (typeof window.crypto === 'object') {
      const arr = new Uint8Array(len);
      window.crypto.getRandomValues(arr);
      return Array.from(
        arr,
        (value) => validChars[value % validChars.length],
      ).join('');
    }

    // Without window.crypto
    let result = '';
    for (let i = 0; i < len; i++) {
      result += validChars.charAt(
        Math.floor(Math.random() * validChars.length),
      );
    }
    return result;
  }
}
</script>

<style scoped>
.login-section {
  background-color: #f8f9fa;
  border: 2px solid #dee2e6;
  border-radius: 8px;
  padding: 24px;
  margin-bottom: 16px;
}

.login-section:hover {
  border-color: #adb5bd;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  transition: all 0.3s ease;
}

h4 {
  color: #212529;
  font-weight: 600;
  margin-bottom: 20px;
  border-bottom: 2px solid #007bff;
  padding-bottom: 10px;
}

.github-login-btn {
  background-color: var(--btn-github-background-color);
  border: 1px solid var(--btn-github-border-color);
  color: var(--btn-github-font-color);
}

.github-login-btn:hover:not(:disabled) {
  background-color: var(--btn-github-background-color--hover);
  border-color: var(--btn-github-border-color--hover);
}

.github-login-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.github-icon {
  display: inline-block;
  margin-right: 8px;
  vertical-align: middle;
}
</style>
