<template>
  <div class="card mt-4">
    <div class="card-header">
      <h2 class="card-title">{{ T.loginSignupHeader }}</h2>
    </div>
    <div class="card-body">
      <form>
        <div class="row">
          <div class="col">
            <input
              v-model="over13Checked"
              type="checkbox"
              @change="updateDateRestriction"
            />
            <label for="checkbox" class="pl-1">
              <omegaup-markdown :markdown="T.over13yearsOld"></omegaup-markdown>
            </label>
          </div>
        </div>

        <div class="row">
          <div v-show="!isUnder13" class="col-md-4 offset-md-2">
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
          <div v-show="isUnder13" class="col-md-4 offset-md-2 introjs-email">
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
                :max="maxDate"
                :min="minDate"
                @input="checkAge"
              />
            </div>
          </div>
        </div>
        <div class="row justify-content-md-center">
          <div class="col-md-4 col-md-offset-2 introjs-password">
            <div class="form-group">
              <label class="control-label">{{ T.loginPasswordCreate }}</label>
              <input
                v-model="password"
                data-signup-password
                name="reg_password"
                type="password"
                class="form-control"
                autocomplete="new-password"
              />
            </div>
          </div>
          <div class="col-md-4 introjs-confirmpassword">
            <div class="form-group">
              <label class="control-label">{{ T.loginRepeatPassword }}</label>
              <input
                v-model="passwordConfirmation"
                data-signup-repeat-password
                name="reg_password_confirmation"
                type="password"
                class="form-control"
                autocomplete="new-password"
              />
            </div>
          </div>
        </div>

        <div class="row justify-content-md-center">
          <div class="col-md-8 introjs-terms-and-conditions">
            <input v-model="checked" type="checkbox" />
            <label for="checkbox" class="pl-1">
              <omegaup-markdown
                :markdown="T.acceptPrivacyPolicy"
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
                @click.prevent="
                  $emit(
                    'register-and-login',
                    username,
                    email,
                    dateOfBirth,
                    parentEmail,
                    password,
                    passwordConfirmation,
                    recaptchaResponse,
                  )
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
import 'intro.js/introjs.css';
import introJs from 'intro.js';
import VueCookies from 'vue-cookies';
Vue.use(VueCookies, { expire: -1 });

@Component({
  components: {
    'omegaup-markdown': omegaup_Markdown,
  },
})
export default class Signup extends Vue {
  @Prop() validateRecaptcha!: boolean;
  @Prop() hasVisitedSection!: boolean;

  T = T;
  username: string = '';
  email: string = '';
  dateOfBirth: string = '';
  parentEmail: string = '';
  password: string = '';
  passwordConfirmation: string = '';
  recaptchaResponse: string = '';
  isUnder13: boolean = false;
  over13Checked: boolean = false;

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

  get maxDate() {
    const currentYear = new Date().getFullYear();
    return this.over13Checked
      ? `${currentYear - 14}-12-31`
      : `${currentYear}-12-31`;
  }

  get minDate() {
    const currentYear = new Date().getFullYear();
    return this.over13Checked ? '1900-01-01' : `${currentYear - 13}-01-01`;
  }

  checkAge() {
    const dateOfBirth = new Date(this.dateOfBirth);
    const today = new Date();
    const age = today.getFullYear() - dateOfBirth.getFullYear();
    this.isUnder13 = age > 13;
    if (this.isUnder13) {
      this.email = '';
    }
  }

  updateDateRestriction() {
    this.checkAge();

    if (this.over13Checked) {
      this.email = '';
      this.isUnder13 = true;
      return;
    }
    this.parentEmail = '';
    this.isUnder13 = false;
  }
}
</script>

<style lang="scss" scoped>
@import '../../../../sass/main.scss';
</style>
