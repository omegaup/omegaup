<template>
  <div class="omegaup-admin-support panel-primary panel">
    <div class="panel-heading">
      <h2 class="panel-title">{{ T.omegaupTitleSupportDashboard }}</h2>
    </div>
    <div class="panel-body">
      <form class="form bottom-margin"
            v-on:submit.prevent="onSearchUsername">
        <div class="row">
          <div class="col-md-6">
            <div class="input-group">
              <input class="form-control"
                   name="username"
                   type="text"
                   v-bind:disabled="valid"
                   v-bind:placeholder="T.wordsUser"
                   v-model="username"> <span class="input-group-btn"><button class=
                   "btn btn-default"
                      type="button"
                      v-bind:disabled="valid"
                      v-on:click.prevent="onSearchUsername">{{ T.wordsSearch }}</button></span>
            </div>
          </div>
        </div>
      </form>
      <form class="form bottom-margin"
            v-on:submit.prevent="onChangePassword"
            v-show="valid">
        <div class="row bottom-margin">
          <div class="col-md-6">
            <div class="input-group">
              <input class="form-control"
                   name="password"
                   type="text"
                   v-bind:placeholder="T.passwordResetPassword"
                   v-model="password"> <span class="input-group-btn"><button class=
                   "btn btn-default"
                      type="button"
                      v-bind:aria-label="T.passwordGenerateRandom"
                      v-bind:title="T.passwordGenerateRandom"
                      v-on:click.prevent="onGeneratePassword"><span aria-hidden="true"
                    class="glyphicon glyphicon-random"></span></button> <button class=
                    "btn btn-default"
                      type="button"
                      v-on:click.prevent="onChangePassword">{{ T.userEditChangePassword
                      }}</button></span>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-12 text-right">
            <button class="btn btn-primary submit"
                 type="reset"
                 v-on:click.prevent="onReset">{{ T.wordsCancel }}</button>
          </div>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import {T} from '../../omegaup.js';

export default {
  props: {
    valid: Boolean,
  },
  data: function() { return {T: T, username: '', password: ''};},
  methods: {
    onSearchUsername: function() {
      this.$emit('search-username', this.username);
    },
    onChangePassword: function() {
      this.$emit('change-password', this.password, this.username);
    },
    onGeneratePassword: function() {
      let chars = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789';
      let length = 8;
      var newPassword = '';
      for (var i = 0; i < length; i++) {
        newPassword += chars[Math.floor(Math.random() * chars.length)];
      }
      this.password = newPassword;
    },
    onReset: function() { this.$emit('reset');}
  }
};
</script>
