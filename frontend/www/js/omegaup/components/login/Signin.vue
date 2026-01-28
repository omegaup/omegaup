<template>
  <div>
    <omegaup-login
      :facebook-url="facebookUrl"
      :github-client-id="githubClientId"
      :github-state="githubState"
      :google-client-id="googleClientId"
      @login="(username, password) => $emit('login', username, password)"
    >
    </omegaup-login>
    <omegaup-signup
      :has-visited-section="hasVisitedSection"
      :validate-recaptcha="validateRecaptcha"
      :use-signup-form-with-birth-date="useSignupFormWithBirthDate"
      @register-and-login="(request) => $emit('register-and-login', request)"
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
  @Prop() facebookUrl!: string;
  @Prop({ default: '' }) githubClientId!: string;
  @Prop({ default: null }) githubState!: string | null;
  @Prop() googleClientId!: string;
  @Prop() hasVisitedSection!: string;
  @Prop({ default: false }) useSignupFormWithBirthDate!: boolean;

  T = T;
}
</script>
