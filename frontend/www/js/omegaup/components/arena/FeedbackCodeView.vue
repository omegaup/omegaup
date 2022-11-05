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
  @Prop({ default: () => new Map<number, ArenaCourseFeedback>() })
  feedbackMap!: Map<number, ArenaCourseFeedback>;
  @Ref('cm-editor') private readonly cmEditor!: HTMLTextAreaElement;

  T = T;
  mode = languageModeMap[this.language] ?? languageModeMap['cpp17-gcc'];
  mapChangeTracker = 1;

  get mapAsList(): 0 | [number, ArenaCourseFeedback][] {
    if (!this.mapChangeTracker) {
      throw new Error('unreachable code');
    }
    return Array.from(this.feedbackMap);
  }

  get numberOfComments(): number {
    return this.mapAsList != 0 ? this.mapAsList.length : 0;
  }

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

        this.setFeedback({
          lineNumber,
          text: null,
          status: FeedbackStatus.New,
        });
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
        lineNumber,
        status: FeedbackStatus.New,
      };
      const feedbackForm = new FeedbackClass({
        propsData: {
          feedback,
        },
      });

      feedbackForm.$mount();

      feedbackForm.$on('submit', (feedback: ArenaCourseFeedback) => {
        this.setFeedback(feedback);
      });

      feedbackForm.$on('cancel', (feedback: ArenaCourseFeedback) => {
        this.deleteFeedback({ lineNumber: feedback.lineNumber });
        editor.removeLineWidget(lineWidget);
        marker.removeChild(feedbackForm.$el);
        feedbackForm.$destroy();
      });

      marker.appendChild(feedbackForm.$el);
      return marker;
    };
  }

  setFeedback({
    lineNumber,
    text = null,
    status,
  }: {
    lineNumber: number;
    text: string | null;
    status: FeedbackStatus;
  }): void {
    this.feedbackMap.set(lineNumber, {
      lineNumber,
      text,
      status,
    });
    this.mapChangeTracker++;
  }

  deleteFeedback({ lineNumber }: { lineNumber: number }): void {
    this.feedbackMap.delete(lineNumber);
    this.mapChangeTracker--;
  }

  saveFeedbackList(): void {
    this.$emit(
      'save-feedback-list',
      Array.from(this.feedbackMap, ([lineNumber, feedback]) => ({
        lineNumber,
        feedback: feedback.text,
      })),
    );
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
