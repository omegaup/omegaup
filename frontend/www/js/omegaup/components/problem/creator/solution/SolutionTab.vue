<template>
  <div class="card">
    <div class="card-body">
      <div class="row">
        <div class="col-md-6">
          <div ref="markdownButtonBar" class="wmd-button-bar"></div>
          <textarea
            ref="markdownInput"
            v-model.lazy="currentSolutionMarkdown"
            data-problem-creator-solution-editor-markdown
            class="wmd-input"
          ></textarea>
        </div>
        <div class="col-md-6">
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

import omegaup_problemMarkdown from '../../Markdown.vue';

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
}
</script>

<style lang="scss" scoped>
@import '../../../../../../sass/main.scss';
@import '../../../../../../third_party/js/pagedown/demo/browser/demo.css';

.wmd-preview,
.wmd-button-bar {
  background-color: var(--wmd-button-bar-background-color);
}
</style>
