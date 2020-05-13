<template>
  <div class="base-timer">
    <span class="base-timer__label">
      {{ formattedTimeLeft }}
    </span>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import * as time from '../time';

@Component
export default class Countdown extends Vue {
  @Prop() timeLeft!: number;

  get formattedTimeLeft(): string {
    return time.formatDelta(this.timeLeft);
  }
}
</script>
