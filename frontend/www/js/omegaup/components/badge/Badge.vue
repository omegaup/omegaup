<template>
  <figure class="badge-container"
          v-tooltip="description">
    <img class="badge-icon"
            v-bind:src="this.iconUrl">
    <figcaption class="badge-name">
      {{ this.name }}
    </figcaption>
  </figure>
</template>

<style>
.badge-container {
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  text-align: center;
}
.badge-icon {
  max-width: 100%;
  height: 70%;
}
.badge-name {
  padding-top: 5px;
}
</style>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { T } from '../../omegaup.js';
import omegaup from '../../api.js';
import 'v-tooltip/dist/v-tooltip.css';
import { VTooltip } from 'v-tooltip';

@Component({
  directives: {
    tooltip: VTooltip,
  },
})
export default class Badge extends Vue {
  @Prop() badge!: omegaup.Badge;

  T = T;

  get name(): string {
    return this.T[`badge_${this.badge.badge_alias}_name`];
  }

  get description(): string {
    return this.T[`badge_${this.badge.badge_alias}_description`];
  }

  get iconUrl(): string {
    return this.badge.unlocked
      ? `/media/dist/badges/${this.badge.badge_alias}.svg`
      : '/media/locked_badge.svg';
  }
}

</script>
