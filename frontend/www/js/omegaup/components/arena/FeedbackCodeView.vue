<template>
  <div>
    <div class="container-fluid" data-feedback-code-mirror>
      <textarea v-show="false" ref="cm-editor" v-model="value"></textarea>
    </div>
    <div class="container-fluid text-right py-2">
      <button
        class="btn btn-primary mx-2"
        :disabled="!feedbackList.length"
        @click="saveFeedbackList"
      >
        {{ T.submissionSendFeedback }}
      </button>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop, Ref } from 'vue-property-decorator';
import T from '../../lang';
import CodeMirror from 'codemirror';
import { EditorOptions, languageModeMap, modeList } from './CodeView.vue';
import Feedback, { ArenaCourseFeedback, FeedbackStatus } from './Feedback.vue';

for (const mode of modeList) {
  require(`codemirror/mode/${mode}/${mode}.js`);
}

@Component({
  components: {},
})
export default class FeedbackCodeView extends Vue {
  @Prop() language!: string;
  @Prop() value!: string;
  @Prop({ default: () => [] }) feedbackList!: ArenaCourseFeedback[];
  @Ref('cm-editor') private readonly cmEditor!: HTMLTextAreaElement;

  T = T;
  mode = languageModeMap[this.language] ?? languageModeMap['cpp17-gcc'];

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
        codeMirror.addLineWidget(numberOfLine, showFeedbackForm(numberOfLine));
      },
    );

    const showFeedbackForm = (numberOfLine: number): HTMLDivElement => {
      const marker = document.createElement('div');
      marker.classList.add('px-2');

      const feedback: ArenaCourseFeedback = {
        text: null,
        line: numberOfLine,
        status: FeedbackStatus.New,
      };
      const componentClass = Vue.extend(Feedback);
      const feedbackForm = new componentClass({
        propsData: {
          feedback,
        },
      });

      feedbackForm.$mount();

      feedbackForm.$on('submit', (feedback: ArenaCourseFeedback) =>
        this.storeFeedback(feedback),
      );

      feedbackForm.$on('cancel', (feedback: ArenaCourseFeedback) => {
        console.log(feedback);
      });

      marker.appendChild(feedbackForm.$el);
      return marker;
    };
  }

  storeFeedback(feedback: ArenaCourseFeedback): void {
    this.feedbackList.push(feedback);
  }

  saveFeedbackList(): void {
    this.$emit('save-feedback-list', this.feedbackList);
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

    .CodeMirror-sizer {
      min-height: 100% !important;
    }

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
