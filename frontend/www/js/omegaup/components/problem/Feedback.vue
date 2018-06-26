<template>
  <div class="row omegaup-feedback-row"
       v-if="qualityHistogram || difficultyHistogram">
    <h5 class="omegaup-feedback-title">{{ T.wordsUsersFeedback }}</h5>
    <div v-bind:class="containerClass"
         v-if="qualityHistogram">
      <omegaup-problem-histogram v-bind:histogram="qualityHistogram"
           v-bind:score="qualityScore"
           v-bind:type="`Quality`"></omegaup-problem-histogram>
    </div>
    <div v-bind:class="containerClass"
         v-if="difficultyHistogram">
      <omegaup-problem-histogram v-bind:histogram="difficultyHistogram"
           v-bind:score="difficultyScore"
           v-bind:type="`Difficulty`"></omegaup-problem-histogram>
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
    return { T, }
  },
  computed: {
    containerClass: function() {
      return this.qualityHistogram && this.difficultyHistogram ? 'col-md-6' :
                                                                 'col-md-12';
    }
  },
  components: {
    'omegaup-problem-histogram': problemHistogram,
  }
}
</script>
