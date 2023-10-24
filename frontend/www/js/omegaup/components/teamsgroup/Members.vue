<template>
  <div class="card">
    <h5 class="card-title mx-2">
      {{
        ui.formatString(T.groupEditMembersTitle, {
          username: teamUsername,
        })
      }}
    </h5>
    <div class="card-body">
      <form class="form" @submit.prevent="onSubmit">
        <div class="form-group">
          <label>{{ T.addUsersMultipleOrSingleUser }}</label>
          <omegaup-common-multi-typeahead
            :existing-options="searchResultUsers"
            :value.sync="typeaheadContestants"
            @update-existing-options="
              (query) => $emit('update-search-result-users', query)
            "
          >
          </omegaup-common-multi-typeahead>
        </div>
        <button class="btn btn-primary" type="submit">
          {{ T.wordsAddMember }}
        </button>
        <button
          class="btn btn-secondary ml-2"
          type="reset"
          @click="$emit('cancel')"
        >
          {{ T.wordsCancel }}
        </button>
      </form>
    </div>
    <table class="table table-striped" data-table-members>
      <thead>
        <tr>
          <th>{{ T.teamsGroupMembersAccountName }}</th>
          <th>{{ T.loginPassword }}</th>
          <th>{{ T.wordsActions }}</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="identity in teamsMembers" :key="identity.username">
          <td>
            <omegaup-user-username
              :classname="identity.classname"
              :linkify="true"
              :username="identity.username"
            ></omegaup-user-username>
          </td>
          <td v-if="!identity.isMainUserIdentity">
            <template
              v-if="
                changePasswordInputEnabled && username === identity.username
              "
            >
              <div class="input-group">
                <input
                  v-model="password"
                  type="password"
                  class="form-control"
                  :placeholder="T.teamsGroupMemberChangePassword"
                />
                <button
                  type="button"
                  class="btn btn-link"
                  :data-save-new-password-identity="identity.username"
                  :title="T.groupEditMembersChangePassword"
                  @click="onChangePasswordMember"
                >
                  <font-awesome-icon :icon="['fas', 'save']" />
                </button>
              </div>
            </template>
            <input v-else type="password" value="password" disabled="true" />
          </td>
          <td v-else></td>
          <td>
            <button
              v-if="!identity.isMainUserIdentity"
              class="btn btn-link"
              :data-change-password-identity="identity.username"
              :title="T.groupEditMembersChangePassword"
              @click="onChangePass(identity.username)"
            >
              <font-awesome-icon :icon="['fas', 'lock']" />
            </button>
            <button
              class="btn btn-link"
              :data-table-remove-member="identity.username"
              :title="T.groupEditMembersRemove"
              @click="
                $emit('remove-member', {
                  username: identity.username,
                  teamUsername,
                })
              "
            >
              <font-awesome-icon :icon="['fas', 'trash-alt']" />
            </button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import * as ui from '../../ui';
import { types } from '../../api_types';
import T from '../../lang';
import user_Username from '../user/Username.vue';
import common_MultiTypeahead from '../common/MultiTypeahead.vue';

import { library } from '@fortawesome/fontawesome-svg-core';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faLock, faTrashAlt, faSave } from '@fortawesome/free-solid-svg-icons';
library.add(faLock, faTrashAlt, faSave);

@Component({
  components: {
    FontAwesomeIcon,
    'omegaup-common-multi-typeahead': common_MultiTypeahead,
    'omegaup-user-username': user_Username,
  },
})
export default class Members extends Vue {
  @Prop() searchResultUsers!: types.ListItem[];
  @Prop() teamUsername!: string;
  @Prop({ default: () => [] }) teamsMembers!: types.TeamMember[];

  T = T;
  ui = ui;
  typeaheadContestants: null | types.ListItem[] = null;
  username: null | string = null;
  changePasswordInputEnabled = false;
  password: null | string = null;

  onSubmit(): void {
    if (!this.typeaheadContestants) return;
    this.$emit('add-members', {
      usersToAdd: this.typeaheadContestants.map((user) => user.key),
      teamUsername: this.teamUsername,
    });
    this.typeaheadContestants = null;
  }

  onChangePass(username: string): void {
    this.changePasswordInputEnabled = true;
    this.username = username;
  }

  onChangePasswordMember(): void {
    this.$emit('change-password-identity', {
      username: this.username,
      newPassword: this.password,
    });
    this.changePasswordInputEnabled = false;
    this.username = null;
    this.password = null;
  }
}
</script>
