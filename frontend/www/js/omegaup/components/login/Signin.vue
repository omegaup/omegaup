<template>
  <div>
    <omegaup-login
      :facebook-u-r-l="facebookURL"
      :linkedin-u-r-l="linkedinURL"
      @login="loginAndRedirect"
    >
    </omegaup-login>
    <omegaup-signup
      :validate-recaptcha="validateRecaptcha"
      @register-and-login="registerAndLogin"
    >
    </omegaup-signup>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import T from '../../lang';
import VueRecaptcha from 'vue-recaptcha';
import omegaup_Login from './Login.vue';
import omegaup_Signup from './Signup.vue';

@Component({
  components: {
    'omegaup-login': omegaup_Login,
    'omegaup-signup': omegaup_Signup,
    'vue-recaptcha': VueRecaptcha,
  },
})
export default class Signin extends Vue {
  @Prop() validateRecaptcha!: boolean;
  @Prop() facebookURL!: string;
  @Prop() linkedinURL!: string;

  tem = this.facebookURL;
  T = T;

  loginAndRedirect(usernameOrEmail: string, password: string) {
    this.$emit('login', usernameOrEmail, password);
  }

  registerAndLogin(
    username: string,
    email: string,
    password: string,
    passwordConfirmation: string,
    recaptchaResponse: string,
  ) {
    this.$emit(
      'register-and-login',
      username,
      email,
      password,
      passwordConfirmation,
      recaptchaResponse,
    );
  }
}
</script>
