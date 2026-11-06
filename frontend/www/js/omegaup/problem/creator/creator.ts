import problem_creator from '../../components/problem/creator/Creator.vue';
import { OmegaUp } from '../../omegaup';
import Vue from 'vue';
import store from './store';
import {
  downloadInputFile,
  downloadZipFile,
  showUpdateSuccessMessage,
} from './downloadHandlers';

OmegaUp.on('ready', () => {
  new Vue({
    el: '#main-container',
    store,
    components: {
      'creator-main': problem_creator,
    },
    render: function (createElement) {
      return createElement('creator-main', {
        on: {
          'show-update-success-message': showUpdateSuccessMessage,
          'download-input-file': downloadInputFile,
          'download-zip-file': downloadZipFile,
        },
      });
    },
  });
});
