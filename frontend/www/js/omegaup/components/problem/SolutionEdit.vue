<template>
  <div class="panel panel-primary">
    <form class="panel-body form" method="post" action="{$smarty.server.REQUEST_URI}" enctype="multipart/form-data">
      <div class="row">
        <label for="solution-language">{{ T.statementLanguage }}</label>
        <select v-model="currentLanguage" name="solution-language">
          <option v-for="(markdown, language) in solutions" v-bind:markdown-contents.sync="currentMarkdown" v-bind:value="language">{{ getLanguageNameText(language) }}</option>
        </select>
      </div>
      <div class="row">
        <div class="col-md-12">
          <div class="panel">
            <ul class="nav nav-tabs">
              <li class="active"><a href="#solution-source" data-toggle="tab">Source</a></li>
              <li><a id="solution-preview-link" href="#solution-preview" data-toggle="tab">Preview</a></li>
            </ul>

            <div class="tab-content">
              <div class="tab-pane active" id="solution-source">
                <div id="wmd-button-bar-solution"></div>
                <textarea class="wmd-input" id="wmd-input-solution" name="wmd-input-solution" v-model="currentMarkdown"></textarea>
              </div>

              <div class="tab-pane" id="solution-preview">
                <h1 style="text-align: center;" class="title"></h1>
                <div class="no-bottom-margin statement" id="wmd-preview-solution" v-html="markdownPreview"></div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="form-group  col-md-6" id="markdown-message-group">
          <label class="control-label" for="markdown-message">{{ T.problemEditCommitMessage }}</label>
          <input v-model="commitMessage" name="message" type="text" class="form-control" />
        </div>
      </div>

      <div class="row">
        <div class="col-md-12">
          <button type='submit' v-bind:disabled="commitMessage === ''" v-on:click.prevent="handleEditSolution" class="btn btn-primary">Editar Soluci�n</button>
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
export default class ProblemSolutionEdit extends Vue {
  @Prop() markdownContents!: string;
  @Prop() markdownPreview!: string;

  T = T;
  UI = UI;
  commitMessage = '';
  currentLanguage = 'es';
  currentMarkdown = this.markdownContents;
  solutions: omegaup.Solutions = {
    'en': {
      'searched': false,
      'markdown': '',
    },
    'es': {
      'searched': true,
      'markdown': '',
    },
    'pt': {
      'searched': false,
      'markdown': '',
    }
  }

  getLanguageNameText(language: string): string {
    switch(language) {
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

  @Watch('markdownContents')
  onMarkdownContentsChange(newMarkdown: string): void {
    this.currentMarkdown = newMarkdown;
    this.solutions[this.currentLanguage].markdown = newMarkdown;
  }

  @Watch('currentLanguage')
  onCurrentLanguageChange(newLanguage: string, oldLanguage: string): void {
    this.solutions[oldLanguage].markdown = this.currentMarkdown;
    this.$emit('update-markdown-contents', this.solutions, newLanguage, this.currentMarkdown);
    if (!this.solutions[newLanguage].searched) {
      this.solutions[newLanguage].searched = true;
    }
  }

  handleEditSolution(): void {
    this.solutions[this.currentLanguage].markdown = this.currentMarkdown;
    this.$emit('edit-solution', this.solutions, this.commitMessage);
    this.solutions = {
      'en': {
        'searched': false,
        'markdown': '',
      },
      'es': {
        'searched': true,
        'markdown': '',
      },
      'pt': {
        'searched': false,
        'markdown': '',
      }
    }
    this.commitMessage = '';
    this.currentMarkdown = '';
    this.currentLanguage = 'es';
  }
}
</script>
