<template>
  <div class="login-card">
    <!-- Left Pane (Visual / Motivational) -->
    <div class="login-left-pane">
      <div class="left-pane-pattern" aria-hidden="true"></div>
      <div class="glow-circle glow-circle-large" aria-hidden="true"></div>
      <div class="glow-circle glow-circle-small" aria-hidden="true"></div>

      <div class="left-pane-top">
        <div class="brand-header">
          <span class="brand-symbol" aria-hidden="true">Ω</span>
          <span class="brand-wordmark">
            <span class="brand-omega">omega</span
            ><span class="brand-up">Up</span>
          </span>
        </div>
      </div>

      <div class="left-pane-center my-auto">
        <h2 class="motivational-headline">
          CODE A BETTER<br />
          FUTURE. FOR<br />
          EVERYONE.
        </h2>
        <p class="motivational-subtext">
          Welcome back to the platform. Choose your preferred way to access your
          account.
        </p>
      </div>

      <div class="left-pane-footer">
        <span>omegaUp © {{ currentYear }}</span>
      </div>
    </div>

    <!-- Right Pane (Functional Login Form) -->
    <div class="login-right-pane">
      <h1 class="login-title">{{ T.loginHeader }}</h1>
      <p class="login-subtitle">
        Welcome back! Please choose your preferred method to access the platform.
      </p>

      <div class="auth-tabs" role="tablist">
        <button
          type="button"
          class="auth-tab"
          :class="{ active: activeTab === 'login' }"
          role="tab"
          :aria-selected="activeTab === 'login'"
          @click.prevent="$emit('change-tab', 'login')"
        >
          {{ T.omegaupTitleLogin }}
        </button>
        <button
          type="button"
          class="auth-tab"
          :class="{ active: activeTab === 'signup' }"
          role="tab"
          :aria-selected="activeTab === 'signup'"
          @click.prevent="$emit('change-tab', 'signup')"
        >
          {{ T.loginSignUp }}
        </button>
      </div>

      <form
        class="native-login-form introjs-native"
        @submit.prevent="$emit('login', usernameOrEmail, password)"
      >
        <div class="form-group mb-3">
          <label for="login-username" class="auth-label">
            {{ T.loginEmailUsername }}
          </label>
          <input
            id="login-username"
            v-model="usernameOrEmail"
            data-login-username
            name="login_username"
            type="text"
            class="form-control auth-input"
            placeholder="Enter your email or username"
            tabindex="1"
            autocomplete="username"
            required
          />
        </div>

        <div class="form-group mb-3">
          <div class="password-label-row">
            <label for="login-password" class="auth-label mb-0">
              {{ T.loginPassword }}
            </label>
            <a
              href="/login/password/recover/"
              data-login-recover
              class="forgot-password-link"
            >
              {{ T.loginRecover }}
            </a>
          </div>
          <omegaup-password-input
            id="login-password"
            v-model="password"
            data-login-password
            name="login_password"
            input-class="auth-input"
            placeholder="••••••••••••"
            :tabindex="2"
            autocomplete="current-password"
            required
          />
        </div>

        <button
          data-login-submit
          type="submit"
          class="btn auth-submit-btn"
          name="login"
        >
          {{ T.loginLogIn }}
        </button>
      </form>

      <div class="auth-divider">
        <span>OR</span>
      </div>

      <div class="social-login-section introjs-federated">
        <div class="social-login-heading">{{ T.loginFederated }}</div>
        <div class="social-login-grid">
          <div class="google-login-container introjs-google">
            <!-- id-lint off -->
            <div
              id="g_id_onload"
              :data-client_id="googleClientId"
              :data-login_uri="loginUri"
              data-auto_prompt="false"
            ></div>
            <div
              v-show="googleClientId"
              class="g_id_signin"
              data-type="standard"
              data-size="large"
              data-theme="outline"
              data-text="signin_with"
              data-shape="rectangular"
              data-logo_alignment="left"
              data-width="190"
            ></div>
            <!-- id-lint on -->
            <button
              v-if="!googleClientId"
              class="social-btn google-btn"
              type="button"
              disabled
            >
              <svg
                class="social-icon google-svg"
                viewBox="0 0 24 24"
                width="18"
                height="18"
                aria-hidden="true"
              >
                <path
                  fill="#4285F4"
                  d="M23.745 12.27c0-.7-.06-1.4-.19-2.07H12v4.51h6.6c-.29 1.52-1.14 2.82-2.4 3.68v3.05h3.88c2.27-2.09 3.665-5.17 3.665-9.17z"
                />
                <path
                  fill="#34A853"
                  d="M12 24c3.24 0 5.95-1.08 7.93-2.91l-3.88-3.05c-1.08.72-2.45 1.16-4.05 1.16-3.12 0-5.77-2.1-6.72-4.93H1.25v3.15C3.26 21.36 7.33 24 12 24z"
                />
                <path
                  fill="#FBBC05"
                  d="M5.28 14.27c-.25-.72-.38-1.49-.38-2.27s.13-1.55.38-2.27V6.58H1.25C.45 8.18 0 9.99 0 12s.45 3.82 1.25 5.42l4.03-3.15z"
                />
                <path
                  fill="#EA4335"
                  d="M12 4.75c1.77 0 3.35.61 4.6 1.8l3.42-3.42C17.95 1.19 15.24 0 12 0 7.33 0 3.26 2.64 1.25 6.58l4.03 3.15c.95-2.83 3.6-4.98 6.72-4.98z"
                />
              </svg>
              <span>Sign in with Google</span>
            </button>
          </div>

          <button
            data-login-github
            class="social-btn github-btn introjs-github"
            type="button"
            :disabled="!githubClientId"
            :aria-label="T.loginGithub"
            @click.prevent="loginWithGithub"
          >
            <svg
              class="social-icon github-svg"
              viewBox="0 0 24 24"
              width="18"
              height="18"
              aria-hidden="true"
            >
              <path
                fill="currentColor"
                d="M12 0C5.37 0 0 5.37 0 12c0 5.31 3.435 9.795 8.205 11.385.6.105.825-.255.825-.57 0-.285-.015-1.23-.015-2.235-3.015.555-3.795-.735-4.035-1.41-.135-.345-.72-1.41-1.23-1.695-.42-.225-1.02-.78-.015-.795.945-.015 1.62.87 1.845 1.23 1.08 1.815 2.805 1.305 3.495.99.105-.78.42-1.305.765-1.605-2.67-.3-5.46-1.335-5.46-5.925 0-1.305.465-2.385 1.23-3.225-.12-.3-.54-1.53.12-3.18 0 0 1.005-.315 3.3 1.23.96-.27 1.98-.405 3-.405s2.04.135 3 .405c2.295-1.56 3.3-1.23 3.3-1.23.66 1.65.24 2.88.12 3.18.765.84 1.23 1.905 1.23 3.225 0 4.605-2.805 5.625-5.475 5.925.435.375.81 1.095.81 2.22 0 1.605-.015 2.895-.015 3.3 0 .315.225.69.825.57A12.02 12.02 0 0024 12c0-6.63-5.37-12-12-12z"
              />
            </svg>
            <span>{{ T.loginGithub }}</span>
          </button>
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

Vue.use(VueCookies, { expires: -1 });

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

  get currentYear(): number {
    return new Date().getFullYear();
  }

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

<style scoped lang="scss">
@import '../../../../sass/main.scss';

.login-card {
  display: flex;
  flex-direction: row;
  align-items: stretch;
  max-width: 896px;
  width: 100%;
  margin: 0 auto;
  background-color: #ffffff;
  border: 1px solid #f1f5f9;
  border-radius: 24px;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
  overflow: hidden;
  font-family:
    Inter,
    -apple-system,
    BlinkMacSystemFont,
    'Segoe UI',
    Roboto,
    sans-serif;

  @media (max-width: 768px) {
    flex-direction: column;
  }
}

// ------------------------------------
// Left Pane (Visual / Motivational)
// ------------------------------------
.login-left-pane {
  position: relative;
  overflow: hidden;
  flex: 0 0 42%;
  min-width: 340px;
  background: linear-gradient(135deg, #1e60f2 0%, #1244c4 100%);
  padding: 40px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  color: #ffffff;

  @media (max-width: 768px) {
    min-width: 100%;
    padding: 32px 24px;
  }

  .left-pane-pattern {
    position: absolute;
    top: 0;
    left: 0;
    width: 180px;
    height: 180px;
    background-image: radial-gradient(
      rgba(255, 255, 255, 0.4) 1.5px,
      transparent 1.5px
    );
    background-size: 16px 16px;
    opacity: 0.25;
    pointer-events: none;
    z-index: 1;
  }

  .glow-circle {
    position: absolute;
    border-radius: 50%;
    pointer-events: none;
    z-index: 1;

    &.glow-circle-large {
      width: 256px;
      height: 256px;
      background: rgba(255, 255, 255, 0.1);
      filter: blur(40px);
      bottom: -64px;
      right: -64px;
    }

    &.glow-circle-small {
      width: 160px;
      height: 160px;
      background: rgba(96, 165, 250, 0.2);
      filter: blur(24px);
      bottom: 32px;
      right: 24px;
    }
  }

  .left-pane-top,
  .left-pane-center,
  .left-pane-footer {
    position: relative;
    z-index: 10;
  }

  .brand-header {
    display: inline-flex;
    align-items: center;
    gap: 12px;

    .brand-symbol {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 38px;
      height: 38px;
      background-color: #ffffff;
      border-radius: 10px;
      color: #1e60f2;
      font-family: Georgia, 'Times New Roman', serif;
      font-size: 24px;
      font-weight: 700;
      line-height: 1;
      box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }

    .brand-wordmark {
      font-size: 24px;
      font-weight: 700;
      letter-spacing: -0.04em;
      line-height: 1;

      .brand-omega {
        color: #000000;
      }

      .brand-up {
        color: #ffffff;
      }
    }
  }

  .left-pane-center {
    margin-top: auto;
    margin-bottom: auto;
    padding: 36px 0;

    .motivational-headline {
      color: #ffffff;
      font-size: 30px;
      font-weight: 800;
      line-height: 1.15;
      letter-spacing: -0.01em;
      text-transform: uppercase;
      margin: 0;
    }

    .motivational-subtext {
      color: #bfdbfe;
      font-size: 14px;
      line-height: 1.55;
      margin-top: 16px;
      margin-bottom: 0;
      opacity: 0.9;
    }
  }

  .left-pane-footer {
    color: #bfdbfe;
    font-size: 12.5px;
    opacity: 0.6;
    font-weight: 500;
  }
}

// ------------------------------------
// Right Pane (The Login Form)
// ------------------------------------
.login-right-pane {
  flex: 1 1 auto;
  background-color: #ffffff;
  padding: 44px 44px;
  display: flex;
  flex-direction: column;
  justify-content: center;

  @media (max-width: 768px) {
    padding: 32px 20px;
  }

  .login-title {
    color: #0f172a;
    font-size: 28px;
    font-weight: 800;
    line-height: 1.2;
    letter-spacing: -0.02em;
    margin: 0 0 6px 0;
  }

  .login-subtitle {
    color: #64748b;
    font-size: 13.5px;
    line-height: 1.5;
    margin: 0 0 20px 0;
  }

  .auth-tabs {
    display: flex;
    gap: 20px;
    margin-bottom: 22px;
    border-bottom: 1.5px solid #f1f5f9;

    .auth-tab {
      background: none;
      border: none;
      padding: 0 0 10px 0;
      font-size: 15px;
      font-weight: 500;
      color: #64748b;
      cursor: pointer;
      position: relative;
      transition: color 0.15s ease;

      &.active {
        color: #0f172a;
        font-weight: 700;

        &::after {
          content: '';
          position: absolute;
          bottom: -1.5px;
          left: 0;
          right: 0;
          height: 3px;
          background-color: #1e60f2;
          border-radius: 3px 3px 0 0;
        }
      }

      &:hover:not(.active) {
        color: #1e60f2;
      }
    }
  }

  .native-login-form {
    .auth-label {
      display: block;
      font-size: 13.5px;
      font-weight: 600;
      color: #0f172a;
      margin-bottom: 6px;
      text-align: left;
    }

    .auth-input {
      width: 100%;
      height: 46px;
      border: 1.5px solid #e2e8f0;
      border-radius: 12px;
      padding: 10px 16px;
      font-size: 14px;
      color: #0f172a;
      background-color: #ffffff;
      box-shadow: none;
      transition: all 0.2s ease;

      &::placeholder {
        color: #94a3b8;
      }

      &:focus {
        border-color: #1e60f2;
        box-shadow: 0 0 0 3px rgba(30, 96, 242, 0.15);
        outline: none;
      }
    }

    .password-label-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 6px;

      .forgot-password-link {
        font-size: 13px;
        font-weight: 600;
        color: #1e60f2;
        text-decoration: none;
        transition: color 0.15s ease;

        &:hover {
          color: #1244c4;
          text-decoration: underline;
        }
      }
    }

    ::v-deep .password-input-wrapper {
      width: 100%;

      input {
        width: 100%;
        height: 46px;
        border: 1.5px solid #e2e8f0;
        border-radius: 12px;
        padding: 10px 42px 10px 16px;
        font-size: 14px;
        color: #0f172a;
        background-color: #ffffff;
        box-shadow: none;
        transition: all 0.2s ease;

        &::placeholder {
          color: #94a3b8;
        }

        &:focus {
          border-color: #1e60f2;
          box-shadow: 0 0 0 3px rgba(30, 96, 242, 0.15);
          outline: none;
        }
      }

      .password-toggle-btn {
        right: 10px;
        color: #94a3b8;

        &:hover {
          color: #1e60f2;
        }
      }
    }

    .auth-submit-btn {
      width: 100%;
      height: 48px;
      background-color: #1e60f2;
      color: #ffffff;
      font-size: 15px;
      font-weight: 600;
      border: none;
      border-radius: 12px;
      box-shadow: 0 4px 14px 0 rgba(30, 96, 242, 0.35);
      cursor: pointer;
      transition: all 0.2s ease;
      margin-top: 10px;

      &:hover {
        background-color: #1852d6;
        box-shadow: 0 6px 18px 0 rgba(30, 96, 242, 0.45);
        transform: translateY(-1px);
        color: #ffffff;
      }

      &:active {
        transform: translateY(0);
        box-shadow: 0 2px 8px 0 rgba(30, 96, 242, 0.35);
      }
    }
  }

  .auth-divider {
    display: flex;
    align-items: center;
    text-align: center;
    margin: 22px 0 18px;
    color: #94a3b8;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 0.05em;

    &::before,
    &::after {
      content: '';
      flex: 1;
      border-bottom: 1px solid #e2e8f0;
    }

    span {
      padding: 0 12px;
    }
  }

  .social-login-section {
    .social-login-heading {
      font-size: 13.5px;
      font-weight: 600;
      color: #475569;
      margin-bottom: 12px;
      text-align: left;
    }

    .social-login-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 12px;
      align-items: center;

      @media (max-width: 576px) {
        grid-template-columns: 1fr;
      }
    }

    .google-login-container {
      display: flex;
      align-items: center;
      justify-content: center;
      width: 100%;
      height: 44px;

      .g_id_signin {
        display: flex;
        justify-content: center;
        width: 100%;
      }

      ::v-deep iframe {
        border-radius: 12px !important;
      }
    }

    .social-btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      width: 100%;
      height: 44px;
      padding: 8px 12px;
      border: 1.5px solid #e2e8f0;
      border-radius: 12px;
      background-color: #ffffff;
      color: #0f172a;
      font-size: 13px;
      font-weight: 500;
      cursor: pointer;
      transition: all 0.2s ease;
      white-space: nowrap;

      .social-icon {
        flex-shrink: 0;
      }

      &:hover:not(:disabled) {
        border-color: #cbd5e1;
        background-color: #f8fafc;
        color: #0f172a;
      }

      &:disabled {
        opacity: 0.5;
        cursor: not-allowed;
      }
    }

    .github-btn {
      color: #0f172a;

      .github-svg {
        color: #0f172a;
      }
    }
  }
}
</style>
