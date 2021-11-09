import { OmegaUp } from '../omegaup';
import * as time from '../time';
import { types } from '../api_types';
import Vue from 'vue';
import arena_ContestList, {
  ContestTab,
} from '../components/arena/ContestListv2.vue';

OmegaUp.on('ready', () => {
  time.setSugarLocale();
  const payload = types.payloadParsers.ContestListv2Payload();
  const locationHash = window.location.hash
    ? parseInt(window.location.hash.substr(1))
    : ContestTab.Current;
  new Vue({
    el: '#main-container',
    components: { 'omegaup-arena-contestlist': arena_ContestList },
    data: () => ({
      query: payload.query,
      contests: payload.contests,
      section: locationHash,
    }),
    render: function (createElement) {
      return createElement('omegaup-arena-contestlist', {
        props: {
          contests: this.contests,
          query: this.query,
          section: locationHash,
        },
      });
    },
  });
});
