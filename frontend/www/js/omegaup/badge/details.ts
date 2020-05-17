import Vue from 'vue';
import badge_Details from '../components/badge/Details.vue';
import { OmegaUp } from '../omegaup';
import { types } from '../api_types';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.BadgeDetailsPayload();
  const badgeDetails = new Vue({
    el: '#main-container',
    render: function(createElement) {
      return createElement('omegaup-badge-details', {
        props: {
          badge: payload.badge,
        },
      });
    },
    components: {
      'omegaup-badge-details': badge_Details,
    },
  });
});
