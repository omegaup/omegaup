import Vue from 'vue';

import { OmegaUp } from '../omegaup';
import { types } from '../api_types';

// TODO: Import Profile.vue when it is merged
import user_Profile from '../components/user/Profilev2.vue';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.UserProfileDetailsPayload();
  const locationHash = window.location.hash.substr(1).split('/');
  const activeTab = getSelectedValidTab(locationHash[0], payload.urlMapping);

  if (activeTab !== locationHash[0]) {
    window.location.hash = activeTab;
  }

  function getSelectedValidTab(tab: string, urls: types.UrlProfile[]): string {
    const validTabs = urls.filter((url) => url.visible).map((url) => url.key);
    return validTabs.includes(tab) ? tab : 'see-profile';
  }

  new Vue({
    el: '#main-container',
    components: {
      'omegaup-user-profile': user_Profile,
    },
    data: () => {
      return {
        profile: payload.profile,
        data: payload.extraProfileDetails,
      };
    },
    render: function (createElement) {
      return createElement('omegaup-user-profile', {
        props: {
          data: payload.extraProfileDetails,
          profile: payload.profile,
          profileBadges: new Set(
            payload.extraProfileDetails?.ownedBadges?.map(
              (badge) => badge.badge_alias,
            ),
          ),
          visitorBadges: new Set(payload.extraProfileDetails?.badges),
          tabSelected: activeTab,
        },
      });
    },
  });
});
