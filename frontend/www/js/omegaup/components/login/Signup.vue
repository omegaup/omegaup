<template>
  <div class="card mt-4">
    <div class="card-header">
      <h2 class="card-title">{{ T.loginSignupHeader }}</h2>
    </div>
    <div class="card-body">
      <form>
        <div class="row justify-content-md-center">
          <div class="col-md-4 col-md-offset-2 introjsusername">
            <div class="form-group">
              <label class="control-label">{{ T.wordsUser }}</label>
              <input
                v-model="username"
                data-signup-username
                name="reg_username"
                class="form-control"
                autocomplete="username"
              />
            </div>
          </div>
          <div class="col-md-4 introjsemail">
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
          <div class="col-md-4 col-md-offset-2 introjspassword">
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
          <div class="col-md-4 introjsconfirmpassword">
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
          <div class="col-md-8">
            <input v-model="checked" type="checkbox" class="introjstandc" />
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
            <div class="form-group introjsregister">
              <button
                data-signup-submit
                class="btn btn-primary form-control introjsclass"
                name="sign_up"
                @click.prevent="
                  $emit(
                    'register-and-login',
                    username,
                    email,
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
  @Prop() hasVisitedSection!: string;

  T = T;
  username: string = '';
  email: string = '';
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
              element: document.querySelector('.introjsusername'),
              title,
              intro: T.signUpFormInteractiveGuideUsername,
            },
            {
              element: document.querySelector('.introjsemail'),
              title,
              intro: T.signUpFormInteractiveGuideEmail,
            },
            {
              element: document.querySelector('.introjspassword'),
              title,
              intro: T.signUpFormInteractiveGuidePassword,
            },
            {
              element: document.querySelector('.introjsconfirmpassword'),
              title,
              intro: T.signUpFormInteractiveGuideConfirmPassword,
            },
            {
              element: document.querySelector('.introjstanc'),
              title,
              intro: T.signUpFormInteractiveGuideTAndC,
            },
            {
              element: document.querySelector('.introjsregister'),
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
}
</script>

<style lang="scss" scoped>
@import '../../../../sass/main.scss';
</style>
