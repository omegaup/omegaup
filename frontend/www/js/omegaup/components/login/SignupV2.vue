<template>
  <div class="signup-container">
    <div class="signup-card">
      <!-- Left Branding / Motivational Pane -->
      <aside class="signup-left-pane">
        <div class="left-pane-pattern" aria-hidden="true"></div>
        <div class="glow-circle glow-circle-large" aria-hidden="true"></div>
        <div class="glow-circle glow-circle-small" aria-hidden="true"></div>

        <div class="left-pane-top">
          <div class="brand-header">
            <h1 class="brand-title">
              <span class="brand-omega">omega</span
              ><span class="brand-up">Up</span>
            </h1>
          </div>
        </div>

        <div class="left-pane-center">
          <h2 class="signup-headline">
            {{ T.signupV2Headline }}
          </h2>
          <p class="signup-subtext">
            {{ T.signupV2Subtext }}
          </p>
        </div>

        <div class="left-pane-footer">
          <p class="copyright">
            {{ T.loginFooterCopyright }} &copy; {{ currentYear }}
          </p>
        </div>
      </aside>

      <!-- Right Registration Pane -->
      <main class="signup-right-pane">
        <div class="form-header">
          <h1 class="signup-title">{{ T.signupV2Header }}</h1>
          <p class="signup-subtitle">
            {{ T.signupV2Subtitle }}
          </p>
        </div>

        <div
          class="auth-tabs"
          role="tablist"
          @keydown.left.prevent="$emit('change-tab', 'login')"
          @keydown.right.prevent="$emit('change-tab', 'signup')"
        >
          <button
            id="signup-tab-login"
            type="button"
            class="auth-tab"
            :class="{ active: activeTab === 'login' }"
            role="tab"
            :aria-selected="activeTab === 'login'"
            aria-controls="signup-tabpanel-login"
            :tabindex="activeTab === 'login' ? 0 : -1"
            @click.prevent="$emit('change-tab', 'login')"
          >
            {{ T.omegaupTitleLogin }}
          </button>
          <button
            id="signup-tab-signup"
            type="button"
            class="auth-tab"
            :class="{ active: activeTab === 'signup' }"
            role="tab"
            :aria-selected="activeTab === 'signup'"
            aria-controls="signup-tabpanel-signup"
            :tabindex="activeTab === 'signup' ? 0 : -1"
            @click.prevent="$emit('change-tab', 'signup')"
          >
            {{ T.loginSignUp }}
          </button>
        </div>

        <!-- Signup Tab Panel -->
        <div
          id="signup-tabpanel-signup"
          role="tabpanel"
          aria-labelledby="signup-tab-signup"
        >
          <form
            class="signup-form introjs-signup-form"
            @submit.prevent="
              $emit('register-and-login', {
                username,
                email,
                password,
                passwordConfirmation,
                recaptchaResponse,
                termsAndPolicies,
              })
            "
          >
            <!-- 2x2 Form Grid -->
            <div class="form-grid">
              <div class="form-group">
                <label for="signup-username" class="auth-label">
                  {{ T.loginAccountName }}
                </label>
                <div class="input-wrapper">
                  <input
                    id="signup-username"
                    v-model="username"
                    data-signup-username
                    name="reg_username"
                    type="text"
                    class="auth-input introjs-username"
                    :placeholder="T.signupV2UsernamePlaceholder"
                    tabindex="1"
                    autocomplete="username"
                    required
                  />
                </div>
              </div>

              <div class="form-group">
                <label for="signup-email" class="auth-label">
                  {{ T.loginEmail }}
                </label>
                <div class="input-wrapper">
                  <input
                    id="signup-email"
                    v-model="email"
                    data-signup-email
                    name="reg_email"
                    type="email"
                    class="auth-input introjs-email"
                    :placeholder="T.signupV2EmailPlaceholder"
                    tabindex="2"
                    autocomplete="email"
                    required
                  />
                </div>
              </div>

              <div class="form-group">
                <label for="signup-password" class="auth-label">
                  {{ T.loginPasswordCreate }}
                </label>
                <div class="input-wrapper">
                  <omegaup-password-input
                    id="signup-password"
                    v-model="password"
                    data-signup-password
                    name="reg_password"
                    input-class="auth-input introjs-password"
                    :placeholder="T.signupV2PasswordPlaceholder"
                    :tabindex="3"
                    autocomplete="new-password"
                    required
                  />
                </div>
              </div>

              <div class="form-group">
                <label for="signup-repeat-password" class="auth-label">
                  {{ T.loginRepeatPassword }}
                </label>
                <div class="input-wrapper">
                  <omegaup-password-input
                    id="signup-repeat-password"
                    v-model="passwordConfirmation"
                    data-signup-repeat-password
                    data-signup-password-confirm
                    name="reg_password_confirmation"
                    input-class="auth-input introjs-confirmpassword"
                    :placeholder="T.signupV2RepeatPasswordPlaceholder"
                    :tabindex="4"
                    autocomplete="new-password"
                    required
                  />
                </div>
              </div>
            </div>

            <!-- Terms & Action Row -->
            <div class="terms-action-row introjs-terms-and-conditions">
              <div class="checkbox-group">
                <!-- id-lint off -->
                <div class="checkbox-wrapper">
                  <input
                    id="signup-v2-accept-privacy-policy"
                    v-model="privacyPolicyAccepted"
                    data-signup-accept-policies
                    data-signup-accept-privacy
                    type="checkbox"
                    class="form-checkbox"
                    required
                  />
                  <label for="signup-v2-accept-privacy-policy" class="checkbox-text-label">
                    <omegaup-markdown
                      :markdown="formattedAcceptPolicyMarkdown"
                    ></omegaup-markdown>
                  </label>
                </div>
                <div class="checkbox-wrapper">
                  <input
                    id="signup-v2-accept-code-of-conduct"
                    v-model="codeOfConductAccepted"
                    data-signup-accept-conduct
                    data-signup-accept-terms
                    type="checkbox"
                    class="form-checkbox"
                    required
                  />
                  <label for="signup-v2-accept-code-of-conduct" class="checkbox-text-label">
                    <omegaup-markdown
                      :markdown="formattedAcceptConductMarkdown"
                    ></omegaup-markdown>
                  </label>
                </div>
                <!-- id-lint on -->
              </div>

              <button
                data-signup-submit
                type="submit"
                class="btn auth-submit-btn btn-register introjs-register"
                name="sign_up"
              >
                {{ T.loginSignUp }}
              </button>
            </div>
          </form>

          <div class="auth-divider">
            <span>{{ T.loginOrDivider }}</span>
          </div>

          <div class="social-login-section">
            <div class="social-login-heading">{{ T.signupV2SocialHeading }}</div>
            <div class="social-buttons-grid">
              <div class="google-login-container introjs-google">
                <!-- id-lint off -->
                <div
                  id="signup_g_id_onload"
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
                  data-text="signup_with"
                  data-shape="rectangular"
                  data-logo_alignment="left"
                  data-width="190"
                ></div>
                <!-- id-lint on -->
                <button
                  v-if="!googleClientId"
                  class="btn-social google-btn"
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
                  <span>{{ T.signupV2GoogleButton }}</span>
                </button>
              </div>

              <button
                data-signup-github
                class="btn-social github-btn introjs-github"
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
                <span>{{ T.signupV2GithubButton }}</span>
              </button>
            </div>
          </div>

          <p class="already-have-account">
            {{ T.signupV2AlreadyHaveAccount }}
            <a
              href="/login/"
              class="signin-link"
              @click.prevent="$emit('change-tab', 'login')"
            >{{ T.signupV2SignIn }}</a>
          </p>
        </div>

        <!-- Login Tab Panel (empty — shown in LoginV2 context) -->
        <div
          id="signup-tabpanel-login"
          role="tabpanel"
          aria-labelledby="signup-tab-login"
          :hidden="activeTab !== 'login'"
        ></div>
      </main>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';
import T from '../../lang';
import omegaup_PasswordInput from '../common/PasswordInput.vue';
import omegaup_Markdown from '../Markdown.vue';
import 'intro.js/introjs.css';
import introJs from 'intro.js';
import VueCookies from 'vue-cookies';
import { getBlogUrl } from '../../urlHelper';
import * as ui from '../../ui';

Vue.use(VueCookies, { expires: -1 });

@Component({
  name: 'omegaup-signup-v2',
  components: {
    'omegaup-password-input': omegaup_PasswordInput,
    'omegaup-markdown': omegaup_Markdown,
  },
})
export default class SignupV2 extends Vue {
  @Prop({ default: '' }) githubClientId!: string;
  @Prop({ default: null }) githubState!: string | null;
  @Prop({ default: '' }) googleClientId!: string;
  @Prop({ default: false }) validateRecaptcha!: boolean;
  @Prop({ default: false }) hasVisitedSection!: boolean;
  @Prop({ default: false }) useSignupFormWithBirthDate!: boolean;
  @Prop({ default: 'signup' }) activeTab!: string;

  T = T;
  username: string = '';
  email: string = '';
  password: string = '';
  passwordConfirmation: string = '';
  recaptchaResponse: string = '';
  privacyPolicyAccepted: boolean = false;
  codeOfConductAccepted: boolean = false;
  githubCsrfState: string | null = null;
  introStarted = false;

  get currentYear(): number {
    return new Date().getFullYear();
  }

  get loginUri(): string {
    return document.location.href;
  }

  get termsAndPolicies(): boolean {
    return this.privacyPolicyAccepted && this.codeOfConductAccepted;
  }

  get formattedAcceptPolicyMarkdown(): string {
    const policyUrl = getBlogUrl('PrivacyPolicyURL');
    return ui.formatString(T.acceptPrivacyPolicy, {
      PrivacyPolicyURL: policyUrl,
    });
  }

  get formattedAcceptConductMarkdown(): string {
    const conductUrl = getBlogUrl('CodeofConductPolicyURL');
    return ui.formatString(T.acceptCodeOfConduct, {
      CodeofConductPolicyURL: conductUrl,
    });
  }

  mounted() {
    // Load Google Sign-In script
    const script = document.createElement('script');
    script.src = 'https://accounts.google.com/gsi/client';
    document.body.appendChild(script);

    this.initializeGithubCsrfToken();

    if (this.hasVisitedSection) {
      this.$cookies.set('has-visited-signup', true, -1);
    }
    this.maybeStartIntro();
  }

  @Watch('activeTab')
  onActiveTabChanged(newValue: string): void {
    if (newValue === 'signup') {
      this.maybeStartIntro();
    }
  }

  maybeStartIntro(): void {
    if (this.introStarted || this.$cookies.get('has-visited-signup')) {
      return;
    }
    if (this.activeTab !== 'signup') {
      return;
    }

    this.$nextTick(() => {
      if (this.introStarted || this.$cookies.get('has-visited-signup')) {
        return;
      }
      const title = T.signUpFormInteractiveGuideTitle;
      const steps: Array<{
        title: string;
        intro: string;
        element?: Element;
      }> = [
        {
          title,
          intro: T.signUpFormInteractiveGuideWelcome,
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

      addStep('.introjs-username', T.signUpFormInteractiveGuideUsername);
      addStep('.introjs-email', T.signUpFormInteractiveGuideEmail);
      addStep('.introjs-password', T.signUpFormInteractiveGuidePassword);
      addStep(
        '.introjs-confirmpassword',
        T.signUpFormInteractiveGuideConfirmPassword,
      );
      addStep(
        '.introjs-terms-and-conditions',
        T.signUpFormInteractiveGuideTermsAndConditions,
      );
      addStep('.introjs-register', T.signUpFormInteractiveGuideRegister);

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
      this.$cookies.set('has-visited-signup', true, -1);
    });
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

    // Fallback without window.crypto
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
.signup-container {
  width: 100%;
  max-width: 1140px;
  margin: 0 auto;
}

.signup-card {
  display: grid;
  grid-template-columns: 380px 1fr;
  width: 100%;
  margin: 0 auto;
  background-color: var(--signup-v2-right-pane-bg);
  border: 1px solid var(--signup-v2-card-border-color);
  border-radius: 28px;
  box-shadow: 0 20px 45px -10px var(--signup-v2-card-box-shadow),
    0 8px 16px -6px rgba(0, 0, 0, 0.04);
  overflow: hidden;
  min-height: 640px;

  @media (max-width: 960px) {
    grid-template-columns: 1fr;
    border-radius: 20px;
  }
}

// ------------------------------------
// Left Pane (Visual / Motivational)
// ------------------------------------
.signup-left-pane {
  position: relative;
  background-color: var(--header-primary-color);
  padding: 44px 38px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  overflow: hidden;
  color: var(--signup-v2-left-pane-font-color);

  @media (max-width: 960px) {
    min-width: 100%;
    padding: 32px 28px;
    gap: 20px;
  }

  .left-pane-pattern {
    position: absolute;
    top: 0;
    left: 0;
    width: 200px;
    height: 200px;
    background-image: radial-gradient(
      var(--signup-v2-left-pane-pattern-dot-color) 1.5px,
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
      background: var(--signup-v2-left-pane-glow-large-color);
      filter: blur(45px);
      bottom: -60px;
      right: -60px;
    }

    &.glow-circle-small {
      width: 170px;
      height: 170px;
      background: var(--signup-v2-left-pane-glow-small-color);
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

    .brand-title {
      font-size: 30px;
      font-weight: 800;
      letter-spacing: -0.5px;
      line-height: 1;
      margin: 0;

      .brand-omega {
        color: var(--signup-v2-brand-omega-color);
      }

      .brand-up {
        color: var(--signup-v2-brand-up-color);
      }
    }
  }

  .left-pane-center {
    margin-top: auto;
    margin-bottom: auto;
    padding: 40px 0;

    .signup-headline {
      font-size: 32px;
      font-weight: 900;
      line-height: 1.15;
      letter-spacing: -0.5px;
      text-transform: uppercase;
      margin-bottom: 20px;
      color: var(--signup-v2-headline-color);

      @media (max-width: 960px) {
        font-size: 24px;
      }
    }

    .signup-subtext {
      font-size: 15px;
      font-weight: 400;
      font-style: italic;
      opacity: 0.9;
      line-height: 1.5;
      color: var(--signup-v2-subtext-color);
      margin: 0;
    }
  }

  .left-pane-footer {
    .copyright {
      font-size: 13px;
      font-weight: 400;
      opacity: 0.75;
      color: var(--signup-v2-footer-color);
      margin: 0;
    }
  }
}

// ------------------------------------
// Right Pane (The Signup Form)
// ------------------------------------
.signup-right-pane {
  background-color: var(--signup-v2-right-pane-bg);
  padding: 48px 52px;
  display: flex;
  flex-direction: column;
  overflow-y: auto;

  @media (max-width: 960px) {
    padding: 32px 24px;
  }

  .form-header {
    margin-bottom: 24px;

    .signup-title {
      font-size: 26px;
      font-weight: 800;
      color: var(--signup-v2-title-color);
      letter-spacing: -0.5px;
      margin-bottom: 8px;
      line-height: 1.25;
    }

    .signup-subtitle {
      font-size: 14.5px;
      color: var(--signup-v2-subtitle-color);
      line-height: 1.45;
      margin: 0;
    }
  }

  .auth-tabs {
    display: flex;
    gap: 28px;
    border-bottom: 1px solid var(--signup-v2-tabs-border-color);
    margin-bottom: 28px;

    .auth-tab {
      background: none;
      border: none;
      padding: 0 0 10px 0;
      font-size: 15px;
      font-weight: 600;
      color: var(--signup-v2-tab-inactive-color);
      cursor: pointer;
      position: relative;
      transition: color 0.15s ease;

      &:hover {
        color: var(--signup-v2-tab-active-color);
      }

      &.active {
        color: var(--signup-v2-tab-active-color);

        &::after {
          content: '';
          position: absolute;
          bottom: -1px;
          left: 0;
          right: 0;
          height: 2px;
          background-color: var(--signup-v2-tab-active-color);
        }
      }
    }
  }

  .signup-form {
    .form-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      column-gap: 20px;
      row-gap: 18px;
      margin-bottom: 22px;

      @media (max-width: 960px) {
        grid-template-columns: 1fr;
      }

      .form-group {
        display: flex;
        flex-direction: column;

        .auth-label {
          display: block;
          font-size: 13.5px;
          font-weight: 600;
          color: var(--signup-v2-label-color);
          margin-bottom: 8px;
          text-align: left;
        }

        .input-wrapper {
          position: relative;
          display: flex;
          align-items: center;
          width: 100%;

          .auth-input {
            width: 100%;
            height: 48px;
            padding: 0 16px;
            font-size: 14px;
            color: var(--signup-v2-label-color);
            background-color: var(--signup-v2-input-bg);
            border: 1.5px solid var(--signup-v2-input-border-color);
            border-radius: 10px;
            outline: none;
            box-shadow: none;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;

            &::placeholder {
              color: var(--signup-v2-placeholder-color);
            }

            &:focus {
              border-color: var(--header-primary-color);
              box-shadow: 0 0 0 3px var(--signup-v2-input-focus-shadow);
            }
          }

          ::v-deep .password-input-wrapper {
            width: 100%;

            input {
              width: 100%;
              height: 48px;
              padding: 0 42px 0 16px;
              font-size: 14px;
              color: var(--signup-v2-label-color);
              background-color: var(--signup-v2-input-bg);
              border: 1.5px solid var(--signup-v2-input-border-color);
              border-radius: 10px;
              outline: none;
              box-shadow: none;
              transition: border-color 0.15s ease, box-shadow 0.15s ease;

              &::placeholder {
                color: var(--signup-v2-placeholder-color);
              }

              &:focus {
                border-color: var(--header-primary-color);
                box-shadow: 0 0 0 3px var(--signup-v2-input-focus-shadow);
              }
            }

            .password-toggle-btn {
              position: absolute;
              right: 14px;
              background: none;
              border: none;
              color: var(--signup-v2-password-toggle-color);
              cursor: pointer;
              display: flex;
              align-items: center;
              padding: 4px;

              &:hover {
                color: var(--header-primary-color);
              }
            }
          }
        }
      }
    }

    .terms-action-row {
      display: flex;
      align-items: flex-start;
      justify-content: space-between;
      gap: 24px;
      margin-top: 8px;
      margin-bottom: 8px;

      @media (max-width: 960px) {
        flex-direction: column;
        align-items: stretch;
      }

      .checkbox-group {
        display: flex;
        flex-direction: column;
        gap: 10px;
        flex: 1;

        .checkbox-wrapper {
          display: flex;
          align-items: flex-start;
          gap: 10px;

          .form-checkbox {
            margin-top: 2px;
            width: 16px;
            height: 16px;
            accent-color: var(--header-primary-color);
            cursor: pointer;
            flex-shrink: 0;
          }

          .checkbox-text-label {
            font-size: 13px;
            color: var(--signup-v2-subtitle-color);
            line-height: 1.4;
            cursor: pointer;
            margin-bottom: 0;

            ::v-deep a {
              color: var(--signup-v2-link-color);
              text-decoration: none;

              &:hover {
                color: var(--signup-v2-link-hover-color);
                text-decoration: underline;
              }
            }

            ::v-deep p {
              margin: 0;
              display: inline;
            }
          }
        }
      }

      .btn-register {
        min-width: 160px;
        height: 46px;
        padding: 0 28px;
        flex-shrink: 0;
        background-color: var(--signup-v2-btn-bg);
        color: var(--signup-v2-btn-font-color);
        border: none;
        border-radius: 10px;
        font-size: 15px;
        font-weight: 600;
        cursor: pointer;
        box-shadow: 0 4px 14px 0 var(--signup-v2-btn-shadow);
        transition: background-color 0.15s ease, transform 0.05s ease;

        @media (max-width: 960px) {
          width: 100%;
        }

        &:hover {
          background-color: var(--signup-v2-btn-hover-bg);
          box-shadow: 0 6px 18px 0 var(--signup-v2-btn-shadow-hover);
          color: var(--signup-v2-btn-font-color);
        }

        &:active {
          transform: translateY(1px);
        }
      }
    }
  }

  .auth-divider {
    display: flex;
    align-items: center;
    text-align: center;
    margin: 24px 0;
    color: var(--signup-v2-divider-color);
    font-size: 11.5px;
    font-weight: 700;
    letter-spacing: 0.5px;

    &::before,
    &::after {
      content: '';
      flex: 1;
      border-bottom: 1px solid var(--signup-v2-divider-line-color);
    }

    span {
      padding: 0 14px;
    }
  }

  .social-login-section {
    display: flex;
    flex-direction: column;

    .social-login-heading {
      font-size: 13px;
      font-weight: 600;
      color: var(--signup-v2-social-heading-color);
      margin-bottom: 12px;
      text-align: left;
    }

    .social-buttons-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 14px;

      @media (max-width: 960px) {
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

    .btn-social {
      height: 44px;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      background-color: var(--signup-v2-social-btn-bg);
      border: 1.5px solid var(--signup-v2-social-btn-border-color);
      border-radius: 10px;
      font-size: 13.5px;
      font-weight: 500;
      color: var(--signup-v2-social-btn-font-color);
      cursor: pointer;
      transition: background-color 0.15s ease, border-color 0.15s ease;
      width: 100%;

      .social-icon {
        width: 18px;
        height: 18px;
        flex-shrink: 0;
      }

      &:hover:not(:disabled) {
        background-color: var(--signup-v2-social-btn-hover-bg);
        border-color: var(--signup-v2-social-btn-hover-border);
        color: var(--signup-v2-social-btn-font-color);
      }

      &:disabled {
        opacity: 0.5;
        cursor: not-allowed;
      }
    }

    .github-btn {
      color: var(--signup-v2-social-btn-font-color);

      .github-svg {
        color: var(--signup-v2-social-btn-font-color);
      }
    }
  }

  .already-have-account {
    margin-top: 24px;
    font-size: 13.5px;
    color: var(--signup-v2-subtitle-color);
    text-align: center;

    .signin-link {
      color: var(--signup-v2-link-color);
      font-weight: 600;
      text-decoration: none;
      margin-left: 4px;
      transition: color 0.15s ease;

      &:hover {
        color: var(--signup-v2-link-hover-color);
        text-decoration: underline;
      }
    }
  }
}
</style>
