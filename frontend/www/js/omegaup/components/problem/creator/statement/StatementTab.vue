<template>
  <div class="card">
    <div class="card-body">
      <div class="row">
        <div class="col-md-6 d-flex flex-column">
          <div ref="markdownButtonBar" class="wmd-button-bar"></div>
          <textarea
            ref="markdownInput"
            v-model.lazy="currentMarkdown"
            data-problem-creator-editor-markdown
            class="wmd-input"
            @paste="handlePaste"
            @drop="handleDrop"
          ></textarea>
        </div>
        <div class="col-md-6 d-flex flex-column">
          <omegaup-markdown
            data-problem-creator-previewer-markdown
            :markdown="
              T.problemCreatorMarkdownPreviewInitialRender + currentMarkdown
            "
            preview="true"
          ></omegaup-markdown>
        </div>
      </div>
      <div class="row">
        <div class="col-md-12">
          <button
            data-problem-creator-save-markdown
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
import T from '../../../../lang';
import * as ui from '../../../../ui';

import omegaup_problemMarkdown from '../../ProblemMarkdown.vue';

const markdownConverter = new markdown.Converter({
  preview: true,
});

@Component({
  components: {
    'omegaup-markdown': omegaup_problemMarkdown,
  },
})
export default class StatementTab extends Vue {
  @Ref() readonly markdownButtonBar!: HTMLDivElement;
  @Ref() readonly markdownInput!: HTMLTextAreaElement;

  @Prop({ default: T.problemCreatorEmpty }) currentMarkdownProp!: string;

  T = T;
  ui = ui;
  markdownEditor: Markdown.Editor | null = null;

  // 256 KB limit for images
  readonly MAX_IMAGE_SIZE = 256 * 1024;

  currentMarkdownInternal: string = T.problemCreatorEmpty;

  get currentMarkdown(): string {
    return this.currentMarkdownInternal;
  }
  set currentMarkdown(newMarkdown: string) {
    this.currentMarkdownInternal = newMarkdown;
  }

  @Watch('currentMarkdownProp')
  onCurrentMarkdownPropChanged() {
    this.currentMarkdown = this.currentMarkdownProp;
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
    this.$store.commit('updateMarkdown', this.currentMarkdown);
    this.$emit('show-update-success-message');
  }

  /**
   * Validates image file size and shows error if too large.
   * @param file The file to validate
   * @returns true if valid, false if too large
   */
  private validateImageSize(file: File): boolean {
    if (file.size > this.MAX_IMAGE_SIZE) {
      ui.error(
        ui.formatString(
          T.problemCreatorMarkdownImageTooLarge ??
            'The image is too large. The maximum allowed size is %(limit). Please use a smaller image.',
          {
            limit: '256 KB',
          },
        ),
      );
      return false;
    }
    return true;
  }

  /**
   * Handles paste events to validate image sizes before insertion.
   */
  handlePaste(event: ClipboardEvent): void {
    const items = event.clipboardData?.items;
    if (!items) return;

    for (const item of items) {
      if (item.type.startsWith('image/')) {
        const file = item.getAsFile();
        if (file && !this.validateImageSize(file)) {
          event.preventDefault();
          return;
        }
      }
    }
  }

  /**
   * Handles drop events to validate image sizes before insertion.
   */
  handleDrop(event: DragEvent): void {
    const files = event.dataTransfer?.files;
    if (!files) return;

    for (const file of files) {
      if (file.type.startsWith('image/')) {
        if (!this.validateImageSize(file)) {
          event.preventDefault();
          return;
        }
      }
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

  [data-problem-creator-previewer-markdown] {
    flex: 1;
    min-height: 400px;
    overflow-y: auto;
    border: 1px solid var(--markdown-preview-border-color);
    padding: 10px;
  }
}
</style>
