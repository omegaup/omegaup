import { OmegaUp } from '../omegaup';
import { types } from '../api_types';
import Vue from 'vue';
import omegaup_ContestPrint from '../components/contest/Print.vue';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.ContestPrintDetailsPayload();

  new Vue({
    el: '#main-container',
    components: {
      'omegaup-contest-print': omegaup_ContestPrint,
    },
    render: function (createElement) {
      return createElement('omegaup-contest-print', {
        props: {
          problems: payload.problems,
          contestTitle: payload.contestTitle,
        },
        on: {
          'print-page': () => {
            window.print();
          },
        },
      });
    },
  });
});
