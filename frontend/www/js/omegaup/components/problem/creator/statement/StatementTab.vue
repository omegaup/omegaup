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
  .wmd-button-bar {
    flex-shrink: 0;
  }

.wmd-input {
    padding: 12px;
    margin: 0;
    border: 1px solid #ddd;
    border-radius: 4px;
    min-height: 400px;
    box-sizing: border-box;
    flex: 1;
    resize: vertical;
  }

[data-problem-creator-previewer-markdown] {
    padding: 12px;
    margin: 0;
    border: 1px solid #ddd;
    border-radius: 4px;
    min-height: 400px;
    background-color: #f9f9f9;
    width: 100%;sed -n '110,145p' /workspaces/winter-of-contributing-viveksahu/frontend/www/js/omegaup/components/problem/creator/statement/StatementTab.vue
    cat /workspaces/winter-of-contributing-viveksahu/frontend/www/js/omegaup/components/problem/creator/statement/StatementTab.vue | sed -n '114,126p'
    grep -n 'height: auto !important' /workspaces/winter-of-contributing-viveksahu/frontend/www/js/omegaup/components/problem/creator/statement/StatementTab.vue
    perl -i -pe 's/.*height: auto !important.*\n//' /workspaces/winter-of-contributing-viveksahu/frontend/www/js/omegaup/components/problem/creator/statement/StatementTab.vue
    sed -n '114,126p' /workspaces/winter-of-contributing-viveksahu/frontend/www/js/omegaup/components/problem/creator/statement/StatementTab.vue
    cd /workspapython3 << 'EOF'
with open('/workspaces/winter-of-contributing-viveksahu/frontend/www/js/omegaup/components/problem/creator/statement/StatementTab.vue', 'r') as f:
    lines = f.readlines()

# Remove lines containing 'height: auto !important'
filtered = [l for l in lines if 'height: auto !important' not in l]

with open('/workspaces/winter-of-contributing-viveksahu/frontend/www/js/omegaup/components/problem/creator/statement/StatementTab.vue', 'w') as f:
    f.writelines(filtered)

print("File cleaned successfully!")
EOF
sed -n '114,126p' /workspaces/winter-of-contributing-viveksahu/frontend/www/js/omegaup/components/problem/creator/statement/StatementTab.vue && echo '---' && grep -n 'height: auto' /workspaces/winter-of-contributing-viveksahu/frontend/www/js/omegaup/components/problem/creator/statement/StatementTab.vue | wc -lcd /workspaces/winter-of-contributing-viveksahu/frontend && yarn build

ces/winter-of-contributing-viveksahu/frontend && yarn build 2>&1 | tail -20

    box-sizing: border-box;
    flex: 1;
    overflow-y: auto;
  }
.row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
    margin-top: 16px;
    height: auto;
  }  
</style>
