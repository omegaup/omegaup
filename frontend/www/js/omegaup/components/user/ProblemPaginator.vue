<template>
  <div>
    <table class="table table-striped">
      <tbody>
        <tr v-for="group in paginatedProblems">
          <td v-for="problem in group">
            <a v-bind:href="`/arena/problem/${problem.alias}`">{{ problem.title }}</a>
          </td>
        </tr>
      </tbody>
    </table>
    <div v-show="!paginatedProblems"><img src="/media/wait.gif"></div>
    <div class="button-container">
      <div class="btn-group"
           role="group">
        <button class="btn btn-primary"
             type="button"
             v-bind:disabled="this.totalPagesCount === 1 || this.currentPageNumber === 0"
             v-on:click="previousPage">{{ T.wordsPrevious }}</button> <button class=
             "btn btn-primary"
             type="button"
             v-bind:disabled=
             "this.totalPagesCount === 1 || this.currentPageNumber >= this.totalPagesCount - 1"
             v-on:click="nextPage">{{ T.wordsNext }}</button>
      </div>
    </div>
  </div>
</template>

<style>
table.table {
  margin: 0;
}

.button-container {
  padding: 5px 0;
  text-align: center;
}
</style>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { T } from '../../omegaup.js';
import omegaup from '../../api.js';

@Component
export default class ProblemPaginator extends Vue {
  @Prop() problems!: omegaup.Problem[];
  @Prop() problemsPerPage!: number;
  @Prop({ default: 3 }) columns!: number;

  T = T;
  currentPageNumber: number = 0;

  nextPage(): void {
    this.currentPageNumber++;
  }

  previousPage(): void {
    this.currentPageNumber--;
  }

  get totalPagesCount(): number {
    const totalGroups = Math.ceil(this.problems.length / this.columns);
    return Math.ceil(totalGroups / this.groupsPerPage);
  }

  get groupsPerPage(): number {
    return Math.ceil(this.problemsPerPage / this.columns);
  }

  get groupedProblems(): omegaup.Problem[][] {
    const groups = [];
    for (let i = 0; i < this.problems.length; i += this.columns) {
      groups.push(this.problems.slice(i, i + this.columns));
    }
    return groups;
  }

  get paginatedProblems(): omegaup.Problem[][] {
    const start = this.currentPageNumber * this.groupsPerPage;
    const end = start + this.groupsPerPage;
    return this.groupedProblems.slice(start, end);
  }
}

</script>
