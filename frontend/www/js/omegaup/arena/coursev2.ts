import { OmegaUp } from '../omegaup';
import { types } from '../api_types';
import Vue from 'vue';
import arena_Course from '../components/arena/Coursev2.vue';

OmegaUp.on('ready', async () => {
  const payload = types.payloadParsers.ArenaCoursePayload();
  console.log(payload);

  new Vue({
    el: '#main-container',
    components: {
      'omegaup-arena-course': arena_Course,
    },
    render: function (createElement) {
      return createElement('omegaup-arena-course', {
        props: {
          course: payload.course,
          assignment: payload.assignment,
          problems: payload.problems,
          currentProblem: payload.currentProblem,
        },
      });
    },
  });
});
