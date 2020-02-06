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
          timeLimit: this.timeLimit,
          extraWallTime: this.extraWallTime,
          memoryLimit: this.memoryLimit,
          outputLimit: this.outputLimit,
          inputLimit: this.inputLimit,
          overallWallTimeLimit: this.overallWallTimeLimit,
          validatorTimeLimit: this.validatorTimeLimit,
          languages: this.languages,
          validLanguages: this.validLanguages,
          validator: this.validator,
          validatorsTypes: this.validatorsTypes,
        },
      });
    },
    data: {
      timeLimit: payload.timeLimit,
      extraWallTime: payload.extraWallTime,
      memoryLimit: payload.memoryLimit,
      outputLimit: payload.outputLimit,
      inputLimit: payload.inputLimit,
      overallWallTimeLimit: payload.overallWallTimeLimit,
      validatorTimeLimit: payload.validatorTimeLimit,
      languages: payload.languages,
      validLanguages: payload.validLanguages,
      validator: payload.validator,
      validatorsTypes: payload.validatorsTypes,
    },
    components: {
      'omegaup-problem-validator': problem_Validator,
    },
  });
});
