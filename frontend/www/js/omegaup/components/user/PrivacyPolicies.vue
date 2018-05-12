<template>
  <div class="panel panel-default">
    <div class="panel-body">
      <div class="panel panel-default no-bottom-margin">
        <div class="panel-heading">
          <h3 class="panel-title">{{ T.wordsPrivacyPolicies }}</h3>
        </div>
        <div class="panel">
          <h2 class="panel-title">{{ policies.privacy_policies }}</h2>
        </div>
      </div>
      <form v-on:submit.prevent="onSubmit">
        <div class="top-margin text-center">
          <label v-show="!accepted"><input name="agree"
                 type="checkbox"
                 v-model="internalAgree"> {{ T.wordsAgree }}</label> <button class=
                 "btn btn-primary"
               v-bind:disabled="!internalAgree"
               v-show="!accepted">{{ T.wordsSaveChanges }}</button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import {T} from '../../omegaup.js';
export default {
  props: {
    policies: Object,
    agree: Boolean,
    accepted: Boolean,
  },
  methods: {onSubmit: function() { this.$emit('submit', this);}},
  data: function() {
    return { T: T, internalAgree: this.agree }
  },
}
</script>
