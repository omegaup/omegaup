import common_Help from '../components/common/Help.vue';
import Vue from 'vue';
import { types } from '../api_types';

document.addEventListener('DOMContentLoaded', () => {
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
});
