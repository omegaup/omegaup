import user_Roles from '../components/admin/Roles.vue';
import { omegaup, OmegaUp } from '../omegaup';
import * as api from '../api';
import * as ui from '../ui';
import T from '../lang';
import Vue from 'vue';
import { types } from '../api_types';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.UserRolesPayload();

  new Vue({
    el: '#main-container',
    components: {
      'omegaup-user-roles': user_Roles,
    },
    render: function (createElement) {
      return createElement('omegaup-user-roles', {
        props: {
          roles: payload.userSystemRoles,
          groups: payload.userSystemGroups,
        },
        on: {
          'change-role': (selectedRole: omegaup.Selectable<types.UserRole>) => {
            if (selectedRole.selected) {
              api.User.addRole({
                username: payload.username,
                role: selectedRole.value.name,
              })
                .then(() => {
                  ui.success(T.userEditSuccess);
                })
                .catch(ui.apiError);
            } else {
              api.User.removeRole({
                username: payload.username,
                role: selectedRole.value.name,
              })
                .then(() => {
                  ui.success(T.userEditSuccess);
                })
                .catch(ui.apiError);
            }
          },
          'change-group': (selectedGroup: omegaup.Selectable<types.Group>) => {
            if (selectedGroup.selected) {
              api.User.addGroup({
                username: payload.username,
                group: selectedGroup.value.name,
              })
                .then(() => {
                  ui.success(T.userEditSuccess);
                })
                .catch(ui.apiError);
            } else {
              api.User.removeGroup({
                username: payload.username,
                group: selectedGroup.value.name,
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
