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
    default: omegaup.CountdownFormat.EVENT_COUNTDOWN,
  })
  countdownFormat!: omegaup.CountdownFormat;

  timerInterval = 0;
  currentTime = Date.now();

  get timeLeft(): number {
    return this.targetTime.getTime() - this.currentTime;
  }

  get formattedTimeLeft(): string {
    if (this.countdownFormat === omegaup.CountdownFormat.EVENT_COUNTDOWN) {
      return time.formatDelta(this.timeLeft);
    }
    if (
      this.countdownFormat ===
      omegaup.CountdownFormat.WAIT_BETWEEN_UPLOADS_SECONDS
    ) {
      return ui.formatString(T.arenaRunSubmitWaitBetweenUploads, {
        submissionGap: Math.ceil(this.timeLeft / 1000),
      });
    }
    return '';
  }

  @Watch('timeLeft')
  onValueChanged(newValue: number): void {
    if (newValue / 1000 > 0) return;
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
