<template>
  <div>
    <div class="panel panel-primary contestants-input-area">
      <div class="panel-body">
        <form class="form" v-on:submit.prevent="onSubmit">
          <div class="form-group">
            <label>{{ T.wordsUser }}</label>
            <omegaup-autocomplete
              v-model="contestant"
              v-bind:init="(el) => typeahead.userTypeahead(el)"
            ></omegaup-autocomplete>
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
      <table class="table table-striped participants">
        <thead>
          <tr>
            <th>{{ T.wordsUser }}</th>
            <th>{{ T.contestAdduserRegisteredUserTime }}</th>
            <th v-if="contest.window_length !== null">
              {{ T.wordsEndTimeContest }}
            </th>
            <th>{{ T.contestAdduserRegisteredUserDelete }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="user in users">
            <td>
              <omegaup-user-username
                v-bind:linkify="true"
                v-bind:username="user.username"
              ></omegaup-user-username>
            </td>
            <td>
              <template v-if="user.access_time !== null">
                {{ time.formatDateTime(user.access_time) }}
              </template>
            </td>
            <td v-if="contest.window_length !== null">
              <div v-if="user.end_time" class="row">
                <div class="col-xs-10">
                  <omegaup-datetimepicker
                    v-model="user.end_time"
                    v-bind:finish="contest.finish_time"
                    v-bind:start="contest.start_time"
                  ></omegaup-datetimepicker>
                </div>
                <div class="col-xs-2">
                  <button
                    class="btn-link glyphicon glyphicon-floppy-disk"
                    v-on:click="onSaveEndTime(user)"
                  ></button>
                </div>
              </div>
            </td>
            <td>
              <button
                class="close"
                type="button"
                v-bind:title="T.contestAdduserRegisteredUserDelete"
                v-on:click="onRemove(user)"
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
import { Vue, Component, Prop } from 'vue-property-decorator';

import { omegaup } from '../../omegaup';
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
  @Prop() data!: omegaup.IdentityContest[];
  @Prop() contest!: omegaup.Contest;

  T = T;
  time = time;
  typeahead = typeahead;
  contestant = '';
  contestants = '';
  users = this.data;
  selected: omegaup.IdentityContest = { username: '' };

  onSaveEndTime(user: omegaup.IdentityContest): void {
    this.selected = user;
    this.$emit('emit-save-end-time', this.selected);
  }

  onSubmit(): void {
    this.$emit('emit-add-user', this);
  }

  onRemove(user: omegaup.IdentityContest): void {
    this.selected = user;
    this.$emit('emit-remove-user', this);
  }
}
</script>
