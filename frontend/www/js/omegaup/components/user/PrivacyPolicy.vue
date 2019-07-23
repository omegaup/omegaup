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
      <form v-on:submit.prevent="$emit('submit', this)">
        <div class="top-margin text-center">
          <label><input name="agreed"
                 type="checkbox"
                 v-bind:disabled="saved"
                 v-model="agreed"> {{ T.wordsAgree }}</label> <button class="btn btn-primary"
               v-bind:disabled="!agreed || saved">{{ T.wordsSaveChanges }}</button>
        </div>
      </form>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { T } from '../../omegaup.js';
import UI from '../../ui.js';

@Component
export default class UserPrivacyPolicy extends Vue {
  @Prop() policyMarkdown!: string;
  @Prop({ default: false }) initialAgreed!: boolean;
  @Prop() saved!: boolean;

  T = T;
  agreed = this.initialAgreed;
  markdownConverter = UI.markdownConverter();

  get policyHtml(): string {
    return this.markdownConverter.makeHtml(this.policyMarkdown);
  }
}

</script>
