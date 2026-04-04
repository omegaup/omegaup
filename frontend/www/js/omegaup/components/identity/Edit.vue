<template>
  <div class="card">
    <h5 class="card-title mx-2">
      {{ identity.username }}
    </h5>
    <div class="card-body">
      <form role="form" @submit.prevent="onEditMember">
        <div class="form-row">
          <div class="form-group col-lg-5 col-md-6 col-sm-6">
            <label class="d-block">
              {{ T.username }}
              <div class="input-group">
                <div class="input-group-prepend">
                  <div class="input-group-text">{{ groupName }}:</div>
                </div>
                <input
                  v-model="identityName"
                  class="form-control"
                  data-identity-name
                />
              </div>
            </label>
          </div>
          <div class="form-group col-lg-3 col-md-6 col-sm-6">
            <label class="d-block">
              {{ T.profile }}
              <input v-model="selectedIdentity.name" class="form-control" />
            </label>
          </div>
          <div class="form-group col-lg-4 col-md-6 col-sm-6">
            <label class="d-block">
              {{ T.wordsGender }}
              <select v-model="selectedIdentity.gender" class="form-control">
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
              <select
                v-model="selectedIdentity.country_id"
                class="form-control"
              >
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
              <select v-model="selectedIdentity.state_id" class="form-control">
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
              <omegaup-common-typeahead
                :existing-options="searchResultSchools"
                :options="searchResultSchools"
                :value.sync="school"
                @update-existing-options="
                  (query) => $emit('update-search-result-schools', query)
                "
              ></omegaup-common-typeahead>
            </label>
          </div>
        </div>
        <div class="form-group float-right">
          <button class="btn btn-primary" data-update-identity>
            {{ T.wordsSaveChanges }}
          </button>
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
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';
import type { types } from '../../api_types';
import T from '../../lang';
import * as iso3166 from '@/third_party/js/iso-3166-2.js/iso3166.min.js';
import common_Typeahead from '../common/Typeahead.vue';

@Component({
  components: {
    'omegaup-common-typeahead': common_Typeahead,
  },
})
export default class IdentityEdit extends Vue {
  @Prop({ default: null }) identity!: types.Identity | null;
  @Prop() countries!: iso3166.Country[];
  @Prop() searchResultSchools!: types.SchoolListItem[];

  T = T;
  selectedIdentity = Object.assign(
    {
      username: '',
      classname: '',
      name: '',
      gender: '',
      school: '',
      country_id: 'MX',
      state_id: '',
    } as types.Identity,
    this.identity,
  );
  school: null | types.SchoolListItem = this.searchResultSchools[0] ?? null;

  get groupName(): string {
    const teamUsername = this.selectedIdentity.username.split(':');
    if (teamUsername.length === 2) {
      return teamUsername[0];
    }
    return `${teamUsername[0]}:${teamUsername[1]}`;
  }

  get identityName(): string {
    const teamUsername = this.selectedIdentity.username.split(':');
    if (teamUsername.length === 2) {
      return teamUsername[1];
    }
    return teamUsername[2];
  }
  set identityName(username: string) {
    this.selectedIdentity.username = `${this.groupName}:${username}`;
  }

  get countryStates(): iso3166.Subdivisions {
    const countryId = this.selectedIdentity.country_id || 'MX';
    const countrySelected = iso3166.country(countryId);
    return countrySelected.sub;
  }

  onEditMember(): void {
    this.$emit('edit-identity-member', {
      originalUsername: this.identity?.username,
      identity: {
        ...this.selectedIdentity,
        school: this.school?.value,
      },
    });
  }

  @Watch('identity')
  onIdentityChanged(newValue: types.Identity): void {
    this.selectedIdentity = newValue;
  }
}
</script>
