<template>
  <div>
    <div class="card contestants-input-area">
      <div class="card-body">
        <form class="form" @submit.prevent="onSubmit">
          <div class="form-group">
            <label>{{ T.wordsUser }}</label>
            <omegaup-common-typeahead
              :existing-options="existingUsers"
              :limit="10"
              @update-existing-options="
                (query) => $emit('update-existing-users', query)
              "
              @update-selected-option="onSelectUser"
            >
            </omegaup-common-typeahead>
          </div>
          <button class="btn btn-primary user-add-single" type="submit">
            {{ T.contestAdduserAddUser }}
          </button>
          <hr />
          <div class="form-group">
            <label>{{ T.wordsMultipleUser }}</label>
            <textarea
              v-model="contestants"
              class="form-control contestants"
              rows="4"
            ></textarea>
          </div>
          <button class="btn btn-primary user-add-bulk" type="submit">
            {{ T.contestAdduserAddUsers }}
          </button>
        </form>
      </div>
      <table class="table table-striped mb-0 participants">
        <thead>
          <tr>
            <th class="text-center">{{ T.wordsUser }}</th>
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
          <tr v-for="user in users" :key="user.username">
            <td class="text-center">
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
                    class="btn-link glyphicon glyphicon-floppy-disk"
                    @click="$emit('emit-save-end-time', user)"
                  ></button>
                </div>
              </div>
            </td>
            <td class="text-center">
              <button
                class="close float-none"
                type="button"
                :title="T.contestAdduserRegisteredUserDelete"
                @click="$emit('emit-remove-user', user)"
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
import common_Typeahead from '../common/Typeahead.vue';

@Component({
  components: {
    'omegaup-datetimepicker': DateTimePicker,
    'omegaup-user-username': user_Username,
    'omegaup-common-typeahead': common_Typeahead,
  },
})
export default class AddContestant extends Vue {
  @Prop() initialUsers!: types.ContestUser[];
  @Prop() contest!: types.ContestAdminDetails;
  @Prop() existingUsers!: { key: string; value: string }[];

  T = T;
  time = time;
  contestant = '';
  contestants = '';
  users = this.initialUsers;
  username = '';

  onSubmit(): void {
    let users: string[] = [];
    if (this.contestants !== '') {
      users = this.contestants.split(',');
    }
    if (this.contestant !== '') {
      users.push(this.contestant);
    }
    if (users.length) {
      this.$emit(
        'emit-add-user',
        users.map((user) => user.trim()),
      );
    }
  }

  onSelectUser(username: string) {
    this.username = username;
  }

  @Watch('initialUsers')
  onInitialUsersChange(newUsers: types.ContestUser[]): void {
    this.users = newUsers;
    this.contestant = '';
    this.contestants = '';
  }
}
</script>
