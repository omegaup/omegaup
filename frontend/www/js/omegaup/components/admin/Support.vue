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
            v-show="valid">
        <div class="row bottom-margin">
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
  props: {valid: Boolean, verified: Boolean},
  data: function() {
    return {
      T: T,
    };
  },
  methods: {
    onSearchUsername: function() {
      this.$emit('search-username', this.username);
    },
    onVerifyUser: function() { this.$emit('verify-user', this.username);},
    onReset: function() { this.$emit('reset');}
  }
};
</script>
