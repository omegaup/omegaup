<template>
  <div
    v-if="qualityHistogram || difficultyHistogram"
    class="row omegaup-feedback-row"
  >
    <h5 class="omegaup-feedback-title w-100">{{ T.wordsUsersFeedback }}</h5>
    <div v-if="qualityHistogram" :class="containerClass">
      <omegaup-problem-histogram
        :histogram="qualityHistogram"
        :score="qualityScore"
        :type="HistogramType.Quality"
      ></omegaup-problem-histogram>
    </div>
    <div v-if="difficultyHistogram" :class="containerClass">
      <omegaup-problem-histogram
        :histogram="difficultyHistogram"
        :score="difficultyScore"
        :type="HistogramType.Difficulty"
      ></omegaup-problem-histogram>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import T from '../../lang';
import problemHistogram, { HistogramType } from './Histogram.vue';

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
  HistogramType = HistogramType;

  get containerClass(): string {
    return this.qualityHistogram && this.difficultyHistogram
      ? 'col-md-6'
      : 'col-md-12';
  }
}
</script>

<style>
.omegaup-feedback-row {
  margin: 30px auto 0;
}

.omegaup-feedback-title {
  font-weight: bold;
  color: gray;
}
</style>
