import contest_Report from '../components/contest/Reportv2.vue';
import { OmegaUp } from '../omegaup';
import Vue from 'vue';
import { types } from '../api_types';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.ContestReportDetailsPayload();

  new Vue({
    el: '#main-container',
    components: {
      'omegaup-contest-report': contest_Report,
    },
    render: function (createElement) {
      return createElement('omegaup-contest-report', {
        props: {
          contestReport: payload.contestReport,
          contestAlias: payload.contestAlias,
        },
      });
    },
  });
});
