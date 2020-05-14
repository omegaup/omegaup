<template>
  <span>
    {{ formattedTimeLeft }}
  </span>
</template>

<script lang="ts">
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';
import * as time from '../time';

@Component
export default class Countdown extends Vue {
  @Prop() targetTime!: Date;

  timerInterval = 0;
  currentTime = Date.now();

  get timeLeft(): number {
    return this.targetTime.getTime() - this.currentTime;
  }

  get formattedTimeLeft(): string {
    return time.formatDelta(this.timeLeft);
  }

  @Watch('timeLeft')
  onValueChanged(newValue: number): void {
    if (newValue > 0) return;
    if (!this.timerInterval) return;
    clearInterval(this.timerInterval);
    this.timerInterval = 0;
    this.$emit('emit-finish');
  }

  mounted() {
    this.timerInterval = window.setInterval(
      () => (this.currentTime = Date.now()),
      1000,
    );
  }
}
</script>
