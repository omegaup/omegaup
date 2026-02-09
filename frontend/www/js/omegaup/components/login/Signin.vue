<template>
  <div>
    <ul class="nav nav-tabs mb-3" role="tablist">
      <li class="nav-item" role="presentation">
        <a
          :href="`#${AvailableTabs.Login}`"
          class="nav-link"
          :class="{ active: activeTab === AvailableTabs.Login }"
          role="tab"
          @click.prevent="setActiveTab(AvailableTabs.Login)"
        >
          {{ T.omegaupTitleLogin }}
        </a>
      </li>
      <li class="nav-item" role="presentation">
        <a
          :href="`#${AvailableTabs.Signup}`"
          class="nav-link"
          :class="{ active: activeTab === AvailableTabs.Signup }"
          role="tab"
          @click.prevent="setActiveTab(AvailableTabs.Signup)"
        >
          {{ T.loginSignUp }}
        </a>
      </li>
    </ul>

    <div class="tab-content">
      <div
        v-show="activeTab === AvailableTabs.Login"
        class="tab-pane"
        :class="{ active: activeTab === AvailableTabs.Login }"
        role="tabpanel"
      >
        <omegaup-login
          :facebook-url="facebookUrl"
          :github-client-id="githubClientId"
          :github-state="githubState"
          :google-client-id="googleClientId"
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
          :validate-recaptcha="validateRecaptcha"
          :use-signup-form-with-birth-date="useSignupFormWithBirthDate"
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
  @Prop() hasVisitedSection!: string;
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

.nav-tabs {
  border-bottom: 1px solid var(--signin-nav-tabs-border-color);
  display: flex;
  margin-bottom: 0;
}

.nav-item {
  list-style: none;
}

.nav-link {
  color: var(--signin-nav-link-color);
  background-color: var(--signin-nav-link-background-color);
  border: 1px solid var(--signin-nav-tabs-border-color);
  border-bottom: none;
  border-top-left-radius: 0.25rem;
  border-top-right-radius: 0.25rem;
  padding: 0.75rem 1.5rem;
  cursor: pointer;
  transition: all 0.2s ease;
  font-size: 1rem;
  text-decoration: none;
  display: block;
}

.nav-link:hover {
  background-color: var(--signin-nav-link-hover-background-color);
  color: var(--signin-nav-link-hover-color);
}

.nav-link.active {
  color: var(--signin-nav-link-active-color);
  background-color: var(--signin-nav-link-active-background-color);
  border-color: var(--signin-nav-tabs-border-color)
    var(--signin-nav-tabs-border-color)
    var(--signin-nav-link-active-background-color);
  position: relative;
  z-index: 1;
  margin-bottom: -1px;
}

.tab-content {
  margin-top: 0;
}

.tab-pane {
  display: block;
}
</style>
