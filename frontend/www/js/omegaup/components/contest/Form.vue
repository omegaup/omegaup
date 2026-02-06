<template>
  <div class="card">
    <div v-if="!update" class="card-header bg-light text-dark panel-heading">
      <h3 class="card-title mb-0">{{ T.contestNew }}</h3>
    </div>
    <div class="card-body px-2 px-sm-4">
      <!-- Style Presets -->
      <div class="btn-group d-block mb-3 text-center introjs-style">
        <button
          class="btn btn-secondary"
          data-contest-omi
          type="button"
          @click="confirmPresetChange(PresetType.OMI)"
        >
          {{ T.contestNewFormOmiStyle }}
        </button>
        <button
          class="btn btn-secondary"
          data-contest-preioi
          type="button"
          @click="confirmPresetChange(PresetType.PreIOI)"
        >
          {{ T.contestNewForm }}
        </button>
        <button
          class="btn btn-secondary"
          data-contest-conacup
          type="button"
          @click="confirmPresetChange(PresetType.Conacup)"
        >
          {{ T.contestNewFormConacupStyle }}
        </button>
        <button
          class="btn btn-secondary"
          data-contest-icpc
          type="button"
          @click="confirmPresetChange(PresetType.ICPC)"
        >
          {{ T.contestNewFormICPCStyle }}
        </button>
      </div>

      <form class="contest-form" novalidate @submit.prevent="onSubmit">
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
                  <font-awesome-icon
                    v-if="hasErrorsInSection(SectionName.Basic)"
                    icon="exclamation-circle"
                    class="text-danger ml-2"
                  />
                </button>
              </h2>
            </div>
            <div class="collapse show card-body basic-info">
              <div class="row">
                <div class="form-group col-md-6">
                  <label>
                    {{ T.wordsTitle }}
                    <span
                      class="required-asterisk"
                      :class="{
                        'text-danger':
                          localErrors[FieldName.Title] ||
                          invalidParameterName === FieldName.Title,
                      }"
                      >*</span
                    >
                  </label>
                  <input
                    v-model="title"
                    class="form-control introjs-contest-title"
                    :class="{
                      'is-invalid':
                        invalidParameterName === FieldName.Title ||
                        localErrors[FieldName.Title],
                    }"
                    name="title"
                    data-title
                    :placeholder="titlePlaceHolder"
                    type="text"
                    required
                    :disabled="isSubmitting"
                    :maxlength="MAX_LENGTH.title"
                    @blur="validateField(FieldName.Title)"
                    @input="clearFieldError(FieldName.Title)"
                  />
                  <small
                    class="character-counter"
                    :class="{ 'text-danger': isExceedingTitle }"
                  >
                    {{ title.length }}/{{ MAX_LENGTH.title }}
                  </small>
                  <div
                    v-if="
                      invalidParameterName === FieldName.Title ||
                      localErrors[FieldName.Title]
                    "
                    class="invalid-feedback"
                  >
                    {{
                      localErrors[FieldName.Title] ||
                      T.contestNewFormTitleRequired
                    }}
                  </div>
                </div>
                <div class="form-group col-md-6">
                  <label>
                    {{ T.contestNewFormShortTitleAlias }}
                    <span
                      class="required-asterisk"
                      :class="{
                        'text-danger':
                          localErrors[FieldName.Alias] ||
                          invalidParameterName === FieldName.Alias,
                      }"
                      >*</span
                    >
                    <font-awesome-icon
                      :title="T.contestNewFormShortTitleAliasDesc"
                      icon="info-circle"
                      class="ml-1 text-muted"
                    />
                  </label>
                  <input
                    v-model="alias"
                    class="form-control introjs-short-title"
                    :class="{
                      'is-invalid':
                        invalidParameterName === FieldName.Alias ||
                        localErrors[FieldName.Alias],
                    }"
                    name="alias"
                    :disabled="update || isSubmitting"
                    type="text"
                    required
                    :maxlength="MAX_LENGTH.alias"
                    @blur="validateField(FieldName.Alias)"
                    @input="clearFieldError(FieldName.Alias)"
                  />
                  <small
                    class="character-counter"
                    :class="{ 'text-danger': isExceedingAlias }"
                  >
                    {{ alias.length }}/{{ MAX_LENGTH.alias }}
                  </small>
                  <div
                    v-if="
                      invalidParameterName === FieldName.Alias ||
                      localErrors[FieldName.Alias]
                    "
                    class="invalid-feedback"
                  >
                    {{
                      localErrors[FieldName.Alias] ||
                      T.contestNewFormShortTitleRequired
                    }}
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="form-group col-md-6">
                  <label>
                    {{ T.contestNewFormStartDate }}
                    <span class="required-asterisk">*</span>
                    <font-awesome-icon
                      :title="T.contestNewFormStartDateDesc"
                      icon="info-circle"
                      class="ml-1 text-muted"
                    />
                  </label>
                  <omegaup-datetimepicker
                    v-model="startTime"
                    data-start-date
                    :start="minDateTimeForContest"
                    :disabled="isSubmitting"
                    @input="validateDates"
                  ></omegaup-datetimepicker>
                </div>
                <div class="form-group col-md-6">
                  <label>
                    {{ T.contestNewFormEndDate }}
                    <span
                      class="required-asterisk"
                      :class="{
                        'text-danger':
                          localErrors[FieldName.FinishTime] ||
                          invalidParameterName === FieldName.FinishTime,
                      }"
                      >*</span
                    >
                    <font-awesome-icon
                      :title="T.contestNewFormEndDateDesc"
                      icon="info-circle"
                      class="ml-1 text-muted"
                    />
                  </label>
                  <omegaup-datetimepicker
                    v-model="finishTime"
                    data-end-date
                    :is-invalid="
                      invalidParameterName === FieldName.FinishTime ||
                      !!localErrors[FieldName.FinishTime]
                    "
                    :disabled="isSubmitting"
                    @input="validateDates"
                  ></omegaup-datetimepicker>
                  <div
                    v-if="localErrors[FieldName.FinishTime]"
                    class="invalid-feedback"
                  >
                    {{ localErrors[FieldName.FinishTime] }}
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="form-group col-md-6 introjs-description">
                  <label>
                    {{ T.contestNewFormDescription }}
                    <span
                      class="required-asterisk"
                      :class="{
                        'text-danger':
                          localErrors[FieldName.Description] ||
                          invalidParameterName === FieldName.Description,
                      }"
                      >*</span
                    >
                  </label>
                  <textarea
                    v-model="description"
                    class="form-control"
                    :class="{
                      'is-invalid':
                        invalidParameterName === FieldName.Description ||
                        localErrors[FieldName.Description],
                    }"
                    data-description
                    name="description"
                    rows="10"
                    required
                    :disabled="isSubmitting"
                    :maxlength="MAX_LENGTH.description"
                    @blur="validateField(FieldName.Description)"
                    @input="clearFieldError(FieldName.Description)"
                  ></textarea>
                  <small
                    class="character-counter"
                    :class="{ 'text-danger': isExceedingDescription }"
                  >
                    {{ description.length }}/{{ MAX_LENGTH.description }}
                  </small>
                  <div
                    v-if="
                      invalidParameterName === FieldName.Description ||
                      localErrors[FieldName.Description]
                    "
                    class="invalid-feedback"
                  >
                    {{
                      localErrors[FieldName.Description] ||
                      T.contestNewFormDescriptionRequired
                    }}
                  </div>
                </div>
                <div class="form-group col-md-6">
                  <label>
                    {{ T.wordsLanguages }}
                    <span
                      class="required-asterisk"
                      :class="{
                        'text-danger': localErrors[FieldName.Languages],
                      }"
                      >*</span
                    >
                  </label>
                  <div
                    :class="{
                      'is-invalid-wrapper': localErrors[FieldName.Languages],
                    }"
                  >
                    <multiselect
                      :value="languages"
                      :options="Object.keys(allLanguages)"
                      :multiple="true"
                      :placeholder="T.contestNewFormLanguages"
                      :close-on-select="false"
                      :allow-empty="true"
                      :disabled="isSubmitting"
                      @remove="onRemove"
                      @select="onSelect"
                    ></multiselect>
                  </div>
                  <div
                    v-if="localErrors[FieldName.Languages]"
                    class="invalid-feedback"
                  >
                    {{ localErrors[FieldName.Languages] }}
                  </div>
                  <small class="form-text text-muted">
                    {{ T.contestNewFormLanguagesHelp }}
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
                  <font-awesome-icon
                    v-if="hasErrorsInSection(SectionName.Logistics)"
                    icon="exclamation-circle"
                    class="text-danger ml-2"
                  />
                </button>
              </h2>
            </div>
            <div class="collapse card-body logistics">
              <div class="row">
                <div class="form-group col-md-6">
                  <label>
                    {{ T.contestNewFormDifferentStarts }}
                    <font-awesome-icon
                      :title="T.contestNewFormDifferentStartsDesc"
                      icon="info-circle"
                      class="ml-1 text-muted"
                    />
                  </label>
                  <div class="checkbox">
                    <label>
                      <input
                        v-model="windowLengthEnabled"
                        data-different-start-check
                        type="checkbox"
                        :disabled="isSubmitting"
                      />
                      {{ T.wordsEnable }}
                    </label>
                  </div>
                  <input
                    v-model.number="windowLength"
                    class="form-control"
                    data-different-start-time-input
                    :class="{
                      'is-invalid':
                        invalidParameterName === FieldName.WindowLength ||
                        localErrors[FieldName.WindowLength],
                    }"
                    name="window_length"
                    type="number"
                    min="0"
                    :disabled="!windowLengthEnabled || isSubmitting"
                    :placeholder="T.contestNewFormWindowLengthPlaceholder"
                    @blur="validateField(FieldName.WindowLength)"
                    @input="clearFieldError(FieldName.WindowLength)"
                  />
                  <div
                    v-if="localErrors[FieldName.WindowLength]"
                    class="invalid-feedback"
                  >
                    {{ localErrors[FieldName.WindowLength] }}
                  </div>
                </div>
                <div class="form-group col-md-6">
                  <label>
                    {{ T.contestNewFormForTeams }}
                    <font-awesome-icon
                      :title="T.contestNewFormForTeamsDesc"
                      icon="info-circle"
                      class="ml-1 text-muted"
                    />
                  </label>
                  <div class="checkbox">
                    <label>
                      <input
                        v-model="currentContestForTeams"
                        data-contest-for-teams
                        type="checkbox"
                        :disabled="update || isSubmitting"
                      />
                      {{ T.wordsEnable }}
                    </label>
                  </div>

                  <omegaup-common-typeahead
                    v-if="currentContestForTeams && !hasSubmissions"
                    :class="{
                      'is-invalid':
                        invalidParameterName === FieldName.TeamsGroup ||
                        localErrors[FieldName.TeamsGroup],
                    }"
                    :existing-options="searchResultTeamsGroups"
                    :options="searchResultTeamsGroups"
                    :value.sync="currentTeamsGroupAlias"
                    :disabled="isSubmitting"
                    @update-existing-options="updateTeamsGroups"
                  ></omegaup-common-typeahead>
                  <input
                    v-else-if="currentContestForTeams"
                    class="form-control"
                    disabled
                    :value="teamsGroupName"
                  />
                  <small
                    v-if="hasSubmissions && currentContestForTeams"
                    class="form-text text-muted"
                  >
                    {{ T.contestNewFormTeamsGroupLocked }}
                  </small>
                </div>
              </div>
              <div class="row">
                <div class="form-group col-md-6">
                  <label>
                    {{ T.contestNewFormScoreboardAtEnd }}
                    <font-awesome-icon
                      :title="T.contestNewFormScoreboardAtEndDesc"
                      icon="info-circle"
                      class="ml-1 text-muted"
                    />
                  </label>
                  <select
                    v-model="showScoreboardAfter"
                    data-show-scoreboard-at-end
                    class="form-control"
                    :disabled="isSubmitting"
                  >
                    <option :value="true">{{ T.wordsYes }}</option>
                    <option :value="false">{{ T.wordsNo }}</option>
                  </select>
                </div>
                <div class="form-group col-md-6">
                  <label>
                    {{ T.contestNewFormScoreboardTimePercent }}
                    <span
                      class="required-asterisk"
                      :class="{
                        'text-danger':
                          localErrors[FieldName.Scoreboard] ||
                          invalidParameterName === FieldName.Scoreboard,
                      }"
                      >*</span
                    >
                    <font-awesome-icon
                      :title="T.contestNewFormScoreboardTimePercentDesc"
                      icon="info-circle"
                      class="ml-1 text-muted"
                    />
                  </label>
                  <input
                    v-model.number="scoreboard"
                    data-score-board-visible-time
                    class="form-control scoreboard-time-percent"
                    :class="{
                      'is-invalid':
                        invalidParameterName === FieldName.Scoreboard ||
                        localErrors[FieldName.Scoreboard],
                    }"
                    name="scoreboard"
                    type="number"
                    min="0"
                    max="100"
                    required
                    :disabled="isSubmitting"
                    @blur="validateField(FieldName.Scoreboard)"
                    @input="clearFieldError(FieldName.Scoreboard)"
                  />
                  <div
                    v-if="localErrors[FieldName.Scoreboard]"
                    class="invalid-feedback"
                  >
                    {{ localErrors[FieldName.Scoreboard] }}
                  </div>
                  <small class="form-text text-muted">{{
                    T.contestNewFormScoreboardPercentRange
                  }}</small>
                </div>
              </div>
              <div class="row">
                <div class="form-group col-md-6">
                  <label>
                    {{ T.contestNewFormRecommended }}
                    <font-awesome-icon
                      :title="
                        canSetRecommended
                          ? T.contestNewFormRecommendedTextAdmin
                          : T.contestNewFormRecommendedTextNonAdmin
                      "
                      icon="info-circle"
                      class="ml-1 text-muted"
                    />
                  </label>
                  <div v-if="canSetRecommended" class="checkbox form-check">
                    <input
                      v-model="recommended"
                      data-recommended
                      class="form-check-input"
                      type="checkbox"
                      :disabled="isSubmitting"
                    />
                    <label class="form-check-label"> {{ T.wordsEnable }}</label>
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
                  <font-awesome-icon
                    v-if="hasErrorsInSection(SectionName.Scoring)"
                    icon="exclamation-circle"
                    class="text-danger ml-2"
                  />
                </button>
              </h2>
            </div>
            <div class="collapse card-body scoring-rules">
              <div class="row">
                <div class="form-group col-md-6">
                  <label>
                    {{ T.contestNewFormScoreMode }}
                    <font-awesome-icon
                      :title="T.contestNewFormScoreModeDesc"
                      icon="info-circle"
                      class="ml-1 text-muted"
                    />
                  </label>
                  <select
                    v-model="currentScoreMode"
                    data-score-mode
                    class="form-control"
                    :disabled="isSubmitting"
                  >
                    <option :value="ScoreMode.Partial">
                      {{ T.contestNewFormScoreModePartial }}
                    </option>
                    <option :value="ScoreMode.AllOrNothing">
                      {{ T.contestNewFormScoreModeAllOrNothing }}
                    </option>
                    <option :value="ScoreMode.MaxPerGroup">
                      {{ T.contestNewFormScoreModeMaxPerGroup }}
                    </option>
                  </select>
                </div>
                <div class="form-group col-md-6">
                  <label>
                    {{ T.wordsFeedback }}
                    <font-awesome-icon
                      :title="T.contestNewFormImmediateFeedbackDesc"
                      icon="info-circle"
                      class="ml-1 text-muted"
                    />
                  </label>
                  <select
                    v-model="feedback"
                    class="form-control"
                    :disabled="isSubmitting"
                  >
                    <option value="none">{{ T.wordsNone }}</option>
                    <option value="summary">{{ T.wordsSummary }}</option>
                    <option value="detailed">{{ T.wordsDetailed }}</option>
                  </select>
                </div>
              </div>
              <div class="row">
                <div class="form-group col-md-6">
                  <label>
                    {{ T.contestNewFormSubmissionsSeparation }}
                    <span
                      class="required-asterisk"
                      :class="{
                        'text-danger':
                          localErrors[FieldName.SubmissionsGap] ||
                          invalidParameterName === FieldName.SubmissionsGap,
                      }"
                      >*</span
                    >
                    <font-awesome-icon
                      :title="T.contestNewFormSubmissionsSeparationDesc"
                      icon="info-circle"
                      class="ml-1 text-muted"
                    />
                  </label>
                  <input
                    v-model.number="submissionsGap"
                    class="form-control"
                    :class="{
                      'is-invalid':
                        invalidParameterName === FieldName.SubmissionsGap ||
                        localErrors[FieldName.SubmissionsGap],
                    }"
                    name="submissions_gap"
                    type="number"
                    min="0"
                    step="1"
                    required
                    :disabled="isSubmitting"
                    @blur="validateField(FieldName.SubmissionsGap)"
                    @input="clearFieldError(FieldName.SubmissionsGap)"
                  />
                  <div
                    v-if="localErrors[FieldName.SubmissionsGap]"
                    class="invalid-feedback"
                  >
                    {{ localErrors[FieldName.SubmissionsGap] }}
                  </div>
                  <small class="form-text text-muted">{{
                    T.contestNewFormSubmissionsGapHelp
                  }}</small>
                </div>
                <div class="form-group col-md-6">
                  <label>
                    {{ T.contestNewFormPenaltyType }}
                    <font-awesome-icon
                      :title="T.contestNewFormPenaltyTypeDesc"
                      icon="info-circle"
                      class="ml-1 text-muted"
                    />
                  </label>
                  <select
                    v-model="penaltyType"
                    class="form-control"
                    :disabled="isSubmitting"
                  >
                    <option value="none">
                      {{ T.contestNewFormNoPenalty }}
                    </option>
                    <option value="problem_open">
                      {{ T.contestNewFormByProblem }}
                    </option>
                    <option value="contest_start">
                      {{ T.contestNewFormByContests }}
                    </option>
                    <option value="runtime">
                      {{ T.contestNewFormByRuntime }}
                    </option>
                  </select>
                </div>
              </div>
              <div class="row">
                <div class="form-group col-md-6">
                  <label>
                    {{ T.wordsPenalty }}
                    <span
                      class="required-asterisk"
                      :class="{
                        'text-danger':
                          localErrors[FieldName.Penalty] ||
                          invalidParameterName === FieldName.Penalty,
                      }"
                      >*</span
                    >
                    <font-awesome-icon
                      :title="T.contestNewFormPenaltyDesc"
                      icon="info-circle"
                      class="ml-1 text-muted"
                    />
                  </label>
                  <input
                    v-model.number="penalty"
                    class="form-control"
                    :class="{
                      'is-invalid':
                        invalidParameterName === FieldName.Penalty ||
                        localErrors[FieldName.Penalty],
                    }"
                    name="penalty"
                    type="number"
                    min="0"
                    step="1"
                    required
                    :disabled="isSubmitting"
                    @blur="validateField(FieldName.Penalty)"
                    @input="clearFieldError(FieldName.Penalty)"
                  />
                  <div
                    v-if="localErrors[FieldName.Penalty]"
                    class="invalid-feedback"
                  >
                    {{ localErrors[FieldName.Penalty] }}
                  </div>
                </div>
                <div class="form-group col-md-6">
                  <label>
                    {{ T.contestNewFormPointDecrementFactor }}
                    <span
                      class="required-asterisk"
                      :class="{
                        'text-danger':
                          localErrors[FieldName.PointsDecayFactor] ||
                          invalidParameterName === FieldName.PointsDecayFactor,
                      }"
                      >*</span
                    >
                    <font-awesome-icon
                      :title="T.contestNewFormPointDecrementFactorDesc"
                      icon="info-circle"
                      class="ml-1 text-muted"
                    />
                  </label>
                  <input
                    v-model.number="pointsDecayFactor"
                    class="form-control"
                    :class="{
                      'is-invalid':
                        invalidParameterName === FieldName.PointsDecayFactor ||
                        localErrors[FieldName.PointsDecayFactor],
                    }"
                    name="points_decay_factor"
                    type="number"
                    min="0"
                    max="1"
                    step="0.01"
                    required
                    :disabled="isSubmitting"
                    @blur="validateField(FieldName.PointsDecayFactor)"
                    @input="clearFieldError(FieldName.PointsDecayFactor)"
                  />
                  <div
                    v-if="localErrors[FieldName.PointsDecayFactor]"
                    class="invalid-feedback"
                  >
                    {{ localErrors[FieldName.PointsDecayFactor] }}
                  </div>
                  <small class="form-text text-muted">{{
                    T.contestNewFormDecayFactorRange
                  }}</small>
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
                    <font-awesome-icon
                      :title="T.contestNewFormBasicInformationRequiredDesc"
                      icon="info-circle"
                      class="ml-1 text-muted"
                    />
                  </label>
                  <div class="checkbox form-check">
                    <input
                      v-model="needsBasicInformation"
                      data-basic-information-required
                      class="form-check-input"
                      type="checkbox"
                      :disabled="isSubmitting"
                    />
                    <label class="form-check-label">
                      {{ T.wordsEnable }}
                    </label>
                  </div>
                </div>
                <div class="form-group col-md-6">
                  <label>
                    {{ T.contestNewFormUserInformationRequired }}
                    <font-awesome-icon
                      :title="T.contestNewFormUserInformationRequiredDesc"
                      icon="info-circle"
                      class="ml-1 text-muted"
                    />
                  </label>
                  <select
                    v-model="requestsUserInformation"
                    data-request-user-information
                    class="form-control"
                    :disabled="isSubmitting"
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
              <span
                class="spinner-border spinner-border-sm mr-2"
                role="status"
                aria-hidden="true"
              ></span>
              {{ T.contestNewFormProcessing }}
            </span>
            <span v-else>
              {{
                update
                  ? T.contestNewFormUpdateContest
                  : T.contestNewFormScheduleContest
              }}
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
import * as ui from '../../ui';
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
import { isValidAlias } from '../../validators';
library.add(fas);

Vue.use(VueCookies, { expire: -1 });

export enum ScoreMode {
  AllOrNothing = 'all_or_nothing',
  Partial = 'partial',
  MaxPerGroup = 'max_per_group',
}

export enum PresetType {
  OMI = 'omi',
  PreIOI = 'preioi',
  Conacup = 'conacup',
  ICPC = 'icpc',
}

export enum FieldName {
  Title = 'title',
  Alias = 'alias',
  Description = 'description',
  Languages = 'languages',
  Scoreboard = 'scoreboard',
  SubmissionsGap = 'submissions_gap',
  Penalty = 'penalty',
  PointsDecayFactor = 'points_decay_factor',
  WindowLength = 'window_length',
  FinishTime = 'finish_time',
  TeamsGroup = 'teams_group_alias',
}

export enum SectionName {
  Basic = 'basic',
  Logistics = 'logistics',
  Scoring = 'scoring',
}

interface LocalErrors {
  [key: string]: string;
}

/**
 * Field mapping for error checking by section
 * Used to determine which fields belong to which accordion section
 */
const SECTION_FIELDS: Record<SectionName, string[]> = {
  [SectionName.Basic]: [
    FieldName.Title,
    FieldName.Alias,
    FieldName.FinishTime,
    FieldName.Description,
    FieldName.Languages,
  ],
  [SectionName.Logistics]: [
    FieldName.WindowLength,
    FieldName.Scoreboard,
    FieldName.TeamsGroup,
  ],
  [SectionName.Scoring]: [
    FieldName.SubmissionsGap,
    FieldName.Penalty,
    FieldName.PointsDecayFactor,
  ],
};

/**
 * Preset configurations for different contest types
 */
interface PresetConfig {
  languages?: boolean; // Whether to set all languages
  titlePlaceHolder: string;
  windowLengthEnabled: boolean;
  windowLength: number | null;
  scoreboard: number;
  pointsDecayFactor: number;
  submissionsGap: number;
  feedback: string;
  penalty: number;
  penaltyType: string;
  showScoreboardAfter: boolean;
  currentScoreMode: ScoreMode;
  languageFilter?: (lang: string) => boolean;
}

const CONTEST_PRESETS: Record<PresetType, PresetConfig> = {
  [PresetType.OMI]: {
    languages: true,
    titlePlaceHolder: T.contestNewFormTitlePlaceholderOmiStyle,
    windowLengthEnabled: false,
    windowLength: 0,
    scoreboard: 0,
    pointsDecayFactor: 0,
    submissionsGap: 1,
    feedback: 'detailed',
    penalty: 0,
    penaltyType: 'none',
    showScoreboardAfter: true,
    currentScoreMode: ScoreMode.Partial,
  },
  [PresetType.PreIOI]: {
    languages: true,
    titlePlaceHolder: T.contestNewFormTitlePlaceholderIoiStyle,
    windowLengthEnabled: true,
    windowLength: 180,
    scoreboard: 0,
    pointsDecayFactor: 0,
    submissionsGap: 0,
    feedback: 'detailed',
    penalty: 0,
    penaltyType: 'none',
    showScoreboardAfter: true,
    currentScoreMode: ScoreMode.Partial,
  },
  [PresetType.Conacup]: {
    languages: true,
    titlePlaceHolder: T.contestNewFormTitlePlaceholderConacupStyle,
    windowLengthEnabled: false,
    windowLength: 0,
    scoreboard: 75,
    pointsDecayFactor: 0,
    submissionsGap: 1,
    feedback: 'detailed',
    penalty: 20,
    penaltyType: 'none',
    showScoreboardAfter: true,
    currentScoreMode: ScoreMode.Partial,
  },
  [PresetType.ICPC]: {
    languages: true,
    titlePlaceHolder: T.contestNewFormTitlePlaceholderICPCStyle,
    windowLengthEnabled: false,
    windowLength: null,
    scoreboard: 80,
    pointsDecayFactor: 0,
    submissionsGap: 1,
    feedback: 'none',
    penalty: 20,
    penaltyType: 'contest_start',
    showScoreboardAfter: true,
    currentScoreMode: ScoreMode.AllOrNothing,
    languageFilter: (lang: string) =>
      lang.includes('c11') ||
      lang.includes('cpp') ||
      lang.includes('py') ||
      lang.includes('java'),
  },
};

const MAX_LENGTH = {
  title: 256,
  alias: 32,
  description: 255,
};

const DANGER_THRESHOLD_PERCENTAGE = 0.8;

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
  T = T;
  ScoreMode = ScoreMode;
  PresetType = PresetType;
  FieldName = FieldName;
  SectionName = SectionName;
  MAX_LENGTH = MAX_LENGTH;

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
  submissionsGap = this.initialSubmissionsGap
    ? this.initialSubmissionsGap / 60
    : 1;
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
              element: document.querySelector(
                '.introjs-contest-title',
              ) as Element,
              title,
              intro: T.createContestInteractiveGuideContestTitle,
            },
            {
              element: document.querySelector(
                '.introjs-short-title',
              ) as Element,
              title,
              intro: T.createContestInteractiveGuideShortTitle,
            },
            {
              element: document.querySelector(
                '.introjs-description',
              ) as Element,
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
      this.clearFieldError(FieldName.WindowLength);
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

  hasErrorsInSection(section: SectionName): boolean {
    const fields = SECTION_FIELDS[section] || [];
    return fields.some((field) => this.localErrors[field]);
  }

  validateField(fieldName: FieldName): boolean {
    switch (fieldName) {
      case FieldName.Title:
        if (!this.title || this.title.trim().length === 0) {
          this.localErrors[fieldName] = T.contestNewFormTitleRequired;
          return false;
        }
        break;
      case FieldName.Alias:
        if (!this.alias || this.alias.trim().length === 0) {
          this.localErrors[fieldName] = T.contestNewFormShortTitleRequired;
          return false;
        }
        if (!isValidAlias(this.alias)) {
          this.localErrors[fieldName] = T.contestNewFormAliasInvalid;
          return false;
        }
        break;
      case FieldName.Description:
        if (!this.description || this.description.trim().length === 0) {
          this.localErrors[fieldName] = T.contestNewFormDescriptionRequired;
          return false;
        }
        break;
      case FieldName.Scoreboard:
        if (this.scoreboard < 0 || this.scoreboard > 100) {
          this.localErrors[fieldName] = T.contestNewFormScoreboardInvalid;
          return false;
        }
        break;
      case FieldName.SubmissionsGap:
        if (this.submissionsGap < 0) {
          this.localErrors[fieldName] = T.contestNewFormSubmissionsGapInvalid;
          return false;
        }
        break;
      case FieldName.Penalty:
        if (this.penalty < 0) {
          this.localErrors[fieldName] = T.contestNewFormPenaltyInvalid;
          return false;
        }
        break;
      case FieldName.PointsDecayFactor:
        if (this.pointsDecayFactor < 0 || this.pointsDecayFactor > 1) {
          this.localErrors[fieldName] =
            T.contestNewFormPointsDecayFactorInvalid;
          return false;
        }
        break;
      case FieldName.WindowLength:
        if (
          this.windowLengthEnabled &&
          (!this.windowLength || this.windowLength <= 0)
        ) {
          this.localErrors[fieldName] = T.contestNewFormWindowLengthInvalid;
          return false;
        }
        break;
    }
    return true;
  }

  validateDates(): void {
    this.clearFieldError(FieldName.FinishTime);
    if (
      this.startTime &&
      this.finishTime &&
      this.finishTime <= this.startTime
    ) {
      this.localErrors[FieldName.FinishTime] =
        T.contestNewFormFinishTimeInvalid;
    }
  }

  clearFieldError(fieldName: FieldName | string): void {
    delete this.localErrors[fieldName];
    this.$forceUpdate();
  }

  validateForm(): boolean {
    this.localErrors = {};

    const fields = [
      FieldName.Title,
      FieldName.Alias,
      FieldName.Description,
      FieldName.Scoreboard,
      FieldName.SubmissionsGap,
      FieldName.Penalty,
      FieldName.PointsDecayFactor,
    ];

    fields.forEach((field) => this.validateField(field));
    this.validateDates();

    if (this.languages.length === 0) {
      this.localErrors[FieldName.Languages] = T.contestNewFormLanguagesRequired;
    }

    if (this.currentContestForTeams && !this.currentTeamsGroupAlias) {
      this.localErrors[FieldName.TeamsGroup] =
        T.contestNewFormTeamsGroupRequired;
    }

    return Object.keys(this.localErrors).length === 0;
  }

  confirmPresetChange(presetType: PresetType): void {
    if (this.hasFormChanged && !this.update) {
      if (!confirm(T.contestNewFormPresetOverwriteWarning)) {
        return;
      }
    }

    this.applyPreset(presetType);
    this.hasFormChanged = true;
  }

  applyPreset(presetType: PresetType): void {
    const preset = CONTEST_PRESETS[presetType];

    if (preset.languages) {
      const allLangs = Object.keys(this.allLanguages);
      this.languages = preset.languageFilter
        ? allLangs.filter(preset.languageFilter)
        : allLangs;
    }

    this.titlePlaceHolder = preset.titlePlaceHolder;
    this.windowLengthEnabled = preset.windowLengthEnabled;
    this.windowLength = preset.windowLength;
    this.scoreboard = preset.scoreboard;
    this.pointsDecayFactor = preset.pointsDecayFactor;
    this.submissionsGap = preset.submissionsGap;
    this.feedback = preset.feedback;
    this.penalty = preset.penalty;
    this.penaltyType = preset.penaltyType;
    this.showScoreboardAfter = preset.showScoreboardAfter;
    this.currentScoreMode = preset.currentScoreMode;
    this.localErrors = {};
  }

  onSubmit() {
    if (!this.validateForm()) {
      const errorsList = Object.values(this.localErrors)
        .filter(Boolean)
        .map((error) => `<li>${error}</li>`)
        .join('');
      ui.error(`**${T.formValidationSummaryTitle}**<ul>${errorsList}</ul>`);

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
      default_show_all_contestants_in_scoreboard: this
        .defaultShowAllContestantsInScoreboard,
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

  // Computed properties for character limit danger thresholds
  get isExceedingTitle(): boolean {
    return this.title.length > MAX_LENGTH.title * DANGER_THRESHOLD_PERCENTAGE;
  }

  get isExceedingAlias(): boolean {
    return this.alias.length > MAX_LENGTH.alias * DANGER_THRESHOLD_PERCENTAGE;
  }

  get isExceedingDescription(): boolean {
    return (
      this.description.length >
      MAX_LENGTH.description * DANGER_THRESHOLD_PERCENTAGE
    );
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
    this.clearFieldError(FieldName.Languages);
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

.required-asterisk {
  transition: color 0.2s ease;
}

.text-muted {
  cursor: help;
}

.form-control:disabled,
.custom-control-input:disabled ~ .custom-control-label,
.btn:disabled {
  cursor: not-allowed;
}

.form-control:disabled,
.custom-control-input:disabled ~ .custom-control-label {
  opacity: 0.6;
}

.invalid-feedback {
  display: block;
}

/* stylelint-disable-next-line selector-pseudo-element-no-unknown */
.is-invalid-wrapper ::v-deep .multiselect__tags {
  border-color: var(--form-input-error-color);
}

.character-counter {
  display: block;
  text-align: right;
  color: var(--form-character-counter-color, #6c757d);
  font-size: 0.8rem;
  margin-top: 0.25rem;
}
</style>
