<template>
  <form role="form" class="card-body" @submit.prevent="onUpdateUserPreferences">
    <div class="form-group">
      <label>{{ T.userEditProfileImage }}</label>
      <a
        href="http://www.gravatar.com"
        target="_blank"
        data-email
        class="btn btn-link"
      >
        {{ T.userEditGravatar }} {{ email }}
      </a>
    </div>
    <div class="form-group">
      <label>{{ T.userEditLanguage }}</label>
      <select
        v-model="locale"
        data-locale
        data-preference-language
        class="custom-select"
      >
        <option value="es">{{ T.wordsSpanish }}</option>
        <option value="en">{{ T.wordsEnglish }}</option>
        <option value="pt">{{ T.wordsPortuguese }}</option>
      </select>
    </div>
    <div class="form-group">
      <label>{{ T.userEditPreferredProgrammingLanguage }}</label>
      <select
        v-model="preferredLanguage"
        data-preferred-language
        class="custom-select"
      >
        <option value=""></option>
        <option
          v-for="[extension, name] in Object.entries(programmingLanguages)"
          :key="extension"
          :value="extension"
        >
          {{ name }}
        </option>
      </select>
    </div>
    <div class="form-group">
      <label>{{ T.userObjectivesModalDescriptionUsage }}</label>
      <select
        v-model="learningTeachingObjective"
        data-learning-teaching-objective
        class="custom-select"
      >
        <option :value="ObjectivesAnswers.Learning">
          {{ T.userObjectivesModalAnswerLearning }}
        </option>
        <option :value="ObjectivesAnswers.Teaching">
          {{ T.userObjectivesModalAnswerTeaching }}
        </option>
        <option :value="ObjectivesAnswers.LearningAndTeaching">
          {{ T.userObjectivesModalAnswerLearningAndTeaching }}
        </option>
        <option :value="ObjectivesAnswers.None">
          {{ T.userObjectivesModalAnswerNone }}
        </option>
      </select>
    </div>
    <div class="form-group">
      <label>{{ scholarCompetitiveObjectiveQuestion }}</label>
      <select
        v-model="scholarCompetitiveObjective"
        :disabled="learningTeachingObjective === ObjectivesAnswers.None"
        data-scholar-competitive-objective
        class="custom-select"
      >
        <option :value="ObjectivesAnswers.Scholar">
          {{ T.userObjectivesModalAnswerScholar }}
        </option>
        <option :value="ObjectivesAnswers.Competitive">
          {{ T.userObjectivesModalAnswerCompetitive }}
        </option>
        <option :value="ObjectivesAnswers.ScholarAndCompetitive">
          {{ T.userObjectivesModalAnswerScholarAndCompetitive }}
        </option>
        <option :value="ObjectivesAnswers.Other">
          {{ T.userObjectivesModalAnswerOther }}
        </option>
      </select>
    </div>
    <div class="form-group">
      <label>
        <input
          v-model="isPrivate"
          type="checkbox"
          :checked="isPrivate"
          data-is-private
          class="mr-2"
          @change="handlePrivateProfileCheckboxChange"
        />{{ T.userEditPrivateProfile }}
      </label>
      <!-- id-lint off -->
      <b-button
        id="popover-private-profile"
        class="ml-1"
        size="sm"
        variant="none"
        @click="show = !show"
      >
        <font-awesome-icon :icon="['fas', 'question-circle']" />
      </b-button>
      <!-- id-lint on -->
      <b-popover
        :show.sync="show"
        target="popover-private-profile"
        variant="danger"
        placement="right"
      >
        <template #title>{{ T.profilePrivateRankMessageTitle }}</template>
        {{ T.profilePrivateRankMessage }}
      </b-popover>
    </div>
    <div class="form-group">
      <label>
        <input
          v-model="hideProblemTags"
          type="checkbox"
          :checked="hideProblemTags"
          data-hide-problem-tags
          class="mr-2"
        />{{ T.userEditHideProblemTags }}
      </label>
    </div>
    <div class="mt-3">
      <button
        type="submit"
        class="btn btn-primary mr-2"
        data-preference-save-button
        :disabled="!hasChanges"
      >
        {{ T.wordsSaveChanges }}
      </button>
      <a href="/profile/" class="btn btn-cancel">{{ T.wordsCancel }}</a>
    </div>
  </form>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { ObjectivesAnswers } from './ObjectivesQuestions.vue';
import { types } from '../../api_types';
import T from '../../lang';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';

// Import Bootstrap and BootstrapVue CSS files (order is important)
import 'bootstrap/dist/css/bootstrap.css';
import 'bootstrap-vue/dist/bootstrap-vue.css';

// Import Only Required Plugins
import { ButtonPlugin, PopoverPlugin } from 'bootstrap-vue';
Vue.use(ButtonPlugin);
Vue.use(PopoverPlugin);

@Component({
  components: {
    FontAwesomeIcon,
  },
})
export default class UserPreferencesEdit extends Vue {
  @Prop() profile!: types.UserProfileInfo;

  show: boolean = false;
  T = T;
  ObjectivesAnswers = ObjectivesAnswers;
  email = this.profile.email;
  locale = this.profile.locale;
  preferredLanguage = this.profile.preferred_language;
  programmingLanguages = this.profile.programming_languages;
  isPrivate = this.profile.is_private;
  hideProblemTags = this.profile.hide_problem_tags;
  hasCompetitiveObjective = this.profile.has_competitive_objective ?? false;
  hasLearningObjective = this.profile.has_learning_objective ?? true;
  hasScholarObjective = this.profile.has_scholar_objective ?? true;
  hasTeachingObjective = this.profile.has_teaching_objective ?? false;

  get scholarCompetitiveObjectiveQuestion(): string {
    if (this.hasLearningObjective && this.hasTeachingObjective) {
      return this.T.userObjectivesModalDescriptionLearningAndTeaching;
    }
    if (this.hasLearningObjective) {
      return this.T.userObjectivesModalDescriptionLearning;
    }
    if (this.hasTeachingObjective) {
      return this.T.userObjectivesModalDescriptionTeaching;
    }
    return T.userObjectivesModalDescriptionUsage;
  }

  get learningTeachingObjective(): string {
    if (this.hasLearningObjective && this.hasTeachingObjective) {
      return ObjectivesAnswers.LearningAndTeaching;
    }
    if (this.hasLearningObjective) {
      return ObjectivesAnswers.Learning;
    }
    if (this.hasTeachingObjective) {
      return ObjectivesAnswers.Teaching;
    }
    return ObjectivesAnswers.None;
  }

  set learningTeachingObjective(newValue: string) {
    switch (newValue) {
      case ObjectivesAnswers.Learning:
        this.hasLearningObjective = true;
        this.hasTeachingObjective = false;
        break;
      case ObjectivesAnswers.Teaching:
        this.hasLearningObjective = false;
        this.hasTeachingObjective = true;
        break;
      case ObjectivesAnswers.LearningAndTeaching:
        this.hasLearningObjective = true;
        this.hasTeachingObjective = true;
        break;
      case ObjectivesAnswers.None:
        this.hasLearningObjective = false;
        this.hasTeachingObjective = false;
        this.hasScholarObjective = false;
        this.hasCompetitiveObjective = false;
        break;
    }
  }

  get scholarCompetitiveObjective(): string {
    if (this.hasCompetitiveObjective && this.hasScholarObjective) {
      return ObjectivesAnswers.ScholarAndCompetitive;
    }
    if (this.hasCompetitiveObjective) {
      return ObjectivesAnswers.Competitive;
    }
    if (this.hasScholarObjective) {
      return ObjectivesAnswers.Scholar;
    }
    return ObjectivesAnswers.Other;
  }

  set scholarCompetitiveObjective(newValue: string) {
    switch (newValue) {
      case ObjectivesAnswers.Scholar:
        this.hasScholarObjective = true;
        this.hasCompetitiveObjective = false;
        break;
      case ObjectivesAnswers.Competitive:
        this.hasScholarObjective = false;
        this.hasCompetitiveObjective = true;
        break;
      case ObjectivesAnswers.ScholarAndCompetitive:
        this.hasScholarObjective = true;
        this.hasCompetitiveObjective = true;
        break;
      case ObjectivesAnswers.Other:
        this.hasScholarObjective = false;
        this.hasCompetitiveObjective = false;
        break;
    }
  }

  get hasChanges(): boolean {
    return (
      this.locale !== this.profile.locale ||
      this.preferredLanguage !== this.profile.preferred_language ||
      this.isPrivate !== this.profile.is_private ||
      this.hideProblemTags !== this.profile.hide_problem_tags ||
      this.hasCompetitiveObjective !==
        (this.profile.has_competitive_objective ?? false) ||
      this.hasLearningObjective !==
        (this.profile.has_learning_objective ?? true) ||
      this.hasScholarObjective !==
        (this.profile.has_scholar_objective ?? true) ||
      this.hasTeachingObjective !==
        (this.profile.has_teaching_objective ?? false)
    );
  }

  onUpdateUserPreferences(): void {
    this.$emit('update-user-preferences', {
      userPreferences: {
        locale: this.locale,
        preferred_language: this.preferredLanguage ?? null,
        is_private: this.isPrivate,
        hide_problem_tags: this.hideProblemTags,
        has_competitive_objective: this.hasCompetitiveObjective,
        has_learning_objective: this.hasLearningObjective,
        has_scholar_objective: this.hasScholarObjective,
        has_teaching_objective: this.hasTeachingObjective,
      },
      localeChanged: this.locale != this.profile.locale,
    });
  }

  handlePrivateProfileCheckboxChange(): void {
    this.show = this.isPrivate;
  }
}
</script>
