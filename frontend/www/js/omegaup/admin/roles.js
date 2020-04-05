import user_Roles from '../components/admin/Roles.vue';
import { OmegaUp } from '../omegaup';
import API from '../api.js';
import * as UI from '../ui';
import T from '../lang';
import Vue from 'vue';

OmegaUp.on('ready', function() {
  var payload = JSON.parse(document.getElementById('payload').innerText);

  var userRoles = new Vue({
    el: '#user-roles',
    render: function(createElement) {
      return createElement('omegaup-user-roles', {
        props: {
          initialRoles: this.roles,
          initialGroups: this.groups,
        },
        on: {
          'on-change-role': function(selectedRole) {
            if (selectedRole.selected) {
              API.User.addRole({
                username: payload.username,
                role: selectedRole.value.name,
              })
                .then(function() {
                  UI.success(T.userEditSuccess);
                })
                .catch(UI.apiError);
            } else {
              API.User.removeRole({
                username: payload.username,
                role: selectedRole.value.name,
              })
                .then(function() {
                  UI.success(T.userEditSuccess);
                })
                .catch(UI.apiError);
            }
          },
          'on-change-group': function(selectedGroup) {
            if (selectedGroup.selected) {
              API.User.addGroup({
                username: payload.username,
                group: selectedGroup.value.name,
              })
                .then(function() {
                  UI.success(T.userEditSuccess);
                })
                .catch(UI.apiError);
            } else {
              API.User.removeGroup({
                username: payload.username,
                group: selectedGroup.value.name,
              })
                .then(function() {
                  UI.success(T.userEditSuccess);
                })
                .catch(UI.apiError);
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
