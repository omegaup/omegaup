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
          <th>{{ T.wordsUser }}</th>
          <th>{{ T.contestEditRegisteredAdminDelete }}</th>
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
          <td>
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
import { faEdit, faLock, faTrashAlt } from '@fortawesome/free-solid-svg-icons';
library.add(faEdit, faLock, faTrashAlt);

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

  onSubmit(): void {
    if (!this.typeaheadContestants) return;
    this.$emit('add-members', {
      usersToAdd: this.typeaheadContestants.map((user) => user.key),
      teamUsername: this.teamUsername,
    });
    this.typeaheadContestants = null;
  }
}
</script>
