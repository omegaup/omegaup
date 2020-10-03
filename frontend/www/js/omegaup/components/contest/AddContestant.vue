<template>
  <div>
    <div class="card">
      <div class="card-body">
        <form class="form" v-on:submit.prevent="onSubmit">
          <div class="form-group">
            <label>{{ T.wordsUser }}</label>
            <omegaup-autocomplete
              v-bind:init="(el) => typeahead.userTypeahead(el)"
              v-model="contestant"
            ></omegaup-autocomplete>
          </div>
          <button class="btn btn-primary" type="submit">
            {{ T.contestAdduserAddUser }}
          </button>
          <hr />
          <div class="form-group">
            <label>{{ T.wordsMultipleUser }}</label>
            <textarea
              class="form-control contestants"
              rows="4"
              v-model="contestants"
            ></textarea>
          </div>
          <button class="btn btn-primary" type="submit">
            {{ T.contestAdduserAddUsers }}
          </button>
        </form>
      </div>
      <table class="table table-striped mb-0">
        <thead>
          <tr>
            <th class="text-center">{{ T.wordsUser }}</th>
            <th class="text-center">
              {{ T.contestAdduserRegisteredUserTime }}
            </th>
            <th class="text-center" v-if="contest.window_length !== null">
              {{ T.wordsEndTimeContest }}
            </th>
            <th class="text-center">
              {{ T.contestAdduserRegisteredUserDelete }}
            </th>
          </tr>
        </thead>
        <tbody>
          <tr v-bind:key="user.username" v-for="user in users">
            <td class="text-center">
              <omegaup-user-username
                v-bind:linkify="true"
                v-bind:username="user.username"
              ></omegaup-user-username>
            </td>
            <td class="text-center">
              <template v-if="user.access_time !== null">
                {{ time.formatDateTime(user.access_time) }}
              </template>
            </td>
            <td class="text-center" v-if="contest.window_length !== null">
              <div class="row" v-if="user.end_time">
                <div class="col-xs-10">
                  <omegaup-datetimepicker
                    v-bind:finish="contest.finish_time"
                    v-bind:start="contest.start_time"
                    v-model="user.end_time"
                  ></omegaup-datetimepicker>
                </div>
                <div class="col-xs-2">
                  <button
                    class="btn-link glyphicon glyphicon-floppy-disk"
                    v-on:click="$emit('emit-save-end-time', user)"
                  ></button>
                </div>
              </div>
            </td>
            <td class="text-center">
              <button
                class="close float-none"
                type="button"
                v-bind:title="T.contestAdduserRegisteredUserDelete"
                v-on:click="$emit('emit-remove-user', user)"
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
import { Vue, Component, Emit, Prop, Watch } from 'vue-property-decorator';

import { types } from '../../api_types';
import T from '../../lang';
import * as typeahead from '../../typeahead';
import * as time from '../../time';
import Autocomplete from '../Autocomplete.vue';
import DateTimePicker from '../DateTimePicker.vue';
import user_Username from '../user/Username.vue';

@Component({
  components: {
    'omegaup-autocomplete': Autocomplete,
    'omegaup-datetimepicker': DateTimePicker,
    'omegaup-user-username': user_Username,
  },
})
export default class Contestant extends Vue {
  @Prop() initialUsers!: types.ContestUser[];
  @Prop() contest!: types.ContestAdminDetails;

  T = T;
  time = time;
  typeahead = typeahead;
  contestant = '';
  contestants = '';
  users = this.initialUsers;

  onSubmit(): void {
    let users: string[] = [];
    if (this.contestants !== '') {
      users = this.contestants.split(',');
    }
    if (this.contestant !== '') {
      users.push(this.contestant);
    }
    if (this.users.length) {
      this.$emit(
        'emit-add-user',
        users.map((user) => user.trim()),
      );
    }
  }

  @Watch('initialUsers')
  onInitialUsersChange(newUsers: types.ContestUser[]): void {
    this.users = newUsers;
    this.contestant = '';
    this.contestants = '';
  }
}
</script>
