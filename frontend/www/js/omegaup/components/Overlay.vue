<template>
  <div v-if="isOverlayShown" data-overlay @click="onOverlayClicked">
    <slot name="popup" :isOverlayShown="isOverlayShown"></slot>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';

export default defineComponent({
  name: 'Overlay',
  props: {
    showOverlay: {
      type: Boolean,
      default: false,
    },
  },
  data() {
    return {
      isOverlayShown: this.showOverlay,
    };
  },
  watch: {
    showOverlay(newValue: boolean): void {
      this.isOverlayShown = newValue;
    },
  },
  methods: {
    onOverlayClicked(evt: Event) {
      if (
        typeof $(evt.composedPath()[0]).attr('data-overlay') !== 'undefined'
      ) {
        this.isOverlayShown = false;
        this.$emit('hide-overlay');
      }
    },
  },
});
</script>

<style lang="scss" scoped>
@import '../../../sass/main.scss';

[data-overlay] {
  display: block !important;
  position: fixed;
  top: 0;
  bottom: 0;
  left: 0;
  right: 0;
  background: rgba(var(--overlay-background-color), 0.5);
  z-index: 9999998 !important;
}
</style>
