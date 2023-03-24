<template>
  <div>
    <omegaup-login
      :facebook-url="facebookUrl"
      :google-client-id="googleClientId"
      @login="(username, password) => $emit('login', username, password)"
    >
    </omegaup-login>
    <omegaup-signup
      :validate-recaptcha="validateRecaptcha"
      @register-and-login="(response) => $emit('register-and-login', response)"
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
  @Prop() googleClientId!: string;

  T = T;
}
</script>
