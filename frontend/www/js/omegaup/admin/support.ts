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
        email: null as null | string,
        contestAlias: null as null | string,
        contestTitle: null as null | string,
        contestFound: false,
        isContestRecommended: false,
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
          email: this.email,
          contestAlias: this.contestAlias,
          contestTitle: this.contestTitle,
          contestFound: this.contestFound,
          isContestRecommended: this.isContestRecommended,
          maintenanceEnabled: payload.maintenanceMode.enabled,
          maintenanceMessageEs: payload.maintenanceMode.message_es || '',
          maintenanceMessageEn: payload.maintenanceMode.message_en || '',
          maintenanceMessagePt: payload.maintenanceMode.message_pt || '',
          maintenanceType: payload.maintenanceMode.type || 'info',
        },
        on: {
          'search-username-or-email': (usernameOrEmail: string): void => {
            adminSupport.username = null;
            adminSupport.link = null;
            adminSupport.lastLogin = null;
            adminSupport.birthDate = null;
            adminSupport.verified = false;
            adminSupport.roles = [];
            adminSupport.email = null;
            api.User.extraInformation({ usernameOrEmail })
              .then((data) => {
                adminSupport.username = data.username;
                adminSupport.verified = data.verified;
                adminSupport.lastLogin = data.last_login ?? null;
                adminSupport.birthDate = data.birth_date ?? null;
                adminSupport.roles = data.roles ?? [];
                adminSupport.email = data.email ?? null;
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
          'verify-user': (usernameOrEmail: string): void => {
            api.User.verifyEmail({ usernameOrEmail })
              .then(() => {
                adminSupport.verified = true;
                ui.success(T.userVerified);
              })
              .catch(ui.apiError);
          },
          'generate-token': (email: string): void => {
            api.Reset.generateToken({ email })
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
            adminSupport.email = null;
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
          'search-contest': (contestAlias: string): void => {
            adminSupport.contestFound = false;
            adminSupport.contestTitle = null;
            adminSupport.isContestRecommended = false;

            api.Contest.details({ contest_alias: contestAlias })
              .then((data) => {
                adminSupport.contestAlias = contestAlias;
                adminSupport.contestTitle = data.title;
                adminSupport.contestFound = true;
                adminSupport.isContestRecommended = data.recommended;
              })
              .catch(ui.apiError);
          },
          'toggle-recommended': (isNowRecommended: boolean): void => {
            api.Contest.setRecommended({
              contest_alias: adminSupport.contestAlias,
              value: isNowRecommended,
            })
              .then(() => {
                ui.success(
                  isNowRecommended
                    ? T.supportContestSetAsRecommended
                    : T.supportContestRemovedFromRecommended,
                );
              })
              .catch(ui.apiError);
          },
          'reset-contest': () => {
            adminSupport.contestAlias = null;
            adminSupport.contestTitle = null;
            adminSupport.contestFound = false;
            adminSupport.isContestRecommended = false;
          },
          'toggle-maintenance': (enabled: boolean): void => {
            if (!enabled) {
              // If disabling, save immediately
              api.Admin.setMaintenanceMode({
                enabled: false,
                message_es: '',
                message_en: '',
                message_pt: '',
                type: 'info',
              })
                .then(() => {
                  ui.success(T.maintenanceModeInactive);
                })
                .catch(ui.apiError);
            }
          },
          'save-maintenance': (data: {
            enabled: boolean;
            message_es: string;
            message_en: string;
            message_pt: string;
            type: string;
          }): void => {
            api.Admin.setMaintenanceMode({
              enabled: data.enabled,
              message_es: data.message_es,
              message_en: data.message_en,
              message_pt: data.message_pt,
              type: data.type,
            })
              .then(() => {
                ui.success(T.maintenanceModeActive);
              })
              .catch(ui.apiError);
          },
        },
      });
    },
  });
});
