import common_Help from '../components/common/Help.vue';
import Vue from 'vue';
import { types } from '../api_types';

// Initialize immediately when module loads
// The common_help.js is loaded as a deferred script, so it will load after DOM is ready
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
