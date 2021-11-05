import { OmegaUp } from '../omegaup';
import * as time from '../time';
import { types } from '../api_types';
import Vue from 'vue';
import arena_ContestList from '../components/arena/ContestListv2.vue';

OmegaUp.on('ready', () => {
  time.setSugarLocale();
  const payload = types.payloadParsers.ContestListv2Payload();
  new Vue({
    el: '#main-container',
    components: { 'omegaup-arena-contestlist': arena_ContestList },
    data: () => ({
      initialQuery: payload.query,
      contests: payload.contests,
    }),
    render: function (createElement) {
      return createElement('omegaup-arena-contestlist', {
        props: {
          contests: this.contests,
          initialQuery: this.initialQuery,
        },
      });
    },
  });
});
