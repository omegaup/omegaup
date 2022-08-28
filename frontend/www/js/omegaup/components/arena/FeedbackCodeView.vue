<template>
  <div data-feedback-code-mirror class="container-fluid">
    <div class="d-flex flex-nowrap">
      <div v-if="enableFeedback" class="gutter flex-nowrap">
        <div
          v-for="line in linesPerChunk"
          :key="line"
          class="line-number d-flex flex-nowrap"
          @mouseover="hover = line"
          @mouseleave="hover = null"
        >
          <div class="number">
            {{ line }}
          </div>
          <div class="add-button">
            <button
              class="btn btn-xs text-weight-bold btn-primary"
              :hidden="hover != line"
              @click.prevent="onPressLine(line)"
            >
              +
            </button>
          </div>
        </div>
      </div>
      <div class="code">
        <codemirror-editor
          :options="editorOptions"
          :value="value"
        ></codemirror-editor>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';
import T from '../../lang';
import { codemirror } from 'vue-codemirror-lite';
import { EditorOptions, languageModeMap, modeList } from './CodeView.vue';

for (const mode of modeList) {
  require(`codemirror/mode/${mode}/${mode}.js`);
}

@Component({
  components: {
    'codemirror-editor': codemirror,
  },
})
export default class FeedbackCodeView extends Vue {
  @Prop() language!: string;
  @Prop() value!: string;
  @Prop({ default: () => [] }) linesPerChunk!: number[];
  @Prop({ default: false }) enableFeedback!: boolean;

  T = T;
  mode = languageModeMap[this.language] || languageModeMap['cpp17-gcc'];
  hover: null | number = null;

  get editorOptions(): EditorOptions {
    return {
      tabSize: 2,
      lineNumbers: false,
      mode: this.mode,
      readOnly: true,
    };
  }

  onPressLine(number: number) {
    this.$emit('show-feedback-form', number);
  }

  @Watch('hover')
  onHoverChange(line: null | number) {
    if (!line) {
      return;
    }
  }
}
</script>

<style lang="scss">
@import '../../../../sass/main.scss';

[data-feedback-code-mirror] {
  height: auto;

  .vue-codemirror-wrap {
    height: 95%;

    .CodeMirror {
      height: auto;

      .CodeMirror-scroll {
        height: auto;
      }
    }
  }

  .gutter {
    width: 72px;
    background-color: var(--codemirror-gutter-background-color);
  }

  .code {
    width: 100%;
  }

  .line-number {
    min-width: 20px;
    text-align: right;
    color: var(--codemirror-line-number-font-color);
    white-space: nowrap;
    cursor: pointer;

    .number {
      width: 50px;
    }

    .add-button {
      width: 22px;
    }
  }

  .btn-xs {
    padding: 0.25rem 0.5rem;
    font-size: 0.575rem;
    line-height: 1.5;
    border-radius: 0.2rem;
  }
}
</style>
