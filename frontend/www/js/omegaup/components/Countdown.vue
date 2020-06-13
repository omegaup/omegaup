<template>
  <span>
    {{ formattedTimeLeft }}
  </span>
</template>

<script lang="ts">
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';
import * as time from '../time';
import * as ui from '../ui';
import T from '../lang';

@Component
export default class Countdown extends Vue {
  @Prop() targetTime!: Date;
  @Prop({ default: false }) countdownToNextSubmission!: boolean;

  timerInterval = 0;
  currentTime = Date.now();

  get timeLeft(): number {
    if (!this.countdownToNextSubmission) {
      return this.targetTime.getTime() - this.currentTime;
    }
    return Math.ceil((this.targetTime.getTime() - this.currentTime) / 1000);
  }

  get formattedTimeLeft(): string {
    if (!this.countdownToNextSubmission) {
      return time.formatDelta(this.timeLeft);
    }
    return ui.formatString(T.arenaRunSubmitWaitBetweenUploads, {
      submissionGap: this.timeLeft,
    });
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
