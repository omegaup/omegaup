<template>
  <div class="panel panel-default">
    <div class="panel-body">
      <div class="panel panel-default no-bottom-margin">
        <div class="panel-heading">
          <h3 class="panel-title">{{ T.wordsPrivacyPolicy }}</h3>
        </div>
        <div class="panel">
          <p v-html="policyHtml"></p>
        </div>
      </div>
      <form v-on:submit.prevent="onSubmit">
        <div class="top-margin text-center">
          <label><input name="agreed"
                 type="checkbox"
                 v-bind:disabled="accepted"
                 v-model="agreed"> {{ T.wordsAgree }}</label> <button class="btn btn-primary"
               v-bind:disabled="!agreed || accepted">{{ T.wordsSaveChanges }}</button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import {T} from '../../omegaup.js';
import UI from '../../ui.js';
export default {
  props: {
    policy_markdown: String,
    initial_agreed: Boolean,
    accepted: Boolean,
  },
  computed: {
    policyHtml: function() {
      return this.markdownConverter.makeHtml(this.policy_markdown);
    }
  },
  methods: {onSubmit: function() { this.$emit('submit', this);}},
  data: function() {
    return {
      T: T, agreed: this.initial_agreed, accepted: this.accepted,
          markdownConverter: UI.markdownConverter(),
    }
  },
}
</script>
