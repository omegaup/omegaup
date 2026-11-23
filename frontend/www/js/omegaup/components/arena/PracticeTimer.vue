<template>
  <aside
    data-practice-timer
    class="practice-timer"
    role="group"
    :aria-label="T.arenaPracticeTimerTitle"
    :style="positionStyle"
  >
    <button
      class="practice-timer__drag-handle"
      type="button"
      :aria-label="T.arenaPracticeTimerDrag"
      :title="T.arenaPracticeTimerDrag"
      @pointerdown.prevent="startDragging"
      @keydown="moveWithKeyboard"
    >
      <span aria-hidden="true">⠿</span>
    </button>

    <span class="practice-timer__title">
      {{ T.arenaPracticeTimerTitle }}
    </span>

    <span class="practice-timer__display" role="timer" aria-live="off">
      {{ formattedElapsed }}
    </span>

    <div class="practice-timer__controls">
      <button
        class="practice-timer__toggle"
        type="button"
        :aria-label="
          isPaused ? T.arenaPracticeTimerResume : T.arenaPracticeTimerPause
        "
        :title="
          isPaused ? T.arenaPracticeTimerResume : T.arenaPracticeTimerPause
        "
        @click="togglePause"
      >
        <font-awesome-icon
          :icon="isPaused ? 'play' : 'pause'"
          aria-hidden="true"
        />
      </button>

      <button
        class="practice-timer__reset"
        type="button"
        :aria-label="T.arenaPracticeTimerReset"
        :title="T.arenaPracticeTimerReset"
        @click="resetTimer"
      >
        <font-awesome-icon icon="redo-alt" aria-hidden="true" />
      </button>
    </div>
  </aside>
</template>

<script lang="ts">
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';
import { library } from '@fortawesome/fontawesome-svg-core';
import { fas } from '@fortawesome/free-solid-svg-icons';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import T from '../../lang';

library.add(fas);

interface TimerPosition {
  left: number;
  top: number;
}

interface StoredTimerState {
  elapsedMs: number;
  isPaused: boolean;
}

@Component({
  components: {
    'font-awesome-icon': FontAwesomeIcon,
  },
})
export default class ArenaPracticeTimer extends Vue {
  @Prop({ required: true }) contestAlias!: string;
  @Prop({ required: true }) problemAlias!: string;
  @Prop({ default: true }) isActive!: boolean;

  T = T;

  elapsedMs = 0;
  isPaused = false;
  startedAt: number | null = null;
  intervalId: number | null = null;

  position: TimerPosition | null = null;

  private dragOffsetX = 0;
  private dragOffsetY = 0;
  private isDragging = false;

  private get timerStorageKey(): string {
    return `arena:practice-timer:${encodeURIComponent(
      this.contestAlias,
    )}:${encodeURIComponent(this.problemAlias)}`;
  }

  private get positionStorageKey(): string {
    return `arena:practice-timer-position:${encodeURIComponent(
      this.contestAlias,
    )}`;
  }

  get formattedElapsed(): string {
    const totalSeconds = Math.floor(this.elapsedMs / 1000);

    const hours = Math.floor(totalSeconds / 3600);
    const minutes = Math.floor((totalSeconds % 3600) / 60);
    const seconds = totalSeconds % 60;

    const pad = (value: number): string =>
      value < 10 ? `0${value}` : `${value}`;

    return `${pad(hours)}:${pad(minutes)}:${pad(seconds)}`;
  }

  get positionStyle(): { [key: string]: string } {
    if (this.position === null) {
      return {};
    }

    return {
      left: `${this.position.left}px`,
      top: `${this.position.top}px`,
      right: 'auto',
    };
  }

  mounted(): void {
    this.loadTimerState();
    this.loadPosition();

    if (!this.isPaused && this.isActive) {
      this.startedAt = Date.now();
    }

    this.intervalId = window.setInterval(this.updateElapsed, 1000);

    window.addEventListener('resize', this.clampPosition);
    this.$nextTick(this.clampPosition);
  }

  @Watch('isActive')
  onActiveChanged(isActive: boolean): void {
    if (isActive) {
      if (!this.isPaused && this.startedAt === null) {
        this.startedAt = Date.now();
      }
      return;
    }

    this.updateElapsed();
    this.startedAt = null;
  }

  beforeDestroy(): void {
    this.updateElapsed();

    if (this.intervalId !== null) {
      window.clearInterval(this.intervalId);
      this.intervalId = null;
    }

    this.startedAt = null;
    this.stopDragging();

    window.removeEventListener('resize', this.clampPosition);
  }

  private loadTimerState(): void {
    try {
      const saved = window.localStorage.getItem(this.timerStorageKey);

      if (!saved) {
        return;
      }

      const state = JSON.parse(saved) as Partial<StoredTimerState>;

      if (
        typeof state.elapsedMs === 'number' &&
        Number.isFinite(state.elapsedMs) &&
        state.elapsedMs >= 0
      ) {
        this.elapsedMs = state.elapsedMs;
      }

      if (typeof state.isPaused === 'boolean') {
        this.isPaused = state.isPaused;
      }
    } catch {
      // Storage is optional; keep the timer functional when unavailable.
    }
  }

  private persistTimerState(): void {
    try {
      const state: StoredTimerState = {
        elapsedMs: this.elapsedMs,
        isPaused: this.isPaused,
      };

      window.localStorage.setItem(this.timerStorageKey, JSON.stringify(state));
    } catch {
      // Storage is optional; keep the timer functional when unavailable.
    }
  }

  private loadPosition(): void {
    try {
      const saved = window.localStorage.getItem(this.positionStorageKey);

      if (!saved) {
        return;
      }

      const position = JSON.parse(saved) as Partial<TimerPosition>;

      if (
        typeof position.left === 'number' &&
        Number.isFinite(position.left) &&
        typeof position.top === 'number' &&
        Number.isFinite(position.top)
      ) {
        this.position = {
          left: position.left,
          top: position.top,
        };
      }
    } catch {
      // Storage is optional; keep the timer functional when unavailable.
    }
  }

  private persistPosition(): void {
    if (this.position === null) {
      return;
    }

    try {
      window.localStorage.setItem(
        this.positionStorageKey,
        JSON.stringify(this.position),
      );
    } catch {
      // Storage is optional; keep the timer functional when unavailable.
    }
  }

  updateElapsed(): void {
    if (this.startedAt === null) {
      return;
    }

    const now = Date.now();

    this.elapsedMs += Math.max(0, now - this.startedAt);
    this.startedAt = now;

    this.persistTimerState();
  }

  togglePause(): void {
    if (this.isPaused) {
      this.isPaused = false;

      if (this.isActive) {
        this.startedAt = Date.now();
      }
    } else {
      this.updateElapsed();
      this.isPaused = true;
      this.startedAt = null;
    }

    this.persistTimerState();
  }

  resetTimer(): void {
    this.elapsedMs = 0;

    this.startedAt = !this.isPaused && this.isActive ? Date.now() : null;

    this.persistTimerState();
  }

  startDragging(event: PointerEvent): void {
    const rect = this.$el.getBoundingClientRect();

    if (this.position === null) {
      this.position = {
        left: rect.left,
        top: rect.top,
      };
    }

    this.dragOffsetX = event.clientX - rect.left;
    this.dragOffsetY = event.clientY - rect.top;

    this.isDragging = true;

    window.addEventListener('pointermove', this.onPointerMove);
    window.addEventListener('pointerup', this.stopDragging);
    window.addEventListener('pointercancel', this.stopDragging);
  }

  private onPointerMove(event: PointerEvent): void {
    if (!this.isDragging) {
      return;
    }

    const rect = this.$el.getBoundingClientRect();

    this.position = {
      left: this.clamp(
        event.clientX - this.dragOffsetX,
        0,
        window.innerWidth - rect.width,
      ),
      top: this.clamp(
        event.clientY - this.dragOffsetY,
        0,
        window.innerHeight - rect.height,
      ),
    };
  }

  stopDragging(): void {
    if (!this.isDragging) {
      return;
    }

    this.isDragging = false;

    window.removeEventListener('pointermove', this.onPointerMove);
    window.removeEventListener('pointerup', this.stopDragging);
    window.removeEventListener('pointercancel', this.stopDragging);

    this.persistPosition();
  }

  moveWithKeyboard(event: KeyboardEvent): void {
    const movements: { [key: string]: [number, number] } = {
      ArrowUp: [0, -10],
      ArrowDown: [0, 10],
      ArrowLeft: [-10, 0],
      ArrowRight: [10, 0],
    };

    const movement = movements[event.key];

    if (!movement) {
      return;
    }

    event.preventDefault();

    if (this.position === null) {
      const rect = this.$el.getBoundingClientRect();

      this.position = {
        left: rect.left,
        top: rect.top,
      };
    }

    const rect = this.$el.getBoundingClientRect();

    this.position = {
      left: this.clamp(
        this.position.left + movement[0],
        0,
        window.innerWidth - rect.width,
      ),
      top: this.clamp(
        this.position.top + movement[1],
        0,
        window.innerHeight - rect.height,
      ),
    };

    this.persistPosition();
  }

  clampPosition(): void {
    if (this.position === null) {
      return;
    }

    const rect = this.$el.getBoundingClientRect();

    this.position = {
      left: this.clamp(this.position.left, 0, window.innerWidth - rect.width),
      top: this.clamp(this.position.top, 0, window.innerHeight - rect.height),
    };

    this.persistPosition();
  }

  private clamp(value: number, min: number, max: number): number {
    return Math.min(Math.max(value, min), Math.max(min, max));
  }
}
</script>

<style lang="scss" scoped>
@import '../../../../sass/main.scss';

.practice-timer {
  position: fixed;
  z-index: 1030;
  top: 5rem;
  right: 1rem;

  display: flex;
  align-items: center;
  gap: 0.6rem;

  padding: 0.65rem 0.85rem;

  background-color: var(--arena-background-color);
  color: inherit;
  border: 1px solid var(--arena-contest-navleft-main-border-color);
  border-radius: 0.35rem;
  box-shadow: 0 0.15rem 0.5rem rgba(0, 0, 0, 0.15);
}

.practice-timer button {
  border: 0;
  background: transparent;
  color: inherit;
  padding: 0.2rem 0.35rem;
}

.practice-timer__controls {
  display: flex;
  align-items: center;
  gap: 0.35rem;
}

.practice-timer__toggle,
.practice-timer__reset {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 2rem;
  height: 2rem;
  cursor: pointer;
  font-size: 1rem;
  border-radius: 0.25rem;
}

.practice-timer__toggle:hover,
.practice-timer__reset:hover {
  opacity: 0.7;
}

.practice-timer__drag-handle {
  cursor: move;
  touch-action: none;
  font-size: 1.2rem;
}

.practice-timer__display {
  min-width: 5.5rem;
  text-align: center;
  font-family: monospace;
  font-size: 1.1rem;
  font-weight: 600;
  font-variant-numeric: tabular-nums;
}

.practice-timer__toggle {
  cursor: pointer;
}

.practice-timer button:focus {
  outline: 2px solid var(--arena-contest-navleft-main-border-color);
  outline-offset: 2px;
}

.practice-timer {
  box-sizing: border-box;
  width: max-content;
  max-width: calc(100vw - 2rem);
  flex-wrap: nowrap;
  white-space: nowrap;
}

.practice-timer__drag-handle,
.practice-timer__title,
.practice-timer__display,
.practice-timer__controls {
  flex: 0 0 auto;
  white-space: nowrap;
}

.practice-timer__title {
  min-width: max-content;
}

@media only screen and (max-width: 360px) {
  .practice-timer {
    gap: 0.35rem;
    padding: 0.45rem 0.55rem;
  }

  .practice-timer__title {
    display: none;
  }

  .practice-timer__display {
    min-width: 4.5rem;
    font-size: 1rem;
  }
}
</style>
