<template>
  <div>
    <ul class="nav nav-tabs mb-3" role="tablist">
      <li class="nav-item" role="presentation">
        <button
          class="nav-link"
          :class="{ active: activeTab === 'login' }"
          type="button"
          role="tab"
          @click="activeTab = 'login'"
        >
          {{ T.omegaupTitleLogin }}
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button
          class="nav-link"
          :class="{ active: activeTab === 'signup' }"
          type="button"
          role="tab"
          @click="activeTab = 'signup'"
        >
          {{ T.loginSignUp }}
        </button>
      </li>
    </ul>

    <div class="tab-content">
      <div
        v-show="activeTab === 'login'"
        class="tab-pane"
        :class="{ active: activeTab === 'login' }"
        role="tabpanel"
      >
        <omegaup-login
          :facebook-url="facebookUrl"
          :google-client-id="googleClientId"
          @login="(username, password) => $emit('login', username, password)"
        >
        </omegaup-login>
      </div>

      <div
        v-show="activeTab === 'signup'"
        class="tab-pane"
        :class="{ active: activeTab === 'signup' }"
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
  activeTab: string = 'login';
}
</script>

<style scoped>
.nav-tabs {
  border-bottom: 1px solid #dee2e6;
  display: flex;
  margin-bottom: 0;
}

.nav-item {
  list-style: none;
}

.nav-link {
  color: #6c757d;
  background-color: #e9ecef;
  border: 1px solid #dee2e6;
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
  background-color: #d6d9dc;
  color: #495057;
}

.nav-link.active {
  color: #212529;
  background-color: #fff;
  border-color: #dee2e6 #dee2e6 #fff;
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
