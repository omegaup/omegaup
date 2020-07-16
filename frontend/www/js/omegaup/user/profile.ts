import Vue from 'vue';

import { OmegaUp } from '../omegaup';
import { types } from '../api_types';

import user_Profile from '../components/user/Profilev2.vue';

OmegaUp.on('ready', function () {
  const payload = types.payloadParsers.UserProfileDetailsPayload();
  const viewProfile = new Vue({
    el: '#main-container',
    render: function (createElement) {
      return createElement('omegaup-user-profile', {
        props: {
          data: payload,
          profileBadges: new Set(
            payload.ownedBadges?.map((badge) => badge.badge_alias),
          ),
          visitorBadges: new Set(payload.badges),
        },
      });
    },
    components: {
      'omegaup-user-profile': user_Profile,
    },
  });
});
