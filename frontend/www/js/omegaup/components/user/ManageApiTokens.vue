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
    <div v-else>
      <!-- Desktop / tablet (md and up): standard table -->
      <div class="table-responsive d-none d-md-block">
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
              <td>{{ apiToken.name }}</td>
              <td>{{ formatTime(apiToken.timestamp) }}</td>
              <td>{{ formatTime(apiToken.last_used) }}</td>
              <td>{{ formatTime(apiToken.rate_limit.reset) }}</td>
              <td>{{ apiToken.rate_limit.remaining }}</td>
              <td>{{ apiToken.rate_limit.limit }}</td>
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

      <!-- Mobile / small screens (below md): vertical card layout -->
      <div class="d-block d-md-none">
        <div
          v-for="apiToken in apiTokens"
          :key="apiToken.name"
          class="api-token-card"
        >
          <div class="api-token-card__row">
            <span class="api-token-card__label">{{ T.apiTokenName }}</span>
            <span class="api-token-card__value">{{ apiToken.name }}</span>
          </div>
          <div class="api-token-card__row">
            <span class="api-token-card__label">{{ T.apiTokenTimestamp }}</span>
            <span class="api-token-card__value">{{
              formatTime(apiToken.timestamp)
            }}</span>
          </div>
          <div class="api-token-card__row">
            <span class="api-token-card__label">{{
              T.apiTokenLastTimeUsed
            }}</span>
            <span class="api-token-card__value">{{
              formatTime(apiToken.last_used)
            }}</span>
          </div>
          <div class="api-token-card__row">
            <span class="api-token-card__label">{{ T.apiTokenResetTime }}</span>
            <span class="api-token-card__value">{{
              formatTime(apiToken.rate_limit.reset)
            }}</span>
          </div>
          <div class="api-token-card__row">
            <span class="api-token-card__label">{{ T.apiTokenRemaining }}</span>
            <span class="api-token-card__value">{{
              apiToken.rate_limit.remaining
            }}</span>
          </div>
          <div class="api-token-card__row">
            <span class="api-token-card__label">{{ T.apiTokenLimit }}</span>
            <span class="api-token-card__value">{{
              apiToken.rate_limit.limit
            }}</span>
          </div>
          <div class="api-token-card__actions">
            <button
              class="btn btn-secondary btn-sm btn-block"
              @click="$emit('revoke-api-token', apiToken.name)"
            >
              {{ T.apiTokenRevoke }}
            </button>
          </div>
        </div>
      </div>
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
  -webkit-overflow-scrolling: touch;
}

.table {
  white-space: nowrap;

  th,
  td {
    vertical-align: middle;
  }
}

// Mobile card layout
.api-token-card {
  border: 1px solid var(--user-api-token-card-border-color);
  border-radius: 0.5rem;
  padding: 1rem;
  margin-bottom: 1rem;
  background-color: var(--user-api-token-card-background-color);
  box-shadow: 0 1px 3px var(--user-api-token-card-box-shadow-color);

  &__row {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    padding: 0.35rem 0;
    border-bottom: 1px solid var(--user-api-token-card-row-border-color);

    &:last-of-type {
      border-bottom: none;
    }
  }

  &__label {
    font-weight: 600;
    color: var(--user-api-token-card-label-color);
    font-size: 0.85rem;
    flex: 0 0 45%;
    padding-right: 0.5rem;
  }

  &__value {
    color: var(--user-api-token-card-value-color);
    font-size: 0.85rem;
    flex: 0 0 55%;
    text-align: right;
    word-break: break-word;
  }

  &__actions {
    margin-top: 0.75rem;

    .btn-block {
      display: block;
      width: 100%;
    }
  }
}
</style>
