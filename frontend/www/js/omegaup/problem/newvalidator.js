import Vue from 'vue';
import problem_New_Validator from '../components/problem/NewValidator.vue';
import { OmegaUp, T, API } from '../omegaup.js';

OmegaUp.on('ready', function() {
  const payload = JSON.parse(document.getElementById('payload').innerText);
  let problemsNewValidator = new Vue({
    el: '#problem-new-validator',
    render: function(createElement) {
      return createElement('omegaup-problem-new-validator', {
        props: {
          IS_UPDATE: this.IS_UPDATE,
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
      IS_UPDATE: payload.IS_UPDATE,
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
      '#validator_time_limit',
    ).val();
    problemsNewValidator.TIME_LIMIT = $('#time_limit').val();
    problemsNewValidator.OVERALL_WALL_TIME_LIMIT = $(
      '#overall_wall_time_limit',
    ).val();
    problemsNewValidator.EXTRA_WALL_TIME = $('#extra_wall_time').val();
    problemsNewValidator.MEMORY_LIMIT = $('#memory_limit').val();
    problemsNewValidator.OUTPUT_LIMIT = $('#output_limit').val();
    problemsNewValidator.INPUT_LIMIT = $('#input_limit').val();
  });
});
