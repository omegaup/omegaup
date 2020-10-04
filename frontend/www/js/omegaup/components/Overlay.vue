<template>
  <div v-if="isOverlayShown" data-overlay @click="hideOverlay">
    <slot name="popup-content"></slot>
  </div>
</template>

<style lang="scss">
@import '../../../sass/main.scss';

[data-overlay] {
  display: block !important;
  position: fixed;
  top: 0;
  bottom: 0;
  left: 0;
  right: 0;
  background: rgba(0, 0, 0, 0.5);
  z-index: 9999998 !important;
}
</style>

<script lang="ts">
import { Vue, Component, Prop, Emit, Watch } from 'vue-property-decorator';

@Component
export default class Overlay extends Vue {
  @Prop({ default: false }) showOverlay!: boolean;

  isOverlayShown = this.showOverlay;

  @Emit('overlay-hidden')
  hideOverlay() {
    this.isOverlayShown = false;
  }

  @Watch('showOverlay')
  overlayVisibilityChanged(newValue: boolean): void {
    this.isOverlayShown = newValue;
  }
}
</script>
