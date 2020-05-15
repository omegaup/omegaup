import { omegaup, OmegaUp } from '../omegaup';
import { types } from '../api_types';
import T from '../lang';
import Vue from 'vue';
import contest_New from '../components/contest/NewForm.vue';
import * as ui from '../ui';
import * as api from '../api';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.ContestNewPayload();
  const startTime = new Date();
  const finishTime = new Date(startTime.getTime() + 5 * 60 * 60 * 1000);
  const contestNew = new Vue({
    el: '#main-container',
    render: function(createElement) {
      return createElement('omegaup-contest-new', {
        props: {
          allLanguages: payload.languages,
          update: false,
          initialStartTime: startTime.getTime(),
          initialFinishTime: finishTime.getTime(),
        },
        on: {
          'create-contest': (contest: omegaup.Contest): void => {
            api.Contest.create(contest)
              .then(data => {
                window.location.replace(
                  `/contest/${contest.alias}/edit/#problems`,
                );
              })
              .catch(ui.apiError);
          },
        },
      });
    },
    components: {
      'omegaup-contest-new': contest_New,
    },
  });
});
