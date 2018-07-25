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
          <td>
            <a v-bind:href="memberProfileUrl(identity.username)">{{ identity.username }}</a>
          </td>
          <td>
            <a class="glyphicon glyphicon-remove"
                href="#"
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
          <td>
            <a v-bind:href="memberProfileUrl(identity.username)">{{ identity.username }}</a>
          </td>
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
                role="form">
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
                         v-bind:value="username">
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
                         v-bind:value="name">
                  </div>
                </div>
                <div class="form-group">
                  <label class="col-md-4 col-sm-4 control-label"
                       for="country_id">{{ T.userEditCountry }}</label>
                  <div class="col-md-7 col-sm-7">
                    <select class="form-control"
                         name="country_id">
                      <option v-bind:value="country_id">
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
                       for="state_id">{{ T.profileState }}</label>
                  <div class="col-md-7 col-sm-7">
                    <select class="form-control"
                         disabled="true"
                         name="state_id">
                      <option v-bind:value="state_id">
                        {{ state }}
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
                         v-bind:value="school">
                  </div><input name="school_id"
                       type="hidden"
                       v-bind:value="school_id">
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button class="btn btn-default"
                   data-dismiss="modal"
                   type="button">{{ T.wordsCancel }}</button> <button class="btn btn-primary"
                   type="button">{{ T.wordsSaveChanges }}</button>
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
                role="form">
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
                         value="">
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
                         value="">
                  </div>
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button class="btn btn-default"
                   data-dismiss="modal"
                   type="button">{{ T.wordsCancel }}</button> <button class="btn btn-primary"
                   type="button">{{ T.wordsSaveChanges }}</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import {T, UI} from '../../omegaup.js';

export default {
  props: {
    identities: Array,
    identitiesCsv: Array,
    countries: Array,
  },
  data: function() {
    return {
      T: T,
      memberUsername: '',
      username: '',
      name: '',
      country: '',
      country_id: '',
      state: '',
      state_id: '',
      school: '',
      school_id: '',
      user_username: ''
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
      this.$emit('add-member', this.memberUsername);
    },
    onEdit: function(identity) {
      this.username = identity.username;
      this.name = identity.name;
      this.country = identity.country;
      this.country_id = identity.country_id;
      this.state = identity.state;
      this.state_id = identity.state_id;
      this.school = identity.school;
      this.school_id = identity.school_id;
      $('.modal-edit').modal();
    },
    onChangePass: function(username) {
      this.username = username;
      $('.modal-change-password').modal();
    },
    onRemove: function(username) { this.$emit('remove', username);},
    reset: function() {
      this.memberUsername = '';

      let inputElem = $('input.typeahead', this.$el);
      inputElem.typeahead('close');
      inputElem.val('');
    },
    memberProfileUrl: function(member) { return '/profile/' + member + '/';},
  },
};
</script>

<style>
label {
  display: inline;
}
</style>
