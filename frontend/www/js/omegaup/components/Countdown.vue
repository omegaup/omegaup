<template>
  <span>
    {{ formattedTimeLeft }}
  </span>
</template>

<script lang="ts">
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';
import { omegaup } from '../omegaup';
import * as time from '../time';
import * as ui from '../ui';
import T from '../lang';

@Component
export default class Countdown extends Vue {
  @Prop() targetTime!: Date;
  @Prop({
    default: omegaup.CountdownFormat.EventCountdown,
  })
  countdownFormat!: omegaup.CountdownFormat;
  // For testing purposes, we can receive a mocked time
  @Prop({ default: () => Date.now() }) time!: number;

  timerInterval = 0;
  currentTime: number = this.time;

  get timeLeft(): number {
    return this.targetTimestamp - this.currentTime;
  }

  get targetTimestamp(): number {
    return this.targetTime.getTime();
  }

  get formattedTimeLeft(): string {
    switch (this.countdownFormat) {
      case omegaup.CountdownFormat.EventCountdown:
        return time.formatDelta(this.timeLeft);
      case omegaup.CountdownFormat.WaitBetweenUploadsSeconds:
        return ui.formatString(T.arenaRunSubmitWaitBetweenUploads, {
          submissionGap: Math.ceil(this.timeLeft / 1000),
        });
      default:
        return '';
    }
  }

  @Watch('timeLeft')
  onValueChanged(newValue: number): void {
    if (newValue > 0) return;
    if (!this.timerInterval) return;
    clearInterval(this.timerInterval);
    this.timerInterval = 0;
    this.$emit('finish');
  }

  getDateNow(): number {
    return Date.now();
  }

  mounted() {
    this.timerInterval = window.setInterval(
      () => (this.currentTime = this.getDateNow()),
      1000,
    );
  }
}
</script>
