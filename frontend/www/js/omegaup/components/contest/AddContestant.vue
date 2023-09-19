<template>
  <div>
    <div class="card contestants-input-area">
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
          <button class="btn btn-primary user-add-typeahead" type="submit">
            {{ T.contestAdduserAddUsers }}
          </button>
          <hr />
          <div class="form-group">
            <!-- TODO: Replace word multiple user with ("Separados por espacio, coma, o salto de linea") -->
            <label>{{ T.wordsMultipleUser }}</label>
            <omegaup-multi-user-add-area
              :users="currentUsers.map((user) => user.username)"
              @update-users="updateUsersList"
            ></omegaup-multi-user-add-area>
          </div>
          <button class="btn btn-primary user-add-bulk" type="submit">
            {{ T.contestAdduserAddUsers }}
          </button>
        </form>
      </div>
      <table class="table table-striped mb-0 participants">
        <thead>
          <tr>
            <th class="text-center">{{ T.contestAddUserContestant }}</th>
            <th class="text-center">
              {{ T.contestAdduserRegisteredUserTime }}
            </th>
            <th v-if="contest.window_length !== null" class="text-center">
              {{ T.wordsEndTimeContest }}
            </th>
            <th class="text-center">
              {{ T.contestAdduserRegisteredUserDelete }}
            </th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="user in currentUsers" :key="user.username">
            <td class="text-center" data-uploaded-contestants>
              <omegaup-user-username
                :linkify="true"
                :username="user.username"
              ></omegaup-user-username>
            </td>
            <td class="text-center">
              <template v-if="user.access_time !== null">
                {{ time.formatDateTime(user.access_time) }}
              </template>
            </td>
            <td v-if="contest.window_length !== null" class="text-center">
              <div v-if="user.end_time" class="row">
                <div class="col-xs-10">
                  <omegaup-datetimepicker
                    v-model="user.end_time"
                    :finish="contest.finish_time"
                    :start="contest.start_time"
                  ></omegaup-datetimepicker>
                </div>
                <div class="col-xs-2">
                  <button
                    class="btn btn-link"
                    @click="$emit('save-end-time', user)"
                  >
                    <font-awesome-icon icon="save" />
                  </button>
                </div>
              </div>
            </td>
            <td class="text-center">
              <button
                class="close float-none"
                type="button"
                :title="T.contestAdduserRegisteredUserDelete"
                @click="$emit('remove-user', user)"
              >
                ×
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';

import { types } from '../../api_types';
import T from '../../lang';
import * as time from '../../time';
import DateTimePicker from '../DateTimePicker.vue';
import user_Username from '../user/Username.vue';
import common_MultiTypeahead from '../common/MultiTypeahead.vue';
import MultiUserAddArea from '../common/MultiUserAddArea.vue';

import {
  FontAwesomeIcon,
  FontAwesomeLayers,
  FontAwesomeLayersText,
} from '@fortawesome/vue-fontawesome';
import { fas } from '@fortawesome/free-solid-svg-icons';
import { library } from '@fortawesome/fontawesome-svg-core';
library.add(fas);

@Component({
  components: {
    'omegaup-datetimepicker': DateTimePicker,
    'omegaup-user-username': user_Username,
    'omegaup-common-multi-typeahead': common_MultiTypeahead,
    'omegaup-multi-user-add-area': MultiUserAddArea,
    'font-awesome-icon': FontAwesomeIcon,
    'font-awesome-layers': FontAwesomeLayers,
    'font-awesome-layers-text': FontAwesomeLayersText,
  },
})
export default class AddContestant extends Vue {
  @Prop() users!: types.ContestUser[];
  @Prop() contest!: types.ContestAdminDetails;
  @Prop() searchResultUsers!: types.ListItem[];

  T = T;
  time = time;
  bulkContestants = '';
  typeaheadContestants: types.ListItem[] = [];
  currentUsers = this.users;

  onSubmit(): void {
    let users: string[] = [];
    // Add each token as a new username chip component
    if (this.bulkContestants !== '') {
      users = this.bulkContestants.split(',');
    }

    if (this.typeaheadContestants) {
      users = [...users, ...this.typeaheadContestants.map((user) => user.key)];
    }

    // If no users were added, do nothing
    if (users.length === 0) {
      return;
    }

    this.$emit(
      'add-user',
      users.map((user) => user.trim()),
    );
  }

  // receives a list of users from the MultiUserAddArea component
  updateUsersList(users: string[]): void {
    this.bulkContestants = users.join(',');
  }

  @Watch('users')
  onUsersChange(newUsers: types.ContestUser[]): void {
    this.currentUsers = newUsers;
    this.typeaheadContestants = [];
    this.bulkContestants = '';
  }
}
</script>
