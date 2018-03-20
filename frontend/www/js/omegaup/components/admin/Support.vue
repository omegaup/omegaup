<template>
  <div class="omegaup-admin-support panel-primary panel">
    <div class="panel-heading">
      <h2 class="panel-title">{{ T.omegaupTitleSupportDashboard }}</h2>
    </div>
    <div class="panel-body">
      <div class="row">
        <form class="form bottom-margin"
              v-on:submit.prevent="onSearchUsername">
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
        </form>
      </div>
      <div class="row">
        <div class="col-md-6">
          <div class="input-group">
            {{ email }}
          </div>
        </div>
      </div>
      <div class="row bottom-margin">
        <form class="form bottom-margin"
              v-on:submit.prevent="onGenerateToken"
              v-show="valid">
          <template v-if="request_password_change">
            <div class="col-md-12">
              <div class="input-group">
                <input class="form-control"
                     disabled="true"
                     name="link"
                     type="text"
                     v-bind:placeholder="T.passwordGenerateTokenDesc"
                     v-model="link"> <span class="input-group-btn"><button class="btn btn-default"
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
            </div>
          </template>
          <template v-else="">
            <div class="col-md-12">
              <div class="input-group">
                {{ T.userDoesNotHaveAnyPasswordChangeRequest }}
              </div>
            </div>
          </template>
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
  </div>
</template>

<script>
import {T} from '../../omegaup.js';

export default {
  props: {
    valid: Boolean,
    request_password_change: Boolean,
    link: String,
    email: String
  },
  data: function() { return {T: T, username: ''};},
  methods: {
    onSearchUsername: function() {
      this.$emit('search-username', this.username);
    },
    onGenerateToken: function() { this.$emit('generate-token', this.email);},
    onCopyToken: function() { this.$emit('copy-token');},
    onReset: function() {
      this.username = '';
      this.$emit('reset');
    }
  }
};
</script>
