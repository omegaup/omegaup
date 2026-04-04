<template>
  <div class="omegaup-histogram-container">
    <p class="omegaup-histogram-title">{{ title }}</p>
    <div class="omegaup-histogram">
      <div class="omegaup-histogram-1">
        <p class="omegaup-histogram-score">{{ score.toFixed(1) }}</p>
        <p class="omegaup-histogram-votes">
          👥 {{ totalVotes + ' ' + T.wordsTotalVotes }}
        </p>
      </div>
      <div class="omegaup-histogram-2">
        <div class="omegaup-histogram-item">
          <div class="omegaup-histogram-bar-name">
            {{ tags[0] }}
          </div>
          <div
            class="omegaup-histogram-bar omegaup-histogram-bar-1"
            :style="`width:${barsWidth[0]}%`"
          >
            {{ `${customHistogram[0]}` }}
          </div>
        </div>
        <div class="omegaup-histogram-item">
          <div class="omegaup-histogram-bar-name">
            {{ tags[1] }}
          </div>
          <div
            class="omegaup-histogram-bar omegaup-histogram-bar-2"
            :style="`width:${barsWidth[1]}%`"
          >
            {{ `${customHistogram[1]}` }}
          </div>
        </div>
        <div class="omegaup-histogram-item">
          <div class="omegaup-histogram-bar-name">
            {{ tags[2] }}
          </div>
          <div
            class="omegaup-histogram-bar omegaup-histogram-bar-3"
            :style="`width:${barsWidth[2]}%`"
          >
            {{ `${customHistogram[2]}` }}
          </div>
        </div>
        <div class="omegaup-histogram-item">
          <div class="omegaup-histogram-bar-name">
            {{ tags[3] }}
          </div>
          <div
            class="omegaup-histogram-bar omegaup-histogram-bar-4"
            :style="`width:${barsWidth[3]}%`"
          >
            {{ `${customHistogram[3]}` }}
          </div>
        </div>
        <div class="omegaup-histogram-item">
          <div class="omegaup-histogram-bar-name">
            {{ tags[4] }}
          </div>
          <div
            class="omegaup-histogram-bar omegaup-histogram-bar-5"
            :style="`width:${barsWidth[4]}%`"
          >
            {{ `${customHistogram[4]}` }}
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import T from '../../lang';

export enum HistogramType {
  Quality = 'quality',
  Difficulty = 'difficulty',
}

@Component
export default class ProblemHistogram extends Vue {
  @Prop() type!: string;
  @Prop() histogram!: number[];
  @Prop() score!: number;

  T = T;

  get tags(): string[] {
    return this.type === HistogramType.Quality
      ? [
          T.qualityFormQualityVeryGood,
          T.qualityFormQualityGood,
          T.qualityFormQualityFair,
          T.qualityFormQualityBad,
          T.qualityFormQualityVeryBad,
        ]
      : [
          T.qualityFormDifficultyVeryEasy,
          T.qualityFormDifficultyEasy,
          T.qualityFormDifficultyMedium,
          T.qualityFormDifficultyHard,
          T.qualityFormDifficultyVeryHard,
        ];
  }

  get title(): string {
    return this.type === HistogramType.Quality
      ? T.wordsQuality
      : T.wordsDifficulty;
  }

  get customHistogram(): number[] {
    return this.type === HistogramType.Quality
      ? this.histogram.reverse()
      : this.histogram;
  }

  get totalVotes(): number {
    return this.histogram.reduce((a: number, b: number) => a + b);
  }

  get barsWidth(): number[] {
    const maxValue = Math.max(...this.customHistogram);
    if (maxValue === 0) return [0, 0, 0, 0, 0];
    return this.customHistogram.map((value) => (value / maxValue) * 100);
  }
}
</script>

<style lang="scss" scoped>
@import '../../../../sass/main.scss';
.omegaup-histogram-container {
  margin: 50px 0;
}

.omegaup-histogram-title {
  font-size: 1.25em;
  font-weight: bold;
  text-align: center;
}

.omegaup-histogram {
  display: grid;
  grid-template-columns: 1fr 2fr;
  margin: 0 auto;
  width: 320px;
  height: 150px;
}

.omegaup-histogram-1 {
  display: grid;
  padding: 20px;
}

.omegaup-histogram-1 p {
  margin: 0;
  text-align: center;
}

.omegaup-histogram-score {
  display: flex;
  justify-content: center;
  align-items: center;
  font-size: 70px;
}

.omegaup-histogram-2 {
  display: grid;
  grid-template-rows: repeat(5, 1fr);
}

.omegaup-histogram-item {
  display: grid;
  align-items: center;
  grid-auto-flow: column;
  grid-template-columns: 60px 1fr;
  grid-gap: 10px;
}

.omegaup-histogram-bar-name {
  font-size: 12px;
}

@keyframes leftToRight {
  to {
    background-position: left bottom;
  }
}

.omegaup-histogram-bar {
  display: flex;
  align-items: center;
  font-size: 15px;
  font-weight: bold;
  background-size: 200% 100%;
  background-position: right bottom;
  animation: leftToRight 0.5s ease 0.65s forwards;
}

.omegaup-histogram-bar-1 {
  background-image: linear-gradient(
    to right,
    var(--arena-histogram-bar-1-background-gradient) 50%,
    transparent 50%
  );
}

.omegaup-histogram-bar-2 {
  background-image: linear-gradient(
    to right,
    var(--arena-histogram-bar-2-background-gradient) 50%,
    transparent 50%
  );
}

.omegaup-histogram-bar-3 {
  background-image: linear-gradient(
    to right,
    var(--arena-histogram-bar-3-background-gradient) 50%,
    transparent 50%
  );
}

.omegaup-histogram-bar-4 {
  background-image: linear-gradient(
    to right,
    var(--arena-histogram-bar-4-background-gradient) 50%,
    transparent 50%
  );
}

.omegaup-histogram-bar-5 {
  background-image: linear-gradient(
    to right,
    var(--arena-histogram-bar-5-background-gradient) 50%,
    transparent 50%
  );
}
</style>
