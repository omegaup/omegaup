<template>
  <div class="panel panel-default">
    <div class="panel-heading">
      <h3 class="panel-title" v-if="showHeader">
        {{
          UI.formatString(T.schoolRankHeader, { count: rank ? rank.length : 0 })
        }}
      </h3>
      <h3 class="panel-title" v-else="">
        {{
          UI.formatString(T.schoolRankRangeHeader, {
            lowCount: (page - 1) * length + 1,
            highCount: page * length,
          })
        }}
      </h3>
    </div>
    <div class="panel-body" v-if="showControls">
      <template v-if="page > 1">
        <a class="prev" v-bind:href="`/rank/schools/?page=${page - 1}`">
          {{ T.wordsPrevPage }}</a
        >
        <span class="delimiter" v-show="showNextPage">|</span>
      </template>
      <a
        class="next"
        v-show="showNextPage"
        v-bind:href="`/rank/schools/?page=${page + 1}`"
        >{{ T.wordsNextPage }}</a
      >
    </div>
    <table class="table">
      <thead>
        <tr>
          <th>#</th>
          <th colspan="2">{{ T.profileSchool }}</th>
          <th class="numericColumn data-rank">{{ T.wordsScore }}</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(school, index) in rank">
          <td v-if="showHeader">{{ index + 1 }}</td>
          <td v-else="">{{ school.ranking ? school.ranking : '' }}</td>
          <td class="cell-school-name" colspan="2" v-bind:title="school.name">
            <omegaup-countryflag
              v-bind:country="school.country_id"
            ></omegaup-countryflag>
            <a v-bind:href="`/schools/profile/${school.school_id}/`">{{
              school.name
            }}</a>
          </td>
          <td class="numericColumn data-rank">
            {{ school.score }}
          </td>
        </tr>
      </tbody>
    </table>
    <div class="panel-footer" v-if="showHeader">
      <a href="/schoolofthemonth/">{{ T.rankViewFull }}</a>
    </div>
    <div class="panel-footer" v-else-if="showControls">
      <template v-if="page > 1">
        <a class="prev" v-bind:href="`/rank/schools/?page=${page - 1}`">
          {{ T.wordsPrevPage }}</a
        >
        <span class="delimiter" v-show="showNextPage">|</span>
      </template>
      <a
        class="next"
        v-show="showNextPage"
        v-bind:href="`/rank/schools/?page=${page + 1}`"
        >{{ T.wordsNextPage }}</a
      >
    </div>
  </div>
</template>

<style>
.cell-school-name {
  max-width: 0;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.data-rank {
  width: 15%;
}
</style>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';

import { omegaup } from '../../omegaup';
import T from '../../lang';
import * as UI from '../../ui';
import CountryFlag from '../CountryFlag.vue';

@Component({
  components: {
    'omegaup-countryflag': CountryFlag,
  },
})
export default class Rank extends Vue {
  @Prop() page!: number;
  @Prop() length!: number;
  @Prop() showHeader!: boolean;
  @Prop() totalRows!: number;
  @Prop() rank!: omegaup.SchoolsRank[];

  T = T;
  UI = UI;

  get showNextPage(): boolean {
    return this.length * this.page < this.totalRows;
  }

  get showControls(): boolean {
    return !this.showHeader && (this.showNextPage || this.page > 1);
  }
}
</script>
