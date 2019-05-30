<template>
  <div class="modal fade modal-edit"
       role="dialog">
    <div class="modal-dialog">
      <div class="modal-content">
        <form class="form-horizontal"
              role="form"
              v-on:submit.prevent="onEditMember">
          <div class="modal-header">
            <button class="close"
                 data-dismiss="modal"
                 type="button">×</button>
            <h4 class="modal-title">{{ T.groupEditIdentity }}</h4>
          </div>
          <div class="modal-body">
            <div class="panel-body">
              <div class="form-group">
                <label class="col-md-4 col-sm-4 control-label"
                     for="username">{{ T.username }}</label>
                <div class="col-md-7 col-sm-7">
                  <input class="form-control"
                       name="username"
                       size="30"
                       type="text"
                       v-model="identity.username">
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
                    <option selected
                            v-bind:value="identity.country_id">
                      {{ identity.country }}
                    </option>
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
                    <option selected
                            v-bind:value="identity.state_id">
                      {{ identity.state }}
                    </option>
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
          </div>
          <div class="modal-footer">
            <button class="btn btn-default"
                 data-dismiss="modal"
                 type="button">{{ T.wordsCancel }}</button> <button class="btn btn-primary"
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
      selectedCountry: '',
      selectedState: '',
      countryStates: [],
    };
  },
  mounted: function() { this.updateStates();},
  methods: {
    onEditMember: function() {
      this.$parent.$emit('edit-identity-member', this.$parent, this.identity,
                         this.username);
    },
    onSelectCountry: function() { this.updateStates();},
    updateStates: function() {
      let country = iso3166.country(this.selectedCountry || 'MX');
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
        this.selectedCountry = this.countryStates[0].code.split('-')[0];
      }
    }
  },
};
</script>
