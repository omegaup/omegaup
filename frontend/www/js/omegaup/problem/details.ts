import Vue from 'vue';
import problem_Details from '../components/problem/Details.vue';
import { OmegaUp } from '../omegaup';
import { types } from '../api_types';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.ProblemDetailsv2Payload();
  const problemDetails = new Vue({
    el: '#main-container',
    render: function (createElement) {
      return createElement('omegaup-problem-details', {
        props: {
          problem: payload.problem,
          user: payload.user,
          nominationStatus: payload.nominationStatus,
        },
      });
    },
    components: {
      'omegaup-problem-details': problem_Details,
    },
  });
});
