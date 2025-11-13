<template>
  <div class="card">
    <h5
      class="card-header d-flex justify-content-between align-items-center school-rank-title"
    >
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

      <a :href="SchoolRankingFeatureGuideURL">
        <font-awesome-icon :icon="['fas', 'question-circle']" />
        {{ T.wordsRankingMeasurement }}
      </a>
    </h5>
    <div v-if="!showHeader" class="card-body form-row">
      <template v-if="Object.keys(availableFilters).length > 0">
        <select
          v-model="currentFilter"
          class="filter form-control col-12 col-md-5 mt-2 mt-md-0"
        >
          <option value="">
            {{ T.wordsSelectFilter }}
          </option>
          <option
            v-for="(item, key, index) in availableFilters"
            :key="index"
            :value="key"
          >
            {{ item }}
          </option>
        </select>
      </template>
    </div>
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
            {{ school.score.toFixed(2) }}
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
</template>

<script lang="ts">
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';

import { omegaup } from '../../omegaup';
import { types } from '../../api_types';
import T from '../../lang';
import * as ui from '../../ui';
import CountryFlag from '../CountryFlag.vue';
import common_Paginator from '../common/Paginator.vue';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { getBlogUrl } from '../../urlHelper';

@Component({
  components: {
    FontAwesomeIcon,
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
  @Prop({ default: () => {} }) availableFilters!: { [key: string]: string };
  @Prop({ default: null }) filter!: string | null;

  T = T;
  ui = ui;
  currentFilter = this.filter;

  get SchoolRankingFeatureGuideURL(): string {
    // Use the key defined in blog-links-config.json
    return getBlogUrl('SchoolRankingFeatureGuideURL');
  }

  @Watch('currentFilter')
  onFilterChange(newFilter: string): void {
    // change url parameters with jquery
    // https://samaxes.com/2011/09/change-url-parameters-with-jquery/
    let queryParameters: { [key: string]: string } = {};
    const re = /([^&=]+)=([^&]*)/g;
    const queryString = location.search.substring(1);
    let m: string[] | null = null;
    while ((m = re.exec(queryString))) {
      queryParameters[decodeURIComponent(m[1])] = decodeURIComponent(m[2]);
    }
    if (newFilter !== '') {
      queryParameters['filter'] = newFilter;
      // When a filter is selected, the parameter 'page' must be reset.
      delete queryParameters['page'];
    } else {
      delete queryParameters['filter'];
    }
    window.location.search = ui.buildURLQuery(queryParameters);
  }
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
  font-size: 1.25rem;
}
</style>
