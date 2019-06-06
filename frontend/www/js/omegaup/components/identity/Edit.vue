<template>
  <div class="omegaup-course-details panel"
       v-show="show">
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
          <button class="btn btn-primary"
               type="submit">{{ T.wordsSaveChanges }}</button> <button class="btn btn-secundary"
               type="reset"
               v-on:click="onCancel">{{ T.wordsCancel }}</button>
        </div>
      </form>
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
    show: {
      type: Boolean,
      'default': false,
    },
  },
  data: function() {
    return {
      T: T,
      selectedCountry: this.identity.country_id,
      selectedState: this.identity.state_id,
    };
  },
  watch: {
    selectedCountry: function(newContry, oldCountry) {
      if (this.identity.country_id == newContry) {
        this.selectedState = this.identity.state_id;
      } else {
        this.selectedState = this.countryStates[0].code.split('-')[1];
      }
    },
  },
  computed: {
    groupName: function() {
      if (Object.entries(this.identity).length === 0) {
        return '';
      }
      return `${this.identity.username.split(':')[0]}`;
    },
    identityName: {
      get: function() {
        if (Object.entries(this.identity).length === 0) {
          return '';
        }
        return this.identity.username.split(':')[1];
      },
      set: function(username) {
        this.identity.username = `${this.groupName}:${username}`;
      },
    },
    countryStates: function() {
      let country = iso3166.country(this.selectedCountry || 'MX');
      let countryStates =
          Object.keys(country.sub)
              .map(function(code) {
                return {code: code, name: country.sub[code].name};
              });

      countryStates.sort(function(a, b) {
        return Intl.Collator().compare(a.name, b.name);
      });
      return countryStates;
    },
  },
  methods: {
    onEditMember: function() {
      this.$emit('edit-identity-member', this.identity, this.selectedCountry,
                 this.selectedState);
    },
    onCancel: function() { this.$emit('cancel');},
  },
};
</script>
