<template>
  <div class="card">
    <div class="card-body">
      <div v-if="showEditControls" class="row">
        <div class="form-group col-md-6">
          <label class="font-weight-bold">{{ T.statementLanguage }}</label>
          <select v-model="currentLanguage" class="form-control">
            <option
              v-for="language in languages"
              v-bind:markdown-contents="currentMarkdown"
              v-bind:value="language"
            >
              {{ getLanguageNameText(language) }}
            </option>
          </select>
        </div>
        <div
          class="form-group col-md-6"
          v-bind:class="{ 'has-error': errors.includes('message') }"
        >
          <label class="control-label">{{ T.problemEditCommitMessage }}</label>
          <input v-model="commitMessage" class="form-control" />
        </div>
      </div>
      <div class="row">
        <div class="col-md-6">
          <div ref="markdownButtonBar" class="wmd-button-bar"></div>
          <textarea
            ref="markdownInput"
            v-model="currentMarkdown"
            class="wmd-input"
          ></textarea>
        </div>
        <div class="col-md-6">
          <h1 class="title text-center">{{ title }}</h1>
          <omegaup-markdown
            v-bind:markdown="currentMarkdown"
            v-bind:image-mapping="statement.images"
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
                    v-bind:classname="problemsetter.classname"
                    v-bind:linkify="true"
                    v-bind:username="problemsetter.username"
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
        v-on:submit="onSubmit"
      >
        <div class="col-md-12">
          <button
            class="btn btn-primary"
            type="submit"
            v-bind:disabled="commitMessage === ''"
          >
            {{
              markdownType === 'solutions'
                ? T.problemEditFormUpdateSolution
                : T.problemEditFormUpdateMarkdown
            }}
          </button>
        </div>
        <input type="hidden" name="message" v-bind:value="commitMessage" />
        <input
          type="hidden"
          name="contents"
          v-bind:value="JSON.stringify(statements)"
        />
        <input type="hidden" name="directory" v-bind:value="markdownType" />
        <input type="hidden" name="problem_alias" v-bind:value="alias" />
        <input type="hidden" name="request" value="markdown" />
      </form>
    </div>
  </div>
</template>

<style lang="scss">
@import '../../../../third_party/js/pagedown/demo/browser/demo.css';
.wmd-preview,
.wmd-button-bar {
  background-color: #fff;
}
</style>

<script lang="ts">
import { Vue, Component, Emit, Prop, Watch, Ref } from 'vue-property-decorator';
import { types } from '../../api_types';
import T from '../../lang';
import * as ui from '../../ui';
import * as Markdown from '@/third_party/js/pagedown/Markdown.Editor.js';
import * as markdown from '../../markdown';

import omegaup_Markdown from '../Markdown.vue';
import user_Username from '../user/Username.vue';

const markdownConverter = new markdown.Converter({
  preview: true,
});

@Component({
  components: {
    'omegaup-user-username': user_Username,
    'omegaup-markdown': omegaup_Markdown,
  },
})
export default class ProblemStatementEdit extends Vue {
  @Ref() readonly markdownButtonBar!: HTMLDivElement;
  @Ref() readonly markdownInput!: HTMLDivElement;
  @Ref() readonly markdownPreview!: HTMLDivElement;
  @Prop() alias!: string;
  @Prop() title!: string;
  @Prop() source!: string;
  @Prop({ default: null }) problemsetter!: types.ProblemsetterInfo;
  @Prop() statement!: types.ProblemStatement;
  @Prop() markdownType!: string;
  @Prop({ default: true }) showEditControls!: boolean;

  T = T;
  commitMessage = T.updateStatementsCommitMessage;
  currentLanguage = this.statement.language;
  currentMarkdown = this.statement.markdown;
  errors: string[] = [];
  languages = ['es', 'en', 'pt'];
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
      'emit-update-markdown-contents',
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
