<template>
  <div class="panel panel-primary">
    <form class="panel-body form" enctype="multipart/form-data" method="post">
      <template v-if="markdownType === 'statement'">
        <input type="hidden" name="problem_alias" v-bind:value="alias" />
        <input type="hidden" name="request" value="markdown" />
      </template>
      <div class="row">
        <label
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
          <div class="panel">
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
                <div v-bind:id="`wmd-button-bar-${markdownType}`"></div>
                <textarea
                  class="wmd-input"
                  v-bind:id="`wmd-input-${markdownType}`"
                  v-bind:name="`wmd-input-${markdownType}`"
                  v-model="currentMarkdown"
                ></textarea>
              </div>
              <div class="tab-pane active" v-show="showTab === 'preview'">
                <h1 class="title text-center">{{ title }}</h1>
                <div
                  ref="preview"
                  class="no-bottom-margin statement"
                  v-bind:id="`wmd-preview-${markdownType}`"
                  v-html="markdownPreview"
                ></div>
                <!-- id-lint on -->
                <template v-if="markdownType === 'statement'">
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
            v-on:click.prevent="handleEditMarkdown"
            v-if="markdownType === 'solution'"
          >
            {{ T.problemEditFormUpdateSolution }}
          </button>
          <button
            class="btn btn-primary"
            type="submit"
            v-bind:disabled="commitMessage === ''"
            v-on:submit="handleEditSubmitMarkdown"
            v-else=""
          >
            {{ T.problemEditFormUpdateMarkdown }}
          </button>
        </div>
      </div>
    </form>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop, Watch, Ref } from 'vue-property-decorator';
import { omegaup } from '../../omegaup';
import T from '../../lang';
import * as ui from '../../ui';
import user_Username from '../user/Username.vue';

@Component({
  components: {
    'omegaup-user-username': user_Username,
  },
})
export default class ProblemStatementEdit extends Vue {
  @Ref() readonly preview!: HTMLElement;
  @Prop() alias!: string;
  @Prop() title!: string;
  @Prop() source!: string;
  @Prop() username!: string;
  @Prop() name!: string;
  @Prop() classname!: string;
  @Prop() markdownContents!: string;
  @Prop() markdownPreview!: string;
  @Prop() initialLanguage!: string;
  @Prop({ default: 'statement' }) markdownType!: string;

  T = T;
  showTab = 'source';
  commitMessage = '';
  currentLanguage = this.initialLanguage;
  currentMarkdown = this.markdownContents;
  errors: string[] = [];
  languages = ['es', 'en', 'pt'];
  statements: omegaup.Statements = {};

  mounted(): void {
    MathJax.Hub.Queue(['Typeset', MathJax.Hub, this.preview]);
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
    if (!!oldLanguage) this.statements[oldLanguage] = this.currentMarkdown;

    this.$emit(
      'update-markdown-contents',
      this.statements,
      newLanguage,
      this.currentMarkdown,
    );
  }

  handleEditMarkdown(): void {
    this.statements[this.currentLanguage] = this.currentMarkdown;
    if (this.markdownType === 'solution') {
      this.$emit(
        'edit-statement',
        this.statements,
        this.commitMessage,
        this.currentLanguage,
      );
      return;
    }
  }

  handleEditSubmitMarkdown(e: Event): void {
    this.errors = [];
    this.statements[this.currentLanguage] = this.currentMarkdown;
    if (this.commitMessage) {
      this.$emit(
        'edit-statement',
        this.statements,
        this.commitMessage,
        this.currentLanguage,
      );
      return;
    }
    this.errors.push('message');
    ui.error(T.editFieldRequired);
    e.preventDefault();
  }
}
</script>
