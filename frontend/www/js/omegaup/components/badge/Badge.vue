<template>
  <figure class="badge-container"
          v-tooltip="description">
    <a class="badge-icon"
            v-bind:href="`/badge/${this.badge.badge_alias}`"><img v-bind:src="this.iconUrl"></a>
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
  display: block;
  height: 70%;
}
.badge-icon img {
  max-height: 100%;
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
