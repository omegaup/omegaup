<template>
  <div class="panel panel-default">
    <div class="panel-heading">
      <h2 class="panel-title">
        {{ title }} <span class="badge">{{ items.length }}</span>
      </h2>
    </div>
    <div class="panel-body text-center" v-if="sortOptions.length > 0">
      <div class="form-check form-check-inline">
        <label v-for="sortOption in sortOptions" class="radio-inline">
          <input
            name="sort-selector"
            type="radio"
            v-bind:value="sortOption.value"
            v-model="currentSortOption"
          />
          {{ sortOption.title }}
        </label>
      </div>
    </div>
    <table class="table table-striped" v-if="items.length &gt; 0">
      <slot name="table-header"></slot>
      <tbody>
        <tr v-for="(group, index) in paginatedItems">
          <td v-if="includePlace" class="text-center">
            {{ currentPageNumber * rowsPerPage + (index + 1) }}
          </td>
          <td v-for="item in group">
            <a v-bind:href="item.getUrl()">{{ item.toString() }}</a>
          </td>
          <td class="numericColumn" v-if="!group[0].getBadge().isEmpty()">
            <strong>{{ group[0].getBadge().get() }}</strong>
          </td>
        </tr>
      </tbody>
    </table>
    <div class="panel-footer text-center" v-if="items.length &gt; 0">
      <div class="btn-group" role="group">
        <button
          class="btn btn-primary"
          type="button"
          v-bind:disabled="
            this.totalPagesCount === 1 || this.currentPageNumber === 0
          "
          v-on:click="previousPage"
        >
          {{ T.wordsPrevious }}
        </button>
        <button
          class="btn btn-primary"
          type="button"
          v-bind:disabled="this.totalPagesCount === 1 || this.currentPageNumber &gt;= this.totalPagesCount - 1"
          v-on:click="nextPage"
        >
          {{ T.wordsNext }}
        </button>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';
import { T } from '../omegaup.js';
import omegaup from '../api.js';
import { LinkableResource } from '../types.ts';

interface SortOption {
  value: string;
  title: string;
}

/**
  Creates a two-dimensional paginated table, with the number of columns passed
  as a prop and the number of rows being calculated taking into account the number
  of items per page, total items and the number of columns.
 */
@Component
export default class GridPaginator extends Vue {
  @Prop() items!: LinkableResource[];
  @Prop() itemsPerPage!: number;
  @Prop({ default: 3 }) columns!: number;
  @Prop() title!: string;
  @Prop({ default: false }) includePlace!: boolean;
  @Prop({ default: () => [] }) sortOptions!: SortOption[];

  private T = T;
  private currentPageNumber: number = 0;
  private currentSortOption =
    this.sortOptions.length > 0 ? this.sortOptions[0].value : '';

  private nextPage(): void {
    this.currentPageNumber++;
  }

  private previousPage(): void {
    this.currentPageNumber--;
  }

  private get totalPagesCount(): number {
    const totalRows = Math.ceil(this.items.length / this.columns);
    return Math.ceil(totalRows / this.rowsPerPage);
  }

  private get rowsPerPage(): number {
    return Math.floor(this.itemsPerPage / this.columns);
  }

  private get itemsRows(): LinkableResource[][] {
    const groups = [];
    for (let i = 0; i < this.items.length; i += this.columns) {
      groups.push(this.items.slice(i, i + this.columns));
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
