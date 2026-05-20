import schools_Rank from '../components/schools/Rank.vue';
import { OmegaUp } from '../omegaup';
import { types } from '../api_types';
import * as api from '../api';
import * as ui from '../ui';
import Vue from 'vue';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.SchoolRankPayload();
  let currentRequestId = 0;

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
          'update-search-result-schools': (() => {
            let timeoutId: number | null = null;
            return (query: string) => {
              if (timeoutId) clearTimeout(timeoutId);

              timeoutId = window.setTimeout(() => {
                const trimmedQuery = query.trim();
                if (!trimmedQuery) {
                  this.searchResultSchools = [];
                  return;
                }
                const requestId = ++currentRequestId;
                api.School.list({ query: trimmedQuery })
                  .then(({ results }) => {
                    if (requestId === currentRequestId) {
                      this.searchResultSchools = results;
                    }
                  })
                  .catch(ui.apiError);
              }, 300);
            };
          })(),
        },
      });
    },
  });
});
