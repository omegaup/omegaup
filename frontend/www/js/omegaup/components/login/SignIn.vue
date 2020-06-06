<template>
  <div>
    <omegaup-login
      v-bind:facebookURL="facebookURL"
      v-bind:linkedinURL="linkedinURL"
      v-on:login="loginAndRedirect"
      v-on:loginGoogle="login"
    >
    </omegaup-login>
    <div class="panel panel-default">
      <div class="panel-heading">
        <h2 class="panel-title">{{ T.loginSignupHeader }}</h2>
      </div>
      <div class="panel-body">
        <form>
          <div class="row">
            <div class="col-md-4 col-md-offset-2">
              <div class="form-group">
                <label class="control-label">{{ T.wordsUser }}</label>
                <input
                  name="reg_username"
                  v-model="username"
                  type="text"
                  class="form-control"
                  autocomplete="username"
                />
              </div>
            </div>

            <div class="col-md-4">
              <div class="form-group">
                <label class="control-label">{{ T.loginEmail }}</label>
                <input
                  name="reg_email"
                  v-model="email"
                  type="email"
                  class="form-control"
                  autocomplete="email"
                />
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-4 col-md-offset-2">
              <div class="form-group">
                <label class="control-label">{{ T.loginPasswordCreate }}</label>
                <input
                  name="reg_password"
                  v-model="password"
                  type="password"
                  class="form-control"
                  autocomplete="new-password"
                />
              </div>
            </div>

            <div class="col-md-4">
              <div class="form-group">
                <label class="control-label">{{ T.loginRepeatPassword }}</label>
                <input
                  name="reg_password_confirmation"
                  v-model="passwordConfirmation"
                  type="password"
                  class="form-control"
                  autocomplete="new-password"
                />
              </div>
            </div>
          </div>
          <div class="row">
            <div
              v-html="T.privacyPolicyNotice"
              class="col-md-4 col-md-offset-2"
            ></div>
            <div class="col-md-4" v-if="validateRecaptcha">
              <vue-recaptcha
                name="recaptcha"
                @verify="verify"
                @expired="expired"
                sitekey="6LdxQfoUAAAAAFQlVIK7_mCYTn0Ah6Y9ckdCTlx4"
              ></vue-recaptcha>
            </div>
          </div>
          <div class="row">
            <div class="col-md-4 col-md-offset-6">
              <div class="form-group">
                <button
                  class="btn btn-primary form-control"
                  name="sign"
                  v-on:click.prevent="
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
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { omegaup } from '../../omegaup';
import T from '../../lang';
import VueRecaptcha from 'vue-recaptcha';
import login from '../login/Login.vue';

@Component({
  components: {
    'omegaup-login': login,
    'vue-recaptcha': VueRecaptcha,
  },
})
export default class SignIn extends Vue {
  @Prop() validateRecaptcha?: boolean;
  @Prop() facebookURL?: string;
  @Prop() linkedinURL?: string;

  username: string = '';
  email: string = '';
  password: string = '';
  passwordConfirmation: string = '';
  tem = this.facebookURL;
  T = T;
  recaptchaResponse: string = '';

  verify(response: string): void {
    this.recaptchaResponse = response;
  }
  expired(): void {
    this.recaptchaResponse = '';
  }

  loginAndRedirect(usernameOrEmail: string, password: string) {
    this.$emit('login', usernameOrEmail, password);
  }

  login(url: string) {
    this.$emit('loginGoogle');
  }
}
</script>
