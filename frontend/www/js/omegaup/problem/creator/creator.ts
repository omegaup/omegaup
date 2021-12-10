import problem_creator from '../../components/problem/creator/Creator.vue';
import { OmegaUp } from '../../omegaup';
import Vue from 'vue';
import { BootstrapVue, BootstrapVueIcons } from 'bootstrap-vue';

import 'bootstrap/dist/css/bootstrap.css';
import 'bootstrap-vue/dist/bootstrap-vue.css';

OmegaUp.on('ready', () => {
  Vue.use(BootstrapVue);
  Vue.use(BootstrapVueIcons);

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
