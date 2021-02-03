import contest_ScoreboardMerge from '../components/contest/ScoreboardMerge.vue';
import { OmegaUp } from '../omegaup-legacy';
import T from '../lang';
import * as ui from '../ui';
import * as api from '../api';
import Vue from 'vue';

OmegaUp.on('ready', function () {
  var scoreboardMerge = new Vue({
    el: '#scoreboard-merge',
    render: function (createElement) {
      return createElement('omegaup-contest-scoreboardmerge', {
        props: {
          availableContests: this.contests,
          scoreboard: this.scoreboard,
          showPenalty: this.showPenalty,
          aliases: this.aliases,
        },
        on: {
          'get-scoreboard': function (contestAliases) {
            api.Contest.scoreboardMerge({
              contest_aliases: contestAliases.map(encodeURIComponent).join(','),
            })
              .then(function (ranks) {
                const ranking = ranks.ranking;
                let scoreboard = [],
                  aliases = [],
                  showPenalty = 0;
                if (ranking.length > 0) {
                  for (const entry of ranking) {
                    showPenalty |= !!entry.total.penalty;
                  }
                  // Get aliases for indexing in the same order all rows
                  for (var entry in ranking[0].contests) {
                    aliases.push(entry);
                  }
                  // Fill scoreboard object
                  for (const index in ranking) {
                    if (!Object.prototype.hasOwnProperty.call(ranking, index))
                      continue;
                    const place = parseInt(index) + 1;
                    const entry = ranking[index];
                    scoreboard.push({
                      place: place,
                      username: entry.username,
                      name: entry.name,
                      contests: entry.contests,
                      totalPoints: entry.total.points,
                      totalPenalty: entry.total.penalty,
                    });
                  }
                }
                // Update the props values
                scoreboardMerge.aliases = aliases;
                scoreboardMerge.showPenalty = showPenalty;
                scoreboardMerge.scoreboard = scoreboard;
              })
              .catch(ui.apiError);
          },
        },
      });
    },
    mounted: function () {
      api.Contest.list()
        .then(function (contests) {
          scoreboardMerge.contests = contests.results;
        })
        .catch(ui.apiError);
    },
    data: {
      contests: [],
      scoreboard: [],
      showPenalty: 0,
      aliases: [],
    },
    components: {
      'omegaup-contest-scoreboardmerge': contest_ScoreboardMerge,
    },
  });
});
