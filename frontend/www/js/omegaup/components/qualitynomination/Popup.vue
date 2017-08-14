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
          <template v-if="currentView == 'question'">
            <h1>{{ T.qualityFormCongrats }}</h1>
            <div class="question-text">
              {{ T.qualityFormRecommendingQuestion }}
            </div>
            <div class="button-row row">
              <button class="col-md-4 btn btn-primary"
                   type="button"
                   v-on:click="onShowSuggestion">{{ T.wordsYes }}</button>
              <div class="col-md-4"></div><button class="col-md-4 btn btn-default"
                   type="button"
                   v-on:click="onHide">{{ T.wordsNo }}</button>
            </div>
          </template>
          <template v-if="currentView == 'suggestion'">
            <div class="form-group">
              <label class="control-label">{{ T.qualityFormDifficulty }}</label><br>
              <label class="radio-inline"><input type="radio"
                     v-model="difficulty"
                     value="1"> {{ T.qualityFormDifficultyVeryEasy }}</label> <label class=
                     "radio-inline"><input type="radio"
                     v-model="difficulty"
                     value="2"> {{ T.qualityFormDifficultyEasy }}</label> <label class=
                     "radio-inline"><input type="radio"
                     v-model="difficulty"
                     value="3"> {{ T.qualityFormDifficultyMedium }}</label> <label class=
                     "radio-inline"><input type="radio"
                     v-model="difficulty"
                     value="4"> {{ T.qualityFormDifficultyHard }}</label> <label class=
                     "radio-inline"><input type="radio"
                     v-model="difficulty"
                     value="5"> {{ T.qualityFormDifficultyVeryHard }}</label>
            </div>
            <div class="form-group">
              <label class="control-label">{{ T.qualityFormTags }} <select class="form-control"
                      multiple
                      v-model="tags">
                <option value="arboles">
                  {{ T.problemTopicTrees }}
                </option>
                <option value="busqueda-binaria">
                  {{ T.problemTopicBinarySearch }}
                </option>
                <option value="busquedas">
                  {{ T.problemTopicSearch }}
                </option>
                <option value="flujo-maximo">
                  {{ T.problemTopicMaxFlow }}
                </option>
                <option value="fuerza-bruta">
                  {{ T.problemTopicBruteForce }}
                </option>
                <option value="grafos">
                  {{ T.problemTopicGraphTheory }}
                </option>
                <option value="ordenamiento">
                  {{ T.problemTopicSorting }}
                </option>
                <option value="pilas-y-colas">
                  {{ T.problemTopicStackQueue }}
                </option>
                <option value="programacion-dinamica">
                  {{ T.problemTopicDP }}
                </option>
                <option value="simulacion">
                  {{ T.problemTopicSimulation }}
                </option>
                <option value="teoria-de-numeros">
                  {{ T.problemTopicNumberTheory }}
                </option>
                <option value="otro">
                  {{ T.problemTopicOther }}
                </option>
              </select></label>
            </div>
            <div class="form-group">
              <label class="control-label">{{ T.qualityFormSource }} <input class="form-control"
                     type="text"
                     v-model="source"></label>
            </div>
            <div class="form-group required">
              <label class="control-label">{{ T.qualityFormRationaleInput }} <input class=
              "form-control"
                     type="text"
                     v-model="rationale"></label>
            </div>
            <div class="row">
              <div class="col-md-4"></div><button class="col-md-4 btn btn-primary"
                   type="submit"
                   v-bind:disabled="rationale.length &lt;= 0"
                   v-on:click="onSubmit">{{ T.wordsSend }}</button> <button class=
                   "col-md-4 btn btn-default"
                   type="button"
                   v-on:click="onHide">{{ T.wordsCancel }}</button>
            </div>
          </template>
          <template v-if="currentView == 'thanks'">
            <h1>{{ T.qualityFormThanksForReview }}</h1>
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
  props: {
    solved: Boolean,
    nominated: Boolean,
    dismissal: Boolean,
    originalSource: String
  },
  data: function() {
    return {
      T: T,
      UI: UI,
      currentView: 'question',
      difficulty: undefined,
      rationale: '',
      source: this.originalSource,
      showFormOverride: true,
      tags: [],
    };
  },
  computed: {
    showForm: function() {
      return this.showFormOverride && this.solved && !this.nominated &&
             !this.dismissal;
    }
  },
  methods: {
    onHide() {
      this.showFormOverride = false;
      this.$emit('dismissal', this);
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
  width: 500px;
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
</style>
