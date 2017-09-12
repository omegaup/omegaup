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
              <label class="control-label">{{ T.qualityFormTags }} <select class=
              "form-control tags-container"
                      multiple
                      v-model="tags">
                <option v-for="problemTopic in sortedProblemTopics">
                  {{ problemTopic.text }}
                </option>
              </select></label>
            </div>
            <div class="formGroup">
              <label class="control-label">{{ T.qualityFormQuality }}</label><br>
              <label class="radio-inline"><input type="radio"
                     v-model="quality"
                     value="0"> {{ T.qualityFormQualityVeryUnlikely }}</label> <label class=
                     "radio-inline"><input type="radio"
                     v-model="quality"
                     value="1"> {{ T.qualityFormQualityNotLikely }}</label> <label class=
                     "radio-inline"><input type="radio"
                     v-model="quality"
                     value="2"> {{ T.qualityFormQualityNeutral }}</label> <label class=
                     "radio-inline"><input type="radio"
                     v-model="quality"
                     value="3"> {{ T.qualityFormQualityLikely }}</label> <label class=
                     "radio-inline"><input type="radio"
                     v-model="quality"
                     value="4"> {{ T.qualityFormQualityVeryLikely }}</label>
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
        {
          'value': 'problemTopicImplementation',
          'text': T.problemTopicImplementation
        },
        {
          'value': 'problemTopicDynamicProgramming',
          'text': T.problemTopicDynamicProgramming
        },
        {'value': 'problemTopicMath', 'text': T.problemTopicMath},
        {'value': 'problemTopicLoops', 'text': T.problemTopicLoops},
        {
          'value': 'problemTopicIfElseSwitch',
          'text': T.problemTopicIfElseSwitch
        },
        {'value': 'problemTopicInputOutput', 'text': T.problemTopicInputOutput},
        {'value': 'problemTopicArrays', 'text': T.problemTopicArrays},
        {'value': 'problemTopicSimulation', 'text': T.problemTopicSimulation},
        {'value': 'problemTopicGreedy', 'text': T.problemTopicGreedy},
        {
          'value': 'problemTopicDataStructures',
          'text': T.problemTopicDataStructures
        },
        {'value': 'problemTopicBruteForce', 'text': T.problemTopicBruteForce},
        {
          'value': 'problemTopicBreadthDepthFirstSearch',
          'text': T.problemTopicBreadthDepthFirstSearch
        },
        {'value': 'problemTopicSorting', 'text': T.problemTopicSorting},
        {
          'value': 'problemTopicBinarySearch',
          'text': T.problemTopicBinarySearch
        },
        {'value': 'problemTopicGraphTheory', 'text': T.problemTopicGraphTheory},
        {'value': 'problemTopicTrees', 'text': T.problemTopicTrees},
        {'value': 'problemTopicStrings', 'text': T.problemTopicStrings},
        {
          'value': 'problemTopicNumberTheory',
          'text': T.problemTopicNumberTheory
        },
        {'value': 'problemTopicGeometry', 'text': T.problemTopicGeometry},
        {
          'value': 'problemTopicCombinatorics',
          'text': T.problemTopicCombinatorics
        },
        {'value': 'problemTopicTwoPointers', 'text': T.problemTopicTwoPointers},
        {
          'value': 'problemTopicDisjointSets',
          'text': T.problemTopicDisjointSets
        },
        {'value': 'problemTopicBitmasks', 'text': T.problemTopicBitmasks},
        {'value': 'problemTopicProbability', 'text': T.problemTopicProbability},
        {
          'value': 'problemTopicShortestPath',
          'text': T.problemTopicShortestPath
        },
        {'value': 'problemTopicHashing', 'text': T.problemTopicHashing},
        {
          'value': 'problemTopicDivideAndConquer',
          'text': T.problemTopicDivideAndConquer
        },
        {'value': 'problemTopicGameTheory', 'text': T.problemTopicGameTheory},
        {'value': 'problemTopicMatrices', 'text': T.problemTopicMatrices},
        {'value': 'problemTopicStackQueue', 'text': T.problemTopicStackQueue},
        {'value': 'problemTopicBigNumbers', 'text': T.problemTopicBigNumbers},
        {'value': 'problemTopicBuckets', 'text': T.problemTopicBuckets},
        {'value': 'problemTopicMaxFlow', 'text': T.problemTopicMaxFlow},
        {'value': 'problemTopicSuffixTree', 'text': T.problemTopicSuffixTree},
        {'value': 'problemTopicSuffixArray', 'text': T.problemTopicSuffixArray},
        {'value': 'problemTopicParsing', 'text': T.problemTopicParsing},
        {
          'value': 'problemTopicTernarySearch',
          'text': T.problemTopicTernarySearch
        },
        {
          'value': 'problemTopicMeetInTheMiddle',
          'text': T.problemTopicMeetInTheMiddle
        },
        {
          'value': 'problemTopicFastFourierTransform',
          'text': T.problemTopicFastFourierTransform
        },
        {'value': 'problemTopic2Sat', 'text': T.problemTopic2Sat},
        {
          'value': 'problemTopicBacktracking',
          'text': T.problemTopicBacktracking
        }
      ],
    };
  },
  computed: {
    showForm: function() {
      return this.showFormOverride && this.solved && !this.nominated;
    },
    sortedProblemTopics: function() {
      function compare(a, b) { return a.text.localeCompare(b.text); }
      return this.problemTopics.sort(compare);
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
  right: 20%;
  z-index: 9999999 !important;
  width: 632px;
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
</style>
