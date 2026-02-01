import common_Help from '../components/common/Help.vue';
import Vue from 'vue';
import { types } from '../api_types';

// Initialize immediately without waiting for OmegaUp.ready
// since other modules might have errors that prevent 'ready' from firing
document.addEventListener('DOMContentLoaded', () => {
  try {
    const { helpResources } = types.payloadParsers.UserHelpPayload();
    new Vue({
      el: '#main-container',
      components: {
        'omegaup-common-help': common_Help,
      },
      render: function (createElement) {
        return createElement('omegaup-common-help', {
          props: {
            helpResources,
          },
        });
      },
    });
  } catch (error) {
    console.error('Error initializing help page:', error);
  }
});
