<template>
  <div data-overlay-popup>
    <div class="close-container">
      <button type="button" class="close" @click="$emit('dismiss')">❌</button>
    </div>
    <slot></slot>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';

@Component
export default class OverlayPopup extends Vue {
  @Prop({ default: false }) showOverlay!: boolean;

  isOverlayShown = this.showOverlay;

  @Watch('showOverlay')
  overlayVisibilityChanged(newValue: boolean): void {
    this.isOverlayShown = newValue;
  }
}
</script>

<style lang="scss" scoped>
@import '../../../sass/main.scss';
div[data-overlay-popup] {
  height: 100%;
}

form[data-run-submit] {
  background: #eee;
  width: 80%;
  height: 90%;
  margin: auto;
  border: 2px solid #ccc;
  padding: 1em;
  position: absolute;
  overflow-y: auto;
  overflow-x: hidden;
  top: 0;
  bottom: 0;
  left: 0;
  right: 0;
  display: flex;
  flex-direction: column;
  z-index: -1;
  .languages {
    width: 100%;
  }
  .filename-extension {
    width: 100%;
  }
  .run-submit-paste-text {
    width: 100%;
  }
  .code-view {
    width: 100%;
    flex-grow: 1;
    overflow: auto;
  }
  .upload-file {
    width: 100%;
  }
  .submit-run {
    width: 100%;
  }
}

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

input[type='submit'] {
  font-size: 110%;
  padding: 0.3em 0.5em;
}
</style>
