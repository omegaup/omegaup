import * as api from '../api';
import * as ui from '../ui';
import Vue from 'vue';
import arena_Virtual from '../components/arena/Virtual.vue';
import { types } from '../api_types';
import { OmegaUp } from '../omegaup';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.ContestVirtualDetailsPayload();

  new Vue({
    el: '#main-container',
    components: { 'omegaup-arena-virtual': arena_Virtual },
    render: function (createElement) {
      return createElement('omegaup-arena-virtual', {
        props: {
          title: payload.contest.title,
          description: payload.contest.description,
          startTime: payload.contest.start_time,
          finishTime: payload.contest.finish_time,
          scoreboard: payload.contest.scoreboard,
          submissionsGap: payload.contest.submissions_gap,
        },
        on: {
          submit: ({
            virtualContestStartTime,
          }: {
            virtualContestStartTime: Date;
          }) => {
            api.Contest.createVirtual({
              alias: payload.contest.alias,
              start_time: virtualContestStartTime.getTime() / 1000,
            })
              .then((response) => {
                window.location.href = `/contest/${response.alias}/edit/`;
              })
              .catch(ui.apiError);
          },
        },
      });
    },
  });
});
