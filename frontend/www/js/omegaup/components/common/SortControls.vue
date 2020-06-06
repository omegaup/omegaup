<template>
  <span class="ml-1">
    <a href="#" v-on:click="toggleSort">
      <font-awesome-icon
        fixed-width
        v-bind:icon="['fas', 'exchange-alt']"
        color="lightgray"
        rotation="90"
        v-if="!selected"
      />
      <template v-else="">
        <a href="#">
          <font-awesome-icon
            v-bind:icon="['fas', 'sort-amount-down']"
            color="black"
            v-if="orderMode === 'asc' && columnType === 'number'"
          />
          <font-awesome-icon
            v-bind:icon="['fas', 'sort-alpha-down']"
            color="black"
            v-if="orderMode === 'asc' && columnType === 'string'"
          />
          <font-awesome-icon
            v-bind:icon="['fas', 'sort-amount-up']"
            color="black"
            v-if="orderMode === 'desc' && columnType === 'number'"
          />
          <font-awesome-icon
            v-bind:icon="['fas', 'sort-alpha-up']"
            color="black"
            v-if="orderMode === 'desc' && columnType === 'string'"
          />
        </a>
      </template>
    </a>
  </span>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';

import { library } from '@fortawesome/fontawesome-svg-core';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import {
  faSortAlphaDown,
  faSortAlphaUp,
  faSortAmountDown,
  faSortAmountUp,
  faExchangeAlt,
} from '@fortawesome/free-solid-svg-icons';
library.add(
  faSortAlphaDown,
  faSortAlphaUp,
  faSortAmountDown,
  faSortAmountUp,
  faExchangeAlt,
);

@Component({
  components: {
    FontAwesomeIcon,
  },
})
export default class SortControls extends Vue {
  @Prop() column!: string;
  @Prop({ default: 'number' }) columnType!: string;

  get selected(): boolean {
    const queryString = window.location.search;
    if (!queryString) return false;
    const urlParams = new URLSearchParams(queryString);
    if (!urlParams.get('order_by')) return false;
    return this.column === urlParams.get('order_by');
  }

  get orderMode(): string {
    const queryString = window.location.search;
    if (!queryString) return 'desc';
    const urlParams = new URLSearchParams(queryString);
    if (!urlParams.get('mode')) return 'desc';
    return urlParams.get('mode') ?? 'desc';
  }

  toggleSort(): void {
    const queryString = window.location.search;
    let mode = 'asc';
    if (!queryString) {
      window.location.replace(
        `/problem/?query=&order_by=${this.column}&mode=${mode}`,
      );
      return;
    }
    const urlParams = new URLSearchParams(queryString);
    if (!urlParams.get('mode')) {
      window.location.replace(`${queryString}&mode=${mode}`);
      return;
    }
    if (!urlParams.get('order_by')) {
      window.location.replace(`${queryString}&order_by=${this.column}`);
      return;
    }
    urlParams.set('mode', urlParams.get('mode') === 'asc' ? 'desc' : 'asc');
    urlParams.set('order_by', this.column);

    const newQueryString = urlParams.toString();
    window.location.replace(`/problem/?${newQueryString}`);
  }
}
</script>
