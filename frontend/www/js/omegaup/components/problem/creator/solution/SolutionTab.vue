<template>
  <div class="card">
    <div class="card-body">
      <div class="row">
        <div class="col-md-6 d-flex flex-column">
          <div
            ref="markdownButtonBar"
            class="wmd-button-bar"
            data-solution-markdown-toolbar
          ></div>
          <textarea
            ref="markdownInput"
            v-model.lazy="currentSolutionMarkdown"
            data-problem-creator-solution-editor-markdown
            class="wmd-input"
          ></textarea>
        </div>
        <div class="col-md-6 d-flex flex-column">
          <omegaup-markdown
            data-problem-creator-solution-previewer-markdown
            :markdown="
              T.problemCreatorMarkdownPreviewInitialRender +
              currentSolutionMarkdown
            "
            preview="true"
          ></omegaup-markdown>
        </div>
      </div>
      <div class="row">
        <div class="col-md-12">
          <button
            data-problem-creator-solution-save-markdown
            class="btn btn-primary"
            type="submit"
            @click="updateMarkdown"
          >
            {{ T.problemCreatorMarkdownSave }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop, Ref, Watch } from 'vue-property-decorator';
import * as Markdown from '@/third_party/js/pagedown/Markdown.Editor.js';
import * as markdown from '../../../../markdown';
import * as ui from '../../../../ui';
import T from '../../../../lang';
import { TabIndex } from '../Tabs.vue';
import introJs from 'intro.js';
import 'intro.js/introjs.css';
import VueCookies from 'vue-cookies';
Vue.use(VueCookies, { expire: -1 });

import omegaup_problemMarkdown from '../../ProblemMarkdown.vue';

const markdownConverter = new markdown.Converter({
  preview: true,
});

@Component({
  components: {
    'omegaup-markdown': omegaup_problemMarkdown,
  },
})
export default class SolutionTab extends Vue {
  @Ref() readonly markdownButtonBar!: HTMLDivElement;
  @Ref() readonly markdownInput!: HTMLTextAreaElement;

  @Prop({ default: T.problemCreatorEmpty })
  currentSolutionMarkdownProp!: string;
  @Prop() activeTabIndex!: TabIndex;

  T = T;
  ui = ui;
  markdownEditor: Markdown.Editor | null = null;

  currentSolutionMarkdownInternal: string = T.problemCreatorEmpty;

  get currentSolutionMarkdown(): string {
    return this.currentSolutionMarkdownInternal;
  }
  set currentSolutionMarkdown(newMarkdown: string) {
    this.currentSolutionMarkdownInternal = newMarkdown;
  }

  @Watch('currentSolutionMarkdownProp')
  onCurrentSolutionMarkdownPropChanged() {
    this.currentSolutionMarkdown = this.currentSolutionMarkdownProp;
  }

  @Watch('activeTabIndex')
  onActiveTabIndexChanged(newIndex: TabIndex) {
    if (newIndex === TabIndex.Solution) {
      this.$nextTick(() => {
        this.startIntroGuide();
      });
    }
  }

  mounted(): void {
    this.markdownEditor = new Markdown.Editor(markdownConverter.converter, '', {
      panels: {
        buttonBar: this.markdownButtonBar,
        preview: null,
        input: this.markdownInput,
      },
    });
    this.markdownEditor.run();
  }

  updateMarkdown() {
    this.$store.commit('updateSolutionMarkdown', this.currentSolutionMarkdown);
    this.$emit('show-update-success-message');
  }

  startIntroGuide() {
    if (!this.$cookies.get('has-visited-solution-tab')) {
      introJs()
        .setOptions({
          nextLabel: T.interactiveGuideNextButton,
          prevLabel: T.interactiveGuidePreviousButton,
          doneLabel: T.interactiveGuideDoneButton,
          steps: [
            {
              title: T.problemCreatorSolutionTabIntroToolbarTitle,
              intro: T.problemCreatorSolutionTabIntroToolbarIntro,
              element: document.querySelector(
                '[data-solution-markdown-toolbar]',
              ) as Element,
            },
            {
              title: T.problemCreatorSolutionTabIntroEditorTitle,
              intro: T.problemCreatorSolutionTabIntroEditorIntro,
              element: document.querySelector(
                '[data-problem-creator-solution-editor-markdown]',
              ) as Element,
            },
            {
              title: T.problemCreatorSolutionTabIntroPreviewTitle,
              intro: T.problemCreatorSolutionTabIntroPreviewIntro,
              element: document.querySelector(
                '[data-problem-creator-solution-previewer-markdown]',
              ) as Element,
            },
            {
              title: T.problemCreatorSolutionTabIntroSaveTitle,
              intro: T.problemCreatorSolutionTabIntroSaveIntro,
              element: document.querySelector(
                '[data-problem-creator-solution-save-markdown]',
              ) as Element,
            },
          ],
        })
        .start();
      this.$cookies.set('has-visited-solution-tab', true, -1);
    }
  }
}
</script>

<style lang="scss" scoped>
@import '../../../../../../sass/main.scss';
@import '../../../../../../third_party/js/pagedown/demo/browser/demo.css';

.wmd-preview,
.wmd-button-bar {
  background-color: var(--wmd-button-bar-background-color);
}

.row {
  .wmd-button-bar {
    flex-shrink: 0;
  }

  .wmd-input {
    flex: 1;
    min-height: 400px;
    height: auto !important;
    resize: vertical;
  }

  [data-problem-creator-solution-previewer-markdown] {
    flex: 1;
    min-height: 400px;
    overflow-y: auto;
    border: 1px solid var(--markdown-preview-border-color);
    padding: 10px;
    width: 100%;
    margin-top: 35px;
    overflow-wrap: anywhere;
  }
}
</style>
