<template>
  <div class="card mt-4">
    <div class="card-header">
      <h2 class="card-title">{{ T.loginSignupHeader }}</h2>
    </div>
    <div class="card-body">
      <form>
        <div class="form-group">
          <label>{{ T.userEditBirthDate }}</label>
          <omegaup-datepicker
            v-model="birthDate"
            name="reg_birthdate"
            :max="new Date()"
          ></omegaup-datepicker>
        </div>
        <div class="row justify-content-md-center">
          <div class="col-md-4 col-md-offset-2">
            <div class="form-group">
              <label class="control-label">{{ T.wordsUser }}</label>
              <input
                v-model="username"
                data-signup-username
                name="reg_username"
                class="form-control"
                :disabled="!birthDate"
                autocomplete="username"
              />
            </div>
          </div>
          <div class="col-md-4">
            <div class="form-group">
              <label class="control-label">{{
                loginEmailDescriptionText
              }}</label>
              <input
                v-if="!isU13"
                v-model="email"
                data-signup-email
                name="reg_email"
                type="email"
                class="form-control"
                :disabled="!birthDate"
                autocomplete="email"
              />
              <input
                v-else
                v-model="parentEmail"
                data-signup-email
                name="reg_parent_email"
                type="email"
                class="form-control"
                :disabled="!birthDate"
                autocomplete="email"
              />
            </div>
          </div>
        </div>
        <div class="row justify-content-md-center">
          <div class="col-md-4 col-md-offset-2">
            <div class="form-group">
              <label class="control-label">{{ T.loginPasswordCreate }}</label>
              <input
                v-model="password"
                data-signup-password
                name="reg_password"
                type="password"
                class="form-control"
                :disabled="!birthDate"
                autocomplete="new-password"
              />
            </div>
          </div>
          <div class="col-md-4">
            <div class="form-group">
              <label class="control-label">{{ T.loginRepeatPassword }}</label>
              <input
                v-model="passwordConfirmation"
                data-signup-repeat-password
                name="reg_password_confirmation"
                type="password"
                class="form-control"
                :disabled="!birthDate"
                autocomplete="new-password"
              />
            </div>
          </div>
        </div>
        <div class="row justify-content-md-center">
          <div class="col-md-8">
            <input
              v-model="privacyPolicyAccepted"
              type="checkbox"
              :disabled="!birthDate"
            />
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
            <div class="form-group">
              <button
                data-signup-submit
                class="btn btn-primary form-control"
                name="sign_up"
                :disabled="!birthDate || !privacyPolicyAccepted"
                @click.prevent="registerAndLogin"
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
import * as time from '../../time';
import DatePicker from '../DatePicker.vue';
import * as ui from '../../ui';
@Component({
  components: {
    'omegaup-markdown': omegaup_Markdown,
    'omegaup-datepicker': DatePicker,
  },
})
export default class Signup extends Vue {
  @Prop() validateRecaptcha!: boolean;
  T = T;
  username: string = '';
  email: null | string = null;
  parentEmail: null | string = null;
  password: string = '';
  passwordConfirmation: string = '';
  recaptchaResponse: string = '';
  birthDate: null | Date = null;
  privacyPolicyAccepted = false;

  get loginEmailDescriptionText(): string {
    if (!this.isU13) {
      return T.loginEmail;
    }
    return !this.isU13 ? T.loginEmail : T.loginEmailParent;
  }

  get isU13(): boolean {
    if (this.birthDate === null) {
      // Most users are not going to be U13. So until they fill out their
      // birthdate, assume they aren't so that they can see the default form.
      return false;
    }
    return time.getDifferenceInCalendarYears(this.birthDate) < 13;
  }

  verify(response: string): void {
    this.recaptchaResponse = response;
  }
  expired(): void {
    this.recaptchaResponse = '';
  }

  registerAndLogin(): void {
    if (this.password !== this.passwordConfirmation) {
      ui.error(T.passwordMismatch);
      return;
    }
    if (this.password && this.password.length < 8) {
      ui.error(T.loginPasswordTooShort);
      return;
    }
    if (!this.isU13 && this.email === null) {
      ui.error(T.loginEmailMissing);
      return;
    } else if(this.isU13 && this.parentEmail === null) {
      ui.error(T.loginParentEmailMissing);
      return;
    }
    const registerParameters = {
      username: this.username,
      password: this.password,
      recaptcha: this.recaptchaResponse,
      birth_date: this.birthDate,
      email: this.email,
      parent_email: this.parentEmail,
    };
    if (this.isU13) {
      // NOTE: validate the parent email here.
      registerParameters.parent_email = this.parentEmail;
    } else {
      // NOTE: validate the email here.
      registerParameters.email = this.email;
    }
    this.$emit('register-and-login', registerParameters);
  }
}
</script>
