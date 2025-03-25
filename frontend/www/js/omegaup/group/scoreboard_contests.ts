import group_ScoreboardContests from '../components/group/ScoreboardContests.vue';
import { OmegaUp } from '../omegaup';
import { types } from '../api_types';
import * as api from '../api';
import * as ui from '../ui';
import T from '../lang';
import Vue from 'vue';

OmegaUp.on('ready', function () {
  const payload = types.payloadParsers.GroupScoreboardContestsPayload();
  const scoreboardContest = new Vue({
    el: '#main-container',
    components: {
      'omegaup-group-scoreboard-contests': group_ScoreboardContests,
    },
    data: () => ({
      contests: payload.contests,
    }),
    render: function (createElement) {
      return createElement('omegaup-group-scoreboard-contests', {
        props: {
          contests: this.contests,
          availableContests: payload.availableContests,
          scoreboard: payload.scoreboardAlias,
        },
        on: {
          'add-contest': (
            source: group_ScoreboardContests,
            selectedContest: string,
            onlyAc: boolean,
            weight: number,
          ): void => {
            api.GroupScoreboard.addContest({
              group_alias: payload.groupAlias,
              scoreboard_alias: payload.scoreboardAlias,
              contest_alias: selectedContest,
              only_ac: onlyAc,
              weight: weight,
            })
              .then(() => {
                ui.success(T.groupEditScoreboardsContestsAdded);
                refreshScoreboardContests(
                  payload.groupAlias,
                  payload.scoreboardAlias,
                );
                source.reset();
              })
              .catch(ui.apiError);
          },
          'remove-contest': (contestAlias: string): void => {
            api.GroupScoreboard.removeContest({
              group_alias: payload.groupAlias,
              scoreboard_alias: payload.scoreboardAlias,
              contest_alias: contestAlias,
            })
              .then(() => {
                ui.success(T.groupEditScoreboardsContestsRemoved);
                refreshScoreboardContests(
                  payload.groupAlias,
                  payload.scoreboardAlias,
                );
              })
              .catch(ui.apiError);
          },
        },
      });
    },
  });

  function refreshScoreboardContests(
    groupAlias: string,
    scoreboardAlias: string,
  ) {
    api.GroupScoreboard.details({
      group_alias: groupAlias,
      scoreboard_alias: scoreboardAlias,
    })
      .then((scoreboard) => {
        scoreboardContest.contests = scoreboard.contests;
      })
      .catch(ui.apiError);
  }
});
