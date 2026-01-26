<template>
  <div class="card">
    <div v-if="!update" class="card-header bg-light text-dark panel-heading">
      <h3 class="card-title mb-0">{{ T.contestNew }}</h3>
    </div>
    <div class="card-body px-2 px-sm-4">
      <!-- Style Presets with Confirmation -->
      <div class="btn-group d-block mb-3 text-center introjs-style">
        <button class="btn btn-secondary" data-contest-omi @click="confirmPresetChange('omi')">
          {{ T.contestNewFormOmiStyle }}
        </button>
        <button class="btn btn-secondary" data-contest-preioi @click="confirmPresetChange('preioi')">
          {{ T.contestNewForm }}
        </button>
        <button class="btn btn-secondary" data-contest-conacup @click="confirmPresetChange('conacup')">
          {{ T.contestNewFormConacupStyle }}
        </button>
        <button class="btn btn-secondary" data-contest-icpc @click="confirmPresetChange('icpc')">
          {{ T.contestNewFormICPCStyle }}
        </button>
      </div>

      <!-- Validation Summary -->
      <div v-if="validationErrors.length > 0" class="alert alert-danger" role="alert">
        <strong>Please fix the following errors:</strong>
        <ul class="mb-0 mt-2">
          <li v-for="error in validationErrors" :key="error">{{ error }}</li>
        </ul>
      </div>

      <form class="contest-form" @submit.prevent="onSubmit" novalidate>
        <div class="accordion mb-3">
          <!-- Basic Info Section -->
          <div class="card">
            <div class="card-header">
              <h2 class="mb-0">
                <button
                  ref="basicInfo"
                  class="btn btn-link btn-block text-left"
                  type="button"
                  data-toggle="collapse"
                  data-target=".basic-info"
                  aria-expanded="true"
                  aria-controls="basic-info-collapse"
                >
                  {{ T.contestNewFormBasicInfo }}
                  <span v-if="hasErrorsInSection('basic')" class="text-danger ml-2">⚠</span>
                </button>
              </h2>
            </div>
            <div id="basic-info-collapse" class="collapse show card-body basic-info">
              <div class="row">
                <div class="form-group col-md-6">
                  <label for="contest-title">{{ T.wordsTitle }} <span class="text-danger">*</span></label>
                  <input
                    id="contest-title"
                    v-model="title"
                    class="form-control introjs-contest-title"
                    :class="{ 'is-invalid': invalidParameterName === 'title' || localErrors.title }"
                    name="title"
                    data-title
                    :placeholder="titlePlaceHolder"
                    type="text"
                    required
                    @blur="validateField('title')"
                    @input="clearFieldError('title')"
                  />
                  <div v-if="invalidParameterName === 'title' || localErrors.title" class="invalid-feedback d-block">
                    {{ localErrors.title || T.contestNewFormTitleRequired }}
                  </div>
                </div>
                <div class="form-group col-md-6">
                  <label for="contest-alias">
                    {{ T.contestNewFormShortTitleAlias }} <span class="text-danger">*</span>
                    <font-awesome-icon :title="T.contestNewFormShortTitleAliasDesc" icon="info-circle" />
                  </label>
                  <input
                    id="contest-alias"
                    v-model="alias"
                    class="form-control introjs-short-title"
                    :class="{ 'is-invalid': invalidParameterName === 'alias' || localErrors.alias }"
                    name="alias"
                    :disabled="update"
                    type="text"
                    required
                    @blur="validateField('alias')"
                    @input="clearFieldError('alias')"
                  />
                  <div v-if="invalidParameterName === 'alias' || localErrors.alias" class="invalid-feedback d-block">
                    {{ localErrors.alias || T.contestNewFormShortTitleRequired }}
                  </div>
                  <small v-if="!update" class="form-text text-muted">
                    Only lowercase letters, numbers, and hyphens. Cannot be changed later.
                  </small>
                </div>
              </div>
              <div class="row">
                <div class="form-group col-md-6">
                  <label for="start-date">
                    {{ T.contestNewFormStartDate }} <span class="text-danger">*</span>
                    <font-awesome-icon :title="T.contestNewFormStartDateDesc" icon="info-circle" />
                  </label>
                  <omegaup-datetimepicker
                    id="start-date"
                    v-model="startTime"
                    data-start-date
                    :start="minDateTimeForContest"
                    @input="validateDates"
                  ></omegaup-datetimepicker>
                </div>
                <div class="form-group col-md-6">
                  <label for="end-date">
                    {{ T.contestNewFormEndDate }} <span class="text-danger">*</span>
                    <font-awesome-icon :title="T.contestNewFormEndDateDesc" icon="info-circle" />
                  </label>
                  <omegaup-datetimepicker
                    id="end-date"
                    v-model="finishTime"
                    data-end-date
                    :is-invalid="invalidParameterName === 'finish_time' || localErrors.finishTime"
                    @input="validateDates"
                  ></omegaup-datetimepicker>
                  <div v-if="localErrors.finishTime" class="invalid-feedback d-block">
                    {{ localErrors.finishTime }}
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="form-group col-md-6 introjs-description">
                  <label for="description">{{ T.contestNewFormDescription }} <span class="text-danger">*</span></label>
                  <textarea
                    id="description"
                    v-model="description"
                    class="form-control"
                    :class="{ 'is-invalid': invalidParameterName === 'description' || localErrors.description }"
                    data-description
                    name="description"
                    rows="10"
                    required
                    @blur="validateField('description')"
                    @input="clearFieldError('description')"
                  ></textarea>
                  <div v-if="invalidParameterName === 'description' || localErrors.description" class="invalid-feedback d-block">
                    {{ localErrors.description || T.contestNewFormDescriptionRequired }}
                  </div>
                </div>
                <div class="form-group col-md-6">
                  <label for="languages">{{ T.wordsLanguages }} <span class="text-danger">*</span></label>
                  <multiselect
                    id="languages"
                    :value="languages"
                    :options="Object.keys(allLanguages)"
                    :multiple="true"
                    :placeholder="T.contestNewFormLanguages"
                    :close-on-select="false"
                    :allow-empty="false"
                    @remove="onRemove"
                    @select="onSelect"
                  ></multiselect>
                  <small class="form-text text-muted">
                    At least one language must be selected
                  </small>
                </div>
              </div>
            </div>
          </div>

          <!-- Logistics Section -->
          <div class="card">
            <div class="card-header">
              <h2 class="mb-0">
                <button
                  ref="logistics"
                  class="btn btn-link btn-block text-left collapsed"
                  type="button"
                  data-toggle="collapse"
                  data-target=".logistics"
                  aria-expanded="false"
                  aria-controls="logistics-collapse"
                  @click.prevent
                >
                  {{ T.contestNewFormLogistics }}
                  <span v-if="hasErrorsInSection('logistics')" class="text-danger ml-2">⚠</span>
                </button>
              </h2>
            </div>
            <div id="logistics-collapse" class="collapse card-body logistics">
              <div class="row">
                <div class="form-group col-md-6">
                  <label>
                    {{ T.contestNewFormDifferentStarts }}
                    <font-awesome-icon :title="T.contestNewFormDifferentStartsDesc" icon="info-circle" />
                  </label>
                  <div class="checkbox">
                    <label>
                      <input
                        v-model="windowLengthEnabled"
                        data-different-start-check
                        type="checkbox"
                      />
                      {{ T.wordsEnable }}
                    </label>
                  </div>
                  <input
                    v-model="windowLength"
                    class="form-control"
                    data-different-start-time-input
                    :class="{ 'is-invalid': invalidParameterName === 'window_length' || localErrors.windowLength }"
                    name="window_length"
                    type="number"
                    min="0"
                    :disabled="!windowLengthEnabled"
                    placeholder="Minutes"
                    @blur="validateField('windowLength')"
                  />
                  <div v-if="localErrors.windowLength" class="invalid-feedback d-block">
                    {{ localErrors.windowLength }}
                  </div>
                </div>
                <div class="form-group col-md-6">
                  <label>
                    {{ T.contestNewFormForTeams }}
                    <font-awesome-icon :title="T.contestNewFormForTeamsDesc" icon="info-circle" />
                  </label>
                  <div class="checkbox">
                    <label>
                      <input
                        v-model="currentContestForTeams"
                        data-contest-for-teams
                        type="checkbox"
                        :disabled="update"
                      />
                      {{ T.wordsEnable }}
                    </label>
                  </div>

                  <omegaup-common-typeahead
                    v-if="currentContestForTeams && !hasSubmissions"
                    :class="{ 'is-invalid': invalidParameterName === 'teams_group_alias' || localErrors.teamsGroup }"
                    :existing-options="searchResultTeamsGroups"
                    :options="searchResultTeamsGroups"
                    :value.sync="currentTeamsGroupAlias"
                    @update-existing-options="updateTeamsGroups"
                  ></omegaup-common-typeahead>
                  <input
                    v-else-if="currentContestForTeams"
                    class="form-control"
                    disabled
                    :value="teamsGroupName"
                  />
                  <small v-if="hasSubmissions && currentContestForTeams" class="form-text text-muted">
                    Team group cannot be changed after submissions have been made
                  </small>
                </div>
              </div>
              <div class="row">
                <div class="form-group col-md-6">
                  <label for="scoreboard-after">
                    {{ T.contestNewFormScoreboardAtEnd }}
                    <font-awesome-icon :title="T.contestNewFormScoreboardAtEndDesc" icon="info-circle" />
                  </label>
                  <select
                    id="scoreboard-after"
                    v-model="showScoreboardAfter"
                    data-show-scoreboard-at-end
                    class="form-control"
                  >
                    <option :value="true">{{ T.wordsYes }}</option>
                    <option :value="false">{{ T.wordsNo }}</option>
                  </select>
                </div>
                <div class="form-group col-md-6">
                  <label for="scoreboard-percent">
                    {{ T.contestNewFormScoreboardTimePercent }} <span class="text-danger">*</span>
                    <font-awesome-icon :title="T.contestNewFormScoreboardTimePercentDesc" icon="info-circle" />
                  </label>
                  <input
                    id="scoreboard-percent"
                    v-model.number="scoreboard"
                    data-score-board-visible-time
                    class="form-control scoreboard-time-percent"
                    :class="{ 'is-invalid': invalidParameterName === 'scoreboard' || localErrors.scoreboard }"
                    name="scoreboard"
                    type="number"
                    min="0"
                    max="100"
                    required
                    @blur="validateField('scoreboard')"
                  />
                  <div v-if="localErrors.scoreboard" class="invalid-feedback d-block">
                    {{ localErrors.scoreboard }}
                  </div>
                  <small class="form-text text-muted">0-100%</small>
                </div>
              </div>
              <div class="row">
                <div class="form-group col-md-6">
                  <label>
                    {{ T.contestNewFormRecommended }}
                    <font-awesome-icon
                      :title="canSetRecommended ? T.contestNewFormRecommendedTextAdmin : T.contestNewFormRecommendedTextNonAdmin"
                      icon="info-circle"
                    />
                  </label>
                  <div v-if="canSetRecommended" class="checkbox form-check">
                    <input
                      v-model="recommended"
                      data-recommended
                      class="form-check-input"
                      type="checkbox"
                    />
                    <label class="form-check-label">{{ T.wordsEnable }}</label>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Scoring Rules Section -->
          <div class="card">
            <div class="card-header">
              <h2 class="mb-0">
                <button
                  ref="scoringRules"
                  class="btn btn-link btn-block text-left collapsed"
                  type="button"
                  data-toggle="collapse"
                  data-target=".scoring-rules"
                  aria-expanded="false"
                >
                  {{ T.contestNewFormScoringRules }}
                  <span v-if="hasErrorsInSection('scoring')" class="text-danger ml-2">⚠</span>
                </button>
              </h2>
            </div>
            <div class="collapse card-body scoring-rules">
              <div class="row">
                <div class="form-group col-md-6">
                  <label for="score-mode">
                    {{ T.contestNewFormScoreMode }}
                    <font-awesome-icon :title="T.contestNewFormScoreModeDesc" icon="info-circle" />
                  </label>
                  <select
                    id="score-mode"
                    v-model="currentScoreMode"
                    data-score-mode
                    class="form-control"
                  >
                    <option :value="ScoreMode.Partial">{{ T.contestNewFormScoreModePartial }}</option>
                    <option :value="ScoreMode.AllOrNothing">{{ T.contestNewFormScoreModeAllOrNothing }}</option>
                    <option :value="ScoreMode.MaxPerGroup">{{ T.contestNewFormScoreModeMaxPerGroup }}</option>
                  </select>
                </div>
                <div class="form-group col-md-6">
                  <label for="feedback">
                    {{ T.wordsFeedback }}
                    <font-awesome-icon :title="T.contestNewFormImmediateFeedbackDesc" icon="info-circle" />
                  </label>
                  <select id="feedback" v-model="feedback" class="form-control">
                    <option value="none">{{ T.wordsNone }}</option>
                    <option value="summary">{{ T.wordsSummary }}</option>
                    <option value="detailed">{{ T.wordsDetailed }}</option>
                  </select>
                </div>
              </div>
              <div class="row">
                <div class="form-group col-md-6">
                  <label for="submissions-gap">
                    {{ T.contestNewFormSubmissionsSeparation }} <span class="text-danger">*</span>
                    <font-awesome-icon :title="T.contestNewFormSubmissionsSeparationDesc" icon="info-circle" />
                  </label>
                  <input
                    id="submissions-gap"
                    v-model.number="submissionsGap"
                    class="form-control"
                    :class="{ 'is-invalid': invalidParameterName === 'submissions_gap' || localErrors.submissionsGap }"
                    name="submissions_gap"
                    type="number"
                    min="0"
                    required
                    @blur="validateField('submissionsGap')"
                  />
                  <div v-if="localErrors.submissionsGap" class="invalid-feedback d-block">
                    {{ localErrors.submissionsGap }}
                  </div>
                  <small class="form-text text-muted">Minutes between submissions</small>
                </div>
                <div class="form-group col-md-6">
                  <label for="penalty-type">
                    {{ T.contestNewFormPenaltyType }}
                    <font-awesome-icon :title="T.contestNewFormPenaltyTypeDesc" icon="info-circle" />
                  </label>
                  <select id="penalty-type" v-model="penaltyType" class="form-control">
                    <option value="none">{{ T.contestNewFormNoPenalty }}</option>
                    <option value="problem_open">{{ T.contestNewFormByProblem }}</option>
                    <option value="contest_start">{{ T.contestNewFormByContests }}</option>
                    <option value="runtime">{{ T.contestNewFormByRuntime }}</option>
                  </select>
                </div>
              </div>
              <div class="row">
                <div class="form-group col-md-6">
                  <label for="penalty">
                    {{ T.wordsPenalty }} <span class="text-danger">*</span>
                    <font-awesome-icon :title="T.contestNewFormPenaltyDesc" icon="info-circle" />
                  </label>
                  <input
                    id="penalty"
                    v-model.number="penalty"
                    class="form-control"
                    :class="{ 'is-invalid': invalidParameterName === 'penalty' || localErrors.penalty }"
                    name="penalty"
                    type="number"
                    min="0"
                    required
                    @blur="validateField('penalty')"
                  />
                  <div v-if="localErrors.penalty" class="invalid-feedback d-block">
                    {{ localErrors.penalty }}
                  </div>
                </div>
                <div class="form-group col-md-6">
                  <label for="decay-factor">
                    {{ T.contestNewFormPointDecrementFactor }} <span class="text-danger">*</span>
                    <font-awesome-icon :title="T.contestNewFormPointDecrementFactorDesc" icon="info-circle" />
                  </label>
                  <input
                    id="decay-factor"
                    v-model.number="pointsDecayFactor"
                    class="form-control"
                    :class="{ 'is-invalid': invalidParameterName === 'points_decay_factor' || localErrors.pointsDecayFactor }"
                    name="points_decay_factor"
                    type="number"
                    min="0"
                    max="1"
                    step="0.01"
                    required
                    @blur="validateField('pointsDecayFactor')"
                  />
                  <div v-if="localErrors.pointsDecayFactor" class="invalid-feedback d-block">
                    {{ localErrors.pointsDecayFactor }}
                  </div>
                  <small class="form-text text-muted">0.0 to 1.0</small>
                </div>
              </div>
            </div>
          </div>

          <!-- Privacy Section -->
          <div class="card">
            <div class="card-header">
              <h2 class="mb-0">
                <button
                  ref="privacy"
                  class="btn btn-link btn-block text-left collapsed"
                  type="button"
                  data-toggle="collapse"
                  data-target=".privacy"
                  aria-expanded="false"
                >
                  {{ T.contestNewFormPrivacy }}
                </button>
              </h2>
            </div>
            <div class="collapse card-body privacy">
              <div class="row">
                <div class="form-group col-md-6">
                  <label>
                    {{ T.contestNewFormBasicInformationRequired }}
                    <font-awesome-icon :title="T.contestNewFormBasicInformationRequiredDesc" icon="info-circle" />
                  </label>
                  <div class="checkbox form-check">
                    <input
                      v-model="needsBasicInformation"
                      data-basic-information-required
                      class="form-check-input"
                      type="checkbox"
                    />
                    <label class="form-check-label">{{ T.wordsEnable }}</label>
                  </div>
                </div>
                <div class="form-group col-md-6">
                  <label for="user-info">
                    {{ T.contestNewFormUserInformationRequired }}
                    <font-awesome-icon :title="T.contestNewFormUserInformationRequiredDesc" icon="info-circle" />
                  </label>
                  <select
                    id="user-info"
                    v-model="requestsUserInformation"
                    data-request-user-information
                    class="form-control"
                  >
                    <option value="no">{{ T.wordsNo }}</option>
                    <option value="optional">{{ T.wordsOptional }}</option>
                    <option value="required">{{ T.wordsRequired }}</option>
                  </select>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="form-group">
          <button
            class="btn btn-primary introjs-schedule"
            type="submit"
            :disabled="isSubmitting"
          >
            <span v-if="isSubmitting">
              <span class="spinner-border spinner-border-sm mr-2" role="status" aria-hidden="true"></span>
              Processing...
            </span>
            <span v-else>
              {{ update ? T.contestNewFormUpdateContest : T.contestNewFormScheduleContest }}
            </span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';
import T from '../../lang';
import common_Typeahead from '../common/Typeahead.vue';
import DateTimePicker from '../DateTimePicker.vue';
import Multiselect from 'vue-multiselect';
import { types } from '../../api_types';
import 'intro.js/introjs.css';
import introJs from 'intro.js';
import VueCookies from 'vue-cookies';
import 'v-tooltip/dist/v-tooltip.css';
import { VTooltip } from 'v-tooltip';

import {
  FontAwesomeIcon,
  FontAwesomeLayers,
  FontAwesomeLayersText,
} from '@fortawesome/vue-fontawesome';
import { fas } from '@fortawesome/free-solid-svg-icons';
import { library } from '@fortawesome/fontawesome-svg-core';
library.add(fas);

Vue.use(VueCookies, { expire: -1 });

export enum ScoreMode {
  AllOrNothing = 'all_or_nothing',
  Partial = 'partial',
  MaxPerGroup = 'max_per_group',
}

interface LocalErrors {
  [key: string]: string;
}

@Component({
  components: {
    'omegaup-common-typeahead': common_Typeahead,
    'omegaup-datetimepicker': DateTimePicker,
    'font-awesome-icon': FontAwesomeIcon,
    'font-awesome-layers': FontAwesomeLayers,
    'font-awesome-layers-text': FontAwesomeLayersText,
    Multiselect,
  },
  directives: {
    tooltip: VTooltip,
  },
})
export default class Form extends Vue {
  @Prop() update!: boolean;
  @Prop() allLanguages!: string[];
  @Prop({ default: 'private' }) admissionMode!: string;
  @Prop({ default: false }) defaultShowAllContestantsInScoreboard!: boolean;
  @Prop({ default: '' }) initialAlias!: string;
  @Prop({ default: '' }) initialDescription!: string;
  @Prop({ default: 'none' }) initialFeedback!: string;
  @Prop() initialLanguages!: string[];
  @Prop() initialFinishTime!: Date;
  @Prop({ default: false }) initialNeedsBasicInformation!: boolean;
  @Prop({ default: 0 }) initialPenalty!: number;
  @Prop({ default: 'none' }) initialPenaltyType!: string;
  @Prop({ default: 0.0 }) initialPointsDecayFactor!: number;
  @Prop({ default: 'no' }) initialRequestsUserInformation!: string;
  @Prop({ default: 100 }) initialScoreboard!: number;
  @Prop({ default: true }) initialShowScoreboardAfter!: boolean;
  @Prop({ default: ScoreMode.Partial }) scoreMode!: ScoreMode;
  @Prop({ default: false }) hasSubmissions!: boolean;
  @Prop() initialStartTime!: Date;
  @Prop() initialSubmissionsGap!: number;
  @Prop({ default: '' }) initialTitle!: string;
  @Prop({ default: null }) initialWindowLength!: null | number;
  @Prop({ default: null }) invalidParameterName!: null | string;
  @Prop({ default: null }) teamsGroupAlias!: null | types.ListItem;
  @Prop() searchResultTeamsGroups!: types.ListItem[];
  @Prop({ default: false }) contestForTeams!: boolean;
  @Prop({ default: null }) problems!: types.ProblemsetProblemWithVersions[];
  @Prop({ default: true }) hasVisitedSection!: boolean;
  @Prop({ default: false }) canSetRecommended!: boolean;
  @Prop({ default: false }) initialRecommended!: boolean;

  T = T;
  ScoreMode = ScoreMode;
  alias = this.initialAlias;
  description = this.initialDescription;
  feedback = this.initialFeedback;
  finishTime = this.initialFinishTime;
  languages = this.initialLanguages;
  needsBasicInformation = this.initialNeedsBasicInformation;
  penalty = this.initialPenalty;
  penaltyType = this.initialPenaltyType;
  pointsDecayFactor = this.initialPointsDecayFactor;
  requestsUserInformation = this.initialRequestsUserInformation;
  scoreboard = this.initialScoreboard;
  showScoreboardAfter = this.initialShowScoreboardAfter;
  currentScoreMode = this.scoreMode;
  startTime = this.initialStartTime;
  submissionsGap = this.initialSubmissionsGap ? this.initialSubmissionsGap / 60 : 1;
  title = this.initialTitle;
  windowLength = this.initialWindowLength;
  windowLengthEnabled = this.initialWindowLength !== null;
  currentContestForTeams = this.contestForTeams;
  currentTeamsGroupAlias = this.teamsGroupAlias;
  titlePlaceHolder = '';
  recommended = this.initialRecommended;
  isSubmitting = false;
  localErrors: LocalErrors = {};
  hasFormChanged = false;

  mounted() {
    const title = T.createContestInteractiveGuideTitle;
    if (!this.hasVisitedSection) {
      introJs()
        .setOptions({
          nextLabel: T.interactiveGuideNextButton,
          prevLabel: T.interactiveGuidePreviousButton,
          doneLabel: T.interactiveGuideDoneButton,
          steps: [
            {
              title,
              intro: T.createContestInteractiveGuideWelcome,
            },
            {
              element: document.querySelector('.introjs-style') as Element,
              title,
              intro: T.createContestInteractiveGuideStyle,
            },
            {
              element: document.querySelector('.introjs-contest-title') as Element,
              title,
              intro: T.createContestInteractiveGuideContestTitle,
            },
            {
              element: document.querySelector('.introjs-short-title') as Element,
              title,
              intro: T.createContestInteractiveGuideShortTitle,
            },
            {
              element: document.querySelector('.introjs-description') as Element,
              title,
              intro: T.createContestInteractiveGuideDescription,
            },
            {
              element: document.querySelector('.introjs-schedule') as Element,
              title,
              intro: T.createContestInteractiveGuideSchedule,
            },
          ],
        })
        .start();
      this.$cookies.set('has-visited-create-contest', true, -1);
    }
  }

  @Watch('windowLengthEnabled')
  onPropertyChange(newValue: boolean): void {
    if (!newValue) {
      this.windowLength = null;
    }
  }

  @Watch('invalidParameterName')
  onInvalidParameterChange(newValue: string | null): void {
    if (!newValue) {
      return;
    }
    this.$nextTick(() => {
      const invalidElement = document.querySelector('.is-invalid');
      if (invalidElement) {
        invalidElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
      }
    });
  }

  get validationErrors(): string[] {
    return Object.values(this.localErrors).filter(Boolean);
  }

  hasErrorsInSection(section: string): boolean {
    const sectionFields = {
      basic: ['title', 'alias', 'finishTime', 'description'],
      logistics: ['windowLength', 'scoreboard', 'teamsGroup'],
      scoring: ['submissionsGap', 'penalty', 'pointsDecayFactor'],
    };
    const fields = sectionFields[section as keyof typeof sectionFields] || [];
    return fields.some(field => this.localErrors[field]);
  }

  validateField(fieldName: string): boolean {
    switch (fieldName) {
      case 'title':
        if (!this.title || this.title.trim().length === 0) {
          this.localErrors.title = 'Contest title is required';
          return false;
        }
        break;
      case 'alias':
        if (!this.alias || this.alias.trim().length === 0) {
          this.localErrors.alias = 'Contest alias is required';
          return false;
        }
        if (!/^[a-z0-9-]+$/.test(this.alias)) {
          this.localErrors.alias = 'Alias can only contain lowercase letters, numbers, and hyphens';
          return false;
        }
        break;
      case 'description':
        if (!this.description || this.description.trim().length === 0) {
          this.localErrors.description = 'Description is required';
          return false;
        }
        break;
      case 'scoreboard':
        if (this.scoreboard < 0 || this.scoreboard > 100) {
          this.localErrors.scoreboard = 'Scoreboard percentage must be between 0 and 100';
          return false;
        }
        break;
      case 'submissionsGap':
        if (this.submissionsGap < 0) {
          this.localErrors.submissionsGap = 'Submissions gap must be 0 or greater';
          return false;
        }
        break;
      case 'penalty':
        if (this.penalty < 0) {
          this.localErrors.penalty = 'Penalty must be 0 or greater';
          return false;
        }
        break;
      case 'pointsDecayFactor':
        if (this.pointsDecayFactor < 0 || this.pointsDecayFactor > 1) {
          this.localErrors.pointsDecayFactor = 'Points decay factor must be between 0 and 1';
          return false;
        }
        break;
      case 'windowLength':
        if (this.windowLengthEnabled && (!this.windowLength || this.windowLength <= 0)) {
          this.localErrors.windowLength = 'Window length must be greater than 0';
          return false;
        }
        break;
    }
    return true;
  }

  validateDates(): void {
    this.clearFieldError('finishTime');
    if (this.startTime && this.finishTime && this.finishTime <= this.startTime) {
      this.localErrors.finishTime = 'End date must be after start date';
    }
  }

  clearFieldError(fieldName: string): void {
    delete this.localErrors[fieldName];
    this.$forceUpdate();
  }

  validateForm(): boolean {
    this.localErrors = {};
    
    const fields = [
      'title', 'alias', 'description', 'scoreboard', 
      'submissionsGap', 'penalty', 'pointsDecayFactor'
    ];
    
    fields.forEach(field => this.validateField(field));
    this.validateDates();

    if (this.languages.length === 0) {
      this.localErrors.languages = 'At least one language must be selected';
    }

    if (this.currentContestForTeams && !this.currentTeamsGroupAlias) {
      this.localErrors.teamsGroup = 'Teams group must be selected for team contests';
    }

    return Object.keys(this.localErrors).length === 0;
  }

  confirmPresetChange(presetType: string): void {
    if (this.hasFormChanged && !this.update) {
      if (!confirm('Applying this preset will overwrite your current form values. Continue?')) {
        return;
      }
    }

    switch (presetType) {
      case 'omi':
        this.fillOmi();
        break;
      case 'preioi':
        this.fillPreIoi();
        break;
      case 'conacup':
        this.fillConacup();
        break;
      case 'icpc':
        this.fillIcpc();
        break;
    }
    
    this.hasFormChanged = true;
  }

  fillOmi(): void {
    this.languages = Object.keys(this.allLanguages);
    this.titlePlaceHolder = T.contestNewFormTitlePlaceholderOmiStyle;
    this.windowLengthEnabled = false;
    this.windowLength = 0;
    this.scoreboard = 0;
    this.pointsDecayFactor = 0;
    this.submissionsGap = 1;
    this.feedback = 'detailed';
    this.penalty = 0;
    this.penaltyType = 'none';
    this.showScoreboardAfter = true;
    this.currentScoreMode = ScoreMode.Partial;
    this.localErrors = {};
  }

  fillPreIoi(): void {
    this.languages = Object.keys(this.allLanguages);
    this.titlePlaceHolder = T.contestNewFormTitlePlaceholderIoiStyle;
    this.windowLengthEnabled = true;
    this.windowLength = 180;
    this.scoreboard = 0;
    this.pointsDecayFactor = 0;
    this.submissionsGap = 0;
    this.feedback = 'detailed';
    this.penalty = 0;
    this.penaltyType = 'none';
    this.showScoreboardAfter = true;
    this.currentScoreMode = ScoreMode.Partial;
    this.localErrors = {};
  }

  fillConacup(): void {
    this.languages = Object.keys(this.allLanguages);
    this.titlePlaceHolder = T.contestNewFormTitlePlaceholderConacupStyle;
    this.windowLengthEnabled = false;
    this.windowLength = 0;
    this.scoreboard = 75;
    this.pointsDecayFactor = 0;
    this.submissionsGap = 1;
    this.feedback = 'detailed';
    this.penalty = 20;
    this.penaltyType = 'none';
    this.showScoreboardAfter = true;
    this.currentScoreMode = ScoreMode.Partial;
    this.localErrors = {};
  }

  fillIcpc(): void {
    const languagesKeys = Object.keys(this.allLanguages);
    this.languages = languagesKeys.filter(
      (lang) =>
        lang.includes('c11') ||
        lang.includes('cpp') ||
        lang.includes('py') ||
        lang.includes('java'),
    );
    this.titlePlaceHolder = T.contestNewFormTitlePlaceholderICPCStyle;
    this.windowLengthEnabled = false;
    this.windowLength = null;
    this.scoreboard = 80;
    this.pointsDecayFactor = 0;
    this.submissionsGap = 1;
    this.feedback = 'none';
    this.penalty = 20;
    this.penaltyType = 'contest_start';
    this.showScoreboardAfter = true;
    this.currentScoreMode = ScoreMode.AllOrNothing;
    this.localErrors = {};
  }

  onSubmit() {
    if (!this.validateForm()) {
      this.$nextTick(() => {
        const firstInvalid = document.querySelector('.is-invalid');
        if (firstInvalid) {
          firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
      });
      return;
    }

    this.isSubmitting = true;

    const contest: types.ContestAdminDetails = {
      admin: true,
      admission_mode: this.update ? this.admissionMode : 'private',
      alias: this.alias,
      archived: false,
      available_languages: {},
      canSetRecommended: false,
      director: '',
      opened: false,
      penalty_calc_policy: 'sum',
      problemset_id: 0,
      show_penalty: true,
      title: this.title,
      description: this.description,
      has_submissions: this.hasSubmissions,
      start_time: this.startTime,
      finish_time: this.finishTime,
      points_decay_factor: this.pointsDecayFactor,
      submissions_gap: (this.submissionsGap || 1) * 60,
      languages: this.languages,
      feedback: this.feedback,
      penalty: this.penalty,
      scoreboard: this.scoreboard,
      penalty_type: this.penaltyType,
      default_show_all_contestants_in_scoreboard: this.defaultShowAllContestantsInScoreboard,
      show_scoreboard_after: this.showScoreboardAfter,
      score_mode: this.currentScoreMode,
      needs_basic_information: this.needsBasicInformation,
      requests_user_information: this.requestsUserInformation,
      contest_for_teams: this.currentContestForTeams,
    };

    if (this.canSetRecommended) {
      contest.recommended = this.recommended;
    }

    if (this.windowLengthEnabled && this.windowLength) {
      contest.window_length = this.windowLength;
    }
    
    const request = {
      contest,
      teamsGroupAlias: this.currentTeamsGroupAlias?.key,
    };
    
    setTimeout(() => {
      this.isSubmitting = false;
    }, 1000);

    if (this.update) {
      this.$emit('update-contest', request);
      return;
    }
    this.$emit('create-contest', request);
  }

  get minDateTimeForContest(): null | Date {
    if (this.update) {
      return null;
    }
    return new Date();
  }

  get teamsGroupName(): null | string {
    return this.currentTeamsGroupAlias?.value ?? null;
  }

  get catLanguageBlocked(): boolean {
    if (!this.problems) {
      return false;
    }
    for (const problem of this.problems) {
      if (problem.languages.split(',').includes('cat')) {
        return true;
      }
    }
    return false;
  }

  onRemove(language: string) {
    if (this.catLanguageBlocked && language == 'cat') {
      this.$emit('language-remove-blocked', language);
      return;
    }
    const index = this.languages.indexOf(language);
    this.languages.splice(index, 1);
    this.hasFormChanged = true;
  }

  onSelect(language: string) {
    this.languages.push(language);
    this.hasFormChanged = true;
  }

  updateTeamsGroups(query: string) {
    this.$emit('update-search-result-teams-groups', query);
  }
}
</script>

<style lang="scss" scoped>
@import '../../../../sass/main.scss';
@import '../../../../../../node_modules/vue-multiselect/dist/vue-multiselect.min.css';

.multiselect__tag {
  background: var(--multiselect-tag-background-color);
}

.spinner-border-sm {
  width: 1rem;
  height: 1rem;
  border-width: 0.2em;
}

.text-danger {
  color: #dc3545 !important;
}
</style>