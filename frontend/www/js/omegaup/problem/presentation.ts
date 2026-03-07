import { OmegaUp } from '../omegaup';
import { types } from '../api_types';
import Vue from 'vue';
import omegaup_ProblemPresentation from '../components/problem/PresentationMode.vue';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.ProblemPrintDetailsPayload();

  new Vue({
    el: '#main-container',
    components: {
      'omegaup-problem-presentation': omegaup_ProblemPresentation,
    },
    render: function (createElement) {
      return createElement('omegaup-problem-presentation', {
        props: {
          problem: payload.details,
        },
      });
    },
  });
});
