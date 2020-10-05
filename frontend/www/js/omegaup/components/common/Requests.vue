<template>
  <div v-if="requests.length !== 0" class="panel panel-primary">
    <div class="panel-body">
      {{ T.pendingRegistrations }}
    </div>
    <table class="table">
      <thead>
        <tr>
          <th>{{ T.wordsUser }}</th>
          <th>{{ T.userEditCountry }}</th>
          <th>{{ T.requestDate }}</th>
          <th>{{ T.currentStatus }}</th>
          <th>{{ T.lastUpdate }}</th>
          <th>{{ textAddParticipant }}</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="request in requests" :key="request.username">
          <td>
            <omegaup-username
              :classname="request.classname"
              :username="request.username"
              :name="request.name"
              :linkify="true"
              :show-name-with-username="true"
            ></omegaup-username>
          </td>
          <td>{{ request.country }}</td>
          <td>{{ time.formatTimestamp(request.request_time) }}</td>
          <td v-if="request.last_update === null">{{ T.wordsPending }}</td>
          <td v-else-if="request.accepted">
            {{ T.wordAccepted }}
          </td>
          <td v-else>{{ T.wordsDenied }}</td>
          <td v-if="request.last_update !== null">
            {{ time.formatTimestamp(request.last_update) }} ({{
              request.admin.username
            }})
          </td>
          <td v-else></td>
          <td v-if="!request.accepted">
            <button
              class="close"
              style="color: red"
              @click="onDenyRequest(request.username)"
            >
              ×
            </button>
            <button
              class="close"
              style="color: green"
              @click="onAcceptRequest(request.username)"
            >
              ✓
            </button>
          </td>
          <td v-else></td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';
import { types } from '../../api_types';
import T from '../../lang';
import * as time from '../../time';
import omegaup_Username from '../user/Username.vue';

@Component({
  components: {
    'omegaup-username': omegaup_Username,
  },
})
export default class Requests extends Vue {
  @Prop() data!: types.IdentityRequest[];
  @Prop() textAddParticipant!: string;

  T = T;
  time = time;
  requests: types.IdentityRequest[] = this.data;

  onAcceptRequest(username: string): void {
    this.$emit('emit-accept-request', this, username);
  }
  onDenyRequest(username: string): void {
    this.$emit('emit-deny-request', this, username);
  }

  @Watch('data')
  onDataChange(): void {
    this.requests = this.data;
  }
}
</script>
