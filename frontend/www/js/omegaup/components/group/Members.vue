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
    </table><omegaup-identity-edit v-bind:countries="countries"
         v-bind:identity="identity"
         v-bind:selected-country="identity.country_id"
         v-bind:selected-state="identity.state_id"
         v-bind:username="username"
         v-if="showEditForm"></omegaup-identity-edit>
         <omegaup-identity-change-password v-bind:username="username"
         v-if="showChangePasswordForm" v-on:emitChangePassword="onChildChangePasswordMember"
         v-on:emitCancel="onChildCancel"></omegaup-identity-change-password>
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
import identity_Edit from '../identity/Edit.vue';
import identity_ChangePassword from '../identity/ChangePassword.vue';

export default {
  props: {
    identities: Array,
    identitiesCsv: Array,
    groupAlias: String,
    countries: {
      type: Array,
    },
  },
  data: function() {
    return {
      T: T,
      identity: {},
      memberUsername: '',
      username: '',
      newPassword: '',
      newPasswordRepeat: '',
      showEditForm: false,
      showChangePasswordForm: false,
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
    onEdit: function(identity) { this.$emit('edit-identity', this, identity);},
    onChangePass: function(username) {
      this.$emit('change-password-identity', this, username);
    },
    onChildChangePasswordMember: function(newPassword, newPasswordRepeat) {
      this.$emit('change-password-identity-member', this, this.username,
                 newPassword, newPasswordRepeat);
    },
    onRemove: function(username) { this.$emit('remove', username);},
    reset: function() {
      this.memberUsername = '';
      let inputElem = $('input.typeahead', this.$el);
      inputElem.typeahead('close');
      inputElem.val('');
    },
    onChildCancel: function() {
      this.$emit('cancel', this);
    }
  },
  components: {
    'omegaup-user-username': user_Username,
    'omegaup-identity-edit': identity_Edit,
    'omegaup-identity-change-password': identity_ChangePassword,
  },
};
</script>
