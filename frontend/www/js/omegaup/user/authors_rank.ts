import Vue from 'vue';
import * as api from '../api';
import { types } from '../api_types';
import authors_Rank from '../components/user/AuthorsRank.vue';
import { OmegaUp } from '../omegaup';
import * as ui from '../ui';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.AuthorRankTablePayload();
  new Vue({
    el: '#main-container',
    components: {
      'omegaup-author-rank': authors_Rank,
    },
    data: () => ({
      searchResultUsers: [] as types.ListItem[],
    }),
    render: function (createElement) {
      return createElement('omegaup-author-rank', {
        props: {
          page: payload.page,
          length: payload.length,
          rankingData: payload.ranking,
          pagerItems: payload.pagerItems,
          searchResultUsers: this.searchResultUsers,
        },
        on: {
          'update-search-result-users': (query: string) => {
            api.User.list({ query })
              .then(({ results }) => {
                this.searchResultUsers = results.map(
                  ({ key, value }: types.ListItem) => ({
                    key,
                    value: `${ui.escape(key)} (<strong>${ui.escape(
                      value,
                    )}</strong>)`,
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
