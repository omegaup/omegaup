import common_Help from '../components/common/Help.vue';
import { OmegaUp } from '../omegaup';
import Vue from 'vue';

OmegaUp.on('ready', () => {
  try {
    new Vue({
      el: '#main-container',
      components: {
        'omegaup-common-help': common_Help,
      },
      render: function (createElement) {
        return createElement('omegaup-common-help');
      },
    });
  } catch (error) {
    console.error('Error initializing help page:', error);
  }
});
