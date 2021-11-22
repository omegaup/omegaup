import Vue from 'vue';

import { OmegaUp } from '../omegaup';
import { types } from '../api_types';
import * as api from '../api';
import * as ui from '../ui';
import T from '../lang';

import user_Profile from '../components/user/Profile.vue';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.UserProfileDetailsPayload();
  const locationHash = window.location.hash.substr(1).split('#');
  let selectedTab = locationHash[0] !== '' ? locationHash[0] : 'view-profile';
  if (locationHash[0] === 'locale-changed') {
    selectedTab = 'edit-preferences';
    history.replaceState({}, 'updateTab', `#${selectedTab}`);
    ui.success(T.userEditPreferencesSuccess);
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
        hasPassword: payload.extraProfileDetails?.hasPassword,
        selectedTab,
      };
    },
    render: function (createElement) {
      return createElement('omegaup-user-profile', {
        props: {
          data: payload.extraProfileDetails,
          profile: this.profile,
          profileBadges: new Set(
            payload.extraProfileDetails?.ownedBadges?.map(
              (badge) => badge.badge_alias,
            ),
          ),
          visitorBadges: new Set(payload.extraProfileDetails?.badges),
          selectedTab: this.selectedTab,
          identities: this.identities,
          countries: payload.countries,
          programmingLanguages: payload.programmingLanguages,
          hasPassword: this.hasPassword,
        },
        on: {
          'update-user-basic-information': (
            userBasicInformation: Partial<types.UserProfileInfo>,
          ) => {
            api.User.update(userBasicInformation)
              .then(() => {
                ui.success(T.userEditSuccess);
              })
              .catch(ui.apiError);
          },
          'update-user-basic-information-error': ({
            description,
          }: {
            description: string;
          }) => {
            ui.error(description);
          },
          'update-user-preferences': ({
            userPreferences,
            localeChanged,
          }: {
            userPreferences: Partial<types.UserProfileInfo>;
            localeChanged: boolean;
          }) => {
            const profile = {
              ...userPreferences,
              ...{ username: this.profile.username },
            };
            api.User.update(profile)
              .then(() => {
                if (localeChanged) {
                  window.location.hash = 'locale-changed';
                  window.location.reload();
                  return;
                }
                ui.success(T.userEditPreferencesSuccess);
              })
              .catch(ui.apiError);
          },
          'update-user-schools': (
            schoolInformation: Partial<types.UserProfileInfo>,
          ) => {
            api.User.update(schoolInformation)
              .then(() => {
                ui.success(T.userEditSchoolSuccess);
              })
              .catch(ui.apiError);
          },
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
          'update-password': ({
            oldPassword,
            newPassword,
          }: {
            oldPassword: string;
            newPassword: string;
          }) => {
            api.User.changePassword({
              old_password: oldPassword,
              password: newPassword,
            })
              .then(() => {
                ui.success(T.passwordResetResetSuccess);
              })
              .catch(ui.apiError);
          },
          'add-password': ({
            username,
            password,
          }: {
            username: string;
            password: string;
          }) => {
            api.User.updateBasicInfo({
              username,
              password,
            })
              .then(() => {
                ui.success(T.passwordAddRequestSuccess);
                userProfile.hasPassword = true;
                userProfile.selectedTab = 'change-password';
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
