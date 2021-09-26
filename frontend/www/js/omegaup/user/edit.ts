import Vue from 'vue';

import { OmegaUp } from '../omegaup';
import * as api from '../api';
import * as ui from '../ui';
import { types } from '../api_types';
import T from '../lang';

import user_Edit from '../components/user/Edit.vue';

OmegaUp.on('ready', () => {
  const commonPayload = types.payloadParsers.CommonPayload();
  const payload = types.payloadParsers.UserProfileEditDetailsPayload();
  new Vue({
    el: '#main-container',
    components: {
      'omegaup-user-profile-edit': user_Edit,
    },
    render: function (createElement) {
      return createElement('omegaup-user-profile-edit', {
        props: {
          data: payload,
          profile: payload.profile,
          identities: commonPayload.associatedIdentities,
          inProduction: commonPayload.inProduction,
        },
        on: {
          'update-user': (user: types.UserProfileInfo) => {
            if (user.username && user.username?.length > 50) {
              ui.error(T.userEditNameTooLong);
              return;
            }
            const request = Object.assign({}, user, {
              user: user,
            });
            api.User.update(request)
              .then(() => {
                ui.success(T.userEditSuccess);
              })
              .catch(ui.apiError);
          },
          'update-password': (
            oldPassword: string,
            newPassword1: string,
            newPassword2: string,
          ) => {
            if (newPassword1 !== newPassword2) {
              ui.error(T.passwordMismatch);
              return;
            }
            api.User.changePassword({
              old_password: oldPassword,
              password: newPassword1,
            })
              .then(function () {
                ui.success(T.passwordResetResetSuccess);
              })
              .catch(ui.apiError);
          },
          'add-identity': (username: string, password: string) => {
            api.User.associateIdentity({
              username: username,
              password: password,
            })
              .then(function (data) {
                ui.success(T.profileIdentityAdded);
              })
              .catch(ui.apiError);
          },
        },
      });
    },
  });
});
