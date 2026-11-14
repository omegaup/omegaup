<template>
  <div class="signup-card">
    <!-- Left Pane (Visual / Motivational) -->
    <div class="signup-left-pane">
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
          JOIN THE<br />
          COMMUNITY.<br />
          START CODING.
        </h2>
        <p class="motivational-subtext">
          Create your account to solve challenging problems, compete in
          contests, and elevate your programming skills.
        </p>
      </div>

      <div class="left-pane-footer">
        <span>omegaUp © {{ currentYear }}</span>
      </div>
    </div>

    <!-- Right Pane (Functional Signup Form) -->
    <div class="signup-right-pane">
      <div class="signup-header card-header">
        <h1 class="signup-title card-title">{{ T.loginSignupHeader }}</h1>
        <p class="signup-subtitle">
          It's quick, easy, and completely free to get started.
        </p>
      </div>

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

      <!-- Standard Signup Form -->
      <form
        v-if="!useSignupFormWithBirthDate"
        class="native-signup-form"
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
        <div class="form-row">
          <div class="form-group introjs-username">
            <label class="auth-label">{{ T.loginAccountName }}</label>
            <input
              v-model="username"
              data-signup-username
              name="reg_username"
              class="form-control auth-input"
              autocomplete="username"
              placeholder="Choose a username"
              required
            />
          </div>
          <div class="form-group introjs-email">
            <label class="auth-label">{{ T.loginEmail }}</label>
            <input
              v-model="email"
              data-signup-email
              name="reg_email"
              type="email"
              class="form-control auth-input"
              autocomplete="email"
              placeholder="name@example.com"
              required
            />
          </div>
        </div>

        <div class="form-row">
          <div class="form-group introjs-password">
            <label class="auth-label">{{ T.loginPasswordCreate }}</label>
            <omegaup-password-input
              v-model="password"
              data-signup-password
              name="reg_password"
              autocomplete="new-password"
            />
          </div>
          <div class="form-group introjs-confirmpassword">
            <label class="auth-label">{{ T.loginRepeatPassword }}</label>
            <omegaup-password-input
              v-model="passwordConfirmation"
              data-signup-repeat-password
              name="reg_password_confirmation"
              autocomplete="new-password"
            />
          </div>
        </div>

        <!-- id-lint off -->
        <div class="terms-section introjs-terms-and-conditions">
          <div class="checkbox-wrapper">
            <input
              id="accept-privacy-policy"
              v-model="privacyPolicyAccepted"
              data-signup-accept-policies
              type="checkbox"
              required
            />
            <label for="accept-privacy-policy">
              <omegaup-markdown
                :markdown="formattedAcceptPolicyMarkdown"
              ></omegaup-markdown>
            </label>
          </div>
          <div class="checkbox-wrapper">
            <input
              id="accept-code-of-conduct"
              v-model="codeOfConductAccepted"
              data-signup-accept-conduct
              type="checkbox"
              required
            />
            <label for="accept-code-of-conduct">
              <omegaup-markdown
                :markdown="formattedAcceptConductMarkdown"
              ></omegaup-markdown>
            </label>
          </div>
        </div>
        <!-- id-lint on -->

        <div v-if="validateRecaptcha" class="recaptcha-wrapper mb-3">
          <vue-recaptcha
            name="recaptcha"
            sitekey="6LfMqdoSAAAAALS8h-PB_sqY7V4nJjFpGK2jAokS"
            @verify="verify"
            @expired="expired"
          ></vue-recaptcha>
        </div>

        <div class="form-group introjs-register">
          <button
            data-signup-submit
            class="auth-submit-btn"
            name="sign_up"
            type="submit"
          >
            {{ T.loginSignUp }}
          </button>
        </div>

        <div class="auth-switch-footer">
          <span>Already have an account?</span>
          <a
            href="#login"
            class="switch-link"
            @click.prevent="$emit('change-tab', 'login')"
          >
            {{ T.omegaupTitleLogin }}
          </a>
        </div>
      </form>

      <!-- Birth-Date Signup Form -->
      <form
        v-else
        class="native-signup-form"
        @submit.prevent="
          $emit('register-and-login', {
            over13Checked,
            username,
            email,
            dateOfBirth,
            parentEmail,
            password,
            passwordConfirmation,
            recaptchaResponse,
            termsAndPolicies,
          })
        "
      >
        <div class="checkbox-wrapper mb-3">
          <input
            id="over-thirteen-checkbox"
            v-model="over13Checked"
            type="checkbox"
            data-over-thirteen-checkbox
            @change="updateDateRestriction"
          />
          <label for="over-thirteen-checkbox">
            {{ T.over13yearsOld }}
          </label>
        </div>

        <div class="form-row">
          <div v-if="isUnder13" class="form-group w-100">
            <label class="auth-label">{{ T.loginParentEmail }}</label>
            <input
              v-model="parentEmail"
              name="reg_parent_email"
              type="email"
              class="form-control auth-input"
              autocomplete="parent-email"
              placeholder="parent@example.com"
            />
          </div>
          <div v-else class="form-group w-100 introjs-email">
            <label class="auth-label">{{ T.loginEmail }}</label>
            <input
              v-model="email"
              data-signup-email
              name="reg_email"
              type="email"
              class="form-control auth-input"
              autocomplete="email"
              placeholder="name@example.com"
            />
          </div>
        </div>

        <div class="form-row">
          <div class="form-group introjs-username">
            <label class="auth-label">{{ T.loginAccountName }}</label>
            <input
              v-model="username"
              data-signup-username
              name="reg_username"
              class="form-control auth-input"
              autocomplete="username"
              placeholder="Choose a username"
            />
          </div>
          <div class="form-group">
            <label class="auth-label">{{ T.loginDateOfBirth }}</label>
            <input
              v-model="dateOfBirth"
              name="reg_date_of_birth"
              type="date"
              class="form-control auth-input"
              autocomplete="date-of-birth"
              :max="maxDateForTimepicker"
              :min="minDateForTimepicker"
            />
          </div>
        </div>

        <div class="form-row">
          <div class="form-group introjs-password">
            <label class="auth-label">{{ T.loginPasswordCreate }}</label>
            <omegaup-password-input
              v-model="password"
              data-signup-password
              name="reg_password"
              autocomplete="new-password"
            />
          </div>
          <div class="form-group introjs-confirmpassword">
            <label class="auth-label">{{ T.loginRepeatPassword }}</label>
            <omegaup-password-input
              v-model="passwordConfirmation"
              data-signup-repeat-password
              name="reg_password_confirmation"
              autocomplete="new-password"
            />
          </div>
        </div>

        <!-- id-lint off -->
        <div class="terms-section introjs-terms-and-conditions">
          <div class="checkbox-wrapper">
            <input
              id="accept-privacy-policy-birthdate"
              v-model="privacyPolicyAccepted"
              data-signup-accept-policies
              type="checkbox"
            />
            <label for="accept-privacy-policy-birthdate">
              <omegaup-markdown
                :markdown="formattedAcceptPolicyMarkdown"
              ></omegaup-markdown>
            </label>
          </div>
          <div class="checkbox-wrapper">
            <input
              id="accept-code-of-conduct-birthdate"
              v-model="codeOfConductAccepted"
              data-signup-accept-conduct
              type="checkbox"
            />
            <label for="accept-code-of-conduct-birthdate">
              <omegaup-markdown
                :markdown="formattedAcceptConductMarkdown"
              ></omegaup-markdown>
            </label>
          </div>
        </div>
        <!-- id-lint on -->

        <div v-if="validateRecaptcha" class="recaptcha-wrapper mb-3">
          <vue-recaptcha
            name="recaptcha"
            sitekey="6LfMqdoSAAAAALS8h-PB_sqY7V4nJjFpGK2jAokS"
            @verify="verify"
            @expired="expired"
          ></vue-recaptcha>
        </div>

        <div class="form-group introjs-register">
          <button
            data-signup-submit
            class="auth-submit-btn"
            name="sign_up"
            type="submit"
          >
            {{ T.loginSignUp }}
          </button>
        </div>

        <div class="auth-switch-footer">
          <span>Already have an account?</span>
          <a
            href="#login"
            class="switch-link"
            @click.prevent="$emit('change-tab', 'login')"
          >
            {{ T.omegaupTitleLogin }}
          </a>
        </div>
      </form>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';
import omegaup_Markdown from '../Markdown.vue';
import T from '../../lang';
import * as ui from '../../ui';
import 'intro.js/introjs.css';
import introJs from 'intro.js';
import VueCookies from 'vue-cookies';
import VueRecaptcha from 'vue-recaptcha';
import { getBlogUrl } from '../../urlHelper';
import omegaup_PasswordInput from '../common/PasswordInput.vue';

Vue.use(VueCookies, { expires: -1 });

@Component({
  components: {
    'omegaup-markdown': omegaup_Markdown,
    'omegaup-password-input': omegaup_PasswordInput,
    'vue-recaptcha': VueRecaptcha,
  },
})
export default class Signup extends Vue {
  @Prop() validateRecaptcha!: boolean;
  @Prop({ default: false }) hasVisitedSection!: boolean;
  @Prop({ default: false }) useSignupFormWithBirthDate!: boolean;
  @Prop({ default: 'signup' }) activeTab!: string;

  T = T;
  ui = ui;
  username: string = '';
  email: string = '';
  dateOfBirth: string = '';
  parentEmail: string = '';
  password: string = '';
  passwordConfirmation: string = '';
  recaptchaResponse: string = '';
  isUnder13: boolean = true;
  over13Checked: boolean = false;
  privacyPolicyAccepted: boolean = false;
  codeOfConductAccepted: boolean = false;
  introStarted: boolean = false;

  get currentYear(): number {
    return new Date().getFullYear();
  }

  mounted() {
    this.maybeStartIntro();
  }

  @Watch('activeTab')
  onActiveTabChanged(newValue: string): void {
    if (newValue === 'signup') {
      this.maybeStartIntro();
    }
  }

  maybeStartIntro(): void {
    if (this.introStarted || this.hasVisitedSection) {
      return;
    }
    if (this.activeTab !== 'signup') {
      return;
    }

    this.$nextTick(() => {
      if (this.introStarted || this.hasVisitedSection) {
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

  verify(response: string): void {
    this.recaptchaResponse = response;
  }

  expired(): void {
    this.recaptchaResponse = '';
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

  get maxDateForTimepicker() {
    const currentDate = new Date();
    const currentYear = currentDate.getFullYear();
    const currentMonth = (currentDate.getMonth() + 1)
      .toString()
      .padStart(2, '0');
    const currentDay = currentDate.getDate().toString().padStart(2, '0');

    return this.over13Checked
      ? `${currentYear - 13}-${currentMonth}-${currentDay}`
      : `${currentYear}-${currentMonth}-${currentDay}`;
  }

  get minDateForTimepicker() {
    const currentDate = new Date();
    const currentYear = currentDate.getFullYear();
    const currentMonth = (currentDate.getMonth() + 1)
      .toString()
      .padStart(2, '0');
    const dayFollowingTheCurrent = (currentDate.getDate() + 1)
      .toString()
      .padStart(2, '0');

    return this.over13Checked
      ? '1900-01-01'
      : `${currentYear - 13}-${currentMonth}-${dayFollowingTheCurrent}`;
  }

  updateDateRestriction() {
    if (this.over13Checked) {
      this.isUnder13 = false;
      return;
    }
    this.isUnder13 = true;
  }
}
</script>

<style scoped lang="scss">
@import '../../../../sass/main.scss';

.signup-card {
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
.signup-left-pane {
  position: relative;
  overflow: hidden;
  flex: 0 0 38%;
  min-width: 320px;
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
// Right Pane (The Signup Form)
// ------------------------------------
.signup-right-pane {
  flex: 1 1 auto;
  background-color: #ffffff;
  padding: 40px 40px;
  display: flex;
  flex-direction: column;
  justify-content: center;

  @media (max-width: 768px) {
    padding: 30px 20px;
  }

  .signup-header {
    background: transparent;
    border: none;
    padding: 0;
    margin-bottom: 6px;
  }

  .signup-title {
    color: #0f172a;
    font-size: 26px;
    font-weight: 800;
    line-height: 1.25;
    letter-spacing: -0.02em;
    margin: 0 0 4px 0;
  }

  .signup-subtitle {
    color: #64748b;
    font-size: 13.5px;
    line-height: 1.5;
    margin: 0 0 18px 0;
  }

  .auth-tabs {
    display: flex;
    gap: 20px;
    margin-bottom: 20px;
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

  .native-signup-form {
    .form-row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 14px;
      margin-bottom: 12px;

      @media (max-width: 600px) {
        grid-template-columns: 1fr;
        gap: 10px;
      }
    }

    .form-group {
      margin-bottom: 0;
    }

    .auth-label {
      display: block;
      font-size: 13px;
      font-weight: 600;
      color: #0f172a;
      margin-bottom: 5px;
      text-align: left;
    }

    .auth-input {
      width: 100%;
      height: 44px;
      border: 1.5px solid #e2e8f0;
      border-radius: 12px;
      padding: 8px 14px;
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

    ::v-deep .password-input-wrapper {
      width: 100%;

      input {
        width: 100%;
        height: 44px;
        border: 1.5px solid #e2e8f0;
        border-radius: 12px;
        padding: 8px 40px 8px 14px;
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

    .terms-section {
      margin: 14px 0 16px 0;
    }

    .checkbox-wrapper {
      display: flex;
      align-items: flex-start;
      gap: 10px;
      margin-bottom: 8px;

      input[type='checkbox'] {
        width: 17px;
        height: 17px;
        margin-top: 2px;
        border: 1.5px solid #cbd5e1;
        border-radius: 4px;
        accent-color: #1e60f2;
        cursor: pointer;
        flex-shrink: 0;
      }

      label {
        margin: 0;
        cursor: pointer;
        font-size: 12.5px;
        line-height: 1.45;
        color: #475569;
        word-wrap: break-word;
        overflow-wrap: break-word;

        ::v-deep p {
          margin: 0;
          display: inline;
          font-size: 12.5px;
          color: #475569;
        }

        ::v-deep a {
          color: #1e60f2;
          font-weight: 600;
          text-decoration: none;

          &:hover {
            color: #1244c4;
            text-decoration: underline;
          }
        }
      }
    }

    .recaptcha-wrapper {
      display: flex;
      justify-content: flex-start;
      margin-bottom: 12px;
    }

    .auth-submit-btn {
      width: 100%;
      height: 46px;
      background-color: #1e60f2;
      color: #ffffff;
      font-size: 15px;
      font-weight: 600;
      border: none;
      border-radius: 12px;
      box-shadow: 0 4px 14px 0 rgba(30, 96, 242, 0.35);
      cursor: pointer;
      transition: all 0.2s ease;
      margin-top: 6px;

      &:hover {
        background-color: #1852d6;
        box-shadow: 0 6px 18px 0 rgba(30, 96, 242, 0.45);
        transform: translateY(-1px);
        color: #ffffff;
      }

      &:active {
        transform: translateY(0);
        box-shadow: 0 2px 8px 0 rgba(30, 96, 242, 0.3);
      }
    }

    .auth-switch-footer {
      margin-top: 16px;
      text-align: center;
      font-size: 13px;
      color: #64748b;

      .switch-link {
        color: #1e60f2;
        font-weight: 600;
        margin-left: 6px;
        text-decoration: none;

        &:hover {
          text-decoration: underline;
          color: #1244c4;
        }
      }
    }
  }
}
</style>
