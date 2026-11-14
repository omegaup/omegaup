<template>
  <div class="text-center py-5">
    <font-awesome-icon
      v-if="icon"
      :icon="icon"
      size="3x"
      class="mb-3 text-muted"
    />
    <h4 v-if="title" class="mb-2">{{ title }}</h4>
    <p v-if="description" class="text-muted mb-4">{{ description }}</p>
    <slot name="action">
      <template v-if="buttonText">
        <a
          v-if="buttonLink"
          class="btn btn-primary btn-lg"
          :href="buttonLink"
          v-bind="buttonDataAttributes"
        >
          {{ buttonText }}
        </a>
        <button
          v-else
          class="btn btn-primary btn-lg"
          v-bind="buttonDataAttributes"
          @click="$emit('action')"
        >
          {{ buttonText }}
        </button>
      </template>
    </slot>
  </div>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { library } from '@fortawesome/fontawesome-svg-core';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import {
  faUsers,
  faGraduationCap,
  faClipboardList,
  faTerminal,
} from '@fortawesome/free-solid-svg-icons';

library.add(faUsers, faGraduationCap, faClipboardList, faTerminal);

export default defineComponent({
  name: 'EmptyState',
  components: {
    FontAwesomeIcon,
  },
  props: {
    icon: {
      type: [String, Array] as PropType<string | string[]>,
      default: '',
    },
    title: {
      type: String,
      default: '',
    },
    description: {
      type: String,
      default: '',
    },
    buttonText: {
      type: String,
      default: '',
    },
    buttonLink: {
      type: String,
      default: '',
    },
    buttonDataAttribute: {
      type: String,
      default: '',
    },
  },
  emits: ['action'],
  computed: {
    buttonDataAttributes(): { [key: string]: string } {
      if (!this.buttonDataAttribute) {
        return {};
      }
      const attr = this.buttonDataAttribute.startsWith('data-')
        ? this.buttonDataAttribute
        : `data-${this.buttonDataAttribute}`;
      return { [attr]: '' };
    },
  },
});
</script>

<style lang="scss" scoped>
@import '../../../../sass/main.scss';
</style>
