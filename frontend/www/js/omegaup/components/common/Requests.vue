<template>
  <div v-if="requests.length" class="card mt-3" data-requests>
    <h5 class="card-header">
      {{ T.pendingRegistrations }}
    </h5>
    <label class="text-right m-3">
      <input v-model="showAllRequests" type="checkbox" />
      {{ T.pendingRegistrationsShowAll }}
    </label>
    <table class="table mb-0">
      <thead>
        <tr>
          <th class="text-center">{{ T.requestCoder }}</th>
          <th class="text-center">{{ T.userEditCountry }}</th>
          <th class="text-center">{{ T.requestDate }}</th>
          <th class="text-center">{{ T.currentStatus }}</th>
          <th class="text-center">{{ T.lastUpdate }}</th>
          <th class="text-center">{{ textAddParticipant }}</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="request in filteredRequests" :key="request.username">
          <td class="text-center">
            <omegaup-username
              :classname="request.classname"
              :username="request.username"
              :name="request.name"
              :linkify="true"
            ></omegaup-username>
          </td>
          <td class="text-center">{{ request.country }}</td>
          <td class="text-center">
            {{ time.formatTimestamp(request.request_time) }}
          </td>
          <td v-if="request.last_update === null" class="text-center">
            {{ T.wordsPending }}
          </td>
          <td v-else-if="request.accepted" class="text-center">
            {{ T.wordAccepted }}
          </td>
          <td v-else class="text-center">{{ T.wordsDenied }}</td>
          <td v-if="request.last_update !== null">
            {{ time.formatTimestamp(request.last_update) }} ({{
              request.admin.username
            }})
          </td>
          <td v-else></td>
          <td v-if="!request.accepted" class="text-center">
            <button
              class="close float-none text-danger mx-2"
              @click="$emit('deny-request', { username: request.username })"
            >
              ×
            </button>
            <button
              class="close float-none text-success mx-2"
              @click="$emit('accept-request', { username: request.username })"
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
  showAllRequests = false;

  @Watch('data')
  onDataChange(): void {
    this.requests = this.data;
  }

  get filteredRequests(): types.IdentityRequest[] {
    if (this.showAllRequests) {
      return this.requests;
    }
    return this.requests.filter((request) => !request.accepted);
  }
}
</script>
