import problem_creator from '../../components/problem/creator/Creator.vue';
import { OmegaUp } from '../../omegaup';
import Vue from 'vue';
import { BootstrapVue, BootstrapVueIcons } from 'bootstrap-vue';
import store from './store';
import T from '../../lang';
import * as ui from '../../ui';
import Sortable from 'sortablejs';

import 'bootstrap/dist/css/bootstrap.css';
import 'bootstrap-vue/dist/bootstrap-vue.css';

Vue.directive('Sortable', {
  inserted: (el: HTMLElement, binding) => {
    new Sortable(el, binding.value || {});
  },
});

OmegaUp.on('ready', () => {
  Vue.use(BootstrapVue);
  Vue.use(BootstrapVueIcons);

  new Vue({
    el: '#main-container',
    store,
    components: {
      'creator-main': problem_creator,
    },
    render: function (createElement) {
      return createElement('creator-main', {
        on: {
          'show-update-success-message': () => {
            ui.success(T.problemCreatorUpdateAlert);
          },
        },
      });
    },
  });
});
