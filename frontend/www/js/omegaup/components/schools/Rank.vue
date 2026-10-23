<template>
  <div class="card" data-schools-rank>
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
      <omegaup-common-typeahead
        class="col col-md-4 pl-0 pr-2"
        :existing-options="searchResultSchools"
        :value.sync="searchedSchool"
        :max-results="10"
        @update-existing-options="
          (query) => $emit('update-search-result-schools', query)
        "
      ></omegaup-common-typeahead>
      <button
        class="btn btn-primary form-control col-4 col-md-2"
        type="button"
        @click="onSubmit"
      >
        {{ T.searchSchool }}
      </button>
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
      <a href="/rank/schools/">{{ T.rankSeeGeneralRanking }}</a>
    </div>
    <div v-else class="card-footer">
      <omegaup-common-paginator
        :pager-items="pagerItems"
      ></omegaup-common-paginator>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import type { PropType } from 'vue';

import { omegaup } from '../../omegaup';
import { types } from '../../api_types';
import T from '../../lang';
import * as ui from '../../ui';
import CountryFlag from '../CountryFlag.vue';
import common_Paginator from '../common/Paginator.vue';
import common_Typeahead from '../common/Typeahead.vue';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { getBlogUrl } from '../../urlHelper';

export default defineComponent({
  name: 'SchoolRank',
  components: {
    FontAwesomeIcon,
    'omegaup-countryflag': CountryFlag,
    'omegaup-common-paginator': common_Paginator,
    'omegaup-common-typeahead': common_Typeahead,
  },
  props: {
    page: {
      type: Number,
      required: true,
    },
    length: {
      type: Number,
      required: true,
    },
    showHeader: {
      type: Boolean,
      required: true,
    },
    totalRows: {
      type: Number,
      required: true,
    },
    rank: {
      type: Array as PropType<omegaup.SchoolsRank[]>,
      required: true,
    },
    pagerItems: {
      type: Array as PropType<types.PageItem[]>,
      required: true,
    },
    searchResultSchools: {
      type: Array as PropType<types.SchoolListItem[]>,
      default: () => [],
    },
  },
  data() {
    return {
      T,
      ui,
      searchedSchool: null as null | types.SchoolListItem,
    };
  },
  computed: {
    SchoolRankingFeatureGuideURL(): string {
      // Use the key defined in blog-links-config.json
      return getBlogUrl('SchoolRankingFeatureGuideURL');
    },
  },
  methods: {
    onSubmit(): void {
      if (!this.searchedSchool) return;
      window.location.href = `/schools/profile/${encodeURIComponent(
        this.searchedSchool.key,
      )}/`;
    },
  },
});
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

[data-schools-rank] {
  max-width: 52rem;
  margin: 0 auto;
}

[data-schools-rank] .tags-input-wrapper-default {
  padding: 0.35rem 0.25rem 0.7rem 0.25rem;
}
</style>
