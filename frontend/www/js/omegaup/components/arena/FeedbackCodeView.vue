<template>
  <div class="container-fluid">
    <textarea
      v-show="false"
      v-model="value"
      data-feedback-code-mirror
    ></textarea>
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
    editor.on(
      'gutterClick',
      (codeMirror: CodeMirror.Editor, numberOfLine: number) => {
        const info = codeMirror.lineInfo(numberOfLine);
        codeMirror.setGutterMarker(
          numberOfLine,
          'breakpoints',
          info.gutterMarkers ? null : makeMarker(numberOfLine),
        );
        codeMirror.addLineWidget(numberOfLine, showFeedbackForm(numberOfLine));
      },
    );

    const makeMarker = (numberOfLine: number): HTMLDivElement => {
      this.onPressLine(numberOfLine);
      const marker = document.createElement('div');
      marker.style.color = '#822';
      marker.innerHTML = '●';
      return marker;
    };

    const showFeedbackForm = (numberOfLine: number): HTMLDivElement => {
      this.onPressLine(numberOfLine);
      const marker = document.createElement('div');
      marker.innerHTML = `
        <div class="card" ref="feedback">
          <div class="card-header">${T.runDetailsNewFeedback}</div>
          <div class="card-body">
            <textarea
              placeholder="${T.runDetailsFeedbackPlaceholder}"
              class="w-100"
            ></textarea>
          </div>
          <div class="card-footer text-muted">
            <div class="form-group my-2">
              <button
                data-button-submit
                class="btn btn-primary mx-2"
              >
                ${T.runDetailsFeedbackAddReview}
              </button>
              <button
                data-button-cancel
                class="btn btn-danger mx-2"
              >
                ${T.runDetailsFeedbackCancel}
              </button>
            </div>
          </div>
        </div>
      `;
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

    .CodeMirror-linenumber:hover::before {
      content: '+';
    }

    .CodeMirror {
      height: auto;

      .CodeMirror-scroll {
        height: auto;
      }
    }
  }
}
</style>
