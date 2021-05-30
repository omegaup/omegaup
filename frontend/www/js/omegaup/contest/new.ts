import { omegaup, OmegaUp } from '../omegaup';
import { types } from '../api_types';
import Vue from 'vue';
import contest_NewForm from '../components/contest/NewForm.vue';
import * as ui from '../ui';
import * as api from '../api';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.ContestNewPayload();
  const startTime = new Date();
  const finishTime = new Date(startTime.getTime() + 5 * 60 * 60 * 1000);
  new Vue({
    el: '#main-container',
    components: {
      'omegaup-contest-new': contest_NewForm,
    },
    data: () => ({
      invalidParameterName: null as null | string,
    }),
    render: function (createElement) {
      return createElement('omegaup-contest-new', {
        props: {
          allLanguages: payload.languages,
          initialLanguages: Object.keys(payload.languages),
          update: false,
          initialStartTime: startTime,
          initialFinishTime: finishTime,
          invalidParameterName: this.invalidParameterName,
        },
        on: {
          'create-contest': (contest: omegaup.Contest): void => {
            api.Contest.create(contest)
              .then(() => {
                this.invalidParameterName = null;
                window.location.replace(
                  `/contest/${contest.alias}/edit/#problems`,
                );
              })
              .catch((error) => {
                ui.apiError(error);
                this.invalidParameterName = error.parameter || null;
              });
          },
        },
      });
    },
  });
});
