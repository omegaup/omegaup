import problem_creator from '../../components/problem/creator/Creator.vue';
import { OmegaUp } from '../../omegaup';
import Vue from 'vue';
import { BootstrapVue, BootstrapVueIcons } from 'bootstrap-vue';
import store from './store';

import 'bootstrap/dist/css/bootstrap.css';
import 'bootstrap-vue/dist/bootstrap-vue.css';

OmegaUp.on('ready', () => {
  Vue.use(BootstrapVue);
  Vue.use(BootstrapVueIcons);

  const creator = new Vue({
    el: '#main-container',
    store,
    components: {
      'creator-main': problem_creator,
    },
    render: function (createElement) {
      return createElement('creator-main');
    },
  });

  // We need to save the creator object in the global scope, so that it can be accessed from Cypress
  if ((window as any).Cypress) {
    (window as any).creator = creator;
  }
});
