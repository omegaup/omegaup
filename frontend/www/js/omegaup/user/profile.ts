import Vue from 'vue';

import { OmegaUp } from '../omegaup';
import { types } from '../api_types';
import T from '../lang';

// TODO: Import Profile.vue when it is merged
import user_Profile from '../components/user/Profilev2.vue';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.UserProfileDetailsPayload();
  const locationHash = window.location.hash.substr(1).split('/');
  const activeTab = getSelectedValidTab(locationHash[0]);

  if (activeTab !== locationHash[0]) {
    window.location.hash = activeTab;
  }

  function getSelectedValidTab(tab: string): string {
    const urlMapping: { key: string; title: string; visible: boolean }[] = [
      { key: 'see-profile', title: T.userEditSeeProfile, visible: true },
      { key: 'edit-basic-information', title: T.profileEdit, visible: true },
      { key: 'edit-preferences', title: T.userEditPreferences, visible: true },
      { key: 'manage-schools', title: T.userEditManageSchools, visible: true },
      {
        key: 'manage-identities',
        title: T.profileManageIdentities,
        visible: true,
      },
      {
        key: 'change-password',
        title: T.userEditChangePassword,
        visible: true,
      },
      { key: 'add-password', title: T.userEditAddPassword, visible: false },
      { key: 'change-email', title: T.userEditChangeEmail, visible: false },
    ];
    const validTabs = urlMapping.map((url: any) => url.key);
    const defaultTab = 'see-information';
    const isValidTab = validTabs.includes(tab);
    return isValidTab ? tab : defaultTab;
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
