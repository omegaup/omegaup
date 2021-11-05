<template>
  <div class="card">
    <div class="card-header">
      <h3 class="card-title">{{ T.userEditPreferences }}</h3>
    </div>
    <form
      role="form"
      class="card-body"
      @submit.prevent="onUpdateUserPreferences"
    >
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
        <select v-model="locale" data-locale class="form-control">
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
          class="form-control"
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
        <label>
          <input
            v-model="isPrivate"
            type="checkbox"
            :checked="isPrivate"
            data-is-private
            class="mr-2"
          />{{ T.userEditPrivateProfile }}
        </label>
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
        <button type="submit" class="btn btn-primary mr-2">
          {{ T.wordsSaveChanges }}
        </button>
        <a href="/profile" class="btn btn-cancel">{{ T.wordsCancel }}</a>
      </div>
    </form>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { types } from '../../api_types';
import T from '../../lang';

@Component({
  components: {},
})
export default class UserPreferencesEdit extends Vue {
  @Prop() profile!: types.UserProfileInfo;

  T = T;
  email = this.profile.email;
  locale = this.profile.locale;
  preferredLanguage = this.profile.preferred_language;
  programmingLanguages = this.profile.programming_languages;
  isPrivate = this.profile.is_private;
  hideProblemTags = this.profile.hide_problem_tags;

  onUpdateUserPreferences(): void {
    this.$emit('update-user-preferences', {
      locale: this.locale,
      localeChanged: this.locale != this.profile.locale,
      preferredLanguage: this.preferredLanguage
        ? this.preferredLanguage
        : undefined,
      isPrivate: this.isPrivate,
      hideProblemTags: this.hideProblemTags,
    });
  }
}
</script>
