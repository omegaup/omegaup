import Vue from 'vue';
import problem_New_Validator from '../components/problem/NewValidator.vue';
import { OmegaUp } from '../omegaup.js';

OmegaUp.on('ready', function() {
  const payload = JSON.parse(document.getElementById('payload').innerText);
  let problemsNewValidator = new Vue({
    el: '#problem-new-validator',
    render: function(createElement) {
      return createElement('omegaup-problem-new-validator', {
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
      TIME_LIMIT: payload.TIME_LIMIT,
      EXTRA_WALL_TIME: payload.EXTRA_WALL_TIME,
      MEMORY_LIMIT: payload.MEMORY_LIMIT,
      OUTPUT_LIMIT: payload.OUTPUT_LIMIT,
      INPUT_LIMIT: payload.INPUT_LIMIT,
      OVERALL_WALL_TIME_LIMIT: payload.OVERALL_WALL_TIME_LIMIT,
      EXTRA_WALL_TIME: payload.EXTRA_WALL_TIME,
      VALIDATOR_TIME_LIMIT: payload.VALIDATOR_TIME_LIMIT,
      LANGUAGES: document.getElementById('languages').value,
    },
    components: {
      'omegaup-problem-new-validator': problem_New_Validator,
    },
  });
  $('#languages').on('change', function() {    
    problemsNewValidator.LANGUAGES = $(this).val();
    problemsNewValidator.VALIDATOR_TIME_LIMIT = $(
      'input[name=validator_time_limit]',
    ).val();
    problemsNewValidator.TIME_LIMIT = $('input[name=time_limit]').val();
    problemsNewValidator.OVERALL_WALL_TIME_LIMIT = $(
      'input[name=overall_wall_time_limit]',
    ).val();
    problemsNewValidator.EXTRA_WALL_TIME = $('input[name=extra_wall_time]').val();
    problemsNewValidator.MEMORY_LIMIT = $('input[name=memory_limit]').val();
    problemsNewValidator.OUTPUT_LIMIT = $('input[name=output_limit]').val();
    problemsNewValidator.INPUT_LIMIT = $('input[name=input_limit]').val();
  });
});
