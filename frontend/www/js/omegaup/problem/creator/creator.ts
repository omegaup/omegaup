import problem_creator from '../../components/problem/creator/Creator.vue';
import { OmegaUp } from '../../omegaup';
import Vue from 'vue';

OmegaUp.on('ready', () => {
  new Vue({
    el: '#main-container',
    components: {
      'creator-main': problem_creator,
    },
    render: function (createElement) {
      return createElement('creator-main');
    },
  });
});
