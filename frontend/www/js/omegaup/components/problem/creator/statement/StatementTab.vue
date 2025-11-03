<template>
  <div class="card">
    <div class="card-body">
      <div class="row">
        <div class="col-md-6">
          <div ref="markdownButtonBar" class="wmd-button-bar"></div>
          <textarea
            ref="markdownInput"
            v-model.lazy="currentMarkdown"
            data-problem-creator-editor-markdown
            class="wmd-input"
          ></textarea>
        </div>
        <div class="col-md-6">
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

import omegaup_problemMarkdown from '../../Markdown.vue';

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
  display: flex;
  
  .col-md-6 {
    display: flex;
    flex-direction: column;
    
    &:first-child {
      .wmd-button-bar {
        flex-shrink: 0;
      }
      
      .wmd-input {
        flex: 1;
        min-height: 400px;
        height: auto !important;
        resize: vertical;
      }
    }
    
    &:last-child {
      omegaup-markdown {
        flex: 1;
        min-height: 400px;
        overflow-y: auto;
        border: 1px solid #ddd;
        padding: 10px;
      }
    }
  }
}
</style>
