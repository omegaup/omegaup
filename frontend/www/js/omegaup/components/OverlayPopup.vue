<template>
  <div data-overlay-popup>
    <div class="close-container">
      <button type="button" class="close" @click="$emit('dismiss')">❌</button>
    </div>
    <slot></slot>
  </div>
</template>

<script lang="ts">
import { Vue, Component } from 'vue-property-decorator';

@Component
export default class OverlayPopup extends Vue {}
</script>

<style lang="scss" scoped>
@import '../../../sass/main.scss';
div[data-overlay-popup] {
  height: 100%;
  .close-container {
    left: 100%;
    transform: translate(-10%, 6%);
    height: 88%;
    .close {
      background-color: transparent;
      border: none;
      font-size: 110%;
      &:hover {
        background-color: #eee;
      }
    }
  }
}
</style>
