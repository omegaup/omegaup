<template>
  <div class="card">
    <div class="card-header">
      <h2 class="card-title">{{ T.loginHeader }}</h2>
    </div>
    <div class="card-body">
      <div class="row justify-content-md-center">
        <div class="col-md-5 mx-2 login-section">
          <h4>{{ T.loginFederated }}</h4>
          <div class="row">
            <div class="col-xs-12 text-left py-2 pl-3">
              <!-- id-lint off -->
              <div
                id="g_id_onload"
                :data-client_id="googleClientId"
                :data-login_uri="loginUri"
                data-auto_prompt="false"
              ></div>
              <div
                class="g_id_signin"
                data-type="standard"
                data-size="large"
                data-theme="outline"
                data-text="signin_with"
                data-shape="rectangular"
                data-logo_alignment="left"
              ></div>
              <!-- id-lint on -->
            </div>
          </div>
        </div>

        <div class="col-md-5 mx-2 login-section">
          <h4>{{ T.loginNative }}</h4>
          <form class="form-horizontal">
            <div class="form-group">
              <label for="user">{{ T.loginEmailUsername }}</label>
              <input
                v-model="usernameOrEmail"
                data-login-username
                name="login_username"
                type="text"
                class="form-control"
                tabindex="1"
                autocomplete="username"
              />
            </div>

            <div class="form-group">
              <label for="pass"
                >{{ T.loginPassword }} (<a href="/login/password/recover/">{{
                  T.loginRecover
                }}</a
                >)</label
              >
              <input
                v-model="password"
                data-login-password
                name="login_password"
                type="password"
                class="form-control"
                tabindex="2"
                autocomplete="current-password"
              />
            </div>

            <div class="form-group">
              <button
                data-login-submit
                class="btn btn-primary form-control"
                name="login"
                @click.prevent="$emit('login', usernameOrEmail, password)"
              >
                {{ T.loginLogIn }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import T from '../../lang';

@Component
export default class Login extends Vue {
  @Prop() facebookUrl!: string;
  @Prop() googleClientId!: string;

  usernameOrEmail: string = '';
  password: string = '';
  T = T;

  mounted() {
    // The reason for loading the script here instead of the `template.tpl` file
    // is that sometimes the script runs after the DOM is ready, and the element
    // may not exist yet
    const script = document.createElement('script');
    script.src = 'https://accounts.google.com/gsi/client';
    document.body.appendChild(script);
  }

  get loginUri(): string {
    return document.location.href;
  }
}
</script>

<style scoped>
.login-section {
  background-color: #f8f9fa;
  border: 2px solid #dee2e6;
  border-radius: 8px;
  padding: 24px;
  margin-bottom: 16px;
}

.login-section:hover {
  border-color: #adb5bd;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  transition: all 0.3s ease;
}

h4 {
  color: #212529;
  font-weight: 600;
  margin-bottom: 20px;
  border-bottom: 2px solid #007bff;
  padding-bottom: 10px;
}
</style>
