<template>
  <div class="panel panel-default omegaup-schools-rank">
    <div class="panel-heading">
      <h3 class="panel-title">{{ UI.formatString(T.schoolRankHeader, {count: rank?rank.length:0})
      }}</h3>
    </div>
    <div class="panel-body no-padding">
      <div class="table-responsive">
        <table class="school-rank-table table table-striped table-hover no-margin">
          <thead>
            <tr>
              <th>#</th>
              <th colspan="2">{{ T.profileSchool }}</th>
              <th class="numericColumn data-rank">{{ T.activeUsers }}</th>
              <th class="numericColumn data-rank">{{ T.profileSolvedProblems }}</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(school, index) in rankFiltered">
              <td>{{index + 1}}</td>
              <td class="cell-school-name"
                  colspan="2"
                  v-bind:title="school.name"><omegaup-countryflag v-bind:country=
                  "school.country_id"></omegaup-countryflag> {{ school.name }}</td>
              <td class="numericColumn data-rank">{{ school.distinct_users }}</td>
              <td class="numericColumn data-rank">{{ school.distinct_problems }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<style>
.table-rank {
  width: 100%;
}

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

import { T } from '../../omegaup.js';
import omegaup from '../../api.js';
import UI from '../../ui.js';
import CountryFlag from '../CountryFlag.vue';

@Component({
  components: {
    'omegaup-countryflag': CountryFlag,
  },
})
export default class Rank extends Vue {
  @Prop() rank!: omegaup.SchoolsRank[];
  @Prop() rowCount!: number;

  T = T;
  UI = UI;

  get rankFiltered() {
    return this.rank.slice(0, this.rowCount);
  }
}

</script>
