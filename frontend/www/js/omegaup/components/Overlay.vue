<template>
  <div>
    <div class="overlay" v-if="showOverlay" data-overlay>
      <slot name="popup-content"></slot>
    </div>
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
import { Vue, Component, Prop } from 'vue-property-decorator';

@Component
export default class Overlay extends Vue {
  @Prop({ default: false }) showOverlay!: boolean;
}
</script>
