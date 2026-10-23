<template>
  <div class="card">
    <div v-if="teams.length === 0" class="empty-table-message py-2">
      {{ T.teamsGroupTeamsEmptyList }}
    </div>
    <table v-else class="table table-striped" data-table-identities>
      <thead>
        <tr>
          <th>{{ T.teamsGroupTeamName }}</th>
          <th>{{ T.profileName }}</th>
          <th>{{ T.profileCountry }}</th>
          <th>{{ T.profileState }}</th>
          <th>{{ T.profileSchool }}</th>
          <th>{{ T.wordsActions }}</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="identity in teams" :key="identity.username">
          <td>
            <omegaup-user-username
              :classname="identity.classname"
              :linkify="true"
              :username="identity.username"
            ></omegaup-user-username>
          </td>
          <td data-group-team-name>{{ identity.name }}</td>
          <td>
            {{ identity.country === 'xx' ? T.countryNotSet : identity.country }}
          </td>
          <td>{{ identity.state }}</td>
          <td>{{ identity.school }}</td>
          <td>
            <button
              class="btn btn-link"
              :data-edit-identity="identity.username"
              :title="T.groupEditMembersEdit"
              @click="onEdit(identity)"
            >
              <font-awesome-icon :icon="['fas', 'edit']" />
            </button>
            <button
              class="btn btn-link"
              :data-add-members-identity="identity.username"
              :title="T.groupEditMembersAddMembers"
              @click="onAddMembers(identity.username)"
            >
              <font-awesome-icon :icon="['fas', 'users']" />
            </button>
            <button
              class="btn btn-link"
              :data-remove-identity="identity.username"
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
      v-if="formToShow === AvailableForms.Edit"
      :countries="countries"
      :identity="identity"
      :search-result-schools="searchResultSchools"
      @update-search-result-schools="
        (query) => $emit('update-search-result-schools', query)
      "
      @cancel="onCancel"
      @edit-identity-member="onEditIdentityTeam"
    ></omegaup-identity-edit>
    <omegaup-identity-change-password
      v-if="formToShow === AvailableForms.ChangePassword"
      :username="username"
      @emit-cancel="onCancel"
      @emit-change-password="onChangePasswordTeam"
    ></omegaup-identity-change-password>
    <omegaup-identity-members
      v-if="formToShow === AvailableForms.AddMembers"
      :team-username="username"
      :teams-members="
        teamsMembers.filter((user) => user.team_alias === username)
      "
      :search-result-users="searchResultUsers"
      @update-search-result-users="
        (query) => $emit('update-search-result-users', query)
      "
      @cancel="onCancel"
      @change-password-identity="
        (request) => $emit('change-password-identity', request)
      "
      @add-members="(request) => $emit('add-members', request)"
      @remove-member="(request) => $emit('remove-member', request)"
    ></omegaup-identity-members>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import type { PropType } from 'vue';
import { dao, types } from '../../api_types';
import T from '../../lang';
import user_Username from '../user/Username.vue';
import identity_Edit from '../identity/Edit.vue';
import identity_ChangePassword from '../identity/ChangePassword.vue';
import teamsgroup_Members from './Members.vue';
import { library } from '@fortawesome/fontawesome-svg-core';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import {
  faEdit,
  faLock,
  faTrashAlt,
  faUsers,
} from '@fortawesome/free-solid-svg-icons';
library.add(faEdit, faLock, faTrashAlt, faUsers);

export enum AvailableForms {
  None,
  Edit,
  ChangePassword,
  AddMembers,
}

export default defineComponent({
  name: 'Teams',
  components: {
    FontAwesomeIcon,
    'omegaup-user-username': user_Username,
    'omegaup-identity-edit': identity_Edit,
    'omegaup-identity-change-password': identity_ChangePassword,
    'omegaup-identity-members': teamsgroup_Members,
  },
  props: {
    teams: {
      type: Array as PropType<types.Identity[]>,
      required: true,
    },
    countries: {
      type: Array as PropType<dao.Countries[]>,
      required: true,
    },
    searchResultUsers: {
      type: Array as PropType<types.ListItem[]>,
      required: true,
    },
    searchResultSchools: {
      type: Array as PropType<types.SchoolListItem[]>,
      required: true,
    },
    teamsMembers: {
      type: Array as PropType<types.TeamMember[]>,
      default: () => [],
    },
  },
  emits: [
    'add-members',
    'change-password-identity',
    'change-password-identity-team',
    'edit-identity-team',
    'remove',
    'remove-member',
    'update-identity-team',
    'update-search-result-schools',
    'update-search-result-users',
  ],
  data() {
    return {
      T,
      AvailableForms,
      identity: null as null | types.Identity,
      username: null as null | string,
      formToShow: AvailableForms.None as AvailableForms,
    };
  },
  methods: {
    onEdit(identity: types.Identity): void {
      this.identity = identity;
      this.formToShow = AvailableForms.Edit;
      this.username = identity.username;
      this.$emit('update-identity-team', this.identity);
    },

    onChangePass(username: string): void {
      this.formToShow = AvailableForms.ChangePassword;
      this.username = username;
    },

    onAddMembers(username: string): void {
      this.formToShow = AvailableForms.AddMembers;
      this.username = username;
    },

    onChangePasswordTeam(newPassword: string, newPasswordRepeat: string): void {
      this.$emit('change-password-identity-team', {
        username: this.username,
        newPassword,
        newPasswordRepeat,
      });
      this.onCancel();
    },

    onEditIdentityTeam(response: {
      originalUsername: string;
      identity: types.Identity;
    }): void {
      this.$emit('edit-identity-team', response);
      this.onCancel();
    },

    onCancel(): void {
      this.identity = null;
      this.formToShow = AvailableForms.None;
      this.username = null;
    },
  },
});
</script>
