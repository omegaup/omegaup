import Vue from 'vue';

import { OmegaUp } from '../omegaup';
import { types } from '../api_types';
import * as api from '../api';
import * as ui from '../ui';
import T from '../lang';

import user_Profile from '../components/user/Profilev2.vue';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.UserProfileDetailsPayload();
  new Vue({
    el: '#main-container',
    components: {
      'omegaup-user-profile': user_Profile,
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
        },
        on: {
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
              })
              .catch(ui.apiError);
          },
        },
      });
    },
  });
});
