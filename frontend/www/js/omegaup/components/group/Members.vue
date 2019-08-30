<template>
  <div class="panel panel-primary">
    <div class="panel-body">
      <form class="form"
            v-on:submit.prevent="onAddMember">
        <div class="form-group">
          <label>{{ T.wordsMember }} <omegaup-autocomplete class="form-control"
                                v-bind:init="el =&gt; UI.userTypeahead(el)"
                                v-model="searchedUsername"></omegaup-autocomplete></label>
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
                v-on:click="$emit('remove', identity.username)"></a>
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
                v-on:click="$emit('remove', identity.username)"></a>
          </td>
        </tr>
      </tbody>
    </table><omegaup-identity-edit v-bind:countries="countries"
         v-bind:identity="identity"
         v-bind:selected-country="identity.country_id"
         v-bind:selected-state="identity.state_id"
         v-bind:username="username"
         v-if="showEditForm"
         v-on:emit-cancel="onChildCancel"
         v-on:emit-edit-identity-member="onChildEditIdentityMember"></omegaup-identity-edit>
         <omegaup-identity-change-password v-bind:username="username"
         v-if="showChangePasswordForm"
         v-on:emit-cancel="onChildCancel"
         v-on:emit-change-password=
         "onChildChangePasswordMember"></omegaup-identity-change-password>
  </div>
</template>

<style>
label {
  display: inline;
}
</style>

<script lang="ts">
import { Vue, Component, Prop, Emit } from 'vue-property-decorator';
import { T } from '../../omegaup.js';
import UI from '../../ui.js';
import omegaup from '../../api.js';
import user_Username from '../user/Username.vue';
import identity_Edit from '../identity/Edit.vue';
import identity_ChangePassword from '../identity/ChangePassword.vue';
import Autocomplete from '../Autocomplete.vue';

interface EditMemberComponent {
  username: string;
}

@Component({
  components: {
    'omegaup-autocomplete': Autocomplete,
    'omegaup-user-username': user_Username,
    'omegaup-identity-edit': identity_Edit,
    'omegaup-identity-change-password': identity_ChangePassword,
  },
})
export default class UserProfile extends Vue {
  @Prop() identities!: omegaup.Identity[];
  @Prop() identitiesCsv!: omegaup.Identity[];
  @Prop() groupAlias!: string;
  @Prop() countries!: Array<string>;

  T = T;
  UI = UI;
  identity = {};
  username = '';
  showEditForm = false;
  showChangePasswordForm = false;
  searchedUsername = '';

  onAddMember(): void {
    this.$emit('add-member', this, this.searchedUsername);
  }

  onEdit(identity: omegaup.Identity): void {
    this.$emit('edit-identity', this, identity);
  }

  onChangePass(username: string): void {
    this.$emit('change-password-identity', this, username);
  }

  onChildChangePasswordMember(
    newPassword: string,
    newPasswordRepeat: string,
  ): void {
    this.$emit(
      'change-password-identity-member',
      this,
      this.username,
      newPassword,
      newPasswordRepeat,
    );
  }

  onChildEditIdentityMember(
    editMemeberComponent: EditMemberComponent,
    identity: omegaup.Identity,
    selectedCountry: string,
    selectedState: string,
  ): void {
    this.$emit(
      'edit-identity-member',
      editMemeberComponent,
      this,
      identity,
      selectedCountry,
      selectedState,
    );
  }

  onChildCancel(): void {
    this.$emit('cancel', this);
  }

  reset(): void {
    this.searchedUsername = '';
  }
}

</script>
