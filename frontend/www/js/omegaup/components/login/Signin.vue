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

    <!-- Tab Content -->
    <div class="tab-content">
      <!-- Login Tab -->
      <div
        v-show="activeTab === AvailableTabs.Login"
        class="tab-pane"
        :class="{ active: activeTab === AvailableTabs.Login }"
        role="tabpanel"
      >
        <omegaup-login
          :facebook-url="facebookUrl"
          :google-client-id="googleClientId"
          @login="(username, password) => $emit('login', username, password)"
        >
        </omegaup-login>
      </div>

      <!-- Sign Up Tab -->
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
import { Vue, Component, Prop } from 'vue-property-decorator';
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
  @Prop() googleClientId!: string;
  @Prop() hasVisitedSection!: string;
  @Prop({ default: false }) useSignupFormWithBirthDate!: boolean;

  T = T;
  AvailableTabs = AvailableTabs;
  activeTab: AvailableTabs = AvailableTabs.Login;

  mounted(): void {
    // Read hash from URL on mount
    const hash = window.location.hash.substring(1);
    if (hash === AvailableTabs.Login || hash === AvailableTabs.Signup) {
      this.activeTab = hash as AvailableTabs;
    }

    // Listen for hash changes
    window.addEventListener('hashchange', this.onHashChange);
  }

  beforeDestroy(): void {
    // Clean up event listener
    window.removeEventListener('hashchange', this.onHashChange);
  }

  setActiveTab(tab: AvailableTabs): void {
    this.activeTab = tab;
    window.location.hash = `#${tab}`;
  }

  onHashChange(): void {
    const hash = window.location.hash.substring(1);
    if (hash === AvailableTabs.Login || hash === AvailableTabs.Signup) {
      this.activeTab = hash as AvailableTabs;
    }
  }
}
</script>

<style scoped>
:root {
  --signin-nav-tabs-border-color: #dee2e6;
  --signin-nav-link-color: #6c757d;
  --signin-nav-link-background-color: #e9ecef;
  --signin-nav-link-hover-background-color: #d6d9dc;
  --signin-nav-link-hover-color: #495057;
  --signin-nav-link-active-color: #212529;
  --signin-nav-link-active-background-color: #fff;
}

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
