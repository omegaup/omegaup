import Vue from 'vue';
import problem_Settings from '../components/problem/Settings.vue';
import { OmegaUp } from '../omegaup';

OmegaUp.on('ready', function() {
  const payload = JSON.parse(
    document.getElementById('problem-payload').innerText,
  );
  let problemSettings = new Vue({
    el: '#problem-settings',
    render: function(createElement) {
      return createElement('omegaup-problem-settings', {
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
          validatorTypes: this.validatorTypes,
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
      validatorTypes: payload.validatorTypes,
    },
    components: {
      'omegaup-problem-settings': problem_Settings,
    },
  });
});
