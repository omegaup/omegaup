import Vue from 'vue';
import problem_random_problem from '../components/problem/RandomProblem.vue';
import { types } from '../api_types';
import { OmegaUp } from '../omegaup';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.RandomProblemPayload();
  new Vue({
    el: '#main-container',
    components: {
      'omegaup-random-problem': problem_random_problem,
    },
    render: function (createElement) {
      return createElement('omegaup-random-problem', {
        props: {
          alias: payload.alias,
        },
      });
    },
  });
});
