<template>
  <form
    role="form"
    class="card-body"
    @submit.prevent="onUpdateUserBasicInformation"
  >
    <div class="form-group">
      <label>{{ T.username }}</label>
      <input v-model="username" data-username class="form-control" />
    </div>
    <div class="form-group">
      <label>{{ T.wordsName }}</label>
      <input v-model="name" data-name class="form-control" />
    </div>
    <div class="form-group">
      <label>{{ T.wordsGender }}</label>
      <select v-model="gender" data-gender class="form-control">
        <option value="female">{{ T.wordsGenderFemale }}</option>
        <option value="male">{{ T.wordsGenderMale }}</option>
        <option value="other">{{ T.wordsGenderOther }}</option>
        <option value="decline">{{ T.wordsGenderDecline }}</option>
      </select>
    </div>
    <div class="form-group">
      <label>{{ T.wordsCountry }}</label>
      <select v-model="countryId" data-countries class="form-control">
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
        v-model="stateId"
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
        v-model="birthDate"
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
export default class UserBasicInformationEdit extends Vue {
  @Prop() data!: types.UserProfileEditDetailsPayload;
  @Prop() profile!: types.UserProfileInfo;

  T = T;
  countries = this.data.countries;
  username = this.profile.username;
  name = this.profile.name;
  gender = this.profile.gender;
  countryId = this.profile.country_id ?? null;
  stateId = this.profile.state_id ?? null;
  birthDate = this.profile.birth_date
    ? time.convertLocalDateToGMTDate(this.profile.birth_date)
    : new Date('');

  get isCountrySelected(): boolean {
    return Boolean(this.countryId);
  }

  get countryStates(): iso3166.Subdivisions {
    const countryId = this.countryId;
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

  onUpdateUserBasicInformation(): void {
    const newUserBasicInformation = {
      username: this.username,
      name: this.name,
      gender: this.gender,
      countryId: this.countryId,
      stateId: this.stateId,
      birthDate: isNaN(this.birthDate.getTime()) ? null : this.birthDate,
    };
    this.$emit('update-user-basic-information', newUserBasicInformation);
  }

  @Watch('countryId')
  onCountryIdChanged(newCountryId: string): void {
    if (!newCountryId) {
      this.countryId = null;
      this.stateId = null;
      return;
    }
    this.stateId = Object.keys(this.countryStates)[0].split('-')[1];
  }
}
</script>
