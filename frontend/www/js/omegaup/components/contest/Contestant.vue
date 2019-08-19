<template>
  <div>
    <div class="panel panel-primary contestants-input-area">
      <div class="panel-body">
        <form class="form"
              v-on:submit.prevent="onSubmit">
          <div class="form-group">
            <label>{{T.wordsUser}}</label> <omegaup-autocomplete v-bind:init=
            "el =&gt; UI.userTypeahead(el)"
                 v-model="contestant"></omegaup-autocomplete>
          </div><button class="btn btn-primary user-add-single"
                type="submit">{{T.contestAdduserAddUser}}</button>
          <hr>
          <div class="form-group">
            <label>{{T.wordsMultipleUser}}</label>
            <textarea class="form-control contestants"
                 rows="4"
                 v-model="contestants"></textarea>
          </div><button class="btn btn-primary user-add-bulk"
                type="submit">{{T.contestAdduserAddUsers}}</button>
        </form>
      </div>
      <table class="table table-striped participants">
        <thead>
          <tr>
            <th>{{T.wordsUser}}</th>
            <th>{{T.contestAdduserRegisteredUserTime}}</th>
            <th v-if="contest.window_length !== null">{{T.wordsEndTimeContest}}</th>
            <th>{{T.contestAdduserRegisteredUserDelete}}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="user in users">
            <td><omegaup-user-username v-bind:linkify="true"
                                   v-bind:username="user.username"></omegaup-user-username></td>
            <td>
              <template v-if="user.access_time !== null">
                {{ UI.formatDateTime(user.access_time) }}
              </template>
            </td>
            <td v-if="contest.window_length !== null">
              <div class="row"
                   v-if="user.end_time">
                <div class="col-xs-10">
                  <omegaup-datetimepicker v-bind:finish="contest.finish_time"
                       v-bind:start="contest.start_time"
                       v-model="user.end_time"></omegaup-datetimepicker>
                </div>
                <div class="col-xs-2">
                  <button class="btn-link glyphicon glyphicon-floppy-disk"
                       v-on:click="onSaveEndTime(user)"></button>
                </div>
              </div>
            </td>
            <td><button class="close"
                    type="button"
                    v-bind:title="T.contestAdduserRegisteredUserDelete"
                    v-on:click="onRemove(user)">×</button></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Emit, Prop } from 'vue-property-decorator';

import { T } from '../../omegaup.js';
import omegaup from '../../api.js';
import UI from '../../ui.js';
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
  UI = UI;
  contestant = '';
  contestants = '';
  users = this.data;
  selected = {};

  onSaveEndTime(user: omegaup.IdentityContest): void {
    this.selected = user;
    this.$parent.$emit('save-end-time', this.selected);
  }

  onSubmit(): void {
    this.$parent.$emit('add-user', this);
  }

  onRemove(user: omegaup.IdentityContest): void {
    this.selected = user;
    this.$parent.$emit('remove-user', this);
  }
}

</script>
