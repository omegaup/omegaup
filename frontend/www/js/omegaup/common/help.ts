import common_Help from '../components/common/Help.vue';
import Vue from 'vue';
import { OmegaUp } from '../omegaup';
import { types } from '../api_types';

OmegaUp.on('ready', () => {
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
