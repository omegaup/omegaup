<template>
  <span data-count-down-timer>
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

  timerInterval = 0;
  currentTime = Date.now();

  get timeLeft(): number {
    return this.targetTime.getTime() - this.currentTime;
  }

  get formattedTimeLeft(): string {
    switch (this.countdownFormat) {
      case omegaup.CountdownFormat.EventCountdown:
        if (this.timeLeft < 0) {
          return '00:00:00';
        }
        return time.formatDelta(this.timeLeft);
      case omegaup.CountdownFormat.WaitBetweenUploadsSeconds:
        return ui.formatString(T.arenaRunSubmitWaitBetweenUploads, {
          submissionGap: Math.ceil(this.timeLeft / 1000),
        });
      case omegaup.CountdownFormat.ContestHasNotStarted:
        if (this.timeLeft < 0) {
          return T.arenaContestHasAlreadyStarted;
        }
        return ui.formatString(T.contestWillBeginIn, {
          time: time.formatDelta(this.timeLeft),
        });
      case omegaup.CountdownFormat.AssignmentHasNotStarted:
        if (this.timeLeft < 0) {
          return T.arenaCourseAssignmentHasAlreadyStarted;
        }
        return ui.formatString(T.arenaCourseAssignmentWillBeginIn, {
          time: time.formatDelta(this.timeLeft),
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

  mounted() {
    this.timerInterval = window.setInterval(
      () => (this.currentTime = Date.now()),
      1000,
    );
  }
}
</script>
