<template>
  <div class="panel panel-primary">
    <div class="panel-body">
      <form class="form"
            v-on:submit.prevent="onAddMember">
        <div class="form-group">
          <label>{{ T.wordsMember }} <input autocomplete="off"
                 class="form-control typeahead"
                 name="username"
                 size="20"
                 type="text"></label>
        </div><button class="btn btn-primary"
              type="submit">{{ T.wordsAddMember }}</button>
      </form>
    </div>
    <table class="table table-striped">
      <thead>
        <tr>
          <th>{{ T.wordsUser }}</th>
          <th>{{ T.contestEditRegisteredAdminDelete }}</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="identity in identities">
          <td><omegaup-user-username v-bind:classname="identity.classname"
                                 v-bind:linkify="true"
                                 v-bind:username="identity.username"></omegaup-user-username></td>
          <td>
            <a class="glyphicon glyphicon-remove"
                href="#"
                v-bind:title="T.groupEditMembersRemove"
                v-on:click="onRemove(identity.username)"></a>
          </td>
        </tr>
      </tbody>
    </table>
    <table class="table table-striped">
      <thead>
        <tr>
          <th>{{ T.wordsIdentity }}</th>
          <th>{{ T.wordsName }}</th>
          <th>{{ T.profileCountry }}</th>
          <th>{{ T.profileState }}</th>
          <th>{{ T.profileSchool }}</th>
          <th>{{ T.wordsActions }}</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="identity in identitiesCsv">
          <td><omegaup-user-username v-bind:classname="identity.classname"
                                 v-bind:linkify="true"
                                 v-bind:username="identity.username"></omegaup-user-username></td>
          <td>{{ identity.name }}</td>
          <td>{{ identity.country }}</td>
          <td>{{ identity.state }}</td>
          <td>{{ identity.school }}</td>
          <td>
            <a class="glyphicon glyphicon-edit"
                href="#"
                v-bind:title="T.groupEditMembersEdit"
                v-on:click="onEdit(identity)"></a> <a class="glyphicon glyphicon-lock"
                href="#"
                v-bind:title="T.groupEditMembersChangePassword"
                v-on:click="onChangePass(identity.username)"></a> <a class=
                "glyphicon glyphicon-remove"
                href="#"
                v-bind:title="T.groupEditMembersRemove"
                v-on:click="onRemove(identity.username)"></a>
          </td>
        </tr>
      </tbody>
    </table><!-- Modal Edit-->
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
                         v-model="username">
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
                         v-model="name">
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
                      <option v-bind:value="countryId">
                        {{ country }}
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
                         v-model="school">
                  </div><input name="schoolId"
                       type="hidden"
                       v-bind:value="schoolId">
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
    </div><!-- Modal Change Password-->
    <div class="modal fade modal-change-password"
         role="dialog">
      <div class="modal-dialog">
        <div class="modal-content">
          <form class="form-horizontal"
                role="form"
                v-on:submit.prevent="onChangePasswordMember">
            <div class="modal-header">
              <button class="close"
                   data-dismiss="modal"
                   type="button">×</button>
              <h4 class="modal-title">{{ T.userEditChangePassword }}</h4>
            </div>
            <div class="modal-body">
              <div class="panel-body">
                <div class="form-group">
                  <label class="col-md-4 col-sm-4 control-label"
                       for="username">{{ T.username }}</label>
                  <div class="col-md-7 col-sm-7">
                    <input class="form-control"
                         disabled="disabled"
                         name="username"
                         size="30"
                         type="text"
                         v-bind:value="username">
                  </div>
                </div>
                <div class="form-group">
                  <label class="col-md-4 col-sm-4 control-label"
                       for="new-password-1">{{ T.userEditChangePasswordNewPassword }}</label>
                  <div class="col-md-7 col-sm-7">
                    <input class="form-control"
                         name="new-password-1"
                         size="30"
                         type="password"
                         v-model="newPassword">
                  </div>
                </div>
                <div class="form-group">
                  <label class="col-md-4 col-sm-4 control-label"
                       for="new-password-2">{{ T.userEditChangePasswordRepeatNewPassword }}</label>
                  <div class="col-md-7 col-sm-7">
                    <input class="form-control"
                         name="new-password-2"
                         size="30"
                         type="password"
                         v-model="newPasswordRepeat">
                  </div>
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
  </div>
</template>

<style>
label {
  display: inline;
}
</style>

<script>
import {T, UI} from '../../omegaup.js';
import user_Username from '../user/Username.vue';
export default {
  props: {
    identities: Array,
    identitiesCsv: Array,
    countries: Array,
  },
  data: function() {
    return {
      T: T,
      selectedIdentity: [],
      selectedCountry: '',
      selectedState: '',
      countryStates: [],
      memberUsername: '',
      username: '',
      name: '',
      country: '',
      countryId: '',
      state: '',
      school: '',
      schoolId: '',
      newPassword: '',
      newPasswordRepeat: '',
    };
  },
  mounted: function() {
    let self = this;
    UI.userTypeahead($('input.typeahead', self.$el), function(event, item) {
      self.memberUsername = item.value;
    });
  },
  methods: {
    onAddMember: function() {
      let hintElem = $('input.typeahead.tt-hint', this.$el);
      let hint = hintElem.val();
      if (hint) {
        // There is a hint currently visible in the UI, the user likely
        // expects that hint to be used when trying to add someone, instead
        // of what they've actually typed so far.
        this.memberUsername = hint;
      } else {
        this.memberUsername = $('input.typeahead.tt-input', this.$el).val();
      }
      this.$emit('add-member', this, this.memberUsername);
    },
    onEdit: function(identity) {
      this.username = identity.username;
      this.name = identity.name;
      this.country = identity.country;
      this.countryId = identity.country_id;
      this.state = identity.state;
      this.stateId = identity.state_id;
      this.school = identity.school;
      this.schoolId = identity.school_id;
      this.selectedCountry = this.countryId;
      this.selectedState = this.stateId;
      this.selectedIdentity = identity;
      this.updateStates();

      $('.modal-edit').modal();
    },
    onEditMember: function() {
      this.$emit('edit-identity-member', this, this.selectedIdentity,
                 this.username, this.name, this.selectedCountry,
                 this.selectedState, this.school);
    },
    onChangePass: function(username) {
      this.username = username;
      $('.modal-change-password').modal();
    },
    onChangePasswordMember: function() {
      this.$emit('change-password-identity-member', this, this.username,
                 this.newPassword, this.newPasswordRepeat);
    },
    onRemove: function(username) { this.$emit('remove', username);},
    onSelectCountry: function() { this.updateStates();},
    reset: function() {
      this.memberUsername = '';
      let inputElem = $('input.typeahead', this.$el);
      inputElem.typeahead('close');
      inputElem.val('');
    },
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

      if (this.countryId == this.selectedCountry) {
        this.selectedState = this.stateId;
      } else {
        this.selectedState = this.countryStates[0].code.split('-')[1];
      }
    }
  },
  components: {
    'omegaup-user-username': user_Username,
  },
};
</script>
