<template>
  <div class="panel panel-primary">
    <form class="panel-body form"
          enctype="multipart/form-data"
          method="post">
      <div class="row">
        <label for="solution-language">{{ T.statementLanguage }}</label> <select name=
        "solution-language"
             v-model="currentLanguage">
          <option v-bind:markdown-contents.sync="currentMarkdown"
                  v-bind:value="language"
                  v-for="language in languages">
            {{ getLanguageNameText(language) }}
          </option>
        </select>
      </div>
      <div class="row">
        <div class="col-md-12">
          <div class="panel">
            <ul class="nav nav-tabs">
              <li class="active">
                <a data-toggle="tab"
                    href="#solution-source">{{ T.wordsSource }}</a>
              </li>
              <li>
                <a data-toggle="tab"
                    href="#solution-preview">{{ T. wordsPreview }}</a>
              </li>
            </ul>
            <div class="tab-content">
              <!-- id-lint off -->
              <div class="tab-pane active"
                   id="solution-source">
                <div id="wmd-button-bar-solution"></div>
                <textarea class="wmd-input"
                     id="wmd-input-solution"
                     name="wmd-input-solution"
                     v-model="currentMarkdown"></textarea>
              </div>
              <div class="tab-pane"
                   id="solution-preview">
                <h1 class="title"
                    style="text-align: center;"></h1>
                <div class="no-bottom-margin"
                     id="wmd-preview-solution"
                     v-html="markdownPreview"></div><!-- id-lint on -->
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="form-group col-md-6">
          <label class="control-label"
               for="markdown-message">{{ T.problemEditCommitMessage }}</label> <input class=
               "form-control"
               name="message"
               type="text"
               v-model="commitMessage">
        </div>
      </div>
      <div class="row">
        <div class="col-md-12">
          <button class="btn btn-primary"
               type='submit'
               v-bind:disabled="commitMessage === ''"
               v-on:click.prevent="handleEditSolution">{{ T.problemEditFormUpdateSolution
               }}</button>
        </div>
      </div>
    </form>
  </div>
</template>

<style>

</style>

<script lang="ts">
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';
import { T } from '../../omegaup.js';
import UI from '../../ui.js';
import omegaup from '../../api.js';

@Component
export default class ProblemStatementEdit extends Vue {
  @Prop() markdownContents!: string;
  @Prop() markdownPreview!: string;
  @Prop() initialLanguage!: string;

  T = T;
  UI = UI;
  commitMessage = '';
  currentLanguage = this.initialLanguage;
  currentMarkdown = this.markdownContents;
  languages = ['es', 'en', 'pt'];
  solutions: omegaup.Solutions = {};

  getLanguageNameText(language: string): string {
    switch (language) {
      case 'en':
        return this.T.statementLanguageEn;
      case 'es':
        return this.T.statementLanguageEs;
      case 'pt':
        return this.T.statementLanguagePt;
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
    this.solutions[this.currentLanguage] = newMarkdown;
  }

  @Watch('currentLanguage')
  onCurrentLanguageChange(newLanguage: string, oldLanguage: string): void {
    if (!!oldLanguage) this.solutions[oldLanguage] = this.currentMarkdown;

    this.$emit(
      'update-markdown-contents',
      this.solutions,
      newLanguage,
      this.currentMarkdown,
    );
  }

  handleEditSolution(): void {
    this.solutions[this.currentLanguage] = this.currentMarkdown;
    this.$emit(
      'edit-solution',
      this.solutions,
      this.commitMessage,
      this.currentLanguage,
    );
  }
}

</script>
