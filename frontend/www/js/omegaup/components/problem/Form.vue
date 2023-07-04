<template>
  <div class="card problem-form">
    <div v-if="!isUpdate" class="card-header">
      <h3 class="card-title mb-0">
        {{ T.problemNew }}
      </h3>
    </div>
    <div class="text-center">
      <p class="mt-3 mb-0">
        {{ T.problemEditFormFirstTimeCreatingAProblem }}
        <strong>
          <a :href="howToWriteProblemLink" target="_blank">
            {{ T.problemEditFormHereIsHowToWriteProblems }}
          </a>
        </strong>
      </p>
    </div>
    <div class="card-body px-2 px-sm-4">
      <form ref="form" method="POST" class="form" enctype="multipart/form-data">
        <div class="accordion mb-3">
          <div class="card">
            <div class="card-header">
              <h2 class="mb-0">
                <button
                  ref="basic-info"
                  class="btn btn-link btn-block text-left"
                  type="button"
                  data-toggle="collapse"
                  data-target=".basic-info"
                  aria-expanded="true"
                  aria-controls="problem-form-problem"
                >
                  {{ T.problemEditBasicInfo }}
                </button>
              </h2>
            </div>
            <div class="collapse show card-body px-2 px-sm-4 basic-info">
              <div class="row">
                <div class="form-group col-md-6">
                  <label class="control-label">{{ T.wordsTitle }}</label>
                  <input
                    v-model="title"
                    required
                    name="title"
                    type="text"
                    class="form-control"
                    :class="{ 'is-invalid': errors.includes('title') }"
                    @blur="onGenerateAlias"
                  />
                </div>
                <div class="form-group col-md-6">
                  <label class="control-label">{{ T.wordsAlias }}</label>
                  <input
                    ref="alias"
                    v-model="alias"
                    required
                    name="problem_alias"
                    type="text"
                    class="form-control"
                    :class="{
                      'is-invalid': errors.includes('problem_alias'),
                    }"
                    :disabled="isUpdate"
                  />
                </div>
              </div>
              <div class="row">
                <div class="form-group col-md-6">
                  <label class="control-label">{{ T.problemEditSource }}</label>
                  <input
                    v-model="source"
                    required
                    name="source"
                    type="text"
                    class="form-control"
                    :class="{ 'is-invalid': errors.includes('source') }"
                  />
                </div>
                <div class="form-group col-md-6">
                  <label class="control-label">{{
                    T.problemEditFormFile
                  }}</label>
                  <input
                    :required="!isUpdate"
                    name="problem_contents"
                    type="file"
                    class="form-control"
                    :class="{
                      'is-invalid': errors.includes('problem_contents'),
                    }"
                    @change="onUploadFile"
                  />
                </div>
              </div>
            </div>
          </div>

          <template v-if="!isUpdate">
            <div class="card">
              <div class="card-header">
                <h2 class="mb-0">
                  <button
                    ref="tags"
                    class="btn btn-link btn-block text-left"
                    type="button"
                    data-toggle="collapse"
                    data-target=".tags"
                    aria-expanded="true"
                    aria-controls="problem-form-problem"
                  >
                    {{ T.problemEditTags }}
                  </button>
                </h2>
              </div>
              <div class="collapse show card-body px-2 px-sm-4 tags">
                <div
                  v-show="selectedTags.length === 0"
                  class="alert alert-info"
                >
                  {{ T.problemEditTagPublicRequired }}
                </div>
                <omegaup-problem-tags
                  :public-tags="data.publicTags"
                  :level-tags="data.levelTags"
                  :alias="data.alias"
                  :is-create="true"
                  :problem-level="problemLevel"
                  :selected-private-tags="selectedPrivateTags"
                  :selected-public-tags="selectedPublicTags"
                  :can-add-new-tags="true"
                  :errors="errors"
                  @emit-add-tag="addTag"
                  @emit-remove-tag="removeTag"
                  @select-problem-level="selectProblemLevel"
                ></omegaup-problem-tags>
                <input
                  name="selected_tags"
                  :value="selectedTagsList"
                  type="hidden"
                />
                <input
                  name="problem_level"
                  :value="problemLevel"
                  type="hidden"
                />
              </div>
            </div>
          </template>
          <div class="card">
            <div class="card-header">
              <h2 class="mb-0">
                <button
                  class="btn btn-link btn-block text-left collapsed"
                  type="button"
                  data-toggle="collapse"
                  data-target=".validation"
                  aria-expanded="true"
                  aria-controls="problem-form-problem"
                >
                  {{ T.problemEditValidation }}
                </button>
              </h2>
            </div>
            <div class="card-body px-2 px-sm-4 validation">
              <div class="row">
                <div class="form-group col-md-6">
                  <label>{{ T.problemEditFormLanguages }}</label>
                  <select
                    v-model="currentLanguages"
                    name="languages"
                    class="form-control"
                    :class="{ 'is-invalid': errors.includes('languages') }"
                    required
                  >
                    <option
                      v-for="(languageText, languageName) in validLanguages"
                      :key="languageName"
                      :value="languageName"
                    >
                      {{ languageText }}
                    </option>
                  </select>
                </div>
                <div class="form-group col-md-6">
                  <label>{{ T.problemEditFormValidatorType }}</label>
                  <select
                    v-model="validator"
                    name="validator"
                    class="form-control"
                    :class="{ 'is-invalid': errors.includes('validator') }"
                    :disabled="currentLanguages === ''"
                    required
                  >
                    <option
                      v-for="(validatorText, validatorIndex) in validatorTypes"
                      :key="validatorIndex"
                      :value="validatorIndex"
                    >
                      {{ validatorText }}
                    </option>
                  </select>
                </div>
              </div>
            </div>
          </div>
          <div class="card">
            <div class="card-header">
              <h2 class="mb-0">
                <button
                  ref="limits"
                  class="btn btn-link btn-block text-left collapsed"
                  type="button"
                  data-toggle="collapse"
                  data-target=".limits"
                  aria-expanded="true"
                  aria-controls="problem-form-problem"
                >
                  {{ T.problemEditLimits }}
                </button>
              </h2>
            </div>
            <div class="collapse card-body px-2 px-sm-4 limits">
              <omegaup-problem-settings
                :errors="errors"
                :current-languages="currentLanguages"
                :time-limit="timeLimit"
                :extra-wall-time="extraWallTime"
                :memory-limit="memoryLimit"
                :output-limit="outputLimit"
                :input-limit="inputLimit"
                :overall-wall-time-limit="overallWallTimeLimit"
                :validator="validator"
                :validator-time-limit="validatorTimeLimit"
              ></omegaup-problem-settings>
            </div>
          </div>
          <div class="card">
            <div class="card-header">
              <h2 class="mb-0">
                <button
                  class="btn btn-link btn-block text-left collapsed"
                  type="button"
                  data-toggle="collapse"
                  data-target=".access"
                  aria-expanded="true"
                  aria-controls="problem-form-problem"
                >
                  {{ T.problemEditAccess }}
                </button>
              </h2>
            </div>
            <div class="collapse card-body px-2 px-sm-4 access">
              <div class="row">
                <div class="form-group col-md-6">
                  <label>{{ T.problemEditEmailClarifications }}</label>
                  <div class="form-control">
                    <div class="form-check form-check-inline">
                      <input
                        v-model="emailClarifications"
                        type="radio"
                        name="email_clarifications"
                        class="form-check-input"
                        :value="true"
                      />
                      <label class="form-check-label">
                        {{ T.wordsYes }}
                      </label>
                    </div>
                    <div class="form-check form-check-inline">
                      <input
                        v-model="emailClarifications"
                        type="radio"
                        name="email_clarifications"
                        class="form-check-input"
                        :value="false"
                      />
                      <label class="form-check-label">
                        {{ T.wordsNo }}
                      </label>
                    </div>
                  </div>
                </div>
                <div class="form-group col-md-6">
                  <label>{{ T.problemEditFormAppearsAsPublic }}</label>
                  <div class="form-control">
                    <label class="form-check form-check-inline">
                      <input
                        v-model="isPublic"
                        type="radio"
                        name="visibility"
                        class="form-check-input"
                        :disabled="!isEditable"
                        :value="true"
                      />
                      <span class="form-check-label">{{ T.wordsYes }}</span>
                    </label>
                    <label class="form-check form-check-inline">
                      <input
                        v-model="isPublic"
                        type="radio"
                        name="visibility"
                        class="form-check-input"
                        :disabled="!isEditable"
                        :value="false"
                      />
                      <span class="form-check-label">{{ T.wordsNo }}</span>
                    </label>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <template v-if="!isUpdate">
            <div class="card">
              <div class="card-header">
                <h2 class="mb-0">
                  <button
                    class="btn btn-link btn-block text-left collapsed"
                    type="button"
                    data-toggle="collapse"
                    data-target=".evaluation"
                    aria-expanded="true"
                    aria-controls="problem-form-problem"
                  >
                    {{ T.problemEditEvaluation }}
                  </button>
                </h2>
              </div>
              <div class="collapse card-body px-2 px-sm-4 evaluation">
                <div class="row">
                  <div class="form-group col-md-6">
                    <label>{{ T.wordsShowCasesDiff }}</label>
                    <select
                      v-model="showDiff"
                      name="show_diff"
                      class="form-control"
                      :class="{ 'is-invalid': errors.includes('show_diff') }"
                      :disabled="languages === ''"
                    >
                      <option value="none">
                        {{ T.problemVersionDiffModeNone }}
                      </option>
                      <option value="examples">
                        {{ T.wordsOnlyExamples }}
                      </option>
                      <option value="all">{{ T.wordsAll }}</option>
                    </select>
                  </div>
                  <div class="form-group col-md-6">
                    <label>{{ T.problemEditGroupScorePolicy }}</label>
                    <select
                      v-model="groupScorePolicy"
                      name="group_score_policy"
                      class="form-control"
                      :class="{
                        'is-invalid': errors.includes('group_score_policy'),
                      }"
                      :disabled="languages === ''"
                    >
                      <option value="sum-if-not-zero">
                        {{ T.problemEditGroupScorePolicySumIfNotZero }}
                      </option>
                      <option value="min">
                        {{ T.problemEditGroupScorePolicyMin }}
                      </option>
                    </select>
                  </div>
                </div>
              </div>
            </div>
          </template>
        </div>
        <template v-if="isUpdate">
          <div class="mt-8 row">
            <div class="form-group col-md-4">
              <label>{{ T.wordsShowCasesDiff }}</label>
              <select
                v-model="showDiff"
                name="show_diff"
                class="form-control"
                :class="{ 'is-invalid': errors.includes('show_diff') }"
              >
                <option value="none">{{ T.problemVersionDiffModeNone }}</option>
                <option value="examples">{{ T.wordsOnlyExamples }}</option>
                <option value="all">{{ T.wordsAll }}</option>
              </select>
            </div>
            <div class="form-group col-md-4">
              <label>{{ T.problemEditGroupScorePolicy }}</label>
              <select
                v-model="groupScorePolicy"
                name="group_score_policy"
                class="form-control"
                :class="{ 'is-invalid': errors.includes('group_score_policy') }"
                :disabled="languages === ''"
              >
                <option value="sum-if-not-zero">
                  {{ T.problemEditGroupScorePolicySumIfNotZero }}
                </option>
                <option value="min">
                  {{ T.problemEditGroupScorePolicyMin }}
                </option>
              </select>
            </div>
            <div class="form-group col-md-4">
              <label class="control-label">{{
                T.problemEditCommitMessage
              }}</label>
              <input
                v-model="message"
                required
                class="form-control"
                :class="{ 'is-invalid': errors.includes('message') }"
                name="message"
                type="text"
              />
            </div>
          </div>
        </template>
        <input
          v-if="isEditable"
          type="hidden"
          name="visibility"
          :value="visibility"
        />
        <input name="request" value="submit" type="hidden" />
        <input name="update_published" value="non-problemset" type="hidden" />
        <div class="row">
          <div class="form-group col-md-6 no-bottom-margin">
            <button
              type="submit"
              class="btn btn-primary"
              :title="
                !problemLevel && !isUpdate ? T.selectProblemLevelDesc : ''
              "
              @click="openCollapsedIfRequired()"
            >
              {{ buttonText }}
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop, Watch, Ref } from 'vue-property-decorator';
import problem_Settings from './Settings.vue';
import problem_Tags from './Tags.vue';
import T from '../../lang';
import latinize from 'latinize';
import { types } from '../../api_types';

@Component({
  components: {
    'omegaup-problem-settings': problem_Settings,
    'omegaup-problem-tags': problem_Tags,
  },
})
export default class ProblemForm extends Vue {
  @Prop() data!: types.ProblemFormPayload;
  @Prop({ default: () => [] }) errors!: string[];
  @Prop({ default: false }) isUpdate!: boolean;
  @Prop({ default: 0 }) originalVisibility!: number;

  @Ref('basic-info') basicInfoRef!: HTMLDivElement;
  @Ref('tags') tagsRef!: HTMLDivElement;
  @Ref('limits') limitsRef!: HTMLDivElement;
  @Ref('form') formRef!: HTMLFormElement;

  T = T;
  title = this.data.title;
  alias = this.data.alias;
  timeLimit = this.data.timeLimit;
  extraWallTime = this.data.extraWallTime;
  memoryLimit = this.data.memoryLimit;
  outputLimit = this.data.outputLimit;
  inputLimit = this.data.inputLimit;
  overallWallTimeLimit = this.data.overallWallTimeLimit;
  validatorTimeLimit = this.data.validatorTimeLimit;
  emailClarifications = this.data.emailClarifications;
  visibility = this.data.visibility;
  allowUserAddTags = this.data.allowUserAddTags;
  source = this.data.source;
  validator = this.data.validator;
  languages = this.data.languages;
  tags = this.data.tags;
  problemLevel = this.data.problem_level || '';
  showDiff = this.data.showDiff;
  groupScorePolicy = this.data.groupScorePolicy || 'sum-if-not-zero';
  selectedTags = this.data.selectedTags || [];
  message = '';
  hasFile = false;
  public = false;
  validLanguages = this.data.validLanguages;
  validatorTypes = this.data.validatorTypes;
  currentLanguages = this.data.languages;

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

  get isPublic(): boolean {
    // when visibility is public warning, then the problem is shown as public
    return this.visibility > this.data.visibilityStatuses.private;
  }

  set isPublic(isPublic: boolean) {
    if (
      this.originalVisibility === this.data.visibilityStatuses.publicWarning ||
      this.originalVisibility === this.data.visibilityStatuses.privateWarning
    ) {
      this.visibility = isPublic
        ? this.data.visibilityStatuses.publicWarning
        : this.data.visibilityStatuses.privateWarning;
      return;
    }
    if (
      this.originalVisibility === this.data.visibilityStatuses.publicBanned ||
      this.originalVisibility === this.data.visibilityStatuses.privateBanned
    ) {
      this.visibility = isPublic
        ? this.data.visibilityStatuses.publicBanned
        : this.data.visibilityStatuses.privateBanned;
      return;
    }
    this.visibility = isPublic
      ? this.data.visibilityStatuses.public
      : this.data.visibilityStatuses.private;
  }

  get isEditable(): boolean {
    return (
      this.data.visibilityStatuses.publicBanned < this.visibility &&
      this.visibility < this.data.visibilityStatuses.promoted
    );
  }

  get selectedPublicTags(): string[] {
    return this.selectedTags
      .filter((tag) => tag.public === true)
      .map((tag) => tag.tagname);
  }

  get selectedPrivateTags(): string[] {
    return this.selectedTags
      .filter((tag) => tag.public === false)
      .map((tag) => tag.tagname);
  }

  addTag(alias: string, tagname: string, isPublic: boolean): void {
    this.selectedTags.push({
      tagname: tagname,
      public: isPublic,
    });
  }

  // eslint-disable-next-line @typescript-eslint/no-unused-vars
  removeTag(alias: string, tagname: string, isPublic: boolean): void {
    this.selectedTags = this.selectedTags.filter(
      (tag) => tag.tagname !== tagname,
    );
  }

  selectProblemLevel(levelTag: string): void {
    this.problemLevel = levelTag;
  }

  onUploadFile(ev: InputEvent): void {
    const uploadedFile = ev.target as HTMLInputElement;
    this.hasFile = uploadedFile.files !== null;
  }

  onGenerateAlias(): void {
    if (this.isUpdate) {
      return;
    }

    // Remove accents
    let generatedAlias = latinize(this.title);

    // Replace whitespace
    generatedAlias = generatedAlias.replace(/\s+/g, '-');

    // Remove invalid characters
    generatedAlias = generatedAlias.replace(/[^a-zA-Z0-9_-]/g, '');

    generatedAlias = generatedAlias.substring(0, 32);

    this.alias = generatedAlias;
  }

  openCollapsedIfRequired() {
    const formData = new FormData(this.formRef);

    let basicInfoCollapsed = this.basicInfoRef.classList.contains('collapsed');
    let limitsCollapsed = this.limitsRef.classList.contains('collapsed');
    let tagsCollapsed = !this.isUpdate
      ? this.tagsRef.classList.contains('collapsed')
      : false;

    for (const [key, value] of formData.entries()) {
      const isEmpty = value === '';
      if (isEmpty) {
        if (
          basicInfoCollapsed &&
          (key === 'title' || key === 'alias' || key === 'source')
        ) {
          this.basicInfoRef.click();
          basicInfoCollapsed = false;
          continue;
        }
        // To avoid making a complex logic check
        if (basicInfoCollapsed && !this.isUpdate && !this.hasFile) {
          this.basicInfoRef.click();
          basicInfoCollapsed = false;
          continue;
        }

        if (tagsCollapsed && key === 'problem_level') {
          this.tagsRef.click();
          tagsCollapsed = false;
          continue;
        }
        if (
          limitsCollapsed &&
          (key === 'time_limit' ||
            key === 'overall_wall_time_limit' ||
            key === 'extra_wall_time' ||
            key === 'memory_limit' ||
            key === 'output_limit' ||
            key === 'input_limit')
        ) {
          this.limitsRef.click();
          limitsCollapsed = false;
          continue;
        }
      }
    }
  }

  @Watch('alias')
  onValueChanged(newValue: string): void {
    if (this.isUpdate) {
      return;
    }
    this.$emit('alias-changed', newValue);
  }
}
</script>

<style>
.problem-form .languages {
  padding: 0;
  width: 100%;
}
</style>
