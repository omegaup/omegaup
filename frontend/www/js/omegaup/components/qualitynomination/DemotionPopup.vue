<template>
  <div class="qualitynomination-demotionpopup">
    <a href="#"
         v-on:click="onReportInappropriateProblem">{{ T.wordsReportProblem }}</a>
    <form class="panel panel-default popup"
          v-show="showReportDialog">
      <template v-if="currentView == 'question'">
        <button class="close"
                  type="button"
                  v-on:click="onHide">×</button>
        <div class="title-text">
          {{ T.reportProblemFormTitle }}
        </div>
        <div class="form-group">
          <div class="question-text">
            {{ T.reportProblemFormQuestion }}
          </div><select class="control-label"
               name="selectedReason"
               v-model="selectedReason">
            <option value="no-problem-statement">
              {{ T.reportProblemFormNotAProblemStatement }}
            </option>
            <option value="offensive">
              {{ T.reportProblemFormOffensive }}
            </option>
            <option value="spam">
              {{ T.reportProblemFormSpam }}
            </option>
            <option value="other">
              {{ T.reportProblemFormOtherReason }}
            </option>
          </select>
        </div>
        <div class="form-group">
          <label class="control-label">{{ T.reportProblemFormAdditionalComments }}</label>
          <textarea class="input-text"
               name="rationale"
               type="text"
               v-model="rationale"></textarea>
        </div>
        <div class="button-row">
          <button class="col-md-4 btn btn-primary"
               type="submit"
               v-bind:disabled=
               "!selectedReason || (!rationale &amp;&amp; selectedReason == 'other')"
               v-on:click.prevent="onSubmit">{{ T.wordsSend }}</button>
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

<script>
import {T} from '../../omegaup.js';
import UI from '../../ui.js';

export default {
  props: {},

  data: function() {
    return {
      T: T,
      UI: UI,
      rationale: '',
      currentView: 'question',
      showReportDialog: false,
      selectedReason: undefined
    };
  },

  methods: {
    onHide() { this.showReportDialog = false;},

    onReportInappropriateProblem() {
      this.showReportDialog = true;
      this.currentView = 'question';
      this.rationale = '';
      this.selectedReason = undefined;
    },

    onSubmit() {
      this.$emit('submit', this);
      this.currentView = 'thanks';
      setTimeout(() => this.onHide(), 1000);
    }
  }
};

</script>

<style>

.qualitynomination-demotionpopup .popup {
	position: fixed;
	bottom: 10px;
	right: 4%;
	z-index: 9999999 !important;
	width: 420px;
	height: 310px;
	margin: 2em auto 0 auto;
	border: 2px solid #ccc;
	padding: 1em;
	overflow: auto;
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
