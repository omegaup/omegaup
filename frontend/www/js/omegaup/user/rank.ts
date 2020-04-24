import users_Rank from '../components/user/Rank.vue';
import Vue from 'vue';
import { OmegaUp } from '../omegaup';
import { types } from '../api_types';
import * as api from '../api_transitional';
import * as ui from '../ui';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.UserRankTablePayload('payload');
  console.log(payload);

  // const ranking = [];
  // for (const user of result.rank) {
  //   let problemsSolvedUser = undefined;
  //   if (payload.isIndex !== true) {
  //     problemsSolvedUser = user.problems_solved;
  //   }
  //   ranking.push({
  //     rank: user.ranking,
  //     country: user.country_id,
  //     username: user.username,
  //     classname: user.classname,
  //     name: user.name,
  //     score: user.score,
  //     problemsSolvedUser: problemsSolvedUser,
  //   });
  // }

  const rankTable = new Vue({
    el: '#main-container',
    render: function(createElement) {
      return createElement('omegaup-user-rank', {
        props: {
          page: this.page,
          length: this.length,
          isIndex: this.isIndex,
          isLogged: this.isLogged,
          availableFilters: this.availableFilters,
          filter: this.filter,
          ranking: this.ranking,
          resultTotal: this.resultTotal,
        },
      });
    },
    data: {
      page: payload.page,
      length: payload.length,
      isIndex: payload.isIndex,
      isLogged: payload.isLogged,
      availableFilters: payload.availableFilters,
      filter: payload.filter,
      ranking: [],
      resultTotal: 0,
    },
    components: {
      'omegaup-user-rank': users_Rank,
    },
  });
});
