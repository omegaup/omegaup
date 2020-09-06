<template>
  <div>
    <slot name="link-title"></slot>
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
  form {
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
  input[type='submit'] {
    font-size: 110%;
    padding: 0.3em 0.5em;
  }
}
.dropdown-cases {
  height: 100%;
  width: 100%;
  margin: 0 auto;
  text-align: center;
  background: rgb(245, 245, 245);
  border-radius: 5px;
}
.vue-codemirror-wrap {
  height: 95%;
  .CodeMirror {
    height: 100%;
  }
}
#run-details .compile_error {
  display: none;
}
.guid {
  font-family: monospace;
  padding: 0 0.3em;
}
#run-details .logs {
  margin-top: 1em;
  border-top: 1px dotted black;
  padding-top: 1em;
  display: none;
}
.cases {
  table {
    width: 100%;
    tr.group {
      border-top: 1px solid #ccc;
      td,
      th {
        padding: 0.2em inherit 0.2em inherit;
      }
    }
  }
  span.collapse {
    padding: 0.2em;
  }
  table {
    thead th,
    td.center,
    th.center {
      text-align: center;
    }
    td.score,
    th.score {
      text-align: right;
    }
    pre.stderr {
      color: #400;
    }
  }
}
</style>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';

@Component
export default class Overlay extends Vue {
  @Prop({ default: false }) showOverlay!: boolean;
}
</script>
