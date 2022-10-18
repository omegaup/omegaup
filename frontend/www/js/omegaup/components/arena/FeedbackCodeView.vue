<template>
  <div class="container-fluid" data-feedback-code-mirror>
    <textarea v-show="false" ref="cm-editor" v-model="value"></textarea>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop, Ref } from 'vue-property-decorator';
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
  @Ref('cm-editor') private readonly cmEditor!: HTMLTextAreaElement;

  T = T;
  mode = languageModeMap[this.language] ?? languageModeMap['cpp17-gcc'];
  hover: null | number = null;

  get editorOptions(): EditorOptions {
    return {
      tabSize: 2,
      lineNumbers: true,
      mode: this.mode,
      readOnly: true,
      gutters: ['CodeMirror-linenumbers', 'breakpoints'],
    };
  }

  mounted() {
    const editor = CodeMirror.fromTextArea(this.cmEditor, this.editorOptions);

    editor.on(
      'gutterClick',
      (codeMirror: CodeMirror.Editor, numberOfLine: number) => {
        codeMirror.addLineWidget(numberOfLine, showFeedbackForm());
      },
    );

    const showFeedbackForm = (): HTMLDivElement => {
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
}
</script>

<style lang="scss">
@import '../../../../sass/main.scss';
@import 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.css';

[data-feedback-code-mirror] {
  height: auto;

  .cm-s-default {
    height: 95%;

    .CodeMirror-linenumbers {
      width: 39px !important;
    }

    .CodeMirror-linenumber:hover::after {
      font: var(--fa-font-solid);
      content: '\f0fe';
      display: inline-block;
      vertical-align: middle;
      font-weight: 900;
      cursor: pointer;
      color: var(--btn-ok-background-color);
      font-size: x-large;
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
