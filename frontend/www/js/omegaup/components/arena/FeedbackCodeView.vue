<template>
  <div class="container-fluid">
    <textarea data-feedback-code-mirror></textarea>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';
import T from '../../lang';
import CodeMirror from 'codemirror';
import { EditorOptions, languageModeMap, modeList } from './CodeView.vue';

for (const mode of modeList) {
  require(`codemirror/mode/${mode}/${mode}.js`);
}

@Component({
  components: {},
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
      lineNumbers: true,
      mode: this.mode,
      readOnly: false,
      value: this.value,
      gutters: ['CodeMirror-linenumbers', 'breakpoints'],
    };
  }

  mounted() {
    const editor = CodeMirror.fromTextArea(
      document.querySelector(
        '[data-feedback-code-mirror]',
      ) as HTMLTextAreaElement,
      this.editorOptions,
    );
    editor.on('gutterClick', (codeMirror, numberOfLine) => {
      const info = codeMirror.lineInfo(numberOfLine);
      codeMirror.setGutterMarker(
        numberOfLine,
        'breakpoints',
        info.gutterMarkers ? null : makeMarker(numberOfLine),
      );
    });

    const makeMarker = (numberOfLine: number): HTMLDivElement => {
      this.onPressLine(numberOfLine);
      const marker = document.createElement('div');
      marker.style.color = '#822';
      marker.innerHTML = '●';
      return marker;
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
