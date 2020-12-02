<template>
  <div class="omegaup-edit-identity card">
    <h2 class="mx-2">
      <span>{{ identity.username }}</span>
    </h2>
    <div class="card-body">
      <form role="form" @submit.prevent="onEditMember">
        <div class="form-row">
          <div class="form-group col-lg-4 col-md-6 col-sm-6">
            <label class="d-block">
              {{ T.username }}
              <div class="input-group">
                <div class="input-group-prepend">
                  <div class="input-group-text">{{ groupName }}:</div>
                </div>
                <input v-model="identityName" class="form-control" />
              </div>
            </label>
          </div>
          <div class="form-group col-lg-4 col-md-6 col-sm-6">
            <label class="d-block">
              {{ T.profile }}
              <input v-model="name" class="form-control" />
            </label>
          </div>
          <div class="form-group col-lg-4 col-md-6 col-sm-6">
            <label class="d-block">
              {{ T.wordsGender }}
              <select v-model="gender" class="form-control">
                <option value="female">{{ T.wordsGenderFemale }}</option>
                <option value="male">{{ T.wordsGenderMale }}</option>
                <option value="other">{{ T.wordsGenderOther }}</option>
                <option value="decline">{{ T.wordsGenderDecline }}</option>
              </select>
            </label>
          </div>
          <div class="form-group col-lg-4 col-md-6 col-sm-6">
            <label class="d-block">
              {{ T.userEditCountry }}
              <select v-model="selectedCountry" class="form-control">
                <option
                  v-for="country in countries"
                  :key="country.country_id"
                  :value="country.country_id"
                >
                  {{ country.name }}
                </option>
              </select>
            </label>
          </div>
          <div class="form-group col-lg-4 col-md-6 col-sm-6">
            <label class="d-block">
              {{ T.profileState }}
              <select v-model="selectedState" class="form-control">
                <option
                  v-for="[code, state] in Object.entries(countryStates)"
                  :key="code"
                  :value="code.split('-')[1]"
                >
                  {{ state.name }}
                </option>
              </select>
            </label>
          </div>
          <div class="form-group col-lg-4 col-md-6 col-sm-6">
            <label class="d-block">
              {{ T.profileSchool }}
              <omegaup-autocomplete
                v-model="school"
                class="form-control"
                :init="(el) => typeahead.schoolTypeahead(el)"
              ></omegaup-autocomplete>
              <input type="hidden" :value="schoolId" />
            </label>
          </div>
        </div>
        <div class="form-group float-right">
          <button class="btn btn-primary">{{ T.wordsSaveChanges }}</button>
          <button
            class="btn btn-secondary ml-2"
            type="reset"
            @click="$emit('cancel')"
          >
            {{ T.wordsCancel }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Watch, Prop } from 'vue-property-decorator';
import { omegaup } from '../../omegaup';
import T from '../../lang';
import * as iso3166 from '@/third_party/js/iso-3166-2.js/iso3166.min.js';
import * as typeahead from '../../typeahead';
import Autocomplete from '../Autocomplete.vue';

@Component({
  components: {
    'omegaup-autocomplete': Autocomplete,
  },
})
export default class IdentityEdit extends Vue {
  @Prop() identity!: omegaup.Identity;
  @Prop() countries!: iso3166.Country[];

  T = T;
  typeahead = typeahead;
  username = this.identity.username;
  name = this.identity.name;
  gender = this.identity.gender;
  school = this.identity.school;
  schoolId = this.identity.school_id;
  selectedCountry = this.identity.country_id ?? 'MX';
  selectedState = this.identity.state_id;

  @Watch('identity')
  onIdentityChanged(newIdentity: omegaup.Identity) {
    this.username = newIdentity.username;
    this.name = newIdentity.name;
    this.gender = newIdentity.gender;
    this.school = newIdentity.school;
    this.schoolId = newIdentity.school_id;
    this.selectedCountry = newIdentity.country_id ?? 'MX';
    this.selectedState = newIdentity.state_id;
  }

  @Watch('selectedCountry')
  onPropertyChanged(newContry: string) {
    if (this.identity.country_id == newContry) {
      this.selectedState = this.identity.state_id;
    } else {
      this.selectedState = Object.keys(this.countryStates)[0].split('-')[1];
    }
  }

  get groupName(): string {
    if (typeof this.identity === 'undefined') {
      return '';
    }
    return `${this.identity.username.split(':')[0]}`;
  }

  get identityName(): string {
    return this.username.split(':')[1];
  }
  set identityName(username: string) {
    this.username = `${this.groupName}:${username}`;
  }

  get countryStates(): iso3166.Subdivisions {
    const countrySelected = iso3166.country(this.selectedCountry);
    return countrySelected.sub;
  }

  onEditMember(): void {
    this.$emit(
      'edit-identity-member',
      this.identity.username,
      this.username,
      this.name,
      this.gender,
      this.selectedCountry,
      this.selectedState,
      this.school,
      this.schoolId,
    );
  }
}
</script>
