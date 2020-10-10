import Vue from 'vue';
import problem_random_problem from '../components/problem/RandomProblem.vue';
import { types } from '../api_types';
import { OmegaUp } from '../omegaup';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.RandomProblemPayload();
  const randomProblem = new Vue({
    el: '#main-container',
    render: function (createElement) {
      return createElement('omegaup-problem-random-problem', {
        props: {
          alias: payload.alias,
        },
      });
    },
    components: {
      'omegaup-problem-collection': problem_random_problem,
    },
  });
});
