<template>
  <div class="problem-solving-progress">
    <div class="progress-container">
      <!-- Circular Progress Chart -->
      <div class="circular-chart-container">
        <svg
          class="circular-chart"
          viewBox="0 0 120 120"
          :aria-label="T.profileProblemProgress"
        >
          <!-- Background circle -->
          <circle
            class="circle-bg"
            cx="60"
            cy="60"
            r="50"
            fill="none"
            stroke="#3a3a3a"
            stroke-width="10"
          />
          <!-- Easy segment -->
          <circle
            class="circle-segment easy"
            cx="60"
            cy="60"
            r="50"
            fill="none"
            stroke="#00b8a3"
            stroke-width="10"
            :stroke-dasharray="easyDash"
            :stroke-dashoffset="easyOffset"
            transform="rotate(-90 60 60)"
          />
          <!-- Medium segment -->
          <circle
            class="circle-segment medium"
            cx="60"
            cy="60"
            r="50"
            fill="none"
            stroke="#ffc01e"
            stroke-width="10"
            :stroke-dasharray="mediumDash"
            :stroke-dashoffset="mediumOffset"
            transform="rotate(-90 60 60)"
          />
          <!-- Hard segment -->
          <circle
            class="circle-segment hard"
            cx="60"
            cy="60"
            r="50"
            fill="none"
            stroke="#ef4743"
            stroke-width="10"
            :stroke-dasharray="hardDash"
            :stroke-dashoffset="hardOffset"
            transform="rotate(-90 60 60)"
          />
          <!-- Unlabelled segment -->
          <circle
            class="circle-segment unlabelled"
            cx="60"
            cy="60"
            r="50"
            fill="none"
            stroke="#808080"
            stroke-width="10"
            :stroke-dasharray="unlabelledDash"
            :stroke-dashoffset="unlabelledOffset"
            transform="rotate(-90 60 60)"
          />
        </svg>
        <div class="center-text">
          <span class="solved-count">{{ solved }}</span>
          <span class="solved-label">
            <span class="checkmark">✓</span> {{ T.profileSolved }}
          </span>
          <span v-if="attempting > 0" class="attempting-label">
            {{ attempting }} {{ T.profileAttempting }}
          </span>
        </div>
      </div>

      <!-- Difficulty Breakdown Cards -->
      <div class="difficulty-cards">
        <div class="difficulty-card easy">
          <span class="difficulty-label">{{ T.profileEasy }}</span>
          <span class="difficulty-count">{{ difficulty.easy }}</span>
        </div>
        <div class="difficulty-card medium">
          <span class="difficulty-label">{{ T.profileMedium }}</span>
          <span class="difficulty-count">{{ difficulty.medium }}</span>
        </div>
        <div class="difficulty-card hard">
          <span class="difficulty-label">{{ T.profileHard }}</span>
          <span class="difficulty-count">{{ difficulty.hard }}</span>
        </div>
        <div class="difficulty-card unlabelled">
          <span class="difficulty-label">{{ T.profileUnlabelled }}</span>
          <span class="difficulty-count">{{ difficulty.unlabelled }}</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Component, Prop, Vue } from 'vue-property-decorator';
import T from '../../lang';

interface DifficultyStats {
  easy: number;
  medium: number;
  hard: number;
  unlabelled: number;
}

@Component
export default class ProblemSolvingProgress extends Vue {
  @Prop({ required: true }) solved!: number;
  @Prop({ required: true }) attempting!: number;
  @Prop({ required: true }) difficulty!: DifficultyStats;

  T = T;

  private readonly circumference = 2 * Math.PI * 50; // r=50

  get totalSolved(): number {
    return (
      this.difficulty.easy +
      this.difficulty.medium +
      this.difficulty.hard +
      this.difficulty.unlabelled
    );
  }

  get easyDash(): string {
    const percent =
      this.totalSolved > 0 ? this.difficulty.easy / this.totalSolved : 0;
    return `${percent * this.circumference} ${this.circumference}`;
  }

  get easyOffset(): number {
    return 0;
  }

  get mediumDash(): string {
    const percent =
      this.totalSolved > 0 ? this.difficulty.medium / this.totalSolved : 0;
    return `${percent * this.circumference} ${this.circumference}`;
  }

  get mediumOffset(): number {
    const easyPercent =
      this.totalSolved > 0 ? this.difficulty.easy / this.totalSolved : 0;
    return -easyPercent * this.circumference;
  }

  get hardDash(): string {
    const percent =
      this.totalSolved > 0 ? this.difficulty.hard / this.totalSolved : 0;
    return `${percent * this.circumference} ${this.circumference}`;
  }

  get hardOffset(): number {
    const easyPercent =
      this.totalSolved > 0 ? this.difficulty.easy / this.totalSolved : 0;
    const mediumPercent =
      this.totalSolved > 0 ? this.difficulty.medium / this.totalSolved : 0;
    return -(easyPercent + mediumPercent) * this.circumference;
  }

  get unlabelledDash(): string {
    const percent =
      this.totalSolved > 0 ? this.difficulty.unlabelled / this.totalSolved : 0;
    return `${percent * this.circumference} ${this.circumference}`;
  }

  get unlabelledOffset(): number {
    const easyPercent =
      this.totalSolved > 0 ? this.difficulty.easy / this.totalSolved : 0;
    const mediumPercent =
      this.totalSolved > 0 ? this.difficulty.medium / this.totalSolved : 0;
    const hardPercent =
      this.totalSolved > 0 ? this.difficulty.hard / this.totalSolved : 0;
    return -(easyPercent + mediumPercent + hardPercent) * this.circumference;
  }
}
</script>

<style lang="scss" scoped>
.problem-solving-progress {
  background-color: #1a1a2e;
  border-radius: 12px;
  padding: 20px;
  color: #fff;
}

.progress-container {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 20px;
  flex-wrap: wrap;
}

.circular-chart-container {
  position: relative;
  width: 180px;
  height: 180px;
}

.circular-chart {
  width: 100%;
  height: 100%;
}

.circle-segment {
  transition: stroke-dasharray 0.3s ease;
}

.center-text {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  text-align: center;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.solved-count {
  font-size: 2.5rem;
  font-weight: bold;
}

.solved-label {
  font-size: 0.9rem;
  color: #00b8a3;
  display: flex;
  align-items: center;
  gap: 4px;
}

.checkmark {
  color: #00b8a3;
}

.attempting-label {
  font-size: 0.75rem;
  color: #ffc01e;
  margin-top: 4px;
}

.difficulty-cards {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.difficulty-card {
  background-color: #2a2a3e;
  border-radius: 8px;
  padding: 12px 20px;
  display: flex;
  flex-direction: column;
  align-items: center;
  min-width: 100px;
}

.difficulty-label {
  font-size: 0.9rem;
  font-weight: 500;
}

.difficulty-count {
  font-size: 1.2rem;
  font-weight: bold;
}

.difficulty-card.easy .difficulty-label {
  color: #00b8a3;
}

.difficulty-card.medium .difficulty-label {
  color: #ffc01e;
}

.difficulty-card.hard .difficulty-label {
  color: #ef4743;
}

.difficulty-card.unlabelled .difficulty-label {
  color: #808080;
}

@media (max-width: 576px) {
  .progress-container {
    flex-direction: column;
    align-items: center;
  }

  .difficulty-cards {
    flex-direction: row;
    flex-wrap: wrap;
    justify-content: center;
  }

  .difficulty-card {
    min-width: 80px;
    padding: 10px 15px;
  }
}
</style>
