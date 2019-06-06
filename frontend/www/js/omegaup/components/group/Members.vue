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
        <tr v-bind:key="identity.username"
            v-for="identity in identitiesCsv">
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
    </table><!-- Modal Change Password-->
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
    groupAlias: String,
  },
  data: function() {
    return {
      T: T,
      identity: {},
      memberUsername: '',
      username: '',
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
    onEdit: function(identity) { this.$emit('edit-identity', identity);},
    onChangePass: function(username) {
      this.username = username;
      $('.modal-change-password').modal();
    },
    onChangePasswordMember: function() {
      this.$emit('change-password-identity-member', this, this.username,
                 this.newPassword, this.newPasswordRepeat);
    },
    onRemove: function(username) { this.$emit('remove', username);},
    reset: function() {
      this.memberUsername = '';
      let inputElem = $('input.typeahead', this.$el);
      inputElem.typeahead('close');
      inputElem.val('');
    },
  },
  components: {
    'omegaup-user-username': user_Username,
  },
};
</script>
