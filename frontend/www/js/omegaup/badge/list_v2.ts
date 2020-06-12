import { OmegaUp } from '../omegaup';
import { types } from '../api_types';
import Vue from 'vue';

import badge_List from '../components/badge/List.vue';

OmegaUp.on('ready', function() {
  const payload = types.payloadParsers.BadgeListPayload();
  console.log(payload);
  let badgeList = new Vue({
    el: '#main-container',
    render: function(createElement) {
      return createElement('omegaup-badge-list', {
        props: {
          allBadges: <Set<string[]>>new Set([payload.badges]),
          visitorBadges: <Set<types.Badge[]>>new Set([payload.owned_badges]),
          showAllBadgesLink: false,
        },
      });
    },
    components: {
      'omegaup-badge-list': badge_List,
    },
  });
});
