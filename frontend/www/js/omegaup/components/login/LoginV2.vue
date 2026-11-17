<template>
  <div class="login-card">
    <!-- Left Branding / Motivational Pane -->
    <div class="login-left-pane">
      <div class="left-pane-pattern" aria-hidden="true"></div>
      <div class="glow-circle glow-circle-large" aria-hidden="true"></div>
      <div class="glow-circle glow-circle-small" aria-hidden="true"></div>

      <div class="left-pane-top">
        <div class="brand-header">
          <span class="brand-wordmark">
            <span class="brand-omega">omega</span
            ><span class="brand-up">Up</span>
          </span>
        </div>
      </div>

      <div class="left-pane-center my-auto">
        <h2 class="motivational-headline">
          {{ T.loginMotivationalHeadline }}
        </h2>
        <p class="motivational-subtext">
          {{ T.loginMotivationalSubtext }}
        </p>
      </div>

      <div class="left-pane-footer">
        <span>{{ T.loginFooterCopyright }} &copy; {{ currentYear }}</span>
      </div>
    </div>

    <!-- Right Authentication Pane -->
    <div class="login-right-pane">
      <h1 class="login-title">{{ T.loginHeader }}</h1>
      <p class="login-subtitle">
        {{ T.loginRightSubtitle }}
      </p>

      <div
        class="auth-tabs"
        role="tablist"
        @keydown.left.prevent="$emit('change-tab', 'login')"
        @keydown.right.prevent="$emit('change-tab', 'signup')"
      >
        <button
          id="tab-login"
          type="button"
          class="auth-tab"
          :class="{ active: activeTab === 'login' }"
          role="tab"
          :aria-selected="activeTab === 'login'"
          aria-controls="tabpanel-login"
          :tabindex="activeTab === 'login' ? 0 : -1"
          @click.prevent="$emit('change-tab', 'login')"
        >
          {{ T.omegaupTitleLogin }}
        </button>
        <button
          id="tab-signup"
          type="button"
          class="auth-tab"
          :class="{ active: activeTab === 'signup' }"
          role="tab"
          :aria-selected="activeTab === 'signup'"
          aria-controls="tabpanel-signup"
          :tabindex="activeTab === 'signup' ? 0 : -1"
          @click.prevent="$emit('change-tab', 'signup')"
        >
          {{ T.loginSignUp }}
        </button>
      </div>

      <div
        id="tabpanel-login"
        role="tabpanel"
        aria-labelledby="tab-login"
      >
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
            :placeholder="T.loginUsernamePlaceholder"
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
      </div>

      <div class="auth-divider">
        <span>{{ T.loginOrDivider }}</span>
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
              <span>{{ T.loginSignInWithGoogle }}</span>
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
  name: 'omegaup-login-v2',
  components: {
    'omegaup-password-input': omegaup_PasswordInput,
  },
})
export default class LoginV2 extends Vue {
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
.login-card {
  display: flex;
  flex-direction: row;
  align-items: stretch;
  max-width: 900px;
  width: 100%;
  margin: 0 auto;
  background-color: var(--login-right-pane-bg);
  border: 1px solid var(--login-card-border-color);
  border-radius: 20px;
  box-shadow: 0 20px 40px -15px var(--login-card-box-shadow);
  overflow: hidden;

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
  min-width: 330px;
  background-color: var(--header-primary-color);
  background: var(--header-primary-color);
  padding: 40px 36px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  color: var(--login-left-pane-font-color);

  @media (max-width: 768px) {
    min-width: 100%;
    padding: 32px 24px;
  }

  .left-pane-pattern {
    position: absolute;
    top: 0;
    left: 0;
    width: 200px;
    height: 200px;
    background-image: radial-gradient(
      var(--login-left-pane-pattern-dot-color) 1.5px,
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
      width: 260px;
      height: 260px;
      background: var(--login-left-pane-glow-large-color);
      filter: blur(45px);
      bottom: -60px;
      right: -60px;
    }

    &.glow-circle-small {
      width: 170px;
      height: 170px;
      background: var(--login-left-pane-glow-small-color);
      filter: blur(28px);
      bottom: 40px;
      right: 30px;
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

    .brand-wordmark {
      font-size: 26px;
      font-weight: 800;
      letter-spacing: -0.03em;
      line-height: 1;

      .brand-omega {
        color: var(--login-brand-omega-color);
      }

      .brand-up {
        color: var(--login-brand-up-color);
      }
    }
  }

  .left-pane-center {
    margin-top: auto;
    margin-bottom: auto;
    padding: 40px 0;

    .motivational-headline {
      color: var(--login-motivational-headline-color);
      font-size: 28px;
      font-weight: 800;
      line-height: 1.2;
      letter-spacing: -0.01em;
      text-transform: uppercase;
      margin: 0;
    }

    .motivational-subtext {
      color: var(--login-motivational-subtext-color);
      font-size: 13.5px;
      font-style: italic;
      line-height: 1.55;
      margin-top: 18px;
      margin-bottom: 0;
      opacity: 0.95;
    }
  }

  .left-pane-footer {
    color: var(--login-footer-color);
    font-size: 12px;
    opacity: 0.75;
    font-weight: 500;
  }
}

// ------------------------------------
// Right Pane (The Login Form)
// ------------------------------------
.login-right-pane {
  flex: 1 1 auto;
  background-color: var(--login-right-pane-bg);
  padding: 44px 44px;
  display: flex;
  flex-direction: column;
  justify-content: center;

  @media (max-width: 768px) {
    padding: 32px 20px;
  }

  .login-title {
    color: var(--login-title-color);
    font-size: 26px;
    font-weight: 800;
    line-height: 1.25;
    letter-spacing: -0.02em;
    margin: 0 0 6px 0;
  }

  .login-subtitle {
    color: var(--login-subtitle-color);
    font-size: 13.5px;
    line-height: 1.5;
    margin: 0 0 20px 0;
  }

  .auth-tabs {
    display: flex;
    gap: 20px;
    margin-bottom: 22px;
    border-bottom: 1.5px solid var(--login-tabs-border-color);

    .auth-tab {
      background: none;
      border: none;
      padding: 0 0 10px 0;
      font-size: 14.5px;
      font-weight: 500;
      color: var(--login-tab-inactive-color);
      cursor: pointer;
      position: relative;
      transition: color 0.15s ease;

      &.active {
        color: var(--login-tab-active-color);
        font-weight: 700;

        &::after {
          content: '';
          position: absolute;
          bottom: -1.5px;
          left: 0;
          right: 0;
          height: 3px;
          background-color: var(--login-tab-active-color);
          border-radius: 3px 3px 0 0;
        }
      }

      &:hover:not(.active) {
        color: var(--login-tab-active-color);
      }
    }
  }

  .native-login-form {
    .auth-label {
      display: block;
      font-size: 13.5px;
      font-weight: 600;
      color: var(--login-label-color);
      margin-bottom: 6px;
      text-align: left;
    }

    .auth-input {
      width: 100%;
      height: 46px;
      border: 1.5px solid var(--login-input-border-color);
      border-radius: 10px;
      padding: 10px 16px;
      font-size: 14px;
      color: var(--login-label-color);
      background-color: var(--login-input-bg);
      box-shadow: none;
      transition: all 0.2s ease;

      &::placeholder {
        color: var(--login-placeholder-color);
      }

      &:focus {
        border-color: var(--header-primary-color);
        box-shadow: 0 0 0 3px var(--login-input-focus-shadow);
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
        font-weight: 500;
        color: var(--login-link-color);
        text-decoration: none;
        transition: color 0.15s ease;

        &:hover {
          color: var(--login-link-hover-color);
          text-decoration: underline;
        }
      }
    }

    ::v-deep .password-input-wrapper {
      width: 100%;

      input {
        width: 100%;
        height: 46px;
        border: 1.5px solid var(--login-input-border-color);
        border-radius: 10px;
        padding: 10px 42px 10px 16px;
        font-size: 14px;
        color: var(--login-label-color);
        background-color: var(--login-input-bg);
        box-shadow: none;
        transition: all 0.2s ease;

        &::placeholder {
          color: var(--login-placeholder-color);
        }

        &:focus {
          border-color: var(--header-primary-color);
          box-shadow: 0 0 0 3px var(--login-input-focus-shadow);
          outline: none;
        }
      }

      .password-toggle-btn {
        right: 10px;
        color: var(--login-password-toggle-color);

        &:hover {
          color: var(--header-primary-color);
        }
      }
    }

    .auth-submit-btn {
      width: 100%;
      height: 48px;
      background-color: var(--login-btn-bg);
      color: var(--login-btn-font-color);
      font-size: 15px;
      font-weight: 600;
      border: none;
      border-radius: 24px;
      box-shadow: 0 6px 16px 0 var(--login-btn-shadow);
      cursor: pointer;
      transition: all 0.2s ease;
      margin-top: 10px;

      &:hover {
        background-color: var(--login-btn-hover-bg);
        box-shadow: 0 8px 20px 0 var(--login-btn-shadow-hover);
        transform: translateY(-1px);
        color: var(--login-btn-font-color);
      }

      &:active {
        transform: translateY(0);
        box-shadow: 0 2px 8px 0 var(--login-btn-shadow);
      }
    }
  }

  .auth-divider {
    display: flex;
    align-items: center;
    text-align: center;
    margin: 22px 0 18px;
    color: var(--login-divider-color);
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 0.05em;

    &::before,
    &::after {
      content: '';
      flex: 1;
      border-bottom: 1px solid var(--login-divider-line-color);
    }

    span {
      padding: 0 12px;
    }
  }

  .social-login-section {
    .social-login-heading {
      font-size: 13.5px;
      font-weight: 600;
      color: var(--login-social-heading-color);
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
        border-radius: 10px !important;
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
      border: 1.5px solid var(--login-social-btn-border-color);
      border-radius: 10px;
      background-color: var(--login-social-btn-bg);
      color: var(--login-social-btn-font-color);
      font-size: 13px;
      font-weight: 500;
      cursor: pointer;
      transition: all 0.2s ease;
      white-space: nowrap;

      .social-icon {
        flex-shrink: 0;
      }

      &:hover:not(:disabled) {
        border-color: var(--login-social-btn-hover-border);
        background-color: var(--login-social-btn-hover-bg);
        color: var(--login-social-btn-font-color);
      }

      &:disabled {
        opacity: 0.5;
        cursor: not-allowed;
      }
    }

    .github-btn {
      color: var(--login-social-btn-font-color);

      .github-svg {
        color: var(--login-social-btn-font-color);
      }
    }
  }
}
</style>
