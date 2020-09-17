import Vue from 'vue';
import problem_Collection from '../components/problem/Collections.vue';
import { types } from '../api_types';
import { omegaup, OmegaUp } from '../omegaup';
import T from '../lang';
import * as api from '../api';
import * as ui from '../ui';

OmegaUp.on('ready', () => {
  const problemsList = new Vue({
    el: '#main-container',
    render: function (createElement) {
      return createElement('omegaup-problem-list', {
        props: {},
        on: {},
      });
    },
    components: {
      'omegaup-problem-collections': problem_Collection,
    },
  });
});
