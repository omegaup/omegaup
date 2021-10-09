import Vue from 'vue';
import { OmegaUp } from '../../omegaup';
import { BootstrapVue, IconsPlugin } from 'bootstrap-vue';
import problem_creator from '../../components/problem/creator/Creator.vue';
import store from './store';

OmegaUp.on('ready', () => {
  Vue.use(BootstrapVue);
  Vue.use(IconsPlugin);

  new Vue({
    store: store,
    el: '#main-container',
    components: {
      'creator-main': problem_creator,
    },
    render: function (createElement) {
      return createElement('creator-main');
    },
  });
});
