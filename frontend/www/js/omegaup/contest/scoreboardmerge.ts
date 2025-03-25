import contest_ScoreboardMerge from '../components/contest/ScoreboardMerge.vue';
import { OmegaUp } from '../omegaup';
import * as ui from '../ui';
import * as api from '../api';
import Vue from 'vue';
import { types } from '../api_types';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.ScoreboardMergePayload();
  const scoreboardMerge = new Vue({
    el: '#main-container',
    components: {
      'omegaup-contest-scoreboardmerge': contest_ScoreboardMerge,
    },
    data: () => ({
      contests: payload.contests,
      scoreboard: [] as types.MergedScoreboardEntry[],
      showPenalty: false,
      aliases: [] as string[],
    }),
    render: function (createElement) {
      return createElement('omegaup-contest-scoreboardmerge', {
        props: {
          availableContests: this.contests,
          scoreboard: this.scoreboard,
          showPenalty: this.showPenalty,
          aliases: this.aliases,
        },
        on: {
          'get-scoreboard': (contestAliases: string[]) => {
            api.Contest.scoreboardMerge({
              contest_aliases: contestAliases.map(encodeURIComponent).join(','),
            })
              .then((response) => {
                const ranking = response.ranking;
                const aliases: string[] = [];
                const scoreboard: types.MergedScoreboardEntry[] = [];
                let showPenalty = false;
                if (ranking.length > 0) {
                  for (const entry of ranking) {
                    showPenalty ||= !!entry.total.penalty;
                  }
                  // Get aliases for indexing in the same order all rows
                  for (const entry in ranking[0].contests) {
                    aliases.push(entry);
                  }
                  // Fill scoreboard object
                  for (const index in ranking) {
                    if (!Object.prototype.hasOwnProperty.call(ranking, index))
                      continue;
                    const place = parseInt(index) + 1;
                    const entry = ranking[index];
                    scoreboard.push({ ...entry, place });
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
  });
});
