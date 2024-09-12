import admin_Support, {
  UpdateEmailRequest,
} from '../components/admin/Support.vue';
import { OmegaUp } from '../omegaup';
import * as api from '../api';
import * as ui from '../ui';
import T from '../lang';
import Vue from 'vue';
import { types } from '../api_types';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.SupportDetailsPayload();

  const adminSupport = new Vue({
    el: '#main-container',
    components: {
      'omegaup-admin-support': admin_Support,
    },
    data: () => {
      return {
        username: null as null | string,
        link: null as null | string,
        verified: false,
        lastLogin: null as null | Date,
        birthDate: null as null | Date,
        roles: [] as Array<string>,
        storedEmail: null as null | string,
      };
    },
    render: function (createElement) {
      return createElement('omegaup-admin-support', {
        props: {
          username: this.username,
          link: this.link,
          verified: this.verified,
          lastLogin: this.lastLogin,
          birthDate: this.birthDate,
          roleNamesWithDescription: payload.roleNamesWithDescription,
          roles: this.roles,
          storedEmail: this.storedEmail,
        },
        on: {
          'search-email': (email: string): void => {
            adminSupport.username = null;
            adminSupport.link = null;
            adminSupport.lastLogin = null;
            adminSupport.birthDate = null;
            adminSupport.verified = false;
            adminSupport.roles = [];
            adminSupport.storedEmail = null;
            api.User.extraInformation({ email: email })
              .then((data) => {
                adminSupport.username = data.username;
                adminSupport.verified = data.verified;
                adminSupport.lastLogin = data.last_login ?? null;
                adminSupport.birthDate = data.birth_date ?? null;
                adminSupport.roles = data.roles ?? [];
                adminSupport.storedEmail = data.email;
              })
              .catch(ui.apiError);
          },
          'update-email': (request: UpdateEmailRequest) => {
            api.User.updateMainEmail({
              originalEmail: request.email,
              email: request.newEmail,
            })
              .then(() => {
                ui.success(T.adminSupportEmailUpdatedSuccessfully);
              })
              .catch(ui.apiError);
          },
          'verify-user': (email: string): void => {
            api.User.verifyEmail({ usernameOrEmail: email })
              .then(() => {
                adminSupport.verified = true;
                ui.success(T.userVerified);
              })
              .catch(ui.apiError);
          },
          'generate-token': (email: string): void => {
            api.Reset.generateToken({
              email: email,
            })
              .then((data) => {
                ui.success(T.passwordResetTokenWasGeneratedSuccessfully);
                adminSupport.link = data.link;
              })
              .catch(ui.apiError);
          },
          reset: () => {
            adminSupport.username = null;
            adminSupport.link = null;
            adminSupport.verified = false;
            adminSupport.lastLogin = null;
            adminSupport.birthDate = null;
            adminSupport.roles = [];
            adminSupport.storedEmail = null;
          },
          'change-role': (role: {
            selected: boolean;
            value: types.UserRole;
          }): void => {
            if (role.selected) {
              api.User.addRole({
                username: adminSupport.username,
                role: role.value.name,
              })
                .then(() => {
                  ui.success(T.userEditSuccess);
                })
                .catch(ui.apiError);
            } else {
              api.User.removeRole({
                username: adminSupport.username,
                role: role.value.name,
              })
                .then(() => {
                  ui.success(T.userEditSuccess);
                })
                .catch(ui.apiError);
            }
          },
        },
      });
    },
  });
});
