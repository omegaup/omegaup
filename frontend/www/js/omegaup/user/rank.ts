import users_Rank from '../components/user/Rank.vue';
import Vue from 'vue';
import { OmegaUp } from '../omegaup';
import { types } from '../api_types';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.UserRankTablePayload();

  const ranking = payload.ranking.rank.map((user, index) => ({
    rank: index + 1,
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
        },
      });
    },
  });
});
