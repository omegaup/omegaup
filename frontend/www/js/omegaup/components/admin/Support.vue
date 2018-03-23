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
                   v-bind:disabled="username != null"
                   v-bind:placeholder="T.email"
                   v-model="email"> <span class="input-group-btn"><button class="btn btn-default"
                      type="button"
                      v-bind:disabled="username != null"
                      v-on:click.prevent="onSearchEmail">{{ T.wordsSearch }}</button></span>
            </div>
          </div>
        </form>
      </div>
      <div class="row bottom-margin">
        <div class="col-md-6">
          <div class="input-group">
            {{ username }}
          </div>
        </div>
      </div>
      <div class="row bottom-margin">
        <form class="form bottom-margin"
              id="verifyUser"
              name="verifyUser"
              v-on:submit.prevent="onVerifyUser"
              v-show="username != null">
          <div class="col-md-6">
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
import UI from '../../ui.js';

export default {
  props: {username: String, verified: Boolean},
  data: function() { return {T: T, email: null};},
  methods: {
    onSearchEmail: function() { this.$emit('search-email', this.email);},
    onVerifyUser: function() { this.$emit('verify-user', this.email);},
    onReset: function() { this.$emit('reset');}
  }
};
</script>
