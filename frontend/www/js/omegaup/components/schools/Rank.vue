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
            <tr v-for="(school, index) in rank">
              <td>{{index + 1}}</td>
              <td class="cell-school-name"
                  colspan="2"
                  v-bind:title="school.name"><span class="country_id">{{ school.country_id
                  }}</span> {{ school.name }}</td>
              <td class="numericColumn data-rank">{{ school.distinct_users }}</td>
              <td class="numericColumn data-rank">{{ school.distinct_problems }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script>
import {T} from '../../omegaup.js';
import UI from '../../ui.js';

export default {
  props: {
    rank: Array,
  },
  data: function() { return {T: T, UI: UI};},
};
</script>

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
