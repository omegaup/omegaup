import user_Roles from '../components/admin/Roles.vue';
import { OmegaUp } from '../omegaup-legacy';
import * as api from '../api';
import * as ui from '../ui';
import T from '../lang';
import Vue from 'vue';

OmegaUp.on('ready', function () {
  var payload = JSON.parse(document.getElementById('payload').innerText);

  var userRoles = new Vue({
    el: '#user-roles',
    render: function (createElement) {
      return createElement('omegaup-user-roles', {
        props: {
          initialRoles: this.roles,
          initialGroups: this.groups,
        },
        on: {
          'on-change-role': function (selectedRole) {
            if (selectedRole.selected) {
              api.User.addRole({
                username: payload.username,
                role: selectedRole.value.name,
              })
                .then(function () {
                  ui.success(T.userEditSuccess);
                })
                .catch(ui.apiError);
            } else {
              api.User.removeRole({
                username: payload.username,
                role: selectedRole.value.name,
              })
                .then(function () {
                  ui.success(T.userEditSuccess);
                })
                .catch(ui.apiError);
            }
          },
          'on-change-group': function (selectedGroup) {
            if (selectedGroup.selected) {
              api.User.addGroup({
                username: payload.username,
                group: selectedGroup.value.name,
              })
                .then(function () {
                  ui.success(T.userEditSuccess);
                })
                .catch(ui.apiError);
            } else {
              api.User.removeGroup({
                username: payload.username,
                group: selectedGroup.value.name,
              })
                .then(function () {
                  ui.success(T.userEditSuccess);
                })
                .catch(ui.apiError);
            }
          },
        },
      });
    },
    data: {
      roles: payload.userSystemRoles,
      groups: payload.userSystemGroups,
    },
    components: {
      'omegaup-user-roles': user_Roles,
    },
  });
});
