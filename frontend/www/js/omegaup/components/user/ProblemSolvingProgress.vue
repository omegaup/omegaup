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
          <!-- Background circle (light gray) -->
          <circle
            class="circle-bg"
            cx="60"
            cy="60"
            r="50"
            fill="none"
            stroke="#e8e8e8"
            stroke-width="14"
          />
          <!-- Easy segment (green) -->
          <circle
            class="circle-segment easy"
            cx="60"
            cy="60"
            r="50"
            fill="none"
            stroke="#00b8a3"
            stroke-width="14"
            :stroke-dasharray="easyDash"
            :stroke-dashoffset="0"
            transform="rotate(-90 60 60)"
          >
            <title>{{ T.profileEasy }}: {{ difficulty.easy }}/{{ total }}</title>
          </circle>
          <!-- Medium segment (yellow) -->
          <circle
            class="circle-segment medium"
            cx="60"
            cy="60"
            r="50"
            fill="none"
            stroke="#ffc01e"
            stroke-width="14"
            :stroke-dasharray="mediumDash"
            :stroke-dashoffset="mediumOffset"
            transform="rotate(-90 60 60)"
          >
            <title>{{ T.profileMedium }}: {{ difficulty.medium }}/{{ total }}</title>
          </circle>
          <!-- Hard segment (red) -->
          <circle
            class="circle-segment hard"
            cx="60"
            cy="60"
            r="50"
            fill="none"
            stroke="#ef4743"
            stroke-width="14"
            :stroke-dasharray="hardDash"
            :stroke-dashoffset="hardOffset"
            transform="rotate(-90 60 60)"
          >
            <title>{{ T.profileHard }}: {{ difficulty.hard }}/{{ total }}</title>
          </circle>
          <!-- Unlabelled segment (gray) -->
          <circle
            class="circle-segment unlabelled"
            cx="60"
            cy="60"
            r="50"
            fill="none"
            stroke="#999999"
            stroke-width="14"
            :stroke-dasharray="unlabelledDash"
            :stroke-dashoffset="unlabelledOffset"
            transform="rotate(-90 60 60)"
          >
            <title>{{ T.profileUnlabelled }}: {{ difficulty.unlabelled }}/{{ total }}</title>
          </circle>
        </svg>
        <div class="center-text">
          <div class="solved-count-container">
            <span class="solved-count">{{ solved }}</span>
            <span class="total-count">/{{ total }}</span>
          </div>
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

  get total(): number {
    return (
      this.difficulty.easy +
      this.difficulty.medium +
      this.difficulty.hard +
      this.difficulty.unlabelled
    );
  }

  get easyDash(): string {
    if (this.total === 0) return `0 ${this.circumference}`;
    const percent = this.difficulty.easy / this.total;
    return `${percent * this.circumference} ${this.circumference}`;
  }

  get mediumDash(): string {
    if (this.total === 0) return `0 ${this.circumference}`;
    const percent = this.difficulty.medium / this.total;
    return `${percent * this.circumference} ${this.circumference}`;
  }

  get mediumOffset(): number {
    if (this.total === 0) return 0;
    const easyPercent = this.difficulty.easy / this.total;
    return -easyPercent * this.circumference;
  }

  get hardDash(): string {
    if (this.total === 0) return `0 ${this.circumference}`;
    const percent = this.difficulty.hard / this.total;
    return `${percent * this.circumference} ${this.circumference}`;
  }

  get hardOffset(): number {
    if (this.total === 0) return 0;
    const prevPercent =
      (this.difficulty.easy + this.difficulty.medium) / this.total;
    return -prevPercent * this.circumference;
  }

  get unlabelledDash(): string {
    if (this.total === 0) return `0 ${this.circumference}`;
    const percent = this.difficulty.unlabelled / this.total;
    return `${percent * this.circumference} ${this.circumference}`;
  }

  get unlabelledOffset(): number {
    if (this.total === 0) return 0;
    const prevPercent =
      (this.difficulty.easy +
        this.difficulty.medium +
        this.difficulty.hard) /
      this.total;
    return -prevPercent * this.circumference;
  }
}
</script>

<style lang="scss" scoped>
.problem-solving-progress {
  background-color: #fff;
  border-radius: 16px;
  padding: 24px;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
}

.progress-container {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 24px;
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

.solved-count-container {
  display: flex;
  align-items: baseline;
}

.solved-count {
  font-size: 2.8rem;
  font-weight: 700;
  color: #333;
}

.total-count {
  font-size: 1.4rem;
  font-weight: 500;
  color: #999;
}

.solved-label {
  font-size: 0.95rem;
  color: #00b8a3;
  display: flex;
  align-items: center;
  gap: 4px;
  font-weight: 500;
}

.checkmark {
  color: #00b8a3;
}

.attempting-label {
  font-size: 0.8rem;
  color: #666;
  margin-top: 6px;
}

.difficulty-cards {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.difficulty-card {
  border-radius: 10px;
  padding: 14px 24px;
  display: flex;
  flex-direction: column;
  align-items: center;
  min-width: 110px;
}

.difficulty-label {
  font-size: 0.95rem;
  font-weight: 600;
}

.difficulty-count {
  font-size: 1.1rem;
  font-weight: 700;
  margin-top: 2px;
}

/* Easy - Mint/Cyan background */
.difficulty-card.easy {
  background-color: #c8f7e8;
}

.difficulty-card.easy .difficulty-label {
  color: #00a383;
}

.difficulty-card.easy .difficulty-count {
  color: #00a383;
}

/* Medium - Yellow/Gold background */
.difficulty-card.medium {
  background-color: #fff3c4;
}

.difficulty-card.medium .difficulty-label {
  color: #d4a005;
}

.difficulty-card.medium .difficulty-count {
  color: #d4a005;
}

/* Hard - Pink/Coral background */
.difficulty-card.hard {
  background-color: #ffd4d4;
}

.difficulty-card.hard .difficulty-label {
  color: #d45050;
}

.difficulty-card.hard .difficulty-count {
  color: #d45050;
}

/* Unlabelled - Gray background */
.difficulty-card.unlabelled {
  background-color: #e8e8e8;
}

.difficulty-card.unlabelled .difficulty-label {
  color: #666;
}

.difficulty-card.unlabelled .difficulty-count {
  color: #666;
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
