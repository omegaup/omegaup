import users_Rank from '../components/user/Rank.vue';
import Vue from 'vue';
import { OmegaUp } from '../omegaup';
import { types } from '../api_types';
import * as ui from '../ui';
import * as api from '../api';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.UserRankTablePayload();

  const ranking = payload.ranking.rank.map((user) => ({
    rank: user.ranking,
    country: user.country_id,
    username: user.username,
    name: user.name,
    classname: user.classname,
    score: user.score,
    problems_solved: user.problems_solved,
  }));
  new Vue({
    el: '#main-container',
    components: {
      'omegaup-user-rank': users_Rank,
    },
    data: () => ({
      searchResultUsers: [] as types.ListItem[],
    }),
    render: function (createElement) {
      return createElement('omegaup-user-rank', {
        props: {
          page: payload.page,
          length: payload.length,
          isIndex: payload.isIndex,
          isLogged: payload.isLogged,
          availableFilters: payload.availableFilters,
          filter: payload.filter,
          ranking,
          resultTotal: payload.ranking.total,
          pagerItems: payload.pagerItems,
          searchResultUsers: this.searchResultUsers,
          lastUpdated: payload.lastUpdated,
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
