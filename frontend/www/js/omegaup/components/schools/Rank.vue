<template>
  <div>
    <h5 class="text-center mb-5 mt-1 school-rank-title">
      {{
        showHeader
          ? ui.formatString(T.schoolRankOfTheMonthHeader, {
              count: rank ? rank.length : 0,
            })
          : ui.formatString(T.schoolRankRangeHeader, {
              lowCount: (page - 1) * length + 1,
              highCount: page * length,
            })
      }}
    </h5>
    <div class="card table-responsive-sm table-width">
      <table class="table mb-0">
        <thead>
          <tr>
            <th class="text-center" scope="col">#</th>
            <th class="text-center" scope="col">{{ T.profileSchool }}</th>
            <th class="text-center" scope="col">{{ T.wordsScore }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(school, index) in rank" :key="index">
            <th class="text-center" scope="row">
              {{ showHeader ? index + 1 : school.ranking || '' }}
            </th>
            <td class="text-truncate text-center">
              <omegaup-countryflag
                :country="school.country_id"
              ></omegaup-countryflag>
              <a :href="`/schools/profile/${school.school_id}/`">{{
                school.name
              }}</a>
            </td>
            <td class="text-center">
              {{ school.score }}
            </td>
          </tr>
        </tbody>
      </table>
      <div v-if="showHeader" class="card-footer">
        <a href="/rank/schools/">{{ T.wordsSeeGeneralRanking }}</a>
      </div>
      <div v-else class="card-footer">
        <omegaup-common-paginator
          :pager-items="pagerItems"
        ></omegaup-common-paginator>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';

import { omegaup } from '../../omegaup';
import { types } from '../../api_types';
import T from '../../lang';
import * as ui from '../../ui';
import CountryFlag from '../CountryFlag.vue';
import common_Paginator from '../common/Paginator.vue';

@Component({
  components: {
    'omegaup-countryflag': CountryFlag,
    'omegaup-common-paginator': common_Paginator,
  },
})
export default class SchoolRank extends Vue {
  @Prop() page!: number;
  @Prop() length!: number;
  @Prop() showHeader!: boolean;
  @Prop() totalRows!: number;
  @Prop() rank!: omegaup.SchoolsRank[];
  @Prop() pagerItems!: types.PageItem[];

  T = T;
  ui = ui;
}
</script>

<style lang="scss" scoped>
@import '../../../../sass/main.scss';
// FIXME: This prevents wrapping a table cell when the name of the school is too long.
// So, both tables (users rank and the current one) are perfectly aligned.
// Another solution should  be taken in the future.
.text-truncate {
  max-width: 250px;
}

.table-width {
  max-width: 52rem;
  margin: 0 auto;
}

.school-rank-title {
  font-size: 1.6rem;
}
</style>
