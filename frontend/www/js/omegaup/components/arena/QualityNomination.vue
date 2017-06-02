<template>
  <transition name="fade">
    <div class="panel panel-default quality-nom-contents" v-show="showForm">
      <div class="quality-nom-contents-text" v-show="showQuestionText">
        {{ T.qualityFormCongrats }}
        <br/> <br/>
        {{ T.qualityFormRecommendingQuestion }}
        <br/>
      </div>
      <div class="quality-nom-yes-no-btns" v-show="showYesNo">
        <button v-on:click="onShowRationale">{{ T.wordsYes }}</button>
        <button v-on:click="onHide">{{ T.wordsNo }}</button>
      </div>
      <div class="quality-nom-yes-rationale required" v-show="showRationale">
        <label class="control-label">{{ T.qualityFormRationaleInput }}: <input type="text" name="Rationale" v-model="rationale"></label>
        <button type="submit" v-on:click.prevent="onSubmit" :disabled="rationale.length <= 0">{{ T.wordsSend }}</button>
      </div>
      <div class="quality-nom-thanks" v-show="showThanks">
        {{ T.qualityFormThanksForReview }}
      </div>
    </div>
  </transition>
</template>

<script>
import {T} from '../../omegaup.js';
import UI from '../../ui.js';

export default {
  props: {
    solved: Boolean,
    nominated: Boolean,
    statement: String,
    source: String
  },
  data: function() {
    return {
      T: T,
      UI: UI,
      showForm: this.solved && !this.nominated,
      showYesNo: true,
      showQuestionText: true,
      showRationale: false,
      showThanks: false,
      rationale: ''
    };
  },
  methods: {
    onHide() {
      this.showForm = false
    },
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
.quality-nom-contents {
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

.fade-enter-active, .fade-leave-active {
  transition: opacity .5s
}

.fade-enter, .fade-leave-to {
  opacity: 0
}

.required .control-label:before {
  content:"*";
  color:red;
  position: absolute;
  margin-left: -10px;
}
</style>
