<template>
  <div class="panel panel-primary">
    <div class="panel-heading">
      <h3 class="panel-title course-header">{{ T.groupEditIdentity }}</h3>
    </div>
    <div class="page-header">
      <h1><span><a class="course-header">{{ username }}</a></span></h1>
    </div>
    <div class="omegaup-course-details panel-primary panel">
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
                  <span class="input-group-addon">{{ extractedGroup }}</span> <input class=
                  "form-control"
                       name="username"
                       size="30"
                       type="text"
                       v-model="extractedIdentity">
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
                     v-model="selectedCountry"
                     v-on:change="onSelectCountry">
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
                  <option v-bind:value="state.code.split('-')[1]"
                          v-for="state in countryStates"
                          v-if="countryStates.length &gt; 0">
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
            <button class="btn btn-default"
                 type="button"
                 v-on:click="onCancel">{{ T.wordsCancel }}</button> <button class="btn btn-primary"
                 type="submit">{{ T.wordsSaveChanges }}</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import {T} from '../../omegaup.js';
export default {
  props: {
    identity: {
      type: Object,
    },
    countries: {
      type: Array,
    },
    username: {
      type: String,
    },
  },
  data: function() {
    return {
      T: T,
      selectedCountry: this.identity.country_id,
      selectedState: this.identity.state_id,
      countryStates: [],
    };
  },
  computed: {
    extractedGroup: function() {
      return `${this.identity.username.split(':')[0]}:`;
    },
    extractedIdentity: {
      get: function() { return this.identity.username.split(':')[1];},
      set: function(username) {
        this.identity.username =
            `${this.identity.username.split(':')[0]}:${username}`;
      },
    }
  },
  mounted: function() { this.updateStates();},
  methods: {
    onEditMember: function() {
      this.$emit('edit-identity-member', this.identity, this.selectedCountry,
                 this.selectedState);
    },
    onSelectCountry: function() { this.updateStates();},
    onCancel: function() { this.$emit('cancel');},

    updateStates: function() {
      let country = iso3166.country(this.selectedCountry || '');
      this.countryStates =
          Object.keys(country.sub)
              .map(function(code) {
                return {code: code, name: country.sub[code].name};
              });

      this.countryStates.sort(function(a, b) {
        return Intl.Collator().compare(a.name, b.name);
      });

      if (this.identity.country_id == this.selectedCountry) {
        this.selectedState = this.identity.state_id;
      } else {
        this.selectedState = this.countryStates[0].code.split('-')[1];
      }
    },
  },
};
</script>
