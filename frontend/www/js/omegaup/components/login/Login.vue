<template>
  <div class="card">
    <div class="card-header">
      <h2 class="card-title">{{ T.loginHeader }}</h2>
    </div>
    <div class="card-body">
      <div class="row justify-content-md-center">
        <div class="col-md-4 col-md-offset-2">
          <h4>{{ T.loginFederated }}</h4>
          <div class="row">
            <div class="col-xs-12 col-sm-4 text-center py-2">
              <!-- id-lint off -->
              <div id="google-signin" :title="T.loginWithGoogle"></div>
              <!-- id-lint on -->
            </div>
            <div class="col-xs-12 col-sm-4 text-center py-2">
              <a :href="facebookURL" :title="T.loginWithFacebook">
                <img src="/css/fb-oauth.png" height="45px" width="45px" />
              </a>
            </div>
            <div class="col-xs-12 col-sm-4 text-center py-2">
              <a
                :href="linkedinURL"
                :title="T.loginWithLinkedIn"
                class="openid_large_btn"
              >
                <img src="/css/ln-oauth.png" height="45px" width="45px" />
              </a>
            </div>
          </div>
        </div>

        <div class="col-md-4 col-md-offset-2">
          <h4>{{ T.loginNative }}</h4>
          <form class="form-horizontal">
            <div class="form-group">
              <label for="user">{{ T.loginEmailUsername }}</label>
              <input
                v-model="usernameOrEmail"
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
                name="login_password"
                type="password"
                class="form-control"
                tabindex="2"
                autocomplete="current-password"
              />
            </div>

            <div class="form-group">
              <button
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
  @Prop() facebookURL!: string;
  @Prop() linkedinURL!: string;
  usernameOrEmail: string = '';
  password: string = '';
  T = T;
}
</script>
