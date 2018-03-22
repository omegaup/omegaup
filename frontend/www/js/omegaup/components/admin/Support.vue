<template>
  <div class="omegaup-admin-support panel-primary panel">
    <div class="panel-heading">
      <h2 class="panel-title">{{ T.omegaupTitleSupportDashboard }}</h2>
    </div>
    <div class="panel-body">
      <div class="row">
        <form class="form bottom-margin"
              v-on:submit.prevent="onSearchEmail">
          <div class="col-md-6">
            <div class="input-group">
              <input class="form-control"
                   name="email"
                   type="text"
                   v-bind:disabled="valid"
                   v-bind:placeholder="T.email"
                   v-model="email"> <span class="input-group-btn"><button class="btn btn-default"
                      type="button"
                      v-bind:disabled="valid"
                      v-on:click.prevent="onSearchEmail">{{ T.wordsSearch }}</button></span>
            </div>
          </div>
        </form>
      </div>
      <div class="row">
        <div class="col-md-6">
          <div class="input-group">
            {{ username }}
          </div>
        </div>
      </div>
      <div class="row bottom-margin">
        <form class="form bottom-margin"
              id="generateToken-"
              name="generateToken-"
              v-on:submit.prevent="onGenerateToken"
              v-show="valid">
          <div class="col-md-12 bottom-margin"
               v-show="password_change_request">
            <div class="input-group">
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
  </div>
</template>

<script>
import {T} from '../../omegaup.js';

export default {
  props: {
    valid: Boolean,
    password_change_request: Boolean,
    link: String,
    username: String
  },
  data: function() { return {T: T, email: ''};},
  methods: {
    onSearchEmail: function() { this.$emit('search-email', this.email);},
    onGenerateToken: function() { this.$emit('generate-token', this.email);},
    onCopyToken: function() {
      let copyText = document.querySelector("input[name=link]");
      copyText.trigger('select');
      document.execCommand("copy");
      this.$emit('copy-token');
    },
    onReset: function() {
      this.email = '';
      this.$emit('reset');
    }
  }
};
</script>
