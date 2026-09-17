import Vue from 'vue';
import common_ViewUnavailable from '../components/common/ViewUnavailable.vue';
import { types } from '../api_types';
import { OmegaUp } from '../omegaup';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.ViewUnavailablePayload();
  new Vue({
    el: '#main-container',
    render: (createElement) =>
      createElement(common_ViewUnavailable, {
        props: {
          description: payload.description,
        },
      }),
  });
});
