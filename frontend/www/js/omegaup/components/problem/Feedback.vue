<template>
  <div class="row omegaup-feedback-row"
       v-if="qualityHistogram || difficultyHistogram">
    <h5 class="omegaup-feedback-title">Opiniones de los usuarios</h5>
    <div v-bind:class="difficultyHistogram ? 'col-md-6' : 'col-lg-12'"
         v-if="qualityHistogram">
      <omegaup-problem-histogram v-bind:histogram="qualityHistogram"
           v-bind:score="qualityScore"
           v-bind:type="quality"></omegaup-problem-histogram>
    </div>
    <div v-bind:class="qualityHistogram ? 'col-md-6' : 'col-lg-12'"
         v-if="difficultyHistogram">
      <omegaup-problem-histogram v-bind:histogram="difficultyHistogram"
           v-bind:score="difficultyScore"
           v-bind:type="difficulty"></omegaup-problem-histogram>
    </div>
  </div>
</template>

<style>
.omegaup-feedback-row {
  margin:  30px auto 0;
}
.omegaup-feedback-title {
  font-weight: bold;
  color: gray;
}
</style>

<script>
import {T} from '../../omegaup.js';
import problemHistogram from './Histogram.vue';

export default {
  props: {
    qualityHistogram: Array,
    difficultyHistogram: Array,
    qualityScore: Number,
    difficultyScore: Number,
  },
  data: function() {
    return { T, quality: T.wordsQuality, difficulty: T.wordsDifficulty, }
  },
  components: {
    'omegaup-problem-histogram': problemHistogram,
  }
}
</script>
