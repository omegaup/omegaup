<template>
  <div class="row omegaup-feedback-row"
       v-if="qualityHistogram || difficultyHistogram">
    <h5 class="omegaup-feedback-title">{{ T.wordsUsersFeedback }}</h5>
    <div v-bind:class="containerClass"
         v-if="qualityHistogram">
      <omegaup-problem-histogram v-bind:histogram="qualityHistogram"
           v-bind:score="qualityScore"
           v-bind:type="`quality`"></omegaup-problem-histogram>
    </div>
    <div v-bind:class="containerClass"
         v-if="difficultyHistogram">
      <omegaup-problem-histogram v-bind:histogram="difficultyHistogram"
           v-bind:score="difficultyScore"
           v-bind:type="`difficulty`"></omegaup-problem-histogram>
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

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { T } from '../../omegaup.js';
import problemHistogram from './Histogram.vue';

@Component({
  components: {
    'omegaup-problem-histogram': problemHistogram,
  },
})
export default class ProblemFeedback extends Vue {
  @Prop() qualityHistogram!: number[];
  @Prop() difficultyHistogram!: number[];
  @Prop() qualityScore!: number;
  @Prop() difficultyScore!: number;

  T = T;

  get containerClass(): string {
    return this.qualityHistogram && this.difficultyHistogram
      ? 'col-md-6'
      : 'col-md-12';
  }
}

</script>
