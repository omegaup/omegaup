<template>
  <figure
    v-tooltip="description"
    class="col-6 col-sm-3 badge-container text-center d-flex flex-column align-items-center"
  >
    <a class="badge-icon d-block w-100" :href="`/badge/${badge.badge_alias}/`"
      ><img
        :class="{ 'badge-gray': !badge.unlocked }"
        :src="iconUrl"
        class="img-fluid"
        style="max-height: 10rem"
        :style="{ filter: badge.unlocked ? '' : 'grayscale(100%)' }"
    /></a>

    <figcaption class="badge-name pt-2">
      {{ name }}
    </figcaption>
  </figure>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { types } from '../../api_types';
import T from '../../lang';
import 'v-tooltip/dist/v-tooltip.css';
import { VTooltip } from 'v-tooltip';

@Component({
  directives: {
    tooltip: VTooltip,
  },
})
export default class Badge extends Vue {
  @Prop() badge!: types.Badge;

  get name(): string {
    return T[`badge_${this.badge.badge_alias}_name`];
  }

  get description(): string {
    return T[`badge_${this.badge.badge_alias}_description`];
  }

  get iconUrl(): string {
    return `/media/dist/badges/${this.badge.badge_alias}.svg`;
  }
}
</script>
