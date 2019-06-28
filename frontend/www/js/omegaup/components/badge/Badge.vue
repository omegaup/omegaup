<template>
  <figure class="badge-container"
          v-tooltip="description">
    <img class="badge-icon"
            v-bind:alt="`badge_${this.alias}`"
            v-bind:src="this.iconUrl">
    <figcaption class="badge-name">
      {{ this.name }}
    </figcaption>
  </figure>
</template>

<style>
.badge-container {
  cursor: pointer;
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
import * as VTooltip from 'v-tooltip';

@Component({
  directives: {
    tooltip: VTooltip.VTooltip,
  },
})
export default class Badge extends Vue {
  @Prop() alias!: string;
  @Prop() unlocked!: boolean;

  T = T;

  get name(): string {
    return this.T[`badge_${this.alias}_name`];
  }

  get description(): string {
    return this.T[`badge_${this.alias}_description`];
  }

  get iconUrl(): string {
    return this.unlocked
      ? `/media/dist/badges/${this.alias}.svg`
      : '/media/locked_badge.svg';
  }
}

</script>
