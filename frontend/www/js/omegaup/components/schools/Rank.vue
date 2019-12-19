<template>
  <div class="panel panel-default">
    <div class="panel-heading">
      <h3 v-if="isIndex" class="panel-title">
        {{
          UI.formatString(T.schoolRankHeader, { count: rank ? rank.length : 0 })
        }}
      </h3>
      <h3 v-else="">
        {{
          UI.formatString(T.schoolRankRangeHeader, {
            lowCount: (page - 1) * length + 1,
            highCount: page * length,
          })
        }}
      </h3>
    </div>
    <div class="panel-body no-padding">
      <div class="table-responsive">
        <table
          class="school-rank-table table table-striped table-hover no-margin"
        >
          <thead>
            <tr>
              <th>#</th>
              <th colspan="2">{{ T.profileSchool }}</th>
              <th class="numericColumn data-rank">{{ T.wordsScore }}</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(school, index) in rank">
              <td>{{ index + 1 }}</td>
              <td
                class="cell-school-name"
                colspan="2"
                v-bind:title="school.name"
              >
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
  @Prop() page!: number;
  @Prop() length!: number;
  @Prop() isIndex!: boolean;
  @Prop() rank!: omegaup.SchoolsRank[];

  T = T;
  UI = UI;
}
</script>
