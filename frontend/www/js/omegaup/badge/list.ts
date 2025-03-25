import { OmegaUp } from '../omegaup';
import { types } from '../api_types';
import Vue from 'vue';

import badge_List from '../components/badge/List.vue';

OmegaUp.on('ready', function () {
  const payload = types.payloadParsers.BadgeListPayload();
  new Vue({
    el: '#main-container',
    components: {
      'omegaup-badge-list': badge_List,
    },
    render: function (createElement) {
      return createElement('omegaup-badge-list', {
        props: {
          allBadges: new Set(payload.badges) as Set<string>,
          visitorBadges: new Set(
            payload.ownedBadges.map((badge) => badge.badge_alias),
          ) as Set<string>,
          showAllBadgesLink: false,
        },
      });
    },
  });
});
