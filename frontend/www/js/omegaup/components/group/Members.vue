<template>
  <div class="card">
    <div class="card-body">
      <form class="form" @submit.prevent="onAddMember">
        <div class="row">
          <div class="form-group col-md-9 mb-1 mt-1">
            <label class="d-inline">{{ T.wordsMember }}</label>
            <omegaup-common-typeahead
              :existing-options="searchResultUsers"
              :value.sync="searchedUsername"
              :max-results="10"
              class="input"
              @update-existing-options="
                (query) => $emit('update-search-result-users', query)
              "
            ></omegaup-common-typeahead>
          </div>
          <div
            class="form-group mb-0 col-md-3 d-flex align-items-center mt-4 margin-phone"
          >
            <button class="btn btn-primary" type="submit">
              {{ T.wordsAddMember }}
            </button>
          </div>
        </div>
      </form>
    </div>
    <table class="table table-striped" data-table-members>
      <thead>
        <tr>
          <th>{{ T.groupEditMembersCoder }}</th>
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
    <table class="table table-striped responsive-table" data-table-identities>
      <thead>
        <tr>
          <th>{{ T.groupEditMembersCoder }}</th>
          <th>{{ T.wordsName }}</th>
          <th>{{ T.profileCountry }}</th>
          <th>{{ T.profileState }}</th>
          <th>{{ T.profileSchool }}</th>
          <th>{{ T.wordsActions }}</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="identity in identitiesCsv" :key="identity.username">
          <td data-members-username>
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
              data-identity-change-password
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
      :search-result-schools="searchResultSchools"
      @cancel="onChildCancel"
      @edit-identity-member="
        (request) => $emit('edit-identity-member', { ...request, showEditForm })
      "
      @update-search-result-schools="
        (query) => $emit('update-search-result-schools', query)
      "
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
import user_Username from '../user/Username.vue';
import identity_Edit from '../identity/Edit.vue';
import identity_ChangePassword from '../identity/ChangePassword.vue';
import common_Typeahead from '../common/Typeahead.vue';

import { library } from '@fortawesome/fontawesome-svg-core';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faEdit, faLock, faTrashAlt } from '@fortawesome/free-solid-svg-icons';
library.add(faEdit, faLock, faTrashAlt);

@Component({
  components: {
    FontAwesomeIcon,
    'omegaup-common-typeahead': common_Typeahead,
    'omegaup-user-username': user_Username,
    'omegaup-identity-edit': identity_Edit,
    'omegaup-identity-change-password': identity_ChangePassword,
  },
})
export default class Members extends Vue {
  @Prop() identities!: types.Identity[];
  @Prop() identitiesCsv!: types.Identity[];
  @Prop() groupAlias!: string;
  @Prop() countries!: Array<dao.Countries>;
  @Prop() searchResultUsers!: types.ListItem[];
  @Prop() searchResultSchools!: types.SchoolListItem[];

  T = T;
  identity = {};
  username = '';
  showEditForm = false;
  showChangePasswordForm = false;
  searchedUsername: null | types.ListItem = null;

  onAddMember(): void {
    this.$emit('add-member', this, this.searchedUsername?.key);
    this.reset();
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
    this.searchedUsername = null;
  }
}
</script>

<style scoped lang="scss">
@media (max-width: 576px) {
  .input {
    width: 100%;
    max-width: 100%;
    margin-top: 0.6rem;
  }
  .responsive-table {
    width: 100%;
    overflow-x: auto;
    display: block;
  }
  .margin-phone {
    margin-top: 0.4rem !important;
  }
}
</style>
