<template>
  <div class="qualitynomination-popup">
    <transition name="fade">
      <div class="panel panel-default popup"
           v-show="showForm">
        <div v-show="showQuestionText">
          {{ T.qualityFormCongrats }}<br>
          <br>
          {{ T.qualityFormRecommendingQuestion }}<br>
        </div>
        <div v-show="showYesNo">
          <button v-on:click="onShowRationale">{{ T.wordsYes }}</button> <button v-on:click=
          "onHide">{{ T.wordsNo }}</button>
        </div>
        <div class="required"
             v-show="showRationale">
          <label class="control-label">{{ T.qualityFormRationaleInput }}: <input name="Rationale"
                 type="text"
                 v-model="rationale"></label> <button type="submit"
               v-bind:disabled="rationale.length &lt;= 0"
               v-on:click.prevent="onSubmit">{{ T.wordsSend }}</button>
        </div>
        <div v-show="showThanks">
          {{ T.qualityFormThanksForReview }}
        </div>
      </div>
    </transition>
  </div>
</template>

<script>
import {T} from '../../omegaup.js';
import UI from '../../ui.js';

export default {
  props:
      {solved: Boolean, nominated: Boolean, statement: String, source: String},
  data: function() {
    return {
      T: T,
      UI: UI,
      showFormOverride: true,
      showYesNo: true,
      showQuestionText: true,
      showRationale: false,
      showThanks: false,
      rationale: ''
    };
  },
  computed: {
    showForm: function() {
      return this.showFormOverride && this.solved && !this.nominated;
    }
  },
  methods: {
    onHide() { this.showFormOverride = false},
    onShowRationale() {
      this.$emit('show-rationale', this);
      this.showYesNo = false;
      this.showRationale = true;
    },
    onSubmit() {
      this.$emit('submit', this);
      this.showRationale = false;
      this.showThanks = true;
      this.showQuestionText = false;

      var self = this;
      setTimeout(function() { self.onHide() }, 1000);
    }
  }
};
</script>

<style>
.qualitynomination-popup .popup {
  position: fixed;
  bottom: 10px;
  right: 20%;
  z-index: 9999999 !important;
  width: 350px;
  height: 11em;
  margin: 2em auto 0 auto;
  border: 2px solid #ccc;
  padding: 1em;
  overflow: auto;
}

.qualitynomination-popup .fade-enter-active, .qualitynomination-popup .fade-leave-active {
  transition: opacity .5s
}

.qualitynomination-popup .fade-enter, .qualitynomination-popup .fade-leave-to {
  opacity: 0
}

.qualitynomination-popup .required .control-label:before {
  content:"*";
  color:red;
  position: absolute;
  margin-left: -10px;
}
</style>
