<template>
  <div class="omegaup-admin-support panel-primary panel">
    <div class="panel-heading">
      <h2 class="panel-title">{{ T.omegaupTitleSupportDashboard }} <span v-if="username != null">-
      {{ username }}</span></h2>
    </div>
    <div class="panel-body">
      <div class="row">
        <form class="form"
              v-on:submit.prevent="onSearchEmail">
          <div class="col-md-6">
            <div class="input-group">
              <input class="form-control"
                   name="email"
                   type="text"
                   v-bind:disabled="username != null"
                   v-bind:placeholder="T.email"
                   v-model="email"> <span class="input-group-btn"><button class="btn btn-default"
                      type="button"
                      v-bind:disabled="username != null"
                      v-on:click.prevent="onSearchEmail">{{ T.wordsSearch }}</button></span>
            </div>
          </div>
        </form>
        <form class="form"
              id="verifyUser"
              name="verifyUser"
              v-on:submit.prevent="onVerifyUser"
              v-show="username != null">
          <div class="col-md-6 bottom-margin">
            <button class="btn btn-default btn-block"
                 type="button"
                 v-bind:disabled="verified"
                 v-on:click.prevent="onVerifyUser">
            <template v-if="verified">
              <span aria-hidden="true"
                        class="glyphicon glyphicon-ok"></span> {{ T.userVerified }}
            </template>
            <template v-else="">
              {{ T.userVerify }}
            </template></button>
          </div>
        </form>
      </div>
      <div class="row bottom-margin">
        <form class="form bottom-margin"
              id="generateToken-"
              name="generateToken-"
              v-on:submit.prevent="onGenerateToken"
              v-show="username != null">
          <div class="col-md-12">
            <div class="input-group bottom-margin">
              <input class="form-control"
                   name="link"
                   type="text"
                   v-bind:placeholder="T.passwordGenerateTokenDesc"
                   v-model="link"> <span class="input-group-btn"><button class="btn btn-default"
                      name="copy"
                      type="button"
                      v-bind:aria-label="T.passwordCopyToken"
                      v-bind:disabled="link == ''"
                      v-bind:title="T.passwordCopyToken"
                      v-on:click.prevent="onCopyToken"><span aria-hidden="true"
                    class="glyphicon glyphicon-copy"></span></button> <button class=
                    "btn btn-default"
                      type="button"
                      v-bind:title="T.passwordGenerateTokenDesc"
                      v-on:click.prevent="onGenerateToken">{{ T.passwordGenerateToken
                      }}</button></span>
            </div>
            <div class="text-right">
              <button class="btn btn-primary submit"
                   type="reset"
                   v-on:click.prevent="onReset">{{ T.wordsCancel }}</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import {T} from '../../omegaup.js';
import UI from '../../ui.js';

export default {
  props: {
    username: String,
    verified: Boolean,
    link: String,
  },
  data: function() { return {T: T, email: null};},
  methods: {
    onSearchEmail: function() { this.$emit('search-email', this.email);},
    onVerifyUser: function() { this.$emit('verify-user', this.email);},
    onGenerateToken: function() { this.$emit('generate-token', this.email);},
    onCopyToken: function() {
      let copyText = this.$el.querySelector("input[name=link]");
      copyText.trigger('select');
      document.execCommand('copy');
      this.$emit('copy-token');
    },
    onReset: function() { this.$emit('reset');}
  }
};
</script>
