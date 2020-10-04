<template>
  <div class="panel panel-primary">
    <div class="panel-body">
      <form class="form" @submit.prevent="onAddMember">
        <div class="form-group">
          <label
            >{{ T.wordsMember }}
            <omegaup-autocomplete
              v-model="searchedUsername"
              class="form-control"
              :init="(el) => typeahead.userTypeahead(el)"
            ></omegaup-autocomplete
          ></label>
        </div>
        <button class="btn btn-primary" type="submit">
          {{ T.wordsAddMember }}
        </button>
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
            <omegaup-user-username
              :classname="identity.classname"
              :linkify="true"
              :username="identity.username"
            ></omegaup-user-username>
          </td>
          <td>
            <a
              class="glyphicon glyphicon-remove"
              href="#"
              :title="T.groupEditMembersRemove"
              @click="$emit('remove', identity.username)"
            ></a>
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
        <tr v-for="identity in identitiesCsv" :key="identity.username">
          <td>
            <omegaup-user-username
              :classname="identity.classname"
              :linkify="true"
              :username="identity.username"
            ></omegaup-user-username>
          </td>
          <td>{{ identity.name }}</td>
          <td>{{ identity.country }}</td>
          <td>{{ identity.state }}</td>
          <td>{{ identity.school }}</td>
          <td>
            <a
              class="glyphicon glyphicon-edit"
              href="#"
              :title="T.groupEditMembersEdit"
              @click="onEdit(identity)"
            ></a>
            <a
              class="glyphicon glyphicon-lock"
              href="#"
              :title="T.groupEditMembersChangePassword"
              @click="onChangePass(identity.username)"
            ></a>
            <a
              class="glyphicon glyphicon-remove"
              href="#"
              :title="T.groupEditMembersRemove"
              @click="$emit('remove', identity.username)"
            ></a>
          </td>
        </tr>
      </tbody>
    </table>
    <omegaup-identity-edit
      v-if="showEditForm"
      :countries="countries"
      :identity="identity"
      :selected-country="identity.country_id"
      :selected-state="identity.state_id"
      :username="username"
      @emit-cancel="onChildCancel"
      @emit-edit-identity-member="onChildEditIdentityMember"
    ></omegaup-identity-edit>
    <omegaup-identity-change-password
      v-if="showChangePasswordForm"
      :username="username"
      @emit-cancel="onChildCancel"
      @emit-change-password="onChildChangePasswordMember"
    ></omegaup-identity-change-password>
  </div>
</template>

<style>
label {
  display: inline;
}
</style>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { omegaup } from '../../omegaup';
import T from '../../lang';
import * as typeahead from '../../typeahead';
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
  typeahead = typeahead;
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
