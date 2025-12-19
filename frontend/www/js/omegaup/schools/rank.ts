import schools_Rank from '../components/schools/Rank.vue';
import { OmegaUp } from '../omegaup';
import { types } from '../api_types';
import * as api from '../api';
import * as ui from '../ui';
import Vue from 'vue';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.SchoolRankPayload();

  new Vue({
    el: '#main-container',
    components: {
      'omegaup-schools-rank': schools_Rank,
    },
    data: () => ({
      searchResultSchools: [] as types.SchoolListItem[],
    }),
    render: function (createElement) {
      return createElement('omegaup-schools-rank', {
        props: {
          page: payload.page,
          length: payload.length,
          showHeader: payload.showHeader,
          rank: payload.rank,
          totalRows: payload.totalRows,
          pagerItems: payload.pagerItems,
          searchResultSchools: this.searchResultSchools,
        },
        on: {
          'update-search-result-schools': (query: string) => {
            api.School.list({ query })
              .then(({ results }) => {
                this.searchResultSchools = results.map(
                  ({ key, value }: types.SchoolListItem) => ({
                    key,
                    value,
                  }),
                );
              })
              .catch(ui.apiError);
          },
        },
      });
    },
  });
});
