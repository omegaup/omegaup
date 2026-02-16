<template>
  <div class="card">
    <div class="card-header">
      <h2 class="card-title">{{ T.loginSignupHeader }}</h2>
    </div>
    <div class="card-body">
      <form v-if="!useSignupFormWithBirthDate">
        <div class="row justify-content-md-center">
          <div class="col-md-4 col-md-offset-2 introjs-username">
            <div class="form-group">
              <label class="control-label">{{ T.loginAccountName }}</label>
              <input
                v-model="username"
                data-signup-username
                name="reg_username"
                class="form-control"
                autocomplete="username"
              />
            </div>
          </div>
          <div class="col-md-4 introjs-email">
            <div class="form-group">
              <label class="control-label">{{ T.loginEmail }}</label>
              <input
                v-model="email"
                data-signup-email
                name="reg_email"
                type="email"
                class="form-control"
                autocomplete="email"
              />
            </div>
          </div>
        </div>
        <div class="row justify-content-md-center">
          <div class="col-md-4 col-md-offset-2 introjs-password">
            <div class="form-group">
              <label class="control-label">{{ T.loginPasswordCreate }}</label>
              <omegaup-password-input
                v-model="password"
                data-signup-password
                name="reg_password"
                autocomplete="new-password"
              />
            </div>
          </div>
          <div class="col-md-4 introjs-confirmpassword">
            <div class="form-group">
              <label class="control-label">{{ T.loginRepeatPassword }}</label>
              <omegaup-password-input
                v-model="passwordConfirmation"
                data-signup-repeat-password
                name="reg_password_confirmation"
                autocomplete="new-password"
              />
            </div>
          </div>
        </div>

        <div class="row justify-content-md-center">
          <div class="col-md-8 introjs-terms-and-conditions">
            <input
              v-model="termsAndPolicies"
              data-signup-accept-policies
              type="checkbox"
              required
            />
            <label for="checkbox">
              <omegaup-markdown
                :markdown="formattedAcceptPolicyMarkdown"
              ></omegaup-markdown>
            </label>
          </div>
          <div v-if="validateRecaptcha" class="col-md-4">
            <vue-recaptcha
              name="recaptcha"
              sitekey="6LfMqdoSAAAAALS8h-PB_sqY7V4nJjFpGK2jAokS"
              @verify="verify"
              @expired="expired"
            ></vue-recaptcha>
          </div>
          <div class="col-md-4 col-md-offset-6">
            <div class="form-group introjs-register">
              <button
                data-signup-submit
                class="btn btn-primary form-control"
                name="sign_up"
                type="submit"
                @click.prevent="
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
                {{ T.loginSignUp }}
              </button>
            </div>
          </div>
        </div>
      </form>

      <form v-else>
        <div class="row">
          <div class="col-md-4 offset-md-2">
            <div class="form-group">
              <input
                v-model="over13Checked"
                type="checkbox"
                data-over-thirteen-checkbox
                @change="updateDateRestriction"
              />
              <label for="checkbox" class="pl-1">
                <omegaup-markdown
                  :markdown="T.over13yearsOld"
                ></omegaup-markdown>
              </label>
            </div>
          </div>
        </div>

        <div class="row">
          <div v-if="isUnder13" class="col-md-8 offset-md-2">
            <div class="form-group">
              <label class="control-label">{{ T.loginParentEmail }}</label>
              <input
                v-model="parentEmail"
                name="reg_parent_email"
                type="email"
                class="form-control"
                autocomplete="parent-email"
              />
            </div>
          </div>
          <div v-else class="col-md-8 offset-md-2 introjs-email">
            <div class="form-group">
              <label class="control-label">{{ T.loginEmail }}</label>
              <input
                v-model="email"
                data-signup-email
                name="reg_email"
                type="email"
                class="form-control"
                autocomplete="email"
              />
            </div>
          </div>
        </div>
        <div class="row justify-content-md-center">
          <div class="col-md-4 col-md-offset-2 introjs-username">
            <div class="form-group">
              <label class="control-label">{{ T.loginAccountName }}</label>
              <input
                v-model="username"
                data-signup-username
                name="reg_username"
                class="form-control"
                autocomplete="username"
              />
            </div>
          </div>
          <div class="col-md-4">
            <div class="form-group">
              <label class="control-label">{{ T.loginDateOfBirth }}</label>
              <input
                v-model="dateOfBirth"
                name="reg_date_of_birth"
                type="date"
                class="form-control"
                autocomplete="date-of-birth"
                :max="maxDateForTimepicker"
                :min="minDateForTimepicker"
              />
            </div>
          </div>
        </div>
        <div class="row justify-content-md-center">
          <div class="col-md-4 col-md-offset-2 introjs-password">
            <div class="form-group">
              <label class="control-label">{{ T.loginPasswordCreate }}</label>
              <omegaup-password-input
                v-model="password"
                data-signup-password
                name="reg_password"
                autocomplete="new-password"
              />
            </div>
          </div>
          <div class="col-md-4 introjs-confirmpassword">
            <div class="form-group">
              <label class="control-label">{{ T.loginRepeatPassword }}</label>
              <omegaup-password-input
                v-model="passwordConfirmation"
                data-signup-repeat-password
                name="reg_password_confirmation"
                autocomplete="new-password"
              />
            </div>
          </div>
        </div>

        <div class="row justify-content-md-center">
          <div class="col-md-10 introjs-terms-and-conditions">
            <input v-model="termsAndPolicies" type="checkbox" />
            <label for="checkbox" class="pl-1">
              <omegaup-markdown
                :markdown="formattedAcceptPolicyMarkdown"
              ></omegaup-markdown>
            </label>
          </div>
        </div>

        <div class="row justify-content-md-center">
          <div v-if="validateRecaptcha" class="col-md-4">
            <vue-recaptcha
              name="recaptcha"
              sitekey="6LfMqdoSAAAAALS8h-PB_sqY7V4nJjFpGK2jAokS"
              @verify="verify"
              @expired="expired"
            ></vue-recaptcha>
          </div>
        </div>

        <div class="row justify-content-md-center">
          <div class="col-md-4 col-md-offset-6">
            <div class="form-group introjs-register">
              <button
                data-signup-submit
                class="btn btn-primary form-control"
                name="sign_up"
                @click.prevent="
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
                {{ T.loginSignUp }}
              </button>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import omegaup_Markdown from '../Markdown.vue';
import T from '../../lang';
import * as ui from '../../ui';
import 'intro.js/introjs.css';
import introJs from 'intro.js';
import VueCookies from 'vue-cookies';
import { getBlogUrl } from '../../urlHelper';
import omegaup_PasswordInput from '../common/PasswordInput.vue';
Vue.use(VueCookies, { expire: -1 });

@Component({
  components: {
    'omegaup-markdown': omegaup_Markdown,
    'omegaup-password-input': omegaup_PasswordInput,
  },
})
export default class Signup extends Vue {
  @Prop() validateRecaptcha!: boolean;
  @Prop() hasVisitedSection!: boolean;
  @Prop({ default: false }) useSignupFormWithBirthDate!: boolean;

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
  termsAndPolicies: boolean = false;

  mounted() {
    const title = T.signUpFormInteractiveGuideTitle;

    if (!this.hasVisitedSection) {
      introJs()
        .setOptions({
          nextLabel: T.interactiveGuideNextButton,
          prevLabel: T.interactiveGuidePreviousButton,
          doneLabel: T.interactiveGuideDoneButton,
          steps: [
            {
              title,
              intro: T.signUpFormInteractiveGuideWelcome,
            },
            {
              element: document.querySelector('.introjs-username') as Element,
              title,
              intro: T.signUpFormInteractiveGuideUsername,
            },
            {
              element: document.querySelector('.introjs-email') as Element,
              title,
              intro: T.signUpFormInteractiveGuideEmail,
            },
            {
              element: document.querySelector('.introjs-password') as Element,
              title,
              intro: T.signUpFormInteractiveGuidePassword,
            },
            {
              element: document.querySelector(
                '.introjs-confirmpassword',
              ) as Element,
              title,
              intro: T.signUpFormInteractiveGuideConfirmPassword,
            },
            {
              element: document.querySelector(
                '.introjs-terms-and-conditions',
              ) as Element,
              title,
              intro: T.signUpFormInteractiveGuideTermsAndConditions,
            },
            {
              element: document.querySelector('.introjs-register') as Element,
              title,
              intro: T.signUpFormInteractiveGuideRegister,
            },
          ],
        })
        .start();
      this.$cookies.set('has-visited-signup', true, -1);
    }
  }

  verify(response: string): void {
    this.recaptchaResponse = response;
  }

  expired(): void {
    this.recaptchaResponse = '';
  }

  get formattedAcceptPolicyMarkdown(): string {
    const policyUrl = getBlogUrl('PrivacyPolicyURL');
    const conductUrl = getBlogUrl('CodeofConductPolicyURL');

    const formattedstring = ui.formatString(T.acceptPrivacyPolicy, {
      PrivacyPolicyURL: policyUrl,
      CodeofConductPolicyURL: conductUrl,
    });

    return formattedstring;
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
