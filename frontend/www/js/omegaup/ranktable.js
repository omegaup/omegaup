import rank_table from './components/RankTable.vue';
import Vue from 'vue';
import {OmegaUp} from './omegaup.js';

OmegaUp.on('ready', function() {
  const payload = JSON.parse(document.getElementById('payload').innerText);
  const ranks = [];

  omegaup.API.User.rankByProblemsSolved({
                    offset: payload.page,
                    rowcount: payload.length,
                    filter: payload.filter
                  })
      .then(function(result) {
        for (const user of result.rank) {
          let problemsSolvedUser = undefined;
          if (payload.is_index !== true) {
            problemsSolvedUser = user.problems_solved;
          }
          ranks.add({
            rank: user.rank,
            flag: omegaup.UI.getFlag(user.country_id),
            username: user.username,
            name: (user.name == null || payload.length == 5 ?
                       '&nbsp;' :
                       ('<br/>' + user.name)),
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
                availableFilters: this.availableFilters,
                filter: this.filter,
                ranks: this.ranks,
              }
            });
          },
          data: {
            page: payload.page,
            length: payload.length,
            isIndex: payload.is_index,
            availableFilters: payload.availableFilters,
            filter: payload.filter,
            ranks: ranks,
          },
          components: {
            'rankTable': rank_table,
          },
        });
        if (payload.length * payload.page >= result.total) {
          let tempList = rankTable.$el.querySelectorAll('.next,.delimiter');
          for (const temp of tempList) {
            temp.style.display = 'none';
          }
        }
      })
      .fail(omegaup.UI.apiError);
});
