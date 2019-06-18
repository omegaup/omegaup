<template>
  <div class="omegaup-course-details panel">
    <div>
      <h1><span><a class="course-header">{{ username }}</a></span></h1>
    </div>
    <div class="panel-body">
      <form class="form-horizontal"
            role="form"
            v-on:submit.prevent="onEditMember">
        <div class="row">
          <div class="form-group">
            <label class="col-md-4 col-sm-4 control-label"
                 for="username">{{ T.username }}</label>
            <div class="col-md-7 col-sm-7">
              <div class="input-group">
                <span class="input-group-addon">{{ groupName }}:</span> <input class="form-control"
                     name="username"
                     size="30"
                     type="text"
                     v-model="identityName">
              </div>
            </div>
          </div>
          <div class="form-group">
            <label class="col-md-4 col-sm-4 control-label"
                 for="name">{{ T.profile }}</label>
            <div class="col-md-7 col-sm-7">
              <input class="form-control"
                   name="name"
                   size="30"
                   type="text"
                   v-model="identity.name">
            </div>
          </div>
          <div class="form-group">
            <label class="col-md-4 col-sm-4 control-label"
                 for="countryId">{{ T.userEditCountry }}</label>
            <div class="col-md-7 col-sm-7">
              <select class="form-control"
                   name="countryId"
                   v-model="selectedCountry">
                <option v-bind:value="country.country_id"
                        v-for="country in countries">
                  {{ country.name }}
                </option>
              </select>
            </div>
          </div>
          <div class="form-group">
            <label class="col-md-4 col-sm-4 control-label"
                 for="stateId">{{ T.profileState }}</label>
            <div class="col-md-7 col-sm-7">
              <select class="form-control"
                   name="stateId"
                   v-model="selectedState">
                <option v-bind:value="code.split('-')[1]"
                        v-for="[code, state] in Object.entries(countryStates)">
                  {{ state.name }}
                </option>
              </select>
            </div>
          </div>
          <div class="form-group">
            <label class="col-md-4 col-sm-4 control-label"
                 for="school">{{ T.profileSchool }}</label>
            <div class="col-md-7 col-sm-7">
              <input class="form-control"
                   name="school"
                   size="20"
                   type="text"
                   v-model="identity.school">
            </div><input name="schoolId"
                 type="hidden"
                 v-bind:value="identity.school_id">
          </div>
        </div>
        <div class="form-group pull-right">
          <button class="btn btn-primary"
               type="submit">{{ T.wordsSaveChanges }}</button> <button class="btn btn-secundary"
               type="reset"
               v-on:click="onCancel">{{ T.wordsCancel }}</button>
        </div>
      </form>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Watch, Prop } from 'vue-property-decorator';
import { T } from '../../omegaup.js';
import omegaup from '../../api.js';
import * as iso3166 from '../../../../third_party/js/iso-3166-2.js/iso3166.min.js';

@Component
export default class IdentityEdit extends Vue {
  @Prop() identity!: omegaup.Identity;
  @Prop() countries!: iso3166.Country[];
  @Prop({ default: 'MX' }) selectedCountry!: string;
  @Prop() selectedState!: string;
  @Prop() username!: string;

  T = T;

  @Watch('selectedCountry')
  onPropertyChanged(newContry: string, oldCountry: string) {
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
  set identityName(username: string): void {
    this.identity.username = `${this.groupName}:${username}`;
  }

  get countryStates(): iso3166.Subdivisions {
    let countrySelected = iso3166.country(this.selectedCountry);
    return countrySelected.sub;
  }

  onEditMember(): void {
    this.$parent.$emit(
      'edit-identity-member',
      this,
      this.$parent,
      this.identity,
      this.selectedCountry,
      this.selectedState,
    );
  }

  onCancel(): void {
    this.$parent.$emit('cancel', this.$parent);
  }
}

</script>
