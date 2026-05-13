<template>
  <div data-overlay-popup>
    <button type="button" class="close" @click="$emit('dismiss')">❌</button>
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
  background: var(--overlay-popup-background-color);
  width: 80%;
  height: -moz-max-content;
  height: fit-content;
  max-height: 90%;
  margin: auto;
  border: 1px solid var(--overlay-popup-border-color);
  padding: var(--overlay-popup-padding);
  position: absolute;
  overflow-y: auto;
  overflow-x: hidden;
  border-radius: var(--overlay-popup-radius);
  box-shadow: var(--overlay-popup-shadow);
  top: 0;
  bottom: 0;
  left: 0;
  right: 0;
  z-index: -1;

  button.close {
    position: sticky;
    position: -webkit-sticky;
    top: 0;
    z-index: 100;
    width: 32px;
    height: 32px;
    border-radius: 999px;
    background-color: transparent;
    border: none;
    color: var(--overlay-popup-close-color);
    font-size: 1rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: background-color 0.15s ease, transform 0.15s ease;

    &:hover {
      background-color: var(--overlay-popup-close-background-color--hover);
      transform: scale(1.05);
    }
  }
}
</style>
