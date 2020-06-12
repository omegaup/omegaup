<template>
  <div class="card">
    <form class="card-body form" enctype="multipart/form-data" method="post">
      <div class="row">
        <label class="font-weight-bold"
          >{{ T.statementLanguage }}
          <select name="statement-language" v-model="currentLanguage">
            <option
              v-bind:markdown-contents.sync="currentMarkdown"
              v-bind:value="language"
              v-for="language in languages"
              >{{ getLanguageNameText(language) }}</option
            >
          </select>
        </label>
      </div>
      <div class="row">
        <div class="col-md-12">
          <ul class="nav nav-tabs">
            <li
              v-bind:class="{ active: showTab === 'source' }"
              v-on:click="showTab = 'source'"
            >
              <a data-toggle="tab">{{ T.wordsSource }}</a>
            </li>
            <li
              v-bind:class="{ active: showTab === 'preview' }"
              v-on:click="showTab = 'preview'"
            >
              <a data-toggle="tab">{{ T.wordsPreview }}</a>
            </li>
          </ul>

          <div class="tab-content">
            <div class="tab-pane active" v-show="showTab === 'source'">
              <!-- id-lint off -->
              <div id="wmd-button-bar-statements"></div>
              <textarea
                class="wmd-input"
                id="wmd-input-statements"
                v-model="currentMarkdown"
              ></textarea>
            </div>
            <div class="tab-pane active" v-show="showTab === 'preview'">
              <h1 class="title text-center">{{ title }}</h1>
              <div
                ref="preview"
                class="no-bottom-margin statement"
                id="wmd-preview-statements"
                v-html="markdownPreview"
              ></div>
              <!-- id-lint on -->
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
                        v-bind:classname="classname"
                        v-bind:linkify="true"
                        v-bind:username="username"
                      ></omegaup-user-username>
                    </a>
                  </em>
                </div>
              </template>
            </div>
          </div>
        </div>
      </div>
      <div class="row">
        <div
          class="form-group col-md-6"
          v-bind:class="{ 'has-error': errors.includes('message') }"
        >
          <label class="control-label"
            >{{ T.problemEditCommitMessage }}
            <input
              class="form-control"
              name="message"
              v-model="commitMessage"
            />
          </label>
        </div>
      </div>
      <div class="row">
        <div class="col-md-12">
          <button
            class="btn btn-primary"
            v-bind:disabled="commitMessage === ''"
            v-on:click="handleEditMarkdown"
          >
            {{
              markdownType === 'solutions'
                ? T.problemEditFormUpdateSolution
                : T.problemEditFormUpdateMarkdown
            }}
          </button>
        </div>
      </div>
      <input
        type="hidden"
        name="contents"
        v-bind:value="JSON.stringify(this.statements)"
      />
      <input type="hidden" name="directory" v-bind:value="markdownType" />
      <input type="hidden" name="problem_alias" v-bind:value="alias" />
      <input type="hidden" name="request" value="markdown" />
    </form>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop, Watch, Ref } from 'vue-property-decorator';
import { omegaup } from '../../omegaup';
import { types } from '../../api_types';
import T from '../../lang';
import * as ui from '../../ui';
import user_Username from '../user/Username.vue';
import * as Markdown from '@/third_party/js/pagedown/Markdown.Editor.js';
import * as markdown from '../../markdown';

const markdownConverter = markdown.markdownConverter({
  preview: true,
  imageMapping: {},
});
const markdownEditor: Markdown.Editor = new Markdown.Editor(
  markdownConverter,
  '-statements',
);

@Component({
  components: {
    'omegaup-user-username': user_Username,
  },
})
export default class ProblemStatementEdit extends Vue {
  @Ref() readonly preview!: HTMLDivElement;
  @Prop() alias!: string;
  @Prop() title!: string;
  @Prop() source!: string;
  @Prop() username!: string;
  @Prop() name!: string;
  @Prop() classname!: string;
  @Prop() markdownContents!: string;
  @Prop() initialLanguage!: string;
  @Prop() markdownType!: string;

  T = T;
  showTab = 'source';
  commitMessage = '';
  currentLanguage = this.initialLanguage;
  currentMarkdown = this.markdownContents;
  errors: string[] = [];
  languages = ['es', 'en', 'pt'];
  statements: types.Statements = {};
  markdownPreview: string = '';

  mounted(): void {
    markdownEditor.hooks.chain('onPreviewRefresh', () => {
      MathJax.Hub.Queue(['Typeset', MathJax.Hub, this.preview]);
    });
    markdownEditor.run();
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

  @Watch('initialLanguage')
  onInitialLanguageChange(newInitial: string): void {
    this.currentLanguage = newInitial;
  }

  @Watch('markdownContents')
  onMarkdownContentsChange(newMarkdown: string): void {
    this.currentMarkdown = newMarkdown;
    this.statements[this.currentLanguage] = newMarkdown;
  }

  @Watch('showTab')
  onShowTabChange(): void {
    if (this.showTab !== 'preview') {
      return;
    }
    MathJax.Hub.Queue(['Typeset', MathJax.Hub, this.preview]);
  }

  @Watch('currentLanguage')
  onCurrentLanguageChange(newLanguage: string, oldLanguage: string): void {
    MathJax.Hub.Queue(['Typeset', MathJax.Hub, this.preview]);
    if (!!oldLanguage) this.statements[oldLanguage] = this.currentMarkdown;

    this.$emit(
      'emit-update-markdown-contents',
      this.statements,
      newLanguage,
      this.currentMarkdown,
    );
  }

  handleEditMarkdown(e: Event) {
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
