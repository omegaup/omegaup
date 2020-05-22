<template>
  <div class="panel panel-default">
    <div class="panel-heading">
      <h2 class="panel-title">{{ T.loginHeader }}</h2>
    </div>

    <div class="row">
      <div class="col-md-4 col-md-offset-2">
        <h4>{{ T.loginFederated }}</h4>
        <div v-bind:title="T.loginWithGoogle"></div>
        <button
          class="btn btn-primary form-control"
          v-on:click.prevent="$emit('loginGoogle', facebookURL)"
        >
          {{ 'google' }}
        </button>
        <a
          v-bind:href="facebookURL"
          v-bind:title="T.loginWithFacebook"
          class="facebook openid_large_btn"
        ></a>
        <a style="float:right"></a>

        <a
          v-bind:href="linkedinURL"
          v-bind:title="T.loginWithLinkedIn"
          class="openid_large_btn"
        >
          <img src="/media/third_party/LinkedIn-Sign-in-Small---Default.png" />
        </a>
      </div>

      <div class="col-md-4">
        <h4>{{ T.loginNative }}</h4>
        <form class="form-horizontal">
          <div class="form-group">
            <label for="user">{{ T.loginEmailUsername }}</label>
            <input
              name="user"
              v-model="usernameOrEmail"
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
              name="pass"
              v-model="password"
              type="password"
              class="form-control"
              tabindex="2"
              autocomplete="current-password"
            />
          </div>

          <div class="form-group">
            <button
              class="btn btn-primary form-control"
              v-on:click.prevent="$emit('login', usernameOrEmail, password)"
            >
              {{ T.loginLogIn }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';
import { omegaup } from '../../omegaup';
import T from '../../lang';

@Component
export default class Login extends Vue {
  @Prop() facebookURL?: string;
  @Prop() linkedinURL?: string;
  usernameOrEmail: string = '';
  password: string = '';
  T = T;
}
</script>
