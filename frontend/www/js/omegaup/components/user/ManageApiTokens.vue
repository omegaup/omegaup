<template>
  <div>
    <form class="form" @submit.prevent="handleCreateApiToken">
      <div class="form-group">
        <p>
          {{ T.apiTokenDescription }}
        </p>
      </div>
      <div class="form-group">
        <label class="w-100">
          {{ T.apiTokenName }}
          <input
            v-model="tokenName"
            autocomplete="off"
            class="form-control username-input"
            size="20"
            type="text"
          />
        </label>
      </div>
      <div class="form-group text-right">
        <button class="btn btn-primary" type="submit">
          {{ T.apiTokenAdd }}
        </button>
      </div>
    </form>
    <div v-if="apiTokens.length == 0">
      <div class="empty-category">
        {{ T.profileApiTokensEmpty }}
      </div>
    </div>
    <div v-else class="table-responsive">
      <table class="table table-striped table-over">
        <thead>
          <tr>
            <th>{{ T.apiTokenName }}</th>
            <th>{{ T.apiTokenTimestamp }}</th>
            <th>{{ T.apiTokenLastTimeUsed }}</th>
            <th>{{ T.apiTokenResetTime }}</th>
            <th>{{ T.apiTokenRemaining }}</th>
            <th>{{ T.apiTokenLimit }}</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="apiToken in apiTokens" :key="apiToken.name">
            <td :data-label="T.apiTokenName">{{ apiToken.name }}</td>
            <td :data-label="T.apiTokenTimestamp">{{ formatTime(apiToken.timestamp) }}</td>
            <td :data-label="T.apiTokenLastTimeUsed">{{ formatTime(apiToken.last_used) }}</td>
            <td :data-label="T.apiTokenResetTime">{{ formatTime(apiToken.rate_limit.reset) }}</td>
            <td :data-label="T.apiTokenRemaining">{{ apiToken.rate_limit.remaining }}</td>
            <td :data-label="T.apiTokenLimit">{{ apiToken.rate_limit.limit }}</td>
            <td>
              <button
                class="btn btn-secondary btn-sm"
                @click="$emit('revoke-api-token', apiToken.name)"
              >
                {{ T.apiTokenRevoke }}
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
import { types } from '../../api_types';
import T from '../../lang';
import * as time from '../../time';

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
    'font-awesome-icon': FontAwesomeIcon,
    'font-awesome-layers': FontAwesomeLayers,
    'font-awesome-layers-text': FontAwesomeLayersText,
  },
})
export default class ManageApiTokens extends Vue {
  @Prop({ default: () => [] }) apiTokens!: types.ApiToken[];

  T = T;
  time = time;

  tokenName = null;

  handleCreateApiToken() {
    this.$emit('create-api-token', this.tokenName);
    this.tokenName = null; // Clear the tokenName value
  }

  formatTime(value: Date) {
    return time.formatDateTime(value);
  }
}
</script>

<style lang="scss" scoped>
.table-responsive {
  overflow-x: auto;
}

// Tablet aur Desktop ke liye normal table
.table {
  th, td { vertical-align: middle; }
}

// Mobile ke liye Card Layout (Max-width: 768px)
@media screen and (max-width: 768px) {
  .table-responsive { border: none; }
  
  .table {
    display: block;
    thead { display: none; } // Headers chhupa diye
    
    tr {
      display: block;
      margin-bottom: 1rem;
      border: 1px solid #ddd;
      border-radius: 8px;
      padding: 10px;
      background: #fdfdfd;
    }

    td {
      display: flex;
      justify-content: space-between;
      border: none;
      padding: 8px 5px;
      text-align: right;

      // Ye label dikhayega left side pe
      &::before {
        content: attr(data-label);
        font-weight: bold;
        color: #555;
        text-align: left;
      }
      
      // Revoke button wala cell
      &:last-child {
        justify-content: center;
        border-top: 1px solid #eee;
        margin-top: 5px;
      }
    }
  }
}
</style>