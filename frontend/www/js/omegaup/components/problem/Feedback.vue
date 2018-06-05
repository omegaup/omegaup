<template>
  <div v-if="qualityHistogram || difficultyHistogram" class="row omegaup-feedback-row">
    <h5 class="omegaup-feedback-title">Opiniones de los usuarios</h5>
    <div v-if="qualityHistogram" v-bind:class="difficultyHistogram ? 'col-md-6' : 'col-lg-12'">
      <omegaup-problem-histogram v-bind:type="`quality`"></omegaup-problem-histogram>
    </div>
    <div v-if="difficultyHistogram" v-bind:class="qualityHistogram ? 'col-md-6' : 'col-lg-12'">
      <omegaup-problem-histogram v-bind:type="`difficulty`"></omegaup-problem-histogram>
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
  },
  data: function() {
    return {
      T,
      quality: 'quality',
      difficulty: 'difficulty',
    }
  },
  components: {
    'omegaup-problem-histogram': problemHistogram,
  }
}
/* INSERT INTO QualityNominations VALUES (18671, 10, 1, 'suggestion', '{"difficulty":0,"tags":["arboles","arbol-de-sufijos","2-sat"],"quality":2}', '2018-06-02 00:54:10', 'open'); */
</script>
