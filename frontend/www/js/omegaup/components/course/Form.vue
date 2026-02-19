<template>
  <div class="omegaup-course-details card">
    <div v-if="!update" class="card-header px-2 px-sm-4">
      <h3 class="card-title mb-0">{{ T.courseNew }}</h3>
    </div>
    <div class="card-body px-2 px-sm-4">
      <div class="required-fields-legend">{{ T.wordsRequiredField }}</div>
      <form class="form" data-course-form @submit.prevent="onSubmit">
        <div class="row">
          <div class="form-group col-md-4">
            <label class="font-weight-bold w-100 introjs-course-name">
              <span
                class="field-required"
                :class="{ 'is-complete': isNameComplete }"
                >{{ T.wordsName }}</span
              >
              <input
                v-model="name"
                :disabled="readOnly"
                class="form-control"
                :class="{ 'is-invalid': invalidParameterName === 'name' }"
                data-course-new-name
                type="text"
                required="required"
                :maxlength="MAX_LENGTH.name"
            /></label>
            <small
              class="character-counter"
              :class="{ 'text-danger': isExceedingName }"
              >{{ name.length }}/{{ MAX_LENGTH.name }}</small
            >
          </div>
          <div class="form-group col-md-4">
            <label class="font-weight-bold w-100 introjs-short-title">
              <span
                class="field-required"
                :class="{ 'is-complete': isAliasComplete }"
                >{{ T.courseNewFormShortTitleAlias }}</span
              >
              <font-awesome-icon
                :title="T.courseNewFormShortTitleAliasDesc"
                icon="info-circle" />
              <input
                v-model="alias"
                class="form-control"
                :class="{
                  'is-invalid': invalidParameterName === 'alias',
                }"
                type="text"
                data-course-new-alias
                :disabled="update || readOnly"
                required="required"
                :maxlength="MAX_LENGTH.alias"
            /></label>
            <small
              class="character-counter"
              :class="{ 'text-danger': isExceedingAlias }"
              >{{ alias.length }}/{{ MAX_LENGTH.alias }}</small
            >
          </div>
          <div class="form-group col-md-4 introjs-scoreboard">
            <span class="font-weight-bold"
              >{{ T.courseNewFormShowScoreboard }}
              <font-awesome-icon
                :title="T.courseNewFormShowScoreboardDesc"
                icon="info-circle"
              />
            </span>
            <omegaup-radio-switch
              :value.sync="showScoreboard"
              :selected-value="showScoreboard"
              name="show-scoreboard"
              :readonly="readOnly"
            ></omegaup-radio-switch>
          </div>
        </div>
        <div class="row">
          <div class="form-group col-md-4">
            <label class="font-weight-bold w-100 introjs-start-date"
              >{{ T.courseNewFormStartDate }}
              <font-awesome-icon
                :title="T.courseNewFormStartDateDesc"
                icon="info-circle" />
              <omegaup-datepicker
                v-model="startTime"
                name="start-date"
                :disabled="readOnly"
                :min="update ? null : new Date()"
              ></omegaup-datepicker
            ></label>
          </div>
          <div class="form-group col-md-4 introjs-duration">
            <span class="font-weight-bold"
              >{{ T.courseNewFormUnlimitedDuration }}
              <font-awesome-icon
                :title="T.courseNewFormUnlimitedDurationDesc"
                icon="info-circle"
              />
            </span>
            <omegaup-radio-switch
              :value.sync="unlimitedDuration"
              :readonly="readOnly"
              :selected-value="unlimitedDuration"
              name="unlimited-duration"
            ></omegaup-radio-switch>
          </div>
          <div class="form-group col-md-4">
            <label class="font-weight-bold w-100 introjs-end-date"
              >{{ T.courseNewFormEndDate }}
              <font-awesome-icon
                :title="T.courseNewFormEndDateDesc"
                icon="info-circle" />
              <omegaup-datepicker
                v-model="finishTime"
                :disabled="readOnly"
                name="end-date"
                :enabled="!unlimitedDuration"
                :is-invalid="invalidParameterName === 'finish_time'"
              ></omegaup-datepicker
            ></label>
          </div>
        </div>
        <div class="row">
          <div class="form-group col-md-4">
            <label class="font-weight-bold w-100 introjs-school">
              <span
                class="field-required"
                :class="{ 'is-complete': isSchoolComplete }"
                >{{ T.profileSchool }}</span
              >
              <omegaup-common-typeahead
                :existing-options="searchResultSchools"
                :options="searchResultSchools"
                :readonly="readOnly"
                :value.sync="school"
                @update-existing-options="
                  (query) => $emit('update-search-result-schools', query)
                "
              ></omegaup-common-typeahead>
            </label>
          </div>
          <div class="form-group col-md-4 introjs-basic-information">
            <span class="font-weight-bold"
              >{{ T.courseNewFormBasicInformationRequired }}
              <font-awesome-icon
                :title="T.courseNewFormBasicInformationRequiredDesc"
                icon="info-circle"
              />
            </span>
            <omegaup-radio-switch
              name="basic-information"
              :readonly="readOnly"
              :value.sync="needsBasicInformation"
              :selected-value="needsBasicInformation"
            ></omegaup-radio-switch>
          </div>
          <div class="form-group col-md-4 introjs-ask-information">
            <span class="font-weight-bold"
              >{{ T.courseNewFormUserInformationRequired }}
              <font-awesome-icon
                :title="T.courseNewFormUserInformationRequiredDesc"
                icon="info-circle"
              />
            </span>
            <select
              v-model="requestsUserInformation"
              data-course-participant-information
              :disabled="readOnly"
              class="form-control"
            >
              <option value="no">
                {{ T.wordsNo }}
              </option>
              <option value="optional">
                {{ T.wordsOptional }}
              </option>
              <option value="required">
                {{ T.wordsRequired }}
              </option>
            </select>
          </div>
        </div>
        <div class="row">
          <div class="form-group col-md-6">
            <label class="font-weight-bold w-100"
              >{{ T.courseNewFormLevel }}
              <font-awesome-icon
                :title="T.courseNewFormLevelDesc"
                icon="info-circle"
              />
            </label>
            <select
              v-model="level"
              :disabled="readOnly"
              data-course-problem-level
              class="form-control introjs-level"
            >
              <option value="" disabled>
                {{ T.courseNewFormLevelPlaceholder }}
              </option>
              <option
                v-for="levelOption in levelOptions"
                :key="levelOption.value"
                :value="levelOption.value"
              >
                {{ levelOption.label }}
              </option>
            </select>
          </div>
          <div class="form-group col-md-6 introjs-language">
            <label class="font-weight-bold w-100">
              <span
                class="field-required"
                :class="{ 'is-complete': isLanguagesComplete }"
                >{{ T.wordsLanguages }}</span
              >
            </label>
            <div
              :class="{
                'is-invalid-wrapper': invalidParameterName === 'languages',
              }"
            >
              <vue-multiselect
                v-model="selectedLanguages"
                :disabled="readOnly"
                :options="Object.keys(allLanguages)"
                :multiple="true"
                :placeholder="T.courseNewFormLanguages"
                :close-on-select="false"
                @select="onSelect"
              >
              </vue-multiselect>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="form-group container-fluid col-md-6">
            <label class="font-weight-bold w-100 introjs-objective"
              >{{ T.courseNewFormObjective }}
              <font-awesome-icon
                :title="T.courseNewFormObjectiveDesc"
                icon="info-circle"
              />
              <textarea
                v-model="objective"
                :disabled="readOnly"
                data-course-objective
                class="form-control"
                :class="{
                  'is-invalid': invalidParameterName === 'objective',
                }"
                cols="30"
                rows="5"
                :maxlength="MAX_LENGTH.objective"
              ></textarea>
            </label>
            <small
              class="character-counter"
              :class="{ 'text-danger': isExceedingObjective }"
              >{{ (objective || '').length }}/{{ MAX_LENGTH.objective }}</small
            >
          </div>
          <div class="form-group container-fluid col-md-6">
            <label class="font-weight-bold w-100 introjs-description">
              <span
                class="field-required"
                :class="{ 'is-complete': isDescriptionComplete }"
                >{{ T.courseNewFormDescription }}</span
              >
              <textarea
                v-model="description"
                :disabled="readOnly"
                data-course-new-description
                class="form-control"
                :class="{
                  'is-invalid': invalidParameterName === 'description',
                }"
                cols="30"
                rows="5"
                required="required"
                :maxlength="MAX_LENGTH.description"
              ></textarea>
            </label>
            <small
              class="character-counter"
              :class="{ 'text-danger': isExceedingDescription }"
              >{{ description.length }}/{{ MAX_LENGTH.description }}</small
            >
          </div>
        </div>
        <div v-if="!readOnly" class="row">
          <div class="form-group col-md-12 text-right">
            <button
              class="btn btn-primary mr-2 submit introjs-submit"
              type="submit"
            >
              <template v-if="update">
                {{ T.courseNewFormUpdateCourse }}
              </template>
              <template v-else>
                {{ T.courseNewFormScheduleCourse }}
              </template>
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop, Emit } from 'vue-property-decorator';
import { types, messages } from '../../api_types';
import T from '../../lang';
import common_Typeahead from '../common/Typeahead.vue';
import DatePicker from '../DatePicker.vue';
import omegaup_RadioSwitch from '../RadioSwitch.vue';
import Multiselect from 'vue-multiselect';
import 'vue-multiselect/dist/vue-multiselect.min.css';
import 'intro.js/introjs.css';
import introJs from 'intro.js';
import VueCookies from 'vue-cookies';
Vue.use(VueCookies, { expire: -1 });

import {
  FontAwesomeIcon,
  FontAwesomeLayers,
  FontAwesomeLayersText,
} from '@fortawesome/vue-fontawesome';
import { fas } from '@fortawesome/free-solid-svg-icons';
import { library } from '@fortawesome/fontawesome-svg-core';
library.add(fas);

const levelOptions = [
  {
    value: 'introductory',
    label: T.courseLevelIntroductory,
  },
  {
    value: 'intermediate',
    label: T.courseLevelIntermediate,
  },
  {
    value: 'advanced',
    label: T.courseLevelAdvanced,
  },
];

const MAX_LENGTH = {
  name: 100,
  alias: 32,
  description: 255,
  objective: 500,
};

const DANGER_THRESHOLD_PERCENTAGE = 0.9;

@Component({
  components: {
    'omegaup-common-typeahead': common_Typeahead,
    'omegaup-datepicker': DatePicker,
    'omegaup-radio-switch': omegaup_RadioSwitch,
    'font-awesome-icon': FontAwesomeIcon,
    'font-awesome-layers': FontAwesomeLayers,
    'font-awesome-layers-text': FontAwesomeLayersText,
    'vue-multiselect': Multiselect,
  },
})
export default class CourseDetails extends Vue {
  @Prop({ default: false }) update!: boolean;
  @Prop({ default: false }) readOnly!: boolean;
  @Prop() course!: types.CourseDetails;
  @Prop({ default: '' }) invalidParameterName!: string;
  @Prop() allLanguages!: string[];
  @Prop() searchResultSchools!: types.SchoolListItem[];
  @Prop({ default: true }) hasVisitedSection!: boolean;

  T = T;
  alias = this.course.alias;
  description = this.course.description;
  finishTime = this.course.finish_time || new Date();
  showScoreboard = this.course.show_scoreboard;
  startTime = this.course.start_time;
  name = this.course.name;
  level = this.course.level ?? '';
  objective = this.course.objective;
  school: null | types.SchoolListItem = this.searchResultSchools[0] ?? null;
  needsBasicInformation = this.course.needs_basic_information;
  requestsUserInformation = this.course.requests_user_information;
  unlimitedDuration = this.course.finish_time === null;
  selectedLanguages = this.course.languages;
  levelOptions = levelOptions;
  MAX_LENGTH = MAX_LENGTH;

  // Computed properties to track if required fields are complete
  get isNameComplete(): boolean {
    return this.name !== null && this.name.trim().length > 0;
  }

  get isAliasComplete(): boolean {
    return this.alias !== null && this.alias.trim().length > 0;
  }

  get isSchoolComplete(): boolean {
    return this.school !== null && this.school.key !== undefined;
  }

  get isLanguagesComplete(): boolean {
    return (this.selectedLanguages?.length ?? 0) > 0;
  }

  get isDescriptionComplete(): boolean {
    return this.description !== null && this.description.trim().length > 0;
  }

  // Computed properties for character limit danger thresholds
  get isExceedingName(): boolean {
    return this.name.length > MAX_LENGTH.name * DANGER_THRESHOLD_PERCENTAGE;
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

  get isExceedingObjective(): boolean {
    return (
      (this.objective || '').length >
      MAX_LENGTH.objective * DANGER_THRESHOLD_PERCENTAGE
    );
  }

  mounted() {
    const title = T.createCourseInteractiveGuideTitle;
    if (!this.hasVisitedSection) {
      introJs()
        .setOptions({
          nextLabel: T.interactiveGuideNextButton,
          prevLabel: T.interactiveGuidePreviousButton,
          doneLabel: T.interactiveGuideDoneButton,
          steps: [
            {
              title,
              intro: T.createCourseInteractiveGuideWelcome,
            },
            {
              element: document.querySelector(
                '.introjs-course-name',
              ) as Element,
              title,
              intro: T.createCourseInteractiveGuideName,
            },
            {
              element: document.querySelector(
                '.introjs-short-title',
              ) as Element,
              title,
              intro: T.createCourseInteractiveGuideShortTitle,
            },
            {
              element: document.querySelector('.introjs-scoreboard') as Element,
              title,
              intro: T.createCourseInteractiveGuideScoreboard,
            },
            {
              element: document.querySelector('.introjs-start-date') as Element,
              title,
              intro: T.createCourseInteractiveGuideStartDate,
            },
            {
              element: document.querySelector('.introjs-duration') as Element,
              title,
              intro: T.createCourseInteractiveGuideDuration,
            },
            {
              element: document.querySelector('.introjs-end-date') as Element,
              title,
              intro: T.createCourseInteractiveGuideEndDate,
            },
            {
              element: document.querySelector('.introjs-school') as Element,
              title,
              intro: T.createCourseInteractiveGuideSchool,
            },
            {
              element: document.querySelector(
                '.introjs-basic-information',
              ) as Element,
              title,
              intro: T.createCourseInteractiveGuideBasicInformation,
            },
            {
              element: document.querySelector(
                '.introjs-ask-information',
              ) as Element,
              title,
              intro: T.createCourseInteractiveGuideAskInformation,
            },
            {
              element: document.querySelector('.introjs-level') as Element,
              title,
              intro: T.createCourseInteractiveGuideLevel,
            },
            {
              element: document.querySelector('.introjs-language') as Element,
              title,
              intro: T.createCourseInteractiveGuideLanguage,
            },
            {
              element: document.querySelector('.introjs-objective') as Element,
              title,
              intro: T.createCourseInteractiveGuideObjective,
            },
            {
              element: document.querySelector(
                '.introjs-description',
              ) as Element,
              title,
              intro: T.createCourseInteractiveGuideDescription,
            },
            {
              element: document.querySelector('.introjs-submit') as Element,
              title,
              intro: T.createCourseInteractiveGuideSubmit,
            },
          ],
        })
        .start();
      this.$cookies.set('has-visited-create-course', true, -1);
    }
  }

  reset(): void {
    this.alias = this.course.alias;
    this.description = this.course.description;
    this.finishTime = this.course.finish_time || new Date();
    this.showScoreboard = this.course.show_scoreboard;
    this.startTime = this.course.start_time;
    this.name = this.course.name;
    this.school = this.searchResultSchools[0];
    this.needsBasicInformation = this.course.needs_basic_information;
    this.requestsUserInformation = this.course.requests_user_information;
    this.unlimitedDuration = this.course.finish_time === null;
  }

  onSubmit(): void {
    if (!this.selectedLanguages || this.selectedLanguages.length === 0) {
      this.$emit('invalid-languages');
      return;
    }
    const request: messages.CourseUpdateRequest = {
      name: this.name,
      description: this.description,
      objective: this.objective,
      start_time: this.startTime,
      alias: this.alias,
      languages: this.selectedLanguages,
      show_scoreboard: this.showScoreboard,
      needs_basic_information: this.needsBasicInformation,
      requests_user_information: this.requestsUserInformation,
      school: this.school,
      unlimited_duration: this.unlimitedDuration,
      finish_time: !this.unlimitedDuration
        ? new Date(this.finishTime).setHours(23, 59, 59, 999) / 1000
        : null,
    };
    // Only include level if the user selected a value
    if (this.level) {
      request.level = this.level;
    }
    this.$emit('submit', request);
  }

  onSelect(): void {
    // Clear the languages validation error when a language is selected
    this.$emit('clear-language-error');
  }

  @Emit('emit-cancel')
  onCancel(): void {
    this.reset();
  }
}
</script>

<style lang="scss" scoped>
@import '../../../../sass/main.scss';
@import '../../../../../../node_modules/vue-multiselect/dist/vue-multiselect.min.css';

.multiselect__tag {
  background: var(--multiselect-tag-background-color);
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
