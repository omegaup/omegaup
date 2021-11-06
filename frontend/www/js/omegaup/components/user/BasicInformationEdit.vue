<template>
  <div class="card">
    <div class="card-header">
      <h3 class="card-title">{{ T.userEditBasicInformation }}</h3>
    </div>
    <form role="form" class="card-body" @submit.prevent="onUpdateUser">
      <div class="form-group">
        <label>{{ T.username }}</label>
        <input
          v-model="selectedProfileInfo.username"
          data-username
          class="form-control"
        />
      </div>
      <div class="form-group">
        <label>{{ T.wordsName }}</label>
        <input
          v-model="selectedProfileInfo.name"
          data-name
          class="form-control"
        />
      </div>
      <div class="form-group">
        <label>{{ T.wordsGender }}</label>
        <select
          v-model="selectedProfileInfo.gender"
          data-gender
          class="form-control"
        >
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
        <label>{{ T.userEditBirthDate }}</label>
        <omegaup-datepicker
          v-model="selectedProfileInfo.birth_date"
          :required="false"
        ></omegaup-datepicker>
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
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';
import { types } from '../../api_types';
import T from '../../lang';
import * as time from '../../time';
import * as iso3166 from '@/third_party/js/iso-3166-2.js/iso3166.min.js';
import DatePicker from '../DatePicker.vue';

@Component({
  components: {
    'omegaup-datepicker': DatePicker,
  },
})
export default class UserProfileEdit extends Vue {
  @Prop() data!: types.UserProfileEditDetailsPayload;
  @Prop() profile!: types.UserProfileInfo;

  T = T;
  countries = this.data.countries;
  selectedProfileInfo = {
    ...this.profile,
    birth_date: this.profile.birth_date
      ? time.convertLocalDateToGMTDate(this.profile.birth_date)
      : new Date(''),
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

  onUpdateUser(): void {
    const user: types.UserProfileInfo = {
      ...this.selectedProfileInfo,
      birth_date: isNaN(this.selectedProfileInfo.birth_date.getTime())
        ? undefined
        : this.selectedProfileInfo.birth_date,
    };
    this.$emit('update-user-basic-information', { user: user });
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
}
</script>
