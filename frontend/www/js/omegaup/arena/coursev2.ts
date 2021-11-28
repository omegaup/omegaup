import { OmegaUp } from '../omegaup';
import Vue from 'vue';
import arena_Course from '../components/arena/Coursev2.vue';

OmegaUp.on('ready', async () => {
  new Vue({
    el: '#main-container',
    components: {
      'omegaup-arena-course': arena_Course,
    },
    render: function (createElement) {
      return createElement('omegaup-arena-course');
    },
  });
});
