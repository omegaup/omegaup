<template>
  <div class="panel panel-primary problem-form">
    <div class="panel-heading" v-if="!isUpdate">
      <h3 class="panel-title">
        {{ T.problemNew }}
      </h3>
    </div>
    <div class="page-header text-center top-margin">
      <p class="no-bottom-margin">
        {{ T.problemEditFormFirstTimeCreatingAProblem }}
        <strong>
          <a v-bind:href="howToWriteProblemLink" target="_blank">
            {{ T.problemEditFormHereIsHowToWriteProblems }}
          </a>
        </strong>
      </p>
    </div>
    <div class="panel-body">
      <form
        method="POST"
        v-bind:action="requestURI"
        class="form"
        enctype="multipart/form-data"
        v-on:submit="onSubmit"
      >
        <input
          type="hidden"
          name="problem_alias"
          v-bind:value="problemAlias"
          v-if="isUpdate"
        />

        <div class="row">
          <div
            class="form-group  col-md-6"
            v-bind:class="{ 'has-error': errors.includes('title') }"
          >
            <label class="control-label">{{ T.wordsTitle }}</label>
            <input
              name="title"
              v-model="title"
              type="text"
              class="form-control"
              v-on:blur="onGenerateAlias"
            />
          </div>

          <div
            class="form-group  col-md-6"
            v-bind:class="{ 'has-error': errors.includes('alias') }"
          >
            <label class="control-label">{{ T.wordsAlias }}</label>
            <input
              name="alias"
              v-model="alias"
              ref="alias"
              type="text"
              class="form-control"
              v-bind:disabled="isUpdate"
            />
          </div>
        </div>

        <omegaup-problem-settings
          v-bind:timeLimit="timeLimit"
          v-bind:extraWallTime="extraWallTime"
          v-bind:memoryLimit="memoryLimit"
          v-bind:outputLimit="outputLimit"
          v-bind:inputLimit="inputLimit"
          v-bind:initialValidator="validator"
          v-bind:initialLanguage="languages"
          v-bind:overallWallTimeLimit="overallWallTimeLimit"
          v-bind:validatorTimeLimit="validatorTimeLimit"
          v-bind:validLanguages="validLanguages"
          v-bind:validatorTypes="validatorTypes"
        ></omegaup-problem-settings>

        <div class="row">
          <div class="form-group col-md-4">
            <label>{{ T.problemEditEmailClarifications }}</label>
            <div class="form-control">
              <label class="radio-inline">
                <input
                  type="radio"
                  name="email_clarifications"
                  v-bind:value="true"
                  v-model="emailClarifications"
                />
                {{ T.wordsYes }}
              </label>
              <label class="radio-inline">
                <input
                  type="radio"
                  name="email_clarifications"
                  v-bind:value="false"
                  v-model="emailClarifications"
                />
                {{ T.wordsNo }}
              </label>
            </div>
          </div>

          <div class="form-group col-md-4">
            <label>{{ T.problemEditFormAppearsAsPublic }}</label>
            <div class="form-control">
              <label class="radio-inline">
                <input
                  type="radio"
                  name="visibility"
                  v-bind:value="1"
                  v-model="visibility"
                />
                {{ T.wordsYes }}
              </label>
              <label class="radio-inline">
                <input
                  type="radio"
                  name="visibility"
                  v-bind:value="0"
                  v-model="visibility"
                />
                {{ T.wordsNo }}
              </label>
            </div>
          </div>

          <div class="form-group col-md-4">
            <label>{{ T.problemEditFormAllowUserAddTags }}</label>
            <div class="form-control">
              <label class="radio-inline">
                <input
                  type="radio"
                  name="allow_user_add_tags"
                  v-bind:value="true"
                  v-model="allowUserAddTags"
                />
                {{ T.wordsYes }}
              </label>
              <label class="radio-inline">
                <input
                  type="radio"
                  name="allow_user_add_tags"
                  v-bind:value="false"
                  v-model="allowUserAddTags"
                />
                {{ T.wordsNo }}
              </label>
            </div>
          </div>
        </div>

        <div class="row">
          <div
            class="form-group  col-md-6"
            v-bind:class="{ 'has-error': errors.includes('source') }"
          >
            <label class="control-label">{{ T.problemEditSource }}</label>
            <input
              name="source"
              v-model="source"
              type="text"
              class="form-control"
            />
          </div>

          <div
            class="form-group col-md-6"
            v-bind:class="{ 'has-error': errors.includes('file') }"
          >
            <label class="control-label">{{ T.problemEditFormFile }}</label>
            <a v-bind:href="howToWriteProblemLink" target="_blank">
              <span>{{ T.problemEditFormHowToWriteProblems }}</span>
            </a>
            <input
              name="problem_contents"
              type="file"
              class="form-control"
              v-on:change="onUploadFile"
            />
          </div>
        </div>

        <div class="panel panel-primary" v-if="!isUpdate">
          <div class="panel-body">
            <div class="form-group">
              <label>{{ T.wordsTags }}</label>
            </div>
            <div class="form-group">
              <div class="tag-list pull-left">
                <a
                  class="tag pull-left"
                  href="#tags"
                  v-bind:data-key="tag.name"
                  v-for="tag in tags"
                  v-on:click="onAddTag(tag.name)"
                >
                  {{ T.hasOwnProperty(tag.name) ? T[tag.name] : tag.name }}
                </a>
              </div>
            </div>
            <div class="form-group">
              <label>{{ T.problemEditTagPublic }}</label>
              <select class="form-control" v-model="public">
                <option v-bind:value="false" selected="selected">
                  {{ T.wordsNo }}
                </option>
                <option v-bind:value="true">{{ T.wordsYes }}</option>
              </select>
            </div>
          </div>

          <table class="table table-striped">
            <thead>
              <tr>
                <th>{{ T.contestEditTagName }}</th>
                <th>{{ T.contestEditTagPublic }}</th>
                <th>{{ T.contestEditTagDelete }}</th>
              </tr>
            </thead>
            <tbody class="problem-tags">
              <tr v-for="selectedTag in selectedTags">
                <td class="tag-name">
                  <a
                    v-bind:data-key="selectedTag.tagname"
                    v-bind:href="`/problem/?tag[]=${selectedTag.tagname}`"
                  >
                    {{
                      T.hasOwnProperty(selectedTag.tagname)
                        ? T[selectedTag.tagname]
                        : selectedTag.tagname
                    }}
                  </a>
                </td>
                <td class="is_public">
                  {{ public ? T.wordsYes : T.wordsNo }}
                </td>
                <td>
                  <button
                    type="button"
                    class="close"
                    v-on:click="onRemoveTag(selectedTag.tagname)"
                  >
                    &times;
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
          <input
            type="hidden"
            name="selected_tags"
            v-bind:value="selectedTagsList"
          />
        </div>

        <div class="row" v-else="">
          <div
            class="form-group  col-md-12"
            v-bind:class="{ 'has-error': errors.includes('message') }"
          >
            <label class="control-label">{{
              T.problemEditCommitMessage
            }}</label>
            <input class="form-control" name="message" type="text" />
          </div>
        </div>

        <input name="request" value="submit" type="hidden" />

        <div class="row">
          <div class="form-group col-md-6 no-bottom-margin">
            <button type="submit" class="btn btn-primary">
              {{ buttonText }}
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>
</template>

<style>
.problem-form .languages {
  padding: 0;
  width: 100%;
}
</style>

<script lang="ts">
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';
import problem_Settings from './Settings.vue';
import T from '../../lang';
import * as ui from '../../ui';
import latinize from 'latinize';

interface SelectedTag {
  tagname: string;
  public: boolean;
}

interface Tag {
  name: string;
}

@Component({
  components: {
    'omegaup-problem-settings': problem_Settings,
  },
})
export default class ProblemForm extends Vue {
  @Prop() isUpdate!: boolean;
  @Prop() requestURI!: string;
  @Prop() problemAlias!: string;
  @Prop() initialTitle!: string;
  @Prop() initialAlias!: string;
  @Prop() initialTimeLimit!: string;
  @Prop() initialExtraWallTime!: number;
  @Prop() initialMemoryLimit!: number;
  @Prop() initialOutputLimit!: number;
  @Prop() initialInputLimit!: number;
  @Prop() initialOverallWallTimeLimit!: number;
  @Prop() initialValidatorTimeLimit!: number;
  @Prop() validLanguages!: Array<string>;
  @Prop() validatorTypes!: Array<string>;
  @Prop() initialEmailClarifications!: boolean;
  @Prop() initialVisibility!: number;
  @Prop() initialAllowUserAddTags!: boolean;
  @Prop() initialSource!: string;
  @Prop() initialValidator!: string;
  @Prop() initialLanguages!: string;
  @Prop() initialMessage!: string;
  @Prop() initialTags!: Tag[];
  @Prop() initialSelectedTags!: SelectedTag[];

  T = T;
  title = this.initialTitle;
  alias = this.initialAlias;
  timeLimit = this.initialTimeLimit;
  extraWallTime = this.initialExtraWallTime;
  memoryLimit = this.initialMemoryLimit;
  outputLimit = this.initialOutputLimit;
  inputLimit = this.initialInputLimit;
  overallWallTimeLimit = this.initialOverallWallTimeLimit;
  validatorTimeLimit = this.initialValidatorTimeLimit;
  emailClarifications = this.initialEmailClarifications;
  visibility = this.initialVisibility;
  allowUserAddTags = this.initialAllowUserAddTags;
  source = this.initialSource;
  validator = this.initialValidator;
  languages = this.initialLanguages;
  tags = this.initialTags;
  selectedTags = this.initialSelectedTags;
  message = this.initialMessage;
  hasFile = false;
  public = false;
  errors: string[] = [];

  get howToWriteProblemLink(): string {
    return 'https://github.com/omegaup/omegaup/wiki/C%C3%B3mo-escribir-problemas-para-Omegaup';
  }

  get buttonText(): string {
    if (this.isUpdate) {
      return T.problemEditFormUpdateProblem;
    }
    return T.problemEditFormCreateProblem;
  }

  get selectedTagsList(): string {
    return JSON.stringify(this.selectedTags);
  }

  onSubmit(e: Event): void {
    this.errors = [];
    if (this.isUpdate && this.message) {
      return;
    }
    if (this.title && this.alias && this.source && this.hasFile) {
      return;
    }
    ui.error(T.editFieldRequired);
    if (!this.title) {
      this.errors.push('title');
    }
    if (!this.alias) {
      this.errors.push('alias');
    }
    if (!this.source) {
      this.errors.push('source');
    }
    if (!this.hasFile) {
      this.errors.push('file');
    }
    if (this.isUpdate && !this.message) {
      this.errors.push('message');
    }
    e.preventDefault();
  }

  onUploadFile(ev: InputEvent): void {
    const uploadedFile = <HTMLInputElement>ev.target;
    if (uploadedFile) {
      this.hasFile = true;
    }
  }

  onAddTag(tagname: string): boolean {
    this.selectedTags.push({ tagname: tagname, public: this.public });
    this.tags = this.tags.filter((val, index, arr) => val.name !== tagname);
    return false; // Prevent refresh
  }

  onRemoveTag(tagname: string): void {
    this.tags.push({ name: tagname });
    this.selectedTags = this.selectedTags.filter(
      (val, index, arr) => val.tagname !== tagname,
    );
  }

  onGenerateAlias(): void {
    // Remove accents
    let generatedAlias = latinize(this.title);

    // Replace whitespace
    generatedAlias = generatedAlias.replace(/\s+/g, '-');

    // Remove invalid characters
    generatedAlias = generatedAlias.replace(/[^a-zA-Z0-9_-]/g, '');

    generatedAlias = generatedAlias.substring(0, 32);

    this.alias = generatedAlias;
  }

  @Watch('alias')
  onValueChanged(newValue: string): void {
    this.$emit('alias-in-use', newValue);
  }
}
</script>
