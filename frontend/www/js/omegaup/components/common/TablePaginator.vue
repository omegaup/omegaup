<template>
  <div class="card" data-table-paginator>
    <h5 v-if="title" class="card-header">
      {{ title }} <span class="badge badge-secondary">{{ items.length }}</span>
      <slot name="header-link"></slot>
    </h5>
    <div v-if="sortOptions.length > 0" class="card-body text-center">
      <div class="form-check form-check-inline">
        <label
          v-for="(sortOption, index) in sortOptions"
          :key="index"
          class="form-check-label mr-4"
        >
          <input
            v-model="currentSortOption"
            class="form-check-input m-0"
            name="sort-selector"
            type="radio"
            :value="sortOption.value"
          />
          {{ sortOption.title }}
        </label>
      </div>
    </div>
    <table
      v-if="items.length > 0"
      class="table table-striped mb-0 table-responsive col-12 table-typo p-0"
    >
      <thead class="d-table col-12">
        <tr>
          <template v-for="column in columnNames"
            ><th :class="column.style">{{ column.name }}</th></template
          >
        </tr>
      </thead>
      <tbody class="d-table col-12">
        <tr v-for="(group, index) in paginatedItems" :key="index">
          <th v-if="showPageOffset" scope="row" class="text-left align-middle">
            {{ currentPageNumber * rowsPerPage + (index + 1) }}
          </th>
          <td
            v-for="(item, itemIndex) in group"
            :key="itemIndex"
            class="align-middle"
          >
            <slot name="item-data" :item="item">
              <a :href="item.getUrl()">
                <img
                  v-if="item.getLogo()"
                  :src="item.getLogo().url"
                  :title="item.getLogo().title"
                  :alt="item.getLogo().title"
                />
                {{ item.toString() }}
              </a>
            </slot>
          </td>
          <td
            v-if="!group[0].getBadge().isEmpty()"
            class="text-right align-middle"
          >
            <strong>{{ group[0].getBadge().get() }}</strong>
          </td>
        </tr>
      </tbody>
    </table>
    <div v-if="items.length > 0" class="card-footer text-center">
      <div class="btn-group" role="group">
        <button
          class="btn btn-primary"
          type="button"
          data-button-previous
          :disabled="totalPagesCount === 1 || currentPageNumber === 0"
          @click="previousPage"
        >
          {{ T.wordsPrevious }}
        </button>
        <button
          class="btn btn-primary"
          type="button"
          data-button-next
          :disabled="
            totalPagesCount === 1 || currentPageNumber >= totalPagesCount - 1
          "
          @click="nextPage"
        >
          {{ T.wordsNext }}
        </button>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';
import T from '../../lang';
import { LinkableResource } from '../../linkable_resource';

interface SortOption {
  value: string;
  title: string;
}

/**
 * Creates a two-dimensional paginated table, with the number of rows being
 * calculated taking into account the number of items per page, total items.
 */
@Component
export default class TablePaginator extends Vue {
  @Prop() items!: LinkableResource[];
  @Prop() itemsPerPage!: number;
  @Prop() columnNames!: Array<{ name: string; style: string }>;
  @Prop() title!: string;
  @Prop({ default: false }) showPageOffset!: boolean;
  @Prop({ default: () => [] }) sortOptions!: SortOption[];

  private T = T;
  private currentPageNumber = 0;
  private currentSortOption =
    this.sortOptions.length > 0 ? this.sortOptions[0].value : '';

  private nextPage(): void {
    this.currentPageNumber++;
  }

  private previousPage(): void {
    this.currentPageNumber--;
  }

  private get totalPagesCount(): number {
    return Math.ceil(this.items.length / this.rowsPerPage);
  }

  private get rowsPerPage(): number {
    return Math.floor(this.itemsPerPage);
  }

  private get itemsRows(): LinkableResource[][] {
    const groups = [];
    for (let i = 0; i < this.items.length; i++) {
      groups.push(this.items.slice(i, i + 1));
    }
    return groups;
  }

  private get paginatedItems(): LinkableResource[][] {
    const start = this.currentPageNumber * this.rowsPerPage;
    const end = start + this.rowsPerPage;
    return this.itemsRows.slice(start, end);
  }

  @Watch('currentSortOption')
  onCurrentSortOptionChange(newSelector: string) {
    this.$emit('sort-option-change', newSelector);
  }
}
</script>

<style>
@media (max-width: 550px) {
  .table-typo td,
  .table-typo th {
    display: block;
    background-color: var(--table-paginator-background-color);
    border: 1px solid var(--table-paginator-border-color);
  }
}

[data-table-paginator] .table td {
  padding: 0.75rem 1.25rem;
}

.table-bordered.table-no-outer-border {
  border: none;
}
</style>
