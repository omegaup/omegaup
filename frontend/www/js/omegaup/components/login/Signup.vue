<template>
  <div class="card mt-4">
    <div class="card-header">
      <h2 class="card-title">{{ T.loginSignupHeader }}</h2>
    </div>
    <div class="card-body">
      <form>
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
          <div v-show="!isUnder13" class="col-md-4 introjs-email">
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
          <div class="col-md-4 col-md-offset-2 introjs-date-of-birth">
            <div class="form-group">
              <label class="control-label">{{ T.loginDateOfBirth }}</label>
              <input
                v-model="dateOfBirth"
                data-signup-date-of-birth
                name="reg_date_of_birth"
                type="date"
                class="form-control"
                autocomplete="date-of-birth"
              />
            </div>
          </div>
          <div v-show="isUnder13" class="col-md-4">
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
            <label for="checkbox">
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
              element: document.querySelector(
                '.introjs-date-of-birth',
              ) as Element,
              title,
              intro: T.signUpFormInteractiveGuideDateOfBirth,
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

  checkAge() {
    const dateOfBirth = new Date(this.dateOfBirth);
    const today = new Date();
    const age = today.getFullYear() - dateOfBirth.getFullYear();
    if (age < 13) {
      this.parentEmail = '';
    }
  }

  get isUnder13() {
    const dateOfBirth = new Date(this.dateOfBirth);
    const today = new Date();
    const age = today.getFullYear() - dateOfBirth.getFullYear();
    return age < 13;
  }
}
</script>

<style lang="scss" scoped>
@import '../../../../sass/main.scss';
</style>
