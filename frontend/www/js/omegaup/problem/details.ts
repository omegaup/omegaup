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
          allRuns: payload.allRuns,
          initialClarifications: payload.clarifications,
          problem: payload.problem,
          runs: payload.runs,
          solvers: payload.solvers,
          user: payload.user,
          nominationStatus: payload.nominationStatus,
          solutionStatus: payload.solutionStatus,
          histogram: payload.histogram,
        },
      });
    },
    components: {
      'omegaup-problem-details': problem_Details,
    },
  });
});
