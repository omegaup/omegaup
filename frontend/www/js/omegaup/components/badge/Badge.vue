<template>
  <figure
    v-tooltip="description"
    class="col-6 col-sm-3 badge-container text-center d-flex flex-column align-items-center"
  >
    <a class="badge-icon d-block w-100" :href="`/badge/${badge.badge_alias}/`">
      <badge-3d>
        <img
          :class="{ 'badge-gray': !badge.unlocked }"
          :src="iconUrl"
          class="img-fluid badge-img"
          :alt="name"
        />
      </badge-3d>
    </a>

    <figcaption class="badge-name pt-2">
      {{ name }}
    </figcaption>
  </figure>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import type { PropType } from 'vue';
import { types } from '../../api_types';
import T from '../../lang';
import 'v-tooltip/dist/v-tooltip.css';
import { VTooltip } from 'v-tooltip';
import Badge3D from './Badge3D.vue';

export default defineComponent({
  name: 'Badge',
  directives: {
    tooltip: VTooltip,
  },
  components: {
    'badge-3d': Badge3D,
  },
  props: {
    badge: {
      type: Object as PropType<types.Badge>,
      required: true,
    },
  },
  computed: {
    name(): string {
      return T[`badge_${this.badge.badge_alias}_name`];
    },
    description(): string {
      return T[`badge_${this.badge.badge_alias}_description`];
    },
    iconUrl(): string {
      return `/media/dist/badges/${this.badge.badge_alias}.svg`;
    },
  },
});
</script>

<style lang="scss" scoped>
.badge-gray {
  filter: grayscale(100%);
}

.badge-img {
  max-height: 10rem;
}

.badge-icon {
  position: relative;
  height: 10rem;
}
</style>
