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
          <template v-if="currentView == 'feedback'">
            <div class="title-text">
              {{T.qualityFormCongrats}}
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
              <label class="control-label">{{ T.qualityFormTopics }} <select class=
              "form-control topics-container"
                      multiple
                      v-model="topics">
                <option value="problemTopicImplementation">
                  {{ T.problemTopicImplementation }}
                </option>
                <option value="problemTopicDynamicProgramming">
                  {{ T.problemTopicDynamicProgramming }}
                </option>
                <option value="problemTopicMath">
                  {{ T.problemTopicMath }}
                </option>
                <option value="problemTopicLoops">
                  {{ T.problemTopicLoops }}
                </option>
                <option value="problemTopicIfElseSwitch">
                  {{ T.problemTopicIfElseSwitch }}
                </option>
                <option value="problemTopicInputOutput">
                  {{ T.problemTopicInputOutput }}
                </option>
                <option value="problemTopicArrays">
                  {{ T.problemTopicArrays }}
                </option>
                <option value="problemTopicSimulation">
                  {{ T.problemTopicSimulation }}
                </option>
                <option value="problemTopicGreedy">
                  {{ T.problemTopicGreedy }}
                </option>
                <option value="problemTopicDataStructures">
                  {{ T.problemTopicDataStructures }}
                </option>
                <option value="problemTopicBruteForce">
                  {{ T.problemTopicBruteForce }}
                </option>
                <option value="problemTopicBreadthDepthFirstSearch">
                  {{ T.problemTopicBreadthDepthFirstSearch }}
                </option>
                <option value="problemTopicSorting">
                  {{ T.problemTopicSorting }}
                </option>
                <option value="problemTopicBinarySearch">
                  {{ T.problemTopicBinarySearch }}
                </option>
                <option value="problemTopicGraphTheory">
                  {{ T.problemTopicGraphTheory }}
                </option>
                <option value="problemTopicTrees">
                  {{ T.problemTopicTrees }}
                </option>
                <option value="problemTopicStrings">
                  {{ T.problemTopicStrings }}
                </option>
                <option value="problemTopicNumberTheory">
                  {{ T.problemTopicNumberTheory }}
                </option>
                <option value="problemTopicGeometry">
                  {{ T.problemTopicGeometry }}
                </option>
                <option value="problemTopicCombinatorics">
                  {{ T.problemTopicCombinatorics }}
                </option>
                <option value="problemTopicTwoPointers">
                  {{ T.problemTopicTwoPointers }}
                </option>
                <option value="problemTopicDisjointSets">
                  {{ T.problemTopicDisjointSets }}
                </option>
                <option value="problemTopicBitmasks">
                  {{ T.problemTopicBitmasks }}
                </option>
                <option value="problemTopicProbability">
                  {{ T.problemTopicProbability }}
                </option>
                <option value="problemTopicShortestPath">
                  {{ T.problemTopicShortestPath }}
                </option>
                <option value="problemTopicHashing">
                  {{ T.problemTopicHashing }}
                </option>
                <option value="problemTopicDivideAndConquer">
                  {{ T.problemTopicDivideAndConquer }}
                </option>
                <option value="problemTopicGameTheory">
                  {{ T.problemTopicGameTheory }}
                </option>
                <option value="problemTopicMatrices">
                  {{ T.problemTopicMatrices }}
                </option>
                <option value="problemTopicStackQueue">
                  {{ T.problemTopicStackQueue }}
                </option>
                <option value="problemTopicBigNumbers">
                  {{ T.problemTopicBigNumbers }}
                </option>
                <option value="problemTopicBuckets">
                  {{ T.problemTopicBuckets }}
                </option>
                <option value="problemTopicMaxFlow">
                  {{ T.problemTopicMaxFlow }}
                </option>
                <option value="problemTopicSuffixTree">
                  {{ T.problemTopicSuffixTree }}
                </option>
                <option value="problemTopicSuffixArray">
                  {{ T.problemTopicSuffixArray }}
                </option>
                <option value="problemTopicParsing">
                  {{ T.problemTopicParsing }}
                </option>
                <option value="problemTopicTernarySearch">
                  {{ T.problemTopicTernarySearch }}
                </option>
                <option value="problemTopicMeetInTheMiddle">
                  {{ T.problemTopicMeetInTheMiddle }}
                </option>
                <option value="problemTopicFastFourierTransform">
                  {{ T.problemTopicFastFourierTransform }}
                </option>
                <option value="problemTopic2Sat">
                  {{ T.problemTopic2Sat }}
                </option>
                <option value="problemTopicBacktracking">
                  {{ T.problemTopicBacktracking }}
                </option>
              </select></label>
            </div>
            <div class="form-group">
              <label class="control-label">{{ T.qualityFormQuality }}</label><br>
              <label class="radio-inline"><input type="radio"
                     v-model="quality"
                     value="0"> {{ T.qualityFormQualityNothing }}</label> <label class=
                     "radio-inline"><input type="radio"
                     v-model="quality"
                     value="1"> {{ T.qualityFormQualityVeryUnlikely }}</label> <label class=
                     "radio-inline"><input type="radio"
                     v-model="quality"
                     value="2"> {{ T.qualityFormQualityUnlikely }}</label> <label class=
                     "radio-inline"><input type="radio"
                     v-model="quality"
                     value="3"> {{ T.qualityFormNeutralLikely }}</label> <label class=
                     "radio-inline"><input type="radio"
                     v-model="quality"
                     value="4"> {{ T.qualityFormQualityVirtuallyCertain }}</label>
            </div>
            <div class="row">
              <div class="col-md-4"></div><button class="col-md-4 btn btn-primary"
                   type="submit"
                   v-bind:disabled="!quality &amp;&amp; !topics.length &amp;&amp; !difficulty"
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
import {T} from '../../omegaup.js';
import UI from '../../ui.js';

export default {
  props: {solved: Boolean},
  data: function() {
    return {
      T: T,
      UI: UI,
      currentView: 'feedback',
      difficulty: undefined,
      quality: undefined,
      showFormOverride: true,
      topics: [],
    };
  },
  computed:
      {showForm: function() { return this.showFormOverride && this.solved;}},
  methods: {
    onHide() {
      this.showFormOverride = false;
      this.$emit('dismiss', this);
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
.qualitynomination-popup .popup {
  position: fixed;
  bottom: 10px;
  right: 20%;
  z-index: 9999999 !important;
  width: 632px;
  height: 400px;
  margin: 2em auto 0 auto;
  border: 2px solid #ccc;
  padding: 1em;
  overflow: auto;
}

.qualitynomination-popup .control-label {
  width: 100%;
}

.qualitynomination-popup .question-text, .qualitynomination-popup h1 {
  text-align: center;
}

.qualitynomination-popup h1 {
  font-size: 120%;
  font-weight: bold;
  margin: 5em 0;
}

.qualitynomination-popup .button-row {
  margin: 4em 0;
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
	padding-bottom: 1em;
}

.qualitynomination-popup .topics-container {
	height: 140px;
}

.qualitynomination-popup .thanks-title {
	display: block;
  font-size: 2em;
  font-weight: bold;
	padding-left: 120px;
	padding-top: 148px;
}
</style>
