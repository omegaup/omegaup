import rank_table from './components/RankTable.vue';
import Vue from 'vue';
import {OmegaUp} from './omegaup.js';

OmegaUp.on('ready', function() {
  const payload =
      JSON.parse(document.getElementById('rank-table-payload').innerText);

  omegaup.API.User.rankByProblemsSolved({
                    offset: payload.page,
                    rowcount: payload.length,
                    filter: payload.filter
                  })
      .then(function(result) {
        const ranking = [];
        for (const user of result.rank) {
          let problemsSolvedUser = undefined;
          if (payload.isIndex !== true) {
            problemsSolvedUser = user.problems_solved;
          }
          ranking.add({
            rank: user.rank,
            country: user.country_id,
            username: user.username,
            name: user.name,
            score: user.score,
            problemsSolvedUser: problemsSolvedUser,
          });
        }

        let rankTable = new Vue({
          el: '#rank-table',
          render: function(createElement) {
            return createElement('rankTable', {
              props: {
                page: this.page,
                length: this.length,
                isIndex: this.isIndex,
                isLogged: this.isLogged,
                availableFilters: this.availableFilters,
                filter: this.filter,
                ranking: this.ranking,
                resultTotal: this.resultTotal,
              }
            });
          },
          data: {
            page: payload.page,
            length: payload.length,
            isIndex: payload.isIndex,
            isLogged: payload.isLogged,
            availableFilters: payload.availableFilters,
            filter: payload.filter,
            ranking: ranking,
            resultTotal: parseInt(result.total),
          },
          components: {
            rankTable: rank_table,
          },
        });
      })
      .fail(omegaup.UI.apiError);
});
