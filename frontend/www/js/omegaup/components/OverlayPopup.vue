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
  border: 2px solid var(--overlay-popup-border-color);
  border-radius: 1.5rem;
  padding: 1em;
  position: absolute;
  overflow-y: auto;
  overflow-x: hidden;
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
    background-color: transparent;
    border: none;
    font-size: 110%;

    &:hover {
      background-color: var(--overlay-popup-close-background-color--hover);
    }
  }
}
</style>
