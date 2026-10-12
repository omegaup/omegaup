<template>
  <div
    class="view-unavailable d-flex align-items-center justify-content-center"
  >
    <div class="card text-center text-muted" role="alert">
      <div class="card-body">
        <font-awesome-icon :icon="icon" class="view-unavailable-icon mb-3" />
        <p class="h5 mb-2">{{ title }}</p>
        <p v-if="description" class="mb-0">{{ description }}</p>
        <slot></slot>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Component, Vue, Prop } from 'vue-property-decorator';
import T from '../../lang';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { fas } from '@fortawesome/free-solid-svg-icons';
import { library } from '@fortawesome/fontawesome-svg-core';

library.add(fas);

@Component({
  components: {
    'font-awesome-icon': FontAwesomeIcon,
  },
})
export default class ViewUnavailable extends Vue {
  @Prop({ default: () => T.viewUnavailableTitle }) title!: string;
  @Prop({ default: '' }) description!: string;
  @Prop({ default: 'ban' }) icon!: string;
}
</script>

<style lang="scss" scoped>
.view-unavailable {
  min-height: 70vh;
  padding: 2rem 1rem;

  .card {
    max-width: 32rem;
  }

  .view-unavailable-icon {
    font-size: 2.5rem;
    opacity: 0.5;
  }
}
</style>
