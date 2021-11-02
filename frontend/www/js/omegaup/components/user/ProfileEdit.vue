<template>
  <div class="card">
    <div class="card-header">
      <h3 class="card-title">{{ T.userEditEditProfile }}</h3>
    </div>
    <form role="form" class="card-body" @submit.prevent="onUpdateUser">
      <div class="form-group">
        <label>{{ T.username }}</label>
        <input v-model="selectedProfileInfo.username" class="form-control" />
      </div>
      <div class="form-group">
        <label>{{ T.wordsName }}</label>
        <input v-model="selectedProfileInfo.name" class="form-control" />
      </div>
      <div class="form-group">
        <label>{{ T.userEditBirthDate }}</label>
        <omegaup-datepicker
          v-model="selectedProfileInfo.birth_date"
          :required="false"
        ></omegaup-datepicker>
      </div>
      <div class="form-group">
        <label>{{ T.wordsGender }}</label>
        <select v-model="selectedProfileInfo.gender" class="form-control">
          <option value="female">{{ T.wordsGenderFemale }}</option>
          <option value="male">{{ T.wordsGenderMale }}</option>
          <option value="other">{{ T.wordsGenderOther }}</option>
          <option value="decline">{{ T.wordsGenderDecline }}</option>
        </select>
      </div>
      <div class="form-group">
        <label>{{ T.wordsCountry }}</label>
        <select
          v-model="selectedProfileInfo.country_id"
          data-countries
          class="form-control"
        >
          <option value=""></option>
          <option
            v-for="country in countries"
            :key="country.country_id"
            :value="country.country_id"
          >
            {{ country.name }}
          </option>
        </select>
      </div>
      <div class="form-group">
        <label>{{ T.profileState }}</label>
        <select
          v-model="selectedProfileInfo.state_id"
          data-states
          :disabled="!isCountrySelected"
          class="form-control"
        >
          <option
            v-for="[code, state] in Object.entries(countryStates)"
            :key="code"
            :value="code.split('-')[1]"
          >
            {{ state.name }}
          </option>
        </select>
      </div>
      <div class="form-group">
        <label>{{ T.profileSchool }}</label>
        <omegaup-autocomplete
          v-model="selectedProfileInfo.school"
          class="form-control"
          :init="
            (el) =>
              typeahead.schoolTypeahead(el, (event, val) => {
                selectedProfileInfo.school = val.value;
                selectedProfileInfo.school_id = val.id;
              })
          "
        ></omegaup-autocomplete>
        <input v-model="selectedProfileInfo.school_id" type="hidden" />
      </div>
      <div class="form-group">
        <label>{{ T.userEditGraduationDate }}</label>
        <omegaup-datepicker
          v-model="selectedProfileInfo.graduation_date"
          :required="false"
          :enabled="isSchoolSet"
        ></omegaup-datepicker>
      </div>
      <div class="form-group">
        <label>{{ T.userEditLanguage }}</label>
        <select v-model="selectedProfileInfo.locale" class="form-control">
          <option value="es">{{ T.wordsSpanish }}</option>
          <option value="en">{{ T.wordsEnglish }}</option>
          <option value="pt">{{ T.wordsPortuguese }}</option>
        </select>
      </div>
      <div class="form-group">
        <label>{{ T.userEditSchoolGrade }}</label>
        <select
          v-model="selectedProfileInfo.scholar_degree"
          class="form-control"
        >
          <option value="none">{{ T.userEditNone }}</option>
          <option value="early_childhood">
            {{ T.userEditEarlyChildhood }}
          </option>
          <option value="pre_primary">{{ T.userEditPrePrimary }}</option>
          <option value="primary">{{ T.userEditPrimary }}</option>
          <option value="lower_secondary">
            {{ T.userEditLowerSecondary }}
          </option>
          <option value="upper_secondary">
            {{ T.userEditUpperSecondary }}
          </option>
          <option value="post_secondary">{{ T.userEditPostSecondary }}</option>
          <option value="tertiary">{{ T.userEditTertiary }}</option>
          <option value="bachelors">{{ T.userEditBachelors }}</option>
          <option value="master">{{ T.userEditMaster }}</option>
          <option value="doctorate">{{ T.userEditDoctorate }}</option>
        </select>
      </div>
      <div class="form-group">
        <label>{{ T.userEditPreferredProgrammingLanguage }}</label>
        <select
          v-model="selectedProfileInfo.preferred_language"
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
        <label>{{ T.userEditProfileImage }}</label>
        <a href="http://www.gravatar.com" target="_blank" class="btn btn-link">
          {{ T.userEditGravatar }} {{ selectedProfileInfo.email }}
        </a>
      </div>
      <div class="form-group">
        <label>
          <input
            v-model="selectedProfileInfo.is_private"
            type="checkbox"
            :checked="selectedProfileInfo.is_private"
            class="mr-2"
          />{{ T.userEditPrivateProfile }}
        </label>
      </div>
      <div class="form-group">
        <span>&nbsp;</span>
        <label>
          <input
            v-model="selectedProfileInfo.hide_problem_tags"
            type="checkbox"
            name="hide_problem_tags"
            class="mr-2"
            :checked="selectedProfileInfo.hide_problem_tags"
          />{{ T.userEditHideProblemTags }}
        </label>
      </div>
      <div>
        <button type="submit" class="btn btn-primary mr-2">
          {{ T.wordsSaveChanges }}
        </button>
        <a href="/profile" class="btn btn-cancel">{{ T.wordsCancel }}</a>
      </div>
    </form>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';
import { types } from '../../api_types';
import T from '../../lang';
import * as iso3166 from '@/third_party/js/iso-3166-2.js/iso3166.min.js';
import * as typeahead from '../../typeahead';
import Autocomplete from '../Autocomplete.vue';
import DatePicker from '../DatePicker.vue';

@Component({
  components: {
    'omegaup-datepicker': DatePicker,
    'omegaup-autocomplete': Autocomplete,
  },
})
export default class UserProfileEdit extends Vue {
  @Prop() data!: types.UserProfileEditDetailsPayload;
  @Prop() profile!: types.UserProfileInfo;

  T = T;
  typeahead = typeahead;
  countries = this.data.countries;
  programmingLanguages = this.profile.programming_languages;
  selectedProfileInfo = {
    ...this.profile,
    birth_date: new Date(
      this.profile.birth_date?.toUTCString().replace('GMT', '') ?? '',
    ),
    graduation_date: new Date(
      this.profile.graduation_date?.toUTCString().replace('GMT', '') ?? '',
    ),
  };

  get isCountrySelected(): boolean {
    return Boolean(this.selectedProfileInfo.country_id);
  }

  get countryStates(): iso3166.Subdivisions {
    const countryId = this.selectedProfileInfo.country_id;
    if (!countryId) {
      return {};
    }
    const countrySelected = iso3166.country(countryId);
    const subdivisions: iso3166.Subdivisions = Object.entries(
      countrySelected.sub,
    )
      .sort((a, b) => Intl.Collator().compare(a[0], b[0]))
      .reduce((r, [code, name]: any) => ({ ...r, [code]: name }), {});
    return subdivisions;
  }

  get isSchoolSet(): boolean {
    return Boolean(this.selectedProfileInfo.school);
  }

  onUpdateUser(): void {
    const user: types.UserProfileInfo = {
      ...this.selectedProfileInfo,
      birth_date: isNaN(this.selectedProfileInfo.birth_date.getTime())
        ? undefined
        : this.selectedProfileInfo.birth_date,
      graduation_date: isNaN(this.selectedProfileInfo.graduation_date.getTime())
        ? undefined
        : this.selectedProfileInfo.graduation_date,
      school_id:
        this.selectedProfileInfo.school_id === this.profile.school_id &&
        this.selectedProfileInfo.school !== this.profile.school
          ? undefined
          : this.selectedProfileInfo.school_id,
    };
    const localeChanged =
      this.selectedProfileInfo.locale != this.profile.locale;
    this.$emit('update-user', { user, localeChanged });
  }

  @Watch('selectedProfileInfo.country_id')
  onCountryIdChanged(newCountryId: string): void {
    if (!newCountryId) {
      this.selectedProfileInfo.country_id = undefined;
      this.selectedProfileInfo.state_id = undefined;
      return;
    }
    this.selectedProfileInfo.state_id = Object.keys(
      this.countryStates,
    )[0].split('-')[1];
  }

  @Watch('selectedProfileInfo.preferred_language')
  onPreferredLanguageChanged(newPreferredLanguage: string): void {
    if (!newPreferredLanguage) {
      this.selectedProfileInfo.preferred_language = undefined;
    }
  }
}
</script>
