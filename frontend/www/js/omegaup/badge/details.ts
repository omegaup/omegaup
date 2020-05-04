import Vue from 'vue';
import badge_Details from '../components/badge/Details.vue';
import { OmegaUp } from '../omegaup';
import * as api from '../api';
import { types } from '../api_types';
import * as UI from '../ui';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.BadgeDetailsPayload(
    'badge-details-payload',
  );
  const badgeDetails = new Vue({
    el: '#badge-details',
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
