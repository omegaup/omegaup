import Vue from 'vue';
import problem_Validator from '../components/problem/Validator.vue';
import { OmegaUp } from '../omegaup.js';

OmegaUp.on('ready', function() {
  const payload = JSON.parse(
    document.getElementById('problem-payload').innerText,
  );
  let problemsValidator = new Vue({
    el: '#problem-validator',
    render: function(createElement) {
      return createElement('omegaup-problem-validator', {
        props: {
          TIME_LIMIT: this.TIME_LIMIT,
          EXTRA_WALL_TIME: this.EXTRA_WALL_TIME,
          MEMORY_LIMIT: this.MEMORY_LIMIT,
          OUTPUT_LIMIT: this.OUTPUT_LIMIT,
          INPUT_LIMIT: this.INPUT_LIMIT,
          OVERALL_WALL_TIME_LIMIT: this.OVERALL_WALL_TIME_LIMIT,
          EXTRA_WALL_TIME: this.EXTRA_WALL_TIME,
          VALIDATOR_TIME_LIMIT: this.VALIDATOR_TIME_LIMIT,
          LANGUAGES: this.LANGUAGES,
        },
      });
    },
    data: {
      TIME_LIMIT: payload.timeLimit,
      MEMORY_LIMIT: payload.memoryLimit,
      OUTPUT_LIMIT: payload.outputLimit,
      INPUT_LIMIT: payload.inputLimit,
      OVERALL_WALL_TIME_LIMIT: payload.overallWallTimeLimit,
      EXTRA_WALL_TIME: payload.extraWallTime,
      VALIDATOR_TIME_LIMIT: payload.validatorTimeLimit,
      LANGUAGES: document.getElementById('languages').value,
    },
    components: {
      'omegaup-problem-validator': problem_Validator,
    },
  });
});
