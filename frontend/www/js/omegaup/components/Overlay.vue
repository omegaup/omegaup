<template>
  <div>
    <slot name="link-title"></slot>
    <div class="overlay" v-if="showOverlay"></div>
  </div>
</template>

<style lang="scss" scoped>
.overlay {
  position: fixed;
  width: 100%;
  height: 100%;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.3);
  z-index: 9999998;
}
</style>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';

@Component
export default class Overlay extends Vue {
  @Prop({ default: false }) showOverlay!: boolean;
}
</script>
