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
          <div ref="markdownPreview" data-markdown-statement></div>
          <omegaup-markdown
            :markdown="currentMarkdown"
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
        preview: this.markdownPreview,
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

<style lang="scss">
@import '../../../../third_party/js/pagedown/demo/browser/demo.css';
@import '../../../../../../node_modules/prismjs/themes/prism.css';
@import '../../../../sass/main.scss';

.wmd-preview,
.wmd-button-bar {
  background-color: #fff;
}

[data-markdown-statement] {
  display: block;
  max-width: 50em;
  margin: 0 auto;

  h1 {
    font-size: 1.3em;
    margin: 1em 0 0.5em 0;
    font-weight: bold;
  }
  h2 {
    font-size: 1.1em;
    margin: 1em 0 0.5em 0;
    font-weight: bold;
  }
  h3 {
    font-size: 1em;
    margin: 1em 0 0.5em 0;
    font-weight: bold;
  }

  p,
  li {
    hyphens: auto;
    line-height: 150%;
    text-align: justify;
    orphans: 2;
    widows: 2;
    page-break-inside: avoid;
  }
  p {
    margin-bottom: 1em;
  }

  ul {
    list-style: disc;
  }
  ol {
    list-style: decimal;
  }
  ul li,
  ol li {
    margin-left: 0.25em;
  }

  pre {
    padding: 16px;
    background: #eee;
    margin: 1em 0;
    border-radius: 6px;
    display: block;
    line-height: 125%;
  }
  & > pre > button {
    margin-right: -16px;
    margin-top: -16px;
    padding: 6px;
    font-size: 90%;
  }
  td > button.clipboard {
    float: right;
    border-color: rgb(218, 224, 229);
    margin-left: 0.5em;
    margin-right: -6px;
    margin-top: -6px;
    padding: 3px;
    font-size: 90%;
  }

  figure {
    text-align: center;
    page-break-inside: avoid;
  }

  details {
    padding: 16px;
    border: 1px solid #eee;
    summary {
      color: $omegaup-blue;
    }
    &[open] > summary {
      margin-bottom: 24px;
    }
  }

  table td {
    border: 1px solid #000;
    padding: 10px;
  }
  table th {
    text-align: center;
  }
  table.sample_io {
    margin: 5px;
    padding: 5px;

    tbody {
      background: #eee;
      border: 1px solid #000;

      tr:nth-child(even) {
        background: #f5f5f5;
      }
    }
    th {
      padding: 10px;
      border-top: 0;
    }
    td {
      vertical-align: top;
      padding: 10px;
      border: 1px solid #000;
    }
    pre {
      white-space: pre;
      word-break: keep-all;
      word-wrap: normal;
      background: transparent;
      border: 0px;
      padding: 0px;
      margin: inherit;
      & > button {
        margin-left: 2em;
        padding: 3px;
        font-size: 80%;
      }
    }
  }

  iframe {
    width: 100%;
    height: 400px;
  }

  a.libinteractive-help {
    display: inline-block;
    float: right;
  }
  code.libinteractive-download,
  code.output-only-download {
    background: #eee;
    color: #ccc;
    margin: 1em 0;
    border: 1px dotted #ccc;
    display: block;
    text-align: center;
    font-size: 2em;
    line-height: 3em;
    min-height: 3em;
  }

  img {
    max-width: 100%;
    page-break-inside: avoid;
  }
}
</style>
