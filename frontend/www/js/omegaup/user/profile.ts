import Vue from 'vue';

import { OmegaUp } from '../omegaup';
import { types } from '../api_types';
import * as api from '../api';
import * as ui from '../ui';
import T from '../lang';

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

  const userProfile = new Vue({
    el: '#main-container',
    components: {
      'omegaup-user-profile': user_Profile,
    },
    data: () => {
      return {
        profile: payload.profile,
        data: payload.extraProfileDetails,
        identities: payload.identities,
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
          urlMapping: payload.urlMapping,
          identities: this.identities,
        },
        on: {
          'add-identity': ({
            username,
            password,
          }: {
            username: string;
            password: string;
          }) => {
            api.User.associateIdentity({
              username: username,
              password: password,
            })
              .then(() => {
                refreshIdentityList();
                ui.success(T.profileIdentityAdded);
              })
              .catch(ui.apiError);
          },
        },
      });
    },
  });

  function refreshIdentityList() {
    api.User.listAssociatedIdentities({})
      .then(function (data) {
        userProfile.identities = data.identities;
      })
      .catch(ui.apiError);
  }
});
