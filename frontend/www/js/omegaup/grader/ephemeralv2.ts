import { OmegaUp } from '../omegaup';
import { types } from '../api_types';
import Vue from 'vue';
import grader_Ephemeral from '../components/grader/Ephemeral.vue';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.EphemeralDetailsPayload();
  new Vue({
    el: '#main-container',
    components: {
      'omegaup-grader-ephemeral': grader_Ephemeral,
    },
    data: () => ({
      theme: payload.theme,
    }),
    render: function (createElement) {
      return createElement('omegaup-grader-ephemeral', {
        props: {
          theme: this.theme,
        },
        on: {
          // TODO: Add all the actions needed
        },
      });
    },
  });
});
