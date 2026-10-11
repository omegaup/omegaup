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
        <a v-if="buttonLink" class="btn btn-primary btn-lg" :href="buttonLink">
          {{ buttonText }}
        </a>
        <button v-else class="btn btn-primary btn-lg" @click="$emit('action')">
          {{ buttonText }}
        </button>
      </template>
    </slot>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { library } from '@fortawesome/fontawesome-svg-core';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import {
  faUsers,
  faGraduationCap,
  faClipboardList,
  faTerminal,
} from '@fortawesome/free-solid-svg-icons';

library.add(faUsers, faGraduationCap, faClipboardList, faTerminal);

@Component({
  components: {
    FontAwesomeIcon,
  },
})
export default class EmptyState extends Vue {
  @Prop() icon!: string | string[];
  @Prop() title!: string;
  @Prop() description!: string;
  @Prop({ default: '' }) buttonText!: string;
  @Prop({ default: '' }) buttonLink!: string;
}
</script>

<style lang="scss" scoped>
@import '../../../../sass/main.scss';
</style>
