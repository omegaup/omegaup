<template>
  <div>
    <span>
      {{ formattedTimeLeft }}
    </span>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';
import * as time from '../time';

@Component
export default class Countdown extends Vue {
  @Prop() startTime!: Date;

  timePassed = 0;
  timerInterval = 0;

  get timeLeft() {
    if (!this.startTime) {
      return 0;
    }
    const timeLimit = this.startTime.getTime() - Date.now();
    return timeLimit - this.timePassed;
  }

  get formattedTimeLeft(): string {
    return time.formatDelta(this.timeLeft);
  }

  @Watch('timeLeft')
  onValueChanged(newValue: number): void {
    if (newValue <= 0) {
      if (!this.timerInterval) return;
      clearInterval(this.timerInterval);
      this.timerInterval = 0;
      this.$emit('emit-finish');
    }
  }

  startTimer(): void {
    this.timerInterval = window.setInterval(() => (this.timePassed += 1), 1000);
  }

  mounted() {
    this.startTimer();
  }
}
</script>
