<template>
  <div>
    Hola
  </div>
</template>

<style>

</style>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { T } from '../../omegaup.js';

@Component
export default class ProfilePaginator extends Vue {
  @Prop() data!: string[];
  @Prop() sizePerPage!: number;
  @Prop({default: 3}) columns!: number;

  T = T;
  currentPageNumber: number = 0;

  nextPage(): void {
    this.currentPageNumber++;
  }

  previousPage(): void {
    this.currentPageNumber--;
  }

  get totalPagesCount(): number {
    const totalGroups = Math.ceil(this.data.length / this.columns);
    return Math.ceil(totalGroups / this.groupsPerPage);
  }

  get groupsPerPage(): number {
    return Math.ceil(this.sizePerPage / this.columns);
  }

  get groupedData(): string[][] {
    const groups = [];
    for (let i = 0; i < this.data.length; i += this.columns) {
      groups.push(this.data.slice(i, i + this.columns));
    }
    return groups;
  }

  get paginatedData(): string[][] {
    const start = this.currentPageNumber * this.sizePerPage;
    const end = start + this.sizePerPage;
    return this.groupedData.slice(start, end);
  }
}
</script>