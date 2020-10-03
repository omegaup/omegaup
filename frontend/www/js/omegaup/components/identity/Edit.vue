<template>
  <div class="omegaup-course-details panel">
    <div>
      <h1>
        <span
          ><a class="course-header">{{ username }}</a></span
        >
      </h1>
    </div>
    <div class="panel-body">
      <form
        class="form-horizontal"
        role="form"
        v-on:submit.prevent="onEditMember"
      >
        <div class="row">
          <div class="form-group">
            <label class="col-md-4 col-sm-4 control-label" for="username">{{
              T.username
            }}</label>
            <div class="col-md-7 col-sm-7">
              <div class="input-group">
                <span class="input-group-addon">{{ groupName }}:</span>
                <input
                  v-model="identityName"
                  class="form-control"
                  name="username"
                  size="30"
                  type="text"
                />
              </div>
            </div>
          </div>
          <div class="form-group">
            <label class="col-md-4 col-sm-4 control-label" for="name">{{
              T.profile
            }}</label>
            <div class="col-md-7 col-sm-7">
              <input
                v-model="identity.name"
                class="form-control"
                name="name"
                size="30"
                type="text"
              />
            </div>
          </div>
          <div class="form-group">
            <label class="col-md-4 col-sm-4 control-label" for="countryId">{{
              T.userEditCountry
            }}</label>
            <div class="col-md-7 col-sm-7">
              <select
                v-model="selectedCountry"
                class="form-control"
                name="countryId"
              >
                <option
                  v-for="country in countries"
                  v-bind:value="country.country_id"
                >
                  {{ country.name }}
                </option>
              </select>
            </div>
          </div>
          <div class="form-group">
            <label class="col-md-4 col-sm-4 control-label" for="stateId">{{
              T.profileState
            }}</label>
            <div class="col-md-7 col-sm-7">
              <select
                v-model="selectedState"
                class="form-control"
                name="stateId"
              >
                <option
                  v-for="[code, state] in Object.entries(countryStates)"
                  v-bind:value="code.split('-')[1]"
                >
                  {{ state.name }}
                </option>
              </select>
            </div>
          </div>
          <div class="form-group">
            <label class="col-md-4 col-sm-4 control-label" for="school">{{
              T.profileSchool
            }}</label>
            <div class="col-md-7 col-sm-7">
              <input
                v-model="identity.school"
                class="form-control"
                name="school"
                size="20"
                type="text"
              />
            </div>
            <input
              name="schoolId"
              type="hidden"
              v-bind:value="identity.school_id"
            />
          </div>
        </div>
        <div class="form-group pull-right">
          <button class="btn btn-primary" type="submit">
            {{ T.wordsSaveChanges }}
          </button>
          <button
            class="btn btn-secundary"
            type="reset"
            v-on:click="$emit('emit-cancel')"
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

@Component
export default class IdentityEdit extends Vue {
  @Prop() identity!: omegaup.Identity;
  @Prop() countries!: iso3166.Country[];
  @Prop({ default: 'MX' }) selectedCountry!: string;
  @Prop() selectedState!: string;
  @Prop() username!: string;

  T = T;

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
    if (typeof this.identity === 'undefined') {
      return '';
    }
    return this.identity.username.split(':')[1];
  }
  set identityName(username: string) {
    this.identity.username = `${this.groupName}:${username}`;
  }

  get countryStates(): iso3166.Subdivisions {
    let countrySelected = iso3166.country(this.selectedCountry);
    return countrySelected.sub;
  }

  onEditMember(): void {
    this.$emit(
      'emit-edit-identity-member',
      this,
      this.identity,
      this.selectedCountry,
      this.selectedState,
    );
  }
}
</script>
