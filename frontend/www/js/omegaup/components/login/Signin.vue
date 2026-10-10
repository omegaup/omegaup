<template>
  <div class="signin-page-wrapper">
    <div class="tab-content w-100">
      <div
        v-show="activeTab === AvailableTabs.Login"
        class="tab-pane"
        :class="{ active: activeTab === AvailableTabs.Login }"
        role="tabpanel"
      >
        <omegaup-login
          :active-tab="activeTab"
          :facebook-url="facebookUrl"
          :github-client-id="githubClientId"
          :github-state="githubState"
          :google-client-id="googleClientId"
          @change-tab="setActiveTab"
          @login="(username, password) => $emit('login', username, password)"
        >
        </omegaup-login>
      </div>

      <div
        v-show="activeTab === AvailableTabs.Signup"
        class="tab-pane"
        :class="{ active: activeTab === AvailableTabs.Signup }"
        role="tabpanel"
      >
        <omegaup-signup
          :has-visited-section="hasVisitedSection"
          :active-tab="activeTab"
          :validate-recaptcha="validateRecaptcha"
          :use-signup-form-with-birth-date="useSignupFormWithBirthDate"
          @change-tab="setActiveTab"
          @register-and-login="
            (request) => $emit('register-and-login', request)
          "
        >
        </omegaup-signup>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';
import T from '../../lang';
import VueRecaptcha from 'vue-recaptcha';
import omegaup_Login from './Login.vue';
import omegaup_Signup from './Signup.vue';

export enum AvailableTabs {
  Login = 'login',
  Signup = 'signup',
}

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
  @Prop() hasVisitedSection!: boolean;
  @Prop({ default: false }) useSignupFormWithBirthDate!: boolean;
  @Prop({ default: AvailableTabs.Login }) initialActiveTab!: AvailableTabs;

  T = T;
  AvailableTabs = AvailableTabs;
  activeTab: AvailableTabs = this.initialActiveTab;

  @Watch('initialActiveTab')
  onInitialActiveTabChanged(newValue: AvailableTabs): void {
    this.activeTab = newValue;
  }

  setActiveTab(tab: AvailableTabs): void {
    this.activeTab = tab;
    window.location.hash = `#${tab}`;
  }
}
</script>

<style scoped lang="scss">
@import '../../../../sass/main.scss';

.signin-page-wrapper {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  width: 100%;
  padding: 40px 16px;

  @media (max-width: 768px) {
    padding: 20px 12px;
  }
}

.tab-content {
  margin-top: 0;
  display: flex;
  justify-content: center;
}

.tab-pane {
  display: block;
  width: 100%;
}
</style>
