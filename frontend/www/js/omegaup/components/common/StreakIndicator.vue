<template>
  <span
    class="streak-indicator d-inline-flex align-items-center px-2 py-1"
    :title="tooltip"
    data-streak-indicator
  >
    <span class="streak-emoji mr-1">{{ emoji }}</span>
    <span class="streak-count">{{ streak }}</span>
  </span>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import T from '../../lang';
import * as ui from '../../ui';

export function getStreakEmoji(streak: number): string {
  if (streak <= 0) {
    return '❄️';
  }
  if (streak <= 6) {
    return '🌱';
  }
  if (streak <= 14) {
    return '✨';
  }
  if (streak <= 29) {
    return '⚡';
  }
  return '🔥';
}

@Component
export default class StreakIndicator extends Vue {
  @Prop({ default: 0 }) streak!: number;

  get emoji(): string {
    return getStreakEmoji(this.streak);
  }

  get tooltip(): string {
    return ui.formatString(T.streakIndicatorTooltip, {
      count: this.streak,
    });
  }
}
</script>

<style lang="scss" scoped>
@import '../../../../sass/main.scss';

.streak-indicator {
  user-select: none;
  font-weight: bold;
}
</style>
