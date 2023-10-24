<template>
  <figure v-tooltip="description" class="col-6 col-sm-3 badge-container">
    <a class="badge-icon" :href="`/badge/${badge.badge_alias}/`"
      ><img
        :class="{ 'badge-gray': !badge.unlocked }"
        :src="iconUrl"
        class="img-fluid"
    /></a>

    <figcaption class="badge-name">
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

<style>
.badge-container {
  align-items: center;
  text-align: center;
}

img {
  max-height: 10rem !important;
}

.badge-icon {
  display: block;
  width: 100%;
}

.badge-icon img {
  max-height: 100%;
}

.badge-name {
  padding-top: 0.5rem;
}

.badge-gray {
  filter: grayscale(100%);
}
</style>
