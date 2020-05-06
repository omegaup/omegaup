import { omegaup, OmegaUp } from '../omegaup';
import { types } from '../api_types';
import T from '../lang';
import Vue from 'vue';
import contest_New from '../components/contest/NewForm.vue';
import * as ui from '../ui';
import * as api from '../api';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.ContestNewPayload('contest-new-payload');
  const startTime = new Date();
  const finishTime = new Date(startTime);
  finishTime.setTime(finishTime.getTime() + 5 * 60 * 60 * 1000);
  const contestNew = new Vue({
    el: '#contest-new',
    render: function(createElement) {
      return createElement('omegaup-contest-new', {
        props: {
          data: null,
          allLanguages: payload.languages,
          update: false,
          initialStartTime: startTime,
          initialFinishTime: finishTime,
        },
        on: {
          'create-contest': (ev: omegaup.Contest): void => {
            api.Contest.create(ev)
              .then(data => {
                window.location.replace(`/contest/${ev.alias}/edit/#problems`);
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
