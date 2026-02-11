<template>
  <div class="card">
    <div class="card-body">
      <div v-if="showEditControls" class="row">
        <div class="form-group col-md-6">
          <label class="font-weight-bold">{{ T.statementLanguage }}</label>
          <select v-model="currentLanguage" class="form-control">
            <option
              v-for="language in languages"
              :key="language"
              :markdown-contents="currentMarkdown"
              :value="language"
            >
              {{ getLanguageNameText(language) }}
            </option>
          </select>
        </div>
        <div
          class="form-group col-md-6"
          :class="{ 'has-error': errors.includes('message') }"
        >
          <label class="control-label">{{ T.problemEditCommitMessage }}</label>
          <input v-model="commitMessage" class="form-control" />
        </div>
      </div>
      <div class="row">
        <div class="col-md-6 d-flex flex-column">
          <div ref="markdownButtonBar" class="wmd-button-bar"></div>
          <textarea
            ref="markdownInput"
            v-model="currentMarkdown"
            class="wmd-input"
            @change="currentMarkdown = $event.target.value"
          ></textarea>
        </div>
        <div class="col-md-6 d-flex flex-column">
          <h1 class="title text-center">{{ title }}</h1>
          <omegaup-markdown
            data-statement-edit-markdown
            :markdown="currentMarkdown"
            :source-mapping="statement.sources"
            :image-mapping="statement.images"
            preview="true"
          ></omegaup-markdown>
          <template v-if="markdownType === 'statements'">
            <hr />
            <div>
              <em
                >{{ T.wordsSource }}:
                <span class="source">{{ source }}</span>
              </em>
            </div>
            <div>
              <em
                >{{ T.wordsProblemsetter }}:
                <a class="problemsetter">
                  <omegaup-user-username
                    v-if="problemsetter"
                    :classname="problemsetter.classname"
                    :linkify="true"
                    :username="problemsetter.username"
                  ></omegaup-user-username>
                </a>
              </em>
            </div>
          </template>
        </div>
      </div>
    </div>
    <div v-if="showEditControls" class="card-footer">
      <form
        class="row"
        enctype="multipart/form-data"
        method="post"
        @submit="onSubmit"
      >
        <div class="col-md-12">
          <button
            class="btn btn-primary"
            type="submit"
            :disabled="commitMessage === ''"
          >
            {{
              markdownType === 'solutions'
                ? T.problemEditFormUpdateSolution
                : T.problemEditFormUpdateMarkdown
            }}
          </button>
        </div>
        <input type="hidden" name="message" :value="commitMessage" />
        <input
          type="hidden"
          name="contents"
          :value="JSON.stringify(statements)"
        />
        <input type="hidden" name="directory" :value="markdownType" />
        <input type="hidden" name="problem_alias" :value="alias" />
        <input type="hidden" name="request" value="markdown" />
      </form>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Emit, Prop, Watch, Ref } from 'vue-property-decorator';
import { types } from '../../api_types';
import T from '../../lang';
import * as ui from '../../ui';
import * as Markdown from '@/third_party/js/pagedown/Markdown.Editor.js';
import * as markdown from '../../markdown';

import user_Username from '../user/Username.vue';
import ProblemMarkdown from './ProblemMarkdown.vue';

const markdownConverter = new markdown.Converter({
  preview: true,
});

@Component({
  components: {
    'omegaup-user-username': user_Username,
    'omegaup-markdown': ProblemMarkdown,
  },
})
export default class ProblemStatementEdit extends Vue {
  @Ref() readonly markdownButtonBar!: HTMLDivElement;
  @Ref() readonly markdownInput!: HTMLTextAreaElement;
  @Prop() alias!: string;
  @Prop() title!: string;
  @Prop() source!: string;
  @Prop({ default: null }) problemsetter!: types.ProblemsetterInfo;
  @Prop() statement!: types.ProblemStatement;
  @Prop() markdownType!: string;
  @Prop({ default: true }) showEditControls!: boolean;
  @Prop({ default: () => ['es', 'en', 'pt'] }) languages!: string[];

  T = T;
  commitMessage = T.updateStatementsCommitMessage;
  currentLanguage = this.statement.language;
  currentMarkdown = this.statement.markdown;
  errors: string[] = [];
  statements: types.Statements = {};
  markdownEditor: Markdown.Editor | null = null;

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

  getLanguageNameText(language: string): string {
    switch (language) {
      case 'en':
        return T.statementLanguageEn;
      case 'es':
        return T.statementLanguageEs;
      case 'pt':
        return T.statementLanguagePt;
      default:
        return '';
    }
  }

  @Watch('statement')
  onStatementChange(newStatement: types.ProblemStatement): void {
    this.currentLanguage = newStatement.language;
    this.currentMarkdown = newStatement.markdown;
  }

  @Watch('currentLanguage')
  onCurrentLanguageChange(newLanguage: string, oldLanguage: string): void {
    if (oldLanguage) this.statements[oldLanguage] = this.currentMarkdown;
    this.$emit(
      'update-markdown-contents',
      this.statements,
      newLanguage,
      this.currentMarkdown,
    );
  }

  @Emit('update:statement')
  @Watch('currentMarkdown')
  onCurrentMarkdownChange(newMarkdown: string): types.ProblemStatement {
    return {
      images: this.statement.images,
      language: this.statement.language,
      sources: this.statement.sources,
      markdown: newMarkdown,
    };
  }

  onSubmit(e: Event) {
    this.errors = [];
    this.statements[this.currentLanguage] = this.currentMarkdown;
    if (this.commitMessage) {
      return;
    }
    this.errors.push('message');
    ui.error(T.editFieldRequired);
    e.preventDefault();
  }
}
</script>

<style lang="scss" scoped>
@import '../../../../sass/main.scss';
@import '../../../../third_party/js/pagedown/demo/browser/demo.css';

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

  .title {
    flex-shrink: 0;
  }

  [data-statement-edit-markdown] {
    flex: 1;
    min-height: 400px;
    overflow-y: auto;
    border: 1px solid var(--markdown-preview-border-color);
    padding: 10px;
    margin-bottom: 10px;
  }

  hr,
  div {
    flex-shrink: 0;
  }
}
</style>
