import Vue from 'vue';
import problem_Validator from '../components/problem/Validator.vue';
import { OmegaUp } from '../omegaup.js';

OmegaUp.on('ready', function() {
  const payload = JSON.parse(
    document.getElementById('problem-payload').innerText,
  );
  let problemValidator = new Vue({
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
          initialLanguage: this.initialLanguage,
          validLanguages: this.validLanguages,
          initialValidator: this.initialValidator,
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
      initialLanguage: payload.languages,
      validLanguages: payload.validLanguages,
      initialValidator: payload.validator,
      validatorsTypes: payload.validatorsTypes,
    },
    components: {
      'omegaup-problem-validator': problem_Validator,
    },
  });
});
