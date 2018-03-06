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
      <form class="form bottom-margin"
            v-show="user != null">
        <div class="row bottom-margin">
          <div class="col-md-6">
            <button class="btn btn-default btn-block"
                 type="button"
                 v-bind:disabled="user != null &amp;&amp; user.verified == '1'"
                 v-on:click.prevent="onVerifyUser">
            <template v-if="user != null &amp;&amp; user.verified == '1'">
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
      let hintElem = $('input.typeahead.tt-hint', this.$el);
      let hint = hintElem.val();
      if (hint) {
        // There is a hint currently visible in the UI, the user likely
        // expects that hint to be used when trying to add someone, instead
        // of what they've actually typed so far.
        this.username = hint;
      } else {
        this.username = $('input.typeahead.tt-input', this.$el).val();
      }
      this.$emit('search-username', this.username);
    },
    onVerifyUser: function() { this.$emit('verify-user', this.username);},
    onReset: function() {
      this.username = '';

      let inputElem = $('input.typeahead', this.$el);
      inputElem.typeahead('close');
      inputElem.val('');
      this.$emit('reset');
    }
  }
};
</script>
