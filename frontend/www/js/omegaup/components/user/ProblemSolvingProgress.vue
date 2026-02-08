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
            stroke-width="14"
          />
          <!-- Easy segment (green) -->
          <circle
            class="circle-segment easy"
            cx="60"
            cy="60"
            r="50"
            fill="none"
            stroke-width="14"
            :stroke-dasharray="easyDash"
            :stroke-dashoffset="0"
            transform="rotate(-90 60 60)"
            @mouseenter="hoveredSegment = 'easy'"
            @mouseleave="hoveredSegment = null"
          >
            <title>
              {{ getProgressTitle('easy') }}
            </title>
          </circle>
          <!-- Medium segment (yellow) -->
          <circle
            class="circle-segment medium"
            cx="60"
            cy="60"
            r="50"
            fill="none"
            stroke-width="14"
            :stroke-dasharray="mediumDash"
            :stroke-dashoffset="mediumOffset"
            transform="rotate(-90 60 60)"
            @mouseenter="hoveredSegment = 'medium'"
            @mouseleave="hoveredSegment = null"
          >
            <title>
              {{ getProgressTitle('medium') }}
            </title>
          </circle>
          <!-- Hard segment (red) -->
          <circle
            class="circle-segment hard"
            cx="60"
            cy="60"
            r="50"
            fill="none"
            stroke-width="14"
            :stroke-dasharray="hardDash"
            :stroke-dashoffset="hardOffset"
            transform="rotate(-90 60 60)"
            @mouseenter="hoveredSegment = 'hard'"
            @mouseleave="hoveredSegment = null"
          >
            <title>
              {{ getProgressTitle('hard') }}
            </title>
          </circle>
          <!-- Unlabelled segment (gray) -->
          <circle
            class="circle-segment unlabelled"
            cx="60"
            cy="60"
            r="50"
            fill="none"
            stroke-width="14"
            :stroke-dasharray="unlabelledDash"
            :stroke-dashoffset="unlabelledOffset"
            transform="rotate(-90 60 60)"
            @mouseenter="hoveredSegment = 'unlabelled'"
            @mouseleave="hoveredSegment = null"
          >
            <title>
              {{ getProgressTitle('unlabelled') }}
            </title>
          </circle>
        </svg>
        <div class="center-text">
          <div class="solved-count-container">
            <span class="solved-count" :style="hoveredCountStyle">{{
              displayCount
            }}</span>
            <span v-if="hoveredSegment" class="total-count">/{{ total }}</span>
          </div>
          <span
            v-if="hoveredSegment"
            class="hover-label"
            :style="hoveredLabelStyle"
          >
            {{ hoveredLabel }}
          </span>
          <span
            v-if="!hoveredSegment && attempting > 0"
            class="attempting-label"
          >
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
import * as ui from '../../ui';

export interface DifficultyStats {
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
  ui = ui;

  hoveredSegment: 'easy' | 'medium' | 'hard' | 'unlabelled' | null = null;

  private readonly circumference = 2 * Math.PI * 50; // r=50

  private readonly segmentColors: Record<string, string> = {
    easy: 'var(--problem-progress-easy-color)',
    medium: 'var(--problem-progress-medium-color)',
    hard: 'var(--problem-progress-hard-color)',
    unlabelled: 'var(--problem-progress-unlabelled-color)',
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

  getProgressTitle(segment: 'easy' | 'medium' | 'hard' | 'unlabelled'): string {
    const labels: Record<string, string> = {
      easy: this.T.profileEasy,
      medium: this.T.profileMedium,
      hard: this.T.profileHard,
      unlabelled: this.T.profileUnlabelled,
    };
    return this.ui.formatString(this.T.profileDifficultyProgress, {
      difficulty: labels[segment],
      count: this.difficulty[segment].toString(),
      total: this.total.toString(),
    });
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
  background-color: var(--problem-progress-background-color);
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
  color: var(--problem-progress-text-primary-color);
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

.circle-bg {
  stroke: var(--problem-progress-border-light-color);
}

.circle-segment {
  transition: stroke-dasharray 0.1s ease;
  cursor: pointer;

  &.easy {
    stroke: var(--problem-progress-easy-color);
  }

  &.medium {
    stroke: var(--problem-progress-medium-color);
  }

  &.hard {
    stroke: var(--problem-progress-hard-color);
  }

  &.unlabelled {
    stroke: var(--problem-progress-unlabelled-color);
  }
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
  color: var(--problem-progress-text-primary-color);
  transition: color 0.1s ease;
}

.total-count {
  font-size: 1.4rem;
  font-weight: 500;
  color: var(--problem-progress-text-muted-color);
}

.attempting-label {
  font-size: 0.8rem;
  color: var(--problem-progress-text-secondary-color);
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
  background-color: var(--problem-progress-bg-light-color);
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
  color: var(--problem-progress-text-secondary-color);
  margin-top: 2px;
}

/* Easy - Green text */
.difficulty-item.easy .difficulty-label {
  color: var(--problem-progress-easy-color);
}

/* Medium - Orange/Yellow text */
.difficulty-item.medium .difficulty-label {
  color: var(--problem-progress-medium-color);
}

/* Hard - Red text */
.difficulty-item.hard .difficulty-label {
  color: var(--problem-progress-hard-color);
}

/* Unlabelled - Gray text */
.difficulty-item.unlabelled .difficulty-label {
  color: var(--problem-progress-unlabelled-color);
}

@media (max-width: 768px) {
  .progress-container {
    justify-content: space-around;
    gap: 16px;
  }

  .circular-chart-container {
    width: 150px;
    height: 150px;
  }

  .difficulty-item {
    min-width: 90px;
    padding: 10px 16px;
  }
}

@media (max-width: 576px) {
  .progress-container {
    flex-direction: column;
    align-items: center;
    justify-content: center;
  }

  .circular-chart-container {
    width: 160px;
    height: 160px;
  }

  .difficulty-list {
    flex-direction: row;
    flex-wrap: wrap;
    justify-content: center;
    gap: 10px;
    width: 100%;
  }

  .difficulty-item {
    flex: 1 1 calc(50% - 10px);
    min-width: 0;
    max-width: calc(50% - 5px);
    padding: 10px 12px;
  }
}
</style>
