<template>
  <a
    :class="[
      'dropdown-item',
      { 'd-flex align-items-center help-dropdown-item': hasIcon },
    ]"
    :href="href"
    :target="target"
    :rel="relValue"
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
    <font-awesome-icon
      v-if="isExternal"
      :icon="['fas', 'external-link-alt']"
      :class="[
        'external-link-icon',
        'text-muted',
        hasIcon ? 'ml-auto flex-shrink-0' : 'ml-1',
      ]"
    />
  </a>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { library } from '@fortawesome/fontawesome-svg-core';
import { faExternalLinkAlt } from '@fortawesome/free-solid-svg-icons';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';

library.add(faExternalLinkAlt);

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

  get isExternal(): boolean {
    try {
      return (
        new URL(this.href, window.location.origin).host !== window.location.host
      );
    } catch {
      return false;
    }
  }

  get relValue(): string | null {
    if (this.isExternal) {
      return this.rel ?? 'noopener noreferrer';
    }
    return this.rel;
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

.external-link-icon {
  font-size: 0.75rem;
}
</style>
