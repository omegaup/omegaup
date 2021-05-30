<template>
  <div class="card">
    <h5 class="card-title mx-2">
      {{ identity.username }}
    </h5>
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
              <omegaup-autocomplete
                v-model="selectedIdentity.school"
                class="form-control"
                :init="
                  (el) =>
                    typeahead.schoolTypeahead(el, (event, item) => {
                      selectedIdentity.school = item.value;
                      selectedIdentity.school_id = item.id;
                    })
                "
              ></omegaup-autocomplete>
              <input type="hidden" :value="selectedIdentity.schoolId" />
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
import { Vue, Component, Prop } from 'vue-property-decorator';
import type { types } from '../../api_types';
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
  @Prop({ default: null }) identity!: types.Identity | null;
  @Prop() countries!: iso3166.Country[];

  T = T;
  typeahead = typeahead;
  selectedIdentity = Object.assign(
    {
      username: '',
      classname: '',
      name: '',
      gender: '',
      school: '',
      school_id: 0,
      country_id: 'MX',
      state_id: '',
    } as types.Identity,
    this.identity,
  );

  get groupName(): string {
    return this.selectedIdentity.username.split(':')[0];
  }

  get identityName(): string {
    return this.selectedIdentity.username.split(':')[1];
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
    this.$emit(
      'edit-identity-member',
      this.identity?.username,
      this.selectedIdentity,
    );
  }
}
</script>
