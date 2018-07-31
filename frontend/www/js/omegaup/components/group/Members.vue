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
            <a v-bind:href=
            "memberProfileUrl(identity.username)"><strong><omegaup-user-username v-bind:classname=
            "identity.classname"
                                   v-bind:username=
                                   "identity.username"></omegaup-user-username></strong></a>
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
        <tr v-for="identity in groupIdentities">
          <td>
            <a v-bind:href=
            "memberProfileUrl(identity.username)"><strong><omegaup-user-username v-bind:classname=
            "identity.classname"
                                   v-bind:username=
                                   "identity.username"></omegaup-user-username></strong></a>
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
    </table>
  </div>
</template>

<script>
import {T, UI} from '../../omegaup.js';
import user_Username from '../user/Username.vue';

export default {
  props: {
    identities: Array,
    groupIdentities: Array,
    countries: Array,
  },
  data: function() {
    return {
      T: T,
      memberUsername: '',
      username: '',
      name: '',
      country: '',
      countryId: '',
      state: '',
      stateId: '',
      school: '',
      schoolId: '',
      userUsername: ''
    };
  },
  mounted: function() {
    UI.userTypeahead($('input.typeahead', this.$el),
                     (event, item) => { this.memberUsername = item.value; });
  },
  methods: {
    onAddMember: function() {
      this.$emit('add-member', $('input.typeahead.tt-input', this.$el).val());
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
      $('.modal-edit').modal();
    },
    onChangePass: function(username) {
      this.username = username;
      $('.modal-change-password').modal();
    },
    onRemove: function(username) { this.$emit('remove', username);},
    reset: function() {
      this.memberUsername = '';

      $('input.typeahead', this.$el).typeahead('close').val('');
    },
    memberProfileUrl: function(member) { return '/profile/' + member + '/';},
  },
  components: {
    'omegaup-user-username': user_Username,
  },
};
</script>

<style>
label {
  display: inline;
}
</style>
