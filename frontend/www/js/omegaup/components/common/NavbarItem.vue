<template>
  <a
    :class="[
      'dropdown-item',
      { 'd-flex align-items-center help-dropdown-item': hasIcon },
    ]"
    :href="href"
    :target="target"
    :rel="rel"
  >
    <font-awesome-icon
      v-if="hasIcon"
      :icon="icon"
      class="help-item-icon flex-shrink-0"
      fixed-width
    />
    <span v-if="description">
      <span class="d-block">{{ title }}</span>
      <small class="text-muted">{{ description }}</small>
    </span>
    <template v-else>{{ title }}</template>
  </a>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';

@Component({
  components: {
    FontAwesomeIcon,
  },
})
export default class NavbarItem extends Vue {
  @Prop({ required: true }) title!: string;
  @Prop({ default: '' }) description!: string;
  @Prop({ default: null }) icon!: string | [string, string] | null;
  @Prop({ required: true }) href!: string;
  @Prop({ default: null }) target!: string | null;
  @Prop({ default: null }) rel!: string | null;

  get hasIcon(): boolean {
    return this.icon !== null;
  }
}
</script>

<style lang="scss" scoped>
.help-dropdown-item {
  white-space: normal;
  padding-top: 0.5rem;
  padding-bottom: 0.5rem;
}

.help-item-icon {
  box-sizing: content-box;
  width: 1.5rem;
  height: 1.5rem;
  padding: 0.55rem;
  margin-right: 0.85rem;
  background-color: var(--header-help-dropdown-icon-background-color);
  border-radius: 4px;
  color: var(--header-help-dropdown-icon-color);
  font-size: 1.25rem;
}
</style>
