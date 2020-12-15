<template>
  <div class="card">
    <div class="card-body">
      <form class="form" @submit.prevent="onAddMember">
        <div class="form-group">
          <label class="d-inline"
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
    <table class="table table-striped" data-table-members>
      <thead>
        <tr>
          <th>{{ T.wordsUser }}</th>
          <th>{{ T.contestEditRegisteredAdminDelete }}</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="identity in identities" :key="identity.username">
          <td>
            <omegaup-user-username
              :classname="identity.classname"
              :linkify="true"
              :username="identity.username"
            ></omegaup-user-username>
          </td>
          <td>
            <button
              class="btn btn-link"
              :title="T.groupEditMembersRemove"
              @click="$emit('remove', identity.username)"
            >
              <font-awesome-icon :icon="['fas', 'trash-alt']" />
            </button>
          </td>
        </tr>
      </tbody>
    </table>
    <table class="table table-striped" data-table-identities>
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
            <button
              class="btn btn-link"
              :title="T.groupEditMembersEdit"
              @click="onEdit(identity)"
            >
              <font-awesome-icon :icon="['fas', 'edit']" />
            </button>
            <button
              class="btn btn-link"
              :title="T.groupEditMembersChangePassword"
              @click="onChangePass(identity.username)"
            >
              <font-awesome-icon :icon="['fas', 'lock']" />
            </button>
            <button
              class="btn btn-link"
              :title="T.groupEditMembersRemove"
              @click="$emit('remove', identity.username)"
            >
              <font-awesome-icon :icon="['fas', 'trash-alt']" />
            </button>
          </td>
        </tr>
      </tbody>
    </table>
    <omegaup-identity-edit
      v-if="showEditForm"
      :countries="countries"
      :identity="identity"
      @cancel="onChildCancel"
      @edit-identity-member="onChildEditIdentityMember"
    ></omegaup-identity-edit>
    <omegaup-identity-change-password
      v-if="showChangePasswordForm"
      :username="username"
      @emit-cancel="onChildCancel"
      @emit-change-password="onChildChangePasswordMember"
    ></omegaup-identity-change-password>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { dao, types } from '../../api_types';
import T from '../../lang';
import * as typeahead from '../../typeahead';
import user_Username from '../user/Username.vue';
import identity_Edit from '../identity/Editv2.vue';
import identity_ChangePassword from '../identity/ChangePasswordv2.vue';
import Autocomplete from '../Autocomplete.vue';

import { library } from '@fortawesome/fontawesome-svg-core';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faEdit, faLock, faTrashAlt } from '@fortawesome/free-solid-svg-icons';
library.add(faEdit, faLock, faTrashAlt);

@Component({
  components: {
    FontAwesomeIcon,
    'omegaup-autocomplete': Autocomplete,
    'omegaup-user-username': user_Username,
    'omegaup-identity-edit': identity_Edit,
    'omegaup-identity-change-password': identity_ChangePassword,
  },
})
export default class Memebers extends Vue {
  @Prop() identities!: types.Identity[];
  @Prop() identitiesCsv!: types.Identity[];
  @Prop() groupAlias!: string;
  @Prop() countries!: Array<dao.Countries>;

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

  onEdit(identity: types.Identity): void {
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
    originalUsername: string,
    identity: types.Identity,
  ): void {
    this.$emit('edit-identity-member', this, originalUsername, identity);
  }

  onChildCancel(): void {
    this.$emit('cancel', this);
  }

  reset(): void {
    this.searchedUsername = '';
  }
}
</script>
