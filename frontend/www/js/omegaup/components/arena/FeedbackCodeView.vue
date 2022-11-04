<template>
  <div>
    <div class="container-fluid" data-feedback-code-mirror>
      <textarea v-show="false" ref="cm-editor" v-model="value"></textarea>
    </div>
    <div class="container-fluid text-right py-2">
      <button
        class="btn btn-primary mx-2"
        :disabled="!numberOfComments"
        @click.prevent="saveFeedbackList"
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

const FeedbackClass = Vue.extend(Feedback);

for (const mode of modeList) {
  require(`codemirror/mode/${mode}/${mode}.js`);
}

@Component({
  components: {},
})
export default class FeedbackCodeView extends Vue {
  @Prop() language!: string;
  @Prop() value!: string;
  @Prop({ default: () => new Map<number, FeedbackClass>() }) feedbackMap!: Map<
    number,
    ArenaCourseFeedback
  >;
  @Ref('cm-editor') private readonly cmEditor!: HTMLTextAreaElement;

  T = T;
  mode = languageModeMap[this.language] ?? languageModeMap['cpp17-gcc'];
  numberOfComments = 0;

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
      (codeMirror: CodeMirror.Editor, lineNumber: number) => {
        codeMirror.addLineWidget(
          lineNumber,
          showFeedbackForm(lineNumber, this),
        );

        this.feedbackMap.set(lineNumber, {
          line: lineNumber,
          text: null,
          status: FeedbackStatus.New,
        });
        this.numberOfComments = this.feedbackMap.size;
      },
    );

    const showFeedbackForm = (
      lineNumber: number,
      lineWidget: any,
    ): HTMLDivElement => {
      const marker = document.createElement('div');
      marker.classList.add('px-2');

      const feedback: ArenaCourseFeedback = {
        text: null,
        line: lineNumber,
        status: FeedbackStatus.New,
      };
      const feedbackForm = new FeedbackClass({
        propsData: {
          feedback,
        },
      });
      this.feedbackMap.set(lineNumber, feedbackForm);

      feedbackForm.$mount();

      feedbackForm.$on('submit', (feedback: ArenaCourseFeedback) => {
        this.feedbackMap.set(feedback.line, {
          line: feedback.line,
          text: feedback.text,
          status: FeedbackStatus.InProgress,
        });
      });

      feedbackForm.$on('cancel', (feedback: ArenaCourseFeedback) => {
        this.feedbackMap.delete(feedback.line);
        editor.removeLineWidget(lineWidget);
        marker.removeChild(feedbackForm.$el);
        feedbackForm.$destroy();
      });

      marker.appendChild(feedbackForm.$el);
      return marker;
    };
  }

  saveFeedbackList(): void {
    this.$emit('save-feedback-list', this.feedbackMap);
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
