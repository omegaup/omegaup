<template>
  <span class="ml-1">
    <a href="#" @click="$emit('apply-filter', column, toggleSort)">
      <font-awesome-icon
        v-if="!selected"
        :icon="['fas', 'exchange-alt']"
        color="lightgray"
        rotation="90"
      />
      <font-awesome-icon v-else :icon="['fas', iconDisplayed]" color="black" />
    </a>
  </span>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { omegaup } from '../../omegaup';

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
  @Prop() sortOrder!: omegaup.SortOrder;
  @Prop({ default: omegaup.ColumnType.Number }) columnType!: omegaup.ColumnType;
  @Prop() columnName!: string;

  get iconDisplayed(): string {
    if (this.sortOrder === omegaup.SortOrder.Descending) {
      if (this.columnType === omegaup.ColumnType.Number) {
        return 'sort-amount-down';
      }
      return 'sort-alpha-down';
    }
    if (this.columnType === omegaup.ColumnType.Number) {
      return 'sort-amount-up';
    }
    return 'sort-alpha-up';
  }

  get selected(): boolean {
    return this.column === this.columnName;
  }

  get toggleSort(): string {
    return this.sortOrder === omegaup.SortOrder.Ascending
      ? omegaup.SortOrder.Descending
      : omegaup.SortOrder.Ascending;
  }
}
</script>
