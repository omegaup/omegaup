import contest_ScoreboardMerge from '../components/contest/ScoreboardMerge.vue';
import {API, UI, OmegaUp, T} from '../omegaup.js';
import Vue from 'vue';

OmegaUp.on('ready', function() {

  var scoreboardMerge = new Vue({
    el: '#scoreboard-merge',
    render: function(createElement) {
      return createElement('omegaup-contest-scoreboardmerge', {
        props: {
          contests: this.contests,
          showTable: this.showTable,
          isMoreThanZero: this.isMoreThanZero,
          scoreboard: this.scoreboard,
          showPenalty: this.showPenalty,
          aliases: this.aliases,
        },
        on: {
          'get-scoreboard': function(contestAliases) {
            scoreboardMerge.showTable = false;
            omegaup.API.Contest
              .scoreboardMerge({
                contest_aliases: contestAliases.map(encodeURIComponent).join(','),
              })
              .then(function(scoreboard) {
                const ranking = scoreboard.ranking;
                let sc = [], aliases = [];
                let data, place;
                let showPenalty = false;
                // Get values to pass through props
                if (ranking.length > 0) {
                  // Show penalty or not
                  for (var entry in ranking) {
                    if (!ranking.hasOwnProperty(entry)) continue;
                    data = ranking[entry];
                    showPenalty |= !!data.total.penalty;
                  }
                  // Get aliases for indexing in the same order all rows
                  for (var entry in ranking[0].contests) {
                    aliases.push(entry);
                  }

                  // Create the scoreboard object
                  for (var entry in ranking) {
                    if (!ranking.hasOwnProperty(entry)) continue;
                    data = ranking[entry];
                    place = parseInt(entry) + 1;
                    sc.push({
                      "place": place,
                      "username": data.username,
                      "name": data.name,
                      "contests": data.contests,
                      "totalPoints": data.total.points,
                      "totalPenalty": data.total.penalty
                    });
                  }
                } else {
                  sc = null;
                }
                // Update the props values
                scoreboardMerge.aliases = aliases;
                scoreboardMerge.showPenalty = showPenalty ? true : false;
                scoreboardMerge.isMoreThanZero = scoreboard.ranking.length > 0 ? true : false;
                scoreboardMerge.scoreboard = sc;
                scoreboardMerge.showTable = true;
              })
              .fail(omegaup.UI.apiError);
          }
        },
      });
    },
    mounted: function() {
      API.Contest.list()
      .then(function(contests) {
        scoreboardMerge.contests = contests.results;
      })
      .fail(omegaup.UI.apiError);
    },
    data: {
      contests: [],
      showTable: false,
      isMoreThanZero: false,
      scoreboard: [],
      showPenalty: false,
      aliases: [],
    },
    components: {
      'omegaup-contest-scoreboardmerge': contest_ScoreboardMerge,
    },
  });
});