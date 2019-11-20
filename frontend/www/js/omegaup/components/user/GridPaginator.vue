<template>
  <div class="panel panel-default">
    <div class="panel-heading">
      <h2 class="panel-title">{{ T.profileUnsolvedProblems }} <span class="badge">{{
      problems.length }}</span></h2>
    </div>
    <table class="table table-striped"
           v-if="problems.length &gt; 0">
      <tbody>
        <tr v-for="group in paginatedProblems">
          <td v-for="problem in group">
            <a v-bind:href="`/arena/problem/${problem.alias}`">{{ problem.title }}</a>
          </td>
        </tr>
      </tbody>
    </table>
    <div v-show="!paginatedProblems"><img src="/media/wait.gif"></div>
    <div class="panel-footer"
         v-if="problems.length &gt; 0">
      <div class="btn-group"
           role="group">
        <button class="btn btn-primary"
             type="button"
             v-bind:disabled="this.totalPagesCount === 1 || this.currentPageNumber === 0"
             v-on:click="previousPage">{{ T.wordsPrevious }}</button> <button class=
             "btn btn-primary"
             type="button"
             v-bind:disabled=
             "this.totalPagesCount === 1 || this.currentPageNumber &gt;= this.totalPagesCount - 1"
             v-on:click="nextPage">{{ T.wordsNext }}</button>
      </div>
    </div>
  </div>
</template>

<style>
.panel-footer {
  text-align: center;
}
</style>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { T } from '../../omegaup.js';
import omegaup from '../../api.js';
/**
  Creates a two-dimensional paginated table, with the number of columns passed
  as a prop and the number of rows being calculated taking into account the number
  of elements per page, total elements and the number of columns.
 */
@Component
export default class GridPaginator extends Vue {
  @Prop() problems!: omegaup.Problem[];
  @Prop() problemsPerPage!: number;
  @Prop({ default: 3 }) columns!: number;
  @Prop() title!: string;

  private T = T;
  private currentPageNumber: number = 0;

  private nextPage(): void {
    this.currentPageNumber++;
  }

  private previousPage(): void {
    this.currentPageNumber--;
  }

  private get totalPagesCount(): number {
    const totalRows = Math.ceil(this.problems.length / this.columns);
    return Math.ceil(totalRows / this.rowsPerPage);
  }

  private get rowsPerPage(): number {
    return Math.floor(this.problemsPerPage / this.columns);
  }

  private get problemsRows(): omegaup.Problem[][] {
    const groups = [];
    for (let i = 0; i < this.problems.length; i += this.columns) {
      groups.push(this.problems.slice(i, i + this.columns));
    }
    return groups;
  }

  private get paginatedProblems(): omegaup.Problem[][] {
    const start = this.currentPageNumber * this.rowsPerPage;
    const end = start + this.rowsPerPage;
    return this.problemsRows.slice(start, end);
  }
}

</script>
