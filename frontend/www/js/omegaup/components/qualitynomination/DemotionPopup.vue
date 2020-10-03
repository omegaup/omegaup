<template>
  <div class="qualitynomination-demotionpopup">
    <a href="#" v-on:click="onReportInappropriateProblem">{{
      T.wordsReportProblem
    }}</a>
    <form v-show="showReportDialog" class="popup h-auto w-auto">
      <template v-if="currentView == 'question'">
        <button class="close" type="button" v-on:click="onHide">×</button>
        <div class="form-group">
          <div class="question-text">
            {{ T.reportProblemFormQuestion }}
          </div>
          <select
            v-model="selectedReason"
            class="control-label"
            name="selectedReason"
          >
            <option value="no-problem-statement">
              {{ T.reportProblemFormNotAProblemStatement }}
            </option>
            <option value="poorly-described">
              {{ T.reportProblemFormPoorlyDescribed }}
            </option>
            <option value="offensive">
              {{ T.reportProblemFormOffensive }}
            </option>
            <option value="spam">
              {{ T.reportProblemFormSpam }}
            </option>
            <option value="duplicate">
              {{ T.reportProblemFormDuplicate }}
            </option>
            <option value="wrong-test-cases">
              {{ T.reportProblemFormCases }}
            </option>
            <option value="other">
              {{ T.reportProblemFormOtherReason }}
            </option>
          </select>
        </div>
        <div v-if="selectedReason == 'duplicate'" class="form-group">
          <label class="control-label">{{
            T.reportProblemFormLinkToOriginalProblem
          }}</label>
          <input v-model="original" class="input-line" name="original" />
        </div>
        <div class="form-group">
          <label class="control-label">{{
            T.reportProblemFormAdditionalComments
          }}</label>
          <textarea
            v-model="rationale"
            class="input-text"
            name="rationale"
            type="text"
          ></textarea>
        </div>
        <div class="text-right">
          <button
            class="col-md-4 btn btn-primary"
            type="submit"
            v-bind:disabled="!selectedReason || (!rationale &amp;&amp; selectedReason == 'other') || (!original &amp;&amp; selectedReason == 'duplicate')"
            v-on:click.prevent="onSubmit"
          >
            {{ T.wordsSend }}
          </button>
        </div>
      </template>
      <template v-if="currentView == 'thanks'">
        <div class="centered">
          <h1>{{ T.reportProblemFormThanksForReview }}</h1>
        </div>
      </template>
    </form>
  </div>
</template>

<style>
.qualitynomination-demotionpopup .popup {
  position: fixed;
  bottom: 10px;
  right: 4%;
  z-index: 9999999 !important;
  margin: 2em auto 0 auto;
  border: 2px solid #ccc;
  padding: 1em;
  overflow: auto;
  background: #fff;
}

.qualitynomination-demotionpopup .question-text {
  font-weight: bold;
  padding-bottom: 4px;
}

.qualitynomination-demotionpopup .title-text {
  font-weight: bold;
  font-size: 1em;
  padding-bottom: 1em;
}

.qualitynomination-demotionpopup .control-label {
  width: 100%;
}

.qualitynomination-demotionpopup .input-text {
  height: 100px;
  width: 100%;
}

.qualitynomination-demotionpopup .input-line {
  width: 100%;
}

.qualitynomination-demotionpopup .button-row {
  width: 100%;
  margin-left: 66%;
}

.qualitynomination-demotionpopup .centered {
  margin-left: 20%;
  margin-top: 24%;
  position: absolute;
}
</style>

<script lang="ts">
import { Vue, Component } from 'vue-property-decorator';
import T from '../../lang';
import * as ui from '../../ui';

@Component
export default class QualityNominationDemotionPopup extends Vue {
  T = T;
  ui = ui;
  rationale = '';
  original = '';
  currentView = 'question';
  showReportDialog = false;
  selectedReason = '';

  onHide(): void {
    this.showReportDialog = false;
  }

  onReportInappropriateProblem(): void {
    this.showReportDialog = true;
    this.currentView = 'question';
    this.rationale = '';
    this.original = '';
    this.selectedReason = '';
  }

  onSubmit(): void {
    this.$emit('submit', this);
    this.currentView = 'thanks';
    setTimeout(() => this.onHide(), 2000);
  }
}
</script>
