import common_Docs from '../components/common/Docs.vue';
import Vue from 'vue';
import { OmegaUp } from '../omegaup';
import { types } from '../api_types';

OmegaUp.on('ready', () => {
  const { docs } = types.payloadParsers.UserDocsPayload();
  new Vue({
    el: '#main-container',
    components: {
      'omegaup-common-docs': common_Docs,
    },
    render: function (createElement) {
      return createElement('omegaup-common-docs', {
        props: {
          docs,
        },
      });
    },
  });
});
