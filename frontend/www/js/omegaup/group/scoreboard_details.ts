import group_ScoreboardDetails from '../components/group/ScoreboardDetails.vue';
import { OmegaUp } from '../omegaup';
import { types } from '../api_types';
import Vue from 'vue';

OmegaUp.on('ready', function () {
  const payload = types.payloadParsers.GroupScoreboardDetailsPayload();
  new Vue({
    el: '#main-container',
    components: {
      'omegaup-group-scoreboard-details': group_ScoreboardDetails,
    },
    render: function (createElement) {
      return createElement('omegaup-group-scoreboard-details', {
        props: {
          groupAlias: payload.groupAlias,
          scoreboardAlias: payload.scoreboardAlias,
          ranking: payload.details.ranking,
          scoreboard: payload.details.scoreboard,
          contests: payload.details.contests,
        },
      });
    },
  });
});
