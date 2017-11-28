<template>
  <div class="qualitynomination-popup">
    <transition name="fade">
      <form class="panel panel-default popup"
            v-on:submit.prevent=""
            v-show="showForm">
        <button class="close"
              type="button"
              v-on:click="onHide">×</button>
        <div class="container-fluid">
          <template v-if="currentView == 'suggestion'">
            <div class="title-text">
              {{T.qualityFormCongrats }}
            </div>
            <div class="form-group">
              <label class="control-label">{{ T.qualityFormDifficulty }}</label><br>
              <label class="radio-inline"><input type="radio"
                     v-model="difficulty"
                     value="0"> {{ T.qualityFormDifficultyVeryEasy }}</label> <label class=
                     "radio-inline"><input type="radio"
                     v-model="difficulty"
                     value="1"> {{ T.qualityFormDifficultyEasy }}</label> <label class=
                     "radio-inline"><input type="radio"
                     v-model="difficulty"
                     value="2"> {{ T.qualityFormDifficultyMedium }}</label> <label class=
                     "radio-inline"><input type="radio"
                     v-model="difficulty"
                     value="3"> {{ T.qualityFormDifficultyHard }}</label> <label class=
                     "radio-inline"><input type="radio"
                     v-model="difficulty"
                     value="4"> {{ T.qualityFormDifficultyVeryHard }}</label>
            </div>
            <div class="form-group">
              <label class="control-label">{{ T.qualityFormTags }}
              <ul class="tag-select">
                <li class="tag-select"
                    v-for="problemTopic in sortedProblemTopics"><label class=
                    "tag-select"><input type="checkbox"
                       v-model="tags" :value = "problemTopic.text"> {{ problemTopic.text }}</label></li>
              </ul></label>
            </div>
            <div class="formGroup">
              <label class="control-label">{{ T.qualityFormQuality }}</label><br>
              <label class="radio-inline"><input type="radio"
                     v-model="quality"
                     value="0"> {{ T.qualityFormQualityVeryBad }}</label> <label class=
                     "radio-inline"><input type="radio"
                     v-model="quality"
                     value="1"> {{ T.qualityFormQualityBad }}</label> <label class=
                     "radio-inline"><input type="radio"
                     v-model="quality"
                     value="2"> {{ T.qualityFormQualityFair }}</label> <label class=
                     "radio-inline"><input type="radio"
                     v-model="quality"
                     value="3"> {{ T.qualityFormQualityGood }}</label> <label class=
                     "radio-inline"><input type="radio"
                     v-model="quality"
                     value="4"> {{ T.qualityFormQualityVeryGood }}</label>
            </div>
            <div class="button-row">
              <div class="col-md-4"></div><button class="col-md-4 btn btn-primary"
                   type="submit"
                   v-bind:disabled="!quality &amp;&amp; !tags.length &amp;&amp; !difficulty"
                   v-on:click="onSubmit">{{ T.wordsSend }}</button> <button class=
                   "col-md-4 btn btn-default"
                   type="button"
                   v-on:click="onHide">{{ T.wordsCancel }}</button>
            </div>
          </template>
          <template v-if="currentView == 'thanks'">
            <div class="thanks-title">
              {{ T.qualityFormThanksForReview }}
            </div>
          </template>
        </div>
      </form>
    </transition>
  </div>
</template>

<script>
import {API, T} from '../../omegaup.js';
import UI from '../../ui.js';

export default {
  props: {solved: Boolean, nominated: Boolean},
  data: function() {
    return {
      API: API,
      T: T,
      UI: UI,
      currentView: 'suggestion',
      difficulty: undefined,
      quality: undefined,
      showFormOverride: true,
      tags: [],
      problemTopics: [
        'problemTopic2Sat',
        'problemTopicArrays',
        'problemTopicBacktracking',
        'problemTopicBigNumbers',
        'problemTopicBinarySearch',
        'problemTopicBitmasks',
        'problemTopicBreadthDepthFirstSearch',
        'problemTopicBruteForce',
        'problemTopicBuckets',
        'problemTopicCombinatorics',
        'problemTopicDataStructures',
        'problemTopicDisjointSets',
        'problemTopicDivideAndConquer',
        'problemTopicDynamicProgramming',
        'problemTopicFastFourierTransform',
        'problemTopicGameTheory',
        'problemTopicGeometry',
        'problemTopicGraphTheory',
        'problemTopicGreedy',
        'problemTopicHashing',
        'problemTopicIfElseSwitch',
        'problemTopicImplementation',
        'problemTopicInputOutput',
        'problemTopicLoops',
        'problemTopicMath',
        'problemTopicMatrices',
        'problemTopicMaxFlow',
        'problemTopicMeetInTheMiddle',
        'problemTopicNumberTheory',
        'problemTopicParsing',
        'problemTopicProbability',
        'problemTopicShortestPath',
        'problemTopicSimulation',
        'problemTopicSorting',
        'problemTopicStackQueue',
        'problemTopicStrings',
        'problemTopicSuffixArray',
        'problemTopicSuffixTree',
        'problemTopicTernarySearch',
        'problemTopicTrees',
        'problemTopicTwoPointers',
      ],
    };
  },
  computed: {
    showForm: function() {
      return this.showFormOverride && this.solved && !this.nominated;
    },
    sortedProblemTopics: function() {
      let topics =
          this.problemTopics.map(x => { return {value: x, text: T[x]}; });
      return topics.sort((a, b) => a.text.localeCompare(b.text, T.lang));
    }
  },
  methods: {
    onHide() {
      this.showFormOverride = false;
      this.$emit('dismiss', this);
    },
    onShowSuggestion() {
      this.$emit('show-suggestion', this);
      this.currentView = 'suggestion';
    },
    onSubmit() {
      this.$emit('submit', this);
      this.currentView = 'thanks';

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
  right: 4%;
  z-index: 9999999 !important;
  width: 550px;
  height: 408px;
  margin: 2em auto 0 auto;
  border: 2px solid #ccc;
  padding: 1em;
  overflow: auto;
}

.qualitynomination-popup .control-label {
  width: 100%;
}

.qualitynomination-popup .button-row {
  margin: 4px 0;
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

.qualitynomination-popup .title-text {
	font-weight: bold;
	font-size: 20px;
	padding-bottom: 8px;
	text-align: center;
}

.qualitynomination-popup .tags-container {
	height: 148px;
}

.qualitynomination-popup .thanks-title {
	display: block;
	font-size: 2em;
	font-weight: bold;
	padding-left: 140px;
	padding-top: 148px;
}

ul.tag-select {
    height: 150px;
    overflow: auto;
    border: 1px solid #ccc;
}

ul.tag-select {
    list-style-type: none;
    margin: 0;
    padding: 0;
    overflow-x: hidden;
}

li.tag-select {
    margin: 0;
    padding: 0;
}

label.tag-select {
    font-weight: normal;
    display: block;
    color: WindowText;
    background-color: Window;
    margin: 0;
    padding: 0;
    width: 100%;
}

label.tag-select:hover {
    background-color: Highlight;
    color: HighlightText;
}
</style>
