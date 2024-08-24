import problem_creator from '../../components/problem/creator/Creator.vue';
import { OmegaUp } from '../../omegaup';
import Vue from 'vue';
import { BootstrapVue, BootstrapVueIcons } from 'bootstrap-vue';
import store from './store';
import T from '../../lang';
import * as ui from '../../ui';

import 'bootstrap/dist/css/bootstrap.css';
import 'bootstrap-vue/dist/bootstrap-vue.css';

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
          'download-input-file': ({
            fileName: fileName,
            fileContent: fileContent,
          }: {
            fileName: string;
            fileContent: string;
          }) => {
            const link = document.createElement('a');
            const blob = new Blob([fileContent], { type: 'text/plain' });
            link.href = URL.createObjectURL(blob);
            link.download = fileName;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
          },
        },
      });
    },
  });
});
