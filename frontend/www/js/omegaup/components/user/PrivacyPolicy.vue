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
import UI from '../../ui.js';
export default {
  props: {
    policy_markdown: String,
    agree: Boolean,
    accepted: Boolean,
    git_object_id: String,
  },
  computed: {
    policyHtml: function() {
      let markdownConverter = UI.markdownConverter({preview: true});
      return markdownConverter.makeHtml(this.policy_markdown);
    }
  },
  methods: {onSubmit: function() { this.$emit('submit', this);}},
  data: function() {
    return { T: T, internalAgree: this.agree }
  },
}
</script>
