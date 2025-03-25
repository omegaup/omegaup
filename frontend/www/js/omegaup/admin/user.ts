import admin_User from '../components/admin/User.vue';
import { OmegaUp } from '../omegaup';
import * as api from '../api';
import * as ui from '../ui';
import T from '../lang';
import Vue from 'vue';
import { types } from '../api_types';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.UserDetailsPayload();

  const adminUser = new Vue({
    el: '#main-container',
    components: {
      'omegaup-admin-user': admin_User,
    },
    data: () => ({
      experiments: payload.experiments,
      roles: payload.systemRoles,
      verified: payload.verified,
    }),
    render: function (createElement) {
      return createElement('omegaup-admin-user', {
        props: {
          emails: payload.emails,
          experiments: this.experiments,
          systemExperiments: payload.systemExperiments,
          roleNames: payload.roleNames,
          roles: this.roles,
          username: payload.username,
          verified: this.verified,
        },
        on: {
          'change-experiment': (experiment: {
            selected: boolean;
            value: types.Experiment;
          }): void => {
            if (experiment.selected) {
              api.User.addExperiment({
                username: payload.username,
                experiment: experiment.value.name,
              })
                .then(() => {
                  ui.success(T.userEditSuccess);
                })
                .catch(ui.apiError);
            } else {
              api.User.removeExperiment({
                username: payload.username,
                experiment: experiment.value.name,
              })
                .then(() => {
                  ui.success(T.userEditSuccess);
                })
                .catch(ui.apiError);
            }
          },
          'change-role': (role: {
            selected: boolean;
            value: types.UserRole;
          }): void => {
            if (role.selected) {
              api.User.addRole({
                username: payload.username,
                role: role.value.name,
              })
                .then(() => {
                  ui.success(T.userEditSuccess);
                })
                .catch(ui.apiError);
            } else {
              api.User.removeRole({
                username: payload.username,
                role: role.value.name,
              })
                .then(() => {
                  ui.success(T.userEditSuccess);
                })
                .catch(ui.apiError);
            }
          },
          'verify-user': (): void => {
            api.User.verifyEmail({ usernameOrEmail: payload.username })
              .then(() => {
                adminUser.verified = true;
              })
              .catch(ui.apiError);
          },
        },
      });
    },
  });
});
