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
          :active-tab="activeTab"
          :facebook-url="facebookUrl"
          :github-client-id="githubClientId"
          :github-state="githubState"
          :google-client-id="googleClientId"
          @login="(username, password) => $emit('login', username, password)"
        />
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
          @register-and-login="(request) => $emit('register-and-login', request)"
        />
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
import introJs, { IntroStep } from 'intro.js';

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

  mounted() {
    this.startIntroGuide();
  }

  startIntroGuide() {
    if (!this.$cookies.get('has-visited-login')) {
      // Define steps
      const steps: Partial<IntroStep>[] = [
        {
          title: T.loginIntroUsernameTitle,
          intro: T.loginIntroUsernameIntro,
          element: document.querySelector('#username') as HTMLElement | null,
        },
        {
          title: T.loginIntroPasswordTitle,
          intro: T.loginIntroPasswordIntro,
          element: document.querySelector('#password') as HTMLElement | null,
        },
        {
          title: T.loginIntroSubmitTitle,
          intro: T.loginIntroSubmitIntro,
          element: document.querySelector('#login-button') as HTMLElement | null,
        },
      ];

      introJs()
        .setOptions({
          nextLabel: T.interactiveGuideNextButton,
          prevLabel: T.interactiveGuidePreviousButton,
          doneLabel: T.interactiveGuideDoneButton,
          steps,
        })
        .start();

      this.$cookies.set('has-visited-login', true, -1);
    }
  }
}
</script>

<style scoped lang="scss">
@import '../../../../sass/main.scss';
</style>