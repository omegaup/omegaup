<template>
  <div>
    <slot name="link-title"></slot>
    <div class="overlay" v-if="showOverlay" data-overlay>
      <slot name="popup-content"></slot>
    </div>
  </div>
</template>

<style lang="scss" scoped>
[data-overlay] {
  z-index: 9999998;
  display: block;
}
</style>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';

@Component
export default class Overlay extends Vue {
  @Prop({ default: false }) showOverlay!: boolean;
}
</script>
