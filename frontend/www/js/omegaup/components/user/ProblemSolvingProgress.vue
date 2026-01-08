<template>
  <div class="problem-solving-progress">
    <h5 class="chart-title">{{ T.profileProblemProgress }}</h5>
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
            @mouseenter="hoveredSegment = 'easy'"
            @mouseleave="hoveredSegment = null"
          >
            <title>
              {{ T.profileEasy }}: {{ difficulty.easy }}/{{ total }}
            </title>
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
            @mouseenter="hoveredSegment = 'medium'"
            @mouseleave="hoveredSegment = null"
          >
            <title>
              {{ T.profileMedium }}: {{ difficulty.medium }}/{{ total }}
            </title>
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
            @mouseenter="hoveredSegment = 'hard'"
            @mouseleave="hoveredSegment = null"
          >
            <title>
              {{ T.profileHard }}: {{ difficulty.hard }}/{{ total }}
            </title>
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
            @mouseenter="hoveredSegment = 'unlabelled'"
            @mouseleave="hoveredSegment = null"
          >
            <title>
              {{ T.profileUnlabelled }}: {{ difficulty.unlabelled }}/{{ total }}
            </title>
          </circle>
        </svg>
        <div class="center-text">
          <div class="solved-count-container">
            <span class="solved-count" :style="hoveredCountStyle">{{
              displayCount
            }}</span>
            <span class="total-count">/{{ total }}</span>
          </div>
          <span
            v-if="hoveredSegment"
            class="hover-label"
            :style="hoveredLabelStyle"
          >
            {{ hoveredLabel }}
          </span>
          <span v-if="!hoveredSegment && attempting > 0" class="attempting-label">
            {{ attempting }} {{ T.profileAttemptingProblems }}
          </span>
        </div>
      </div>

      <!-- Difficulty Breakdown -->
      <div class="difficulty-list">
        <div class="difficulty-item easy">
          <span class="difficulty-label">{{ T.profileEasy }}</span>
          <span class="difficulty-count"
            >{{ difficulty.easy }}/{{ total }}</span
          >
        </div>
        <div class="difficulty-item medium">
          <span class="difficulty-label">{{ T.profileMedium }}</span>
          <span class="difficulty-count"
            >{{ difficulty.medium }}/{{ total }}</span
          >
        </div>
        <div class="difficulty-item hard">
          <span class="difficulty-label">{{ T.profileHard }}</span>
          <span class="difficulty-count"
            >{{ difficulty.hard }}/{{ total }}</span
          >
        </div>
        <div class="difficulty-item unlabelled">
          <span class="difficulty-label">{{ T.profileUnlabelled }}</span>
          <span class="difficulty-count"
            >{{ difficulty.unlabelled }}/{{ total }}</span
          >
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
  @Prop({ required: true }) difficulty!: DifficultyStats;
  @Prop({ default: 0 }) attempting!: number;

  T = T;

  hoveredSegment: 'easy' | 'medium' | 'hard' | 'unlabelled' | null = null;

  private readonly circumference = 2 * Math.PI * 50; // r=50

  private readonly segmentColors: Record<string, string> = {
    easy: '#00b8a3',
    medium: '#ffc01e',
    hard: '#ef4743',
    unlabelled: '#999999',
  };

  get displayCount(): number {
    if (!this.hoveredSegment) return this.total;
    return this.difficulty[this.hoveredSegment];
  }

  get hoveredLabel(): string {
    if (!this.hoveredSegment) return '';
    const labels: Record<string, string> = {
      easy: this.T.profileEasy,
      medium: this.T.profileMedium,
      hard: this.T.profileHard,
      unlabelled: this.T.profileUnlabelled,
    };
    return labels[this.hoveredSegment];
  }

  get hoveredCountStyle(): Record<string, string> {
    if (!this.hoveredSegment) return {};
    return { color: this.segmentColors[this.hoveredSegment] };
  }

  get hoveredLabelStyle(): Record<string, string> {
    if (!this.hoveredSegment) return {};
    return { color: this.segmentColors[this.hoveredSegment] };
  }

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
      (this.difficulty.easy + this.difficulty.medium + this.difficulty.hard) /
      this.total;
    return -prevPercent * this.circumference;
  }
}
</script>

<style lang="scss" scoped>
.problem-solving-progress {
  background-color: #fff;
  border-radius: 12px;
  padding: 20px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
  min-height: 340px;
  height: 100%;
  width: 100%;
}

.chart-title {
  font-size: 1rem;
  font-weight: 600;
  color: #333;
  margin-bottom: 15px;
}

.progress-container {
  display: flex;
  align-items: center;
  justify-content: flex-start;
  gap: 24px;
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
  transition: stroke-dasharray 0.1s ease;
  cursor: pointer;
}

.circle-segment:hover {
  filter: brightness(1.1);
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
  transition: color 0.1s ease;
}

.total-count {
  font-size: 1.4rem;
  font-weight: 500;
  color: #999;
}

.attempting-label {
  font-size: 0.8rem;
  color: #666;
  margin-top: 6px;
}

.hover-label {
  font-size: 0.9rem;
  font-weight: 600;
  margin-top: 4px;
  transition: color 0.1s ease;
}

.difficulty-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.difficulty-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  background-color: #f7f7f7;
  border-radius: 8px;
  padding: 12px 20px;
  min-width: 100px;
}

.difficulty-label {
  font-size: 0.85rem;
  font-weight: 600;
}

.difficulty-count {
  font-size: 0.8rem;
  font-weight: 500;
  color: #666;
  margin-top: 2px;
}

/* Easy - Green text */
.difficulty-item.easy .difficulty-label {
  color: #00b8a3;
}

/* Medium - Orange/Yellow text */
.difficulty-item.medium .difficulty-label {
  color: #ffc01e;
}

/* Hard - Red text */
.difficulty-item.hard .difficulty-label {
  color: #ef4743;
}

/* Unlabelled - Gray text */
.difficulty-item.unlabelled .difficulty-label {
  color: #999999;
}

@media (max-width: 576px) {
  .progress-container {
    flex-direction: column;
    align-items: center;
  }

  .difficulty-list {
    flex-direction: row;
    flex-wrap: wrap;
    justify-content: center;
    gap: 12px;
  }

  .difficulty-item {
    min-width: 80px;
    padding: 10px 16px;
  }
}
</style>
