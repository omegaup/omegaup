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
              <input class="form-control typeahead"
                   name="username"
                   type="text"
                   v-bind:disabled="user != null"
                   v-bind:placeholder="T.wordsUser"
                   v-model="username"> <span class="input-group-btn"><button class=
                   "btn btn-default"
                      type="button"
                      v-bind:disabled="user != null"
                      v-on:click.prevent="onSearchUsername">{{ T.wordsSearch }}</button></span>
            </div>
          </div>
        </div>
      </form>
      <div class="row bottom-margin"
           v-if="user != null">
        <div class="col-md-6">
          <div class="input-group">
            {{ user.email }}
          </div>
        </div>
      </div>
      <form class="form bottom-margin"
            v-show="user != null">
        <div class="row bottom-margin">
          <div class="col-md-6">
            <button class="btn btn-default btn-block"
                 type="button"
                 v-bind:disabled="user != null &amp;&amp; user.verified"
                 v-on:click.prevent="onVerifyUser">
            <template v-if="user != null &amp;&amp; user.verified">
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
import UI from '../../ui.js';

export default {
  props: {user: Object},
  data: function() { return {T: T, username: ''};},
  mounted: function() {
    let self = this;
    UI.userTypeahead($('input.typeahead', self.$el),
                     function(event, item) { self.username = item.value; });
  },
  methods: {
    onSearchUsername: function() {
      this.username = $('input.typeahead.tt-hint', this.$el).val() ||
                      $('input.typeahead.tt-input', this.$el).val();
      this.$emit('search-username', this.username);
    },
    onVerifyUser: function() { this.$emit('verify-user', this.username);},
    onReset: function() {
      $('input.typeahead', this.$el).typeahead('close').val('');
      this.$emit('reset');
    }
  }
};
</script>
