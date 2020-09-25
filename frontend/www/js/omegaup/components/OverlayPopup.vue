<template>
  <div data-overlay-popup>
    <slot name="popup-content">
      <form>
        <slot name="popup-close-button">
          <div class="close-container">
            <button type="button" class="close" v-on:click="$emit('dismiss')">
              ❌
            </button>
          </div>
        </slot>
        <div class="justify-content-center">
          <slot name="popup-content">
            Some content
          </slot>
        </div>
        <div class="row mt-auto">
          <div class="col-3">
            <button type="submit" class="btn btn-primary w-100">
              <slot name="popup-submit-button">
                <span>{{ T.wordsSend }}</span>
              </slot>
            </button>
          </div>
          <div class="col-3">
            <button class="btn btn-secondary w-100">
              <slot name="popup-cancel-button">
                <span>{{ T.wordsCancel }}</span>
              </slot>
            </button>
          </div>
        </div>
      </form>
    </slot>
  </div>
</template>

<style lang="scss">
@import '../../../sass/main.scss';

[data-overlay-popup] {
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
  .close-container {
    width: 100%;
    .close {
      position: absolute;
      top: 0;
      right: 0;
      background-color: $omegaup-white;
      border: 1px solid #ccc;
      border-width: 0 0 1px 1px;
      font-size: 110%;
      width: 25px;
      height: 25px;
      &:hover {
        background-color: #eee;
      }
    }
  }
}
</style>

<script lang="ts">
import { Vue, Component, Prop, Emit, Watch } from 'vue-property-decorator';
import T from '../lang';

@Component
export default class OverlayPopup extends Vue {
  T = T;
}
</script>
