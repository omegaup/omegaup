import user_Roles from '../components/admin/Roles.vue';
import {API, UI, OmegaUp, T} from '../omegaup.js';
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
              omegaup.API.User.addRole({
                                username: payload.username,
                                role: selectedRole.value.name,
                              })
                  .then(function() { omegaup.UI.success(T.userEditSuccess); })
                  .fail(omegaup.UI.apiError);
            } else {
              omegaup.API.User.removeRole({
                                username: payload.username,
                                role: selectedRole.value.name,
                              })
                  .then(function() { omegaup.UI.success(T.userEditSuccess); })
                  .fail(omegaup.UI.apiError);
            }
          },
          'on-change-group': function(selectedGroup) {
            if (selectedGroup.selected) {
              omegaup.API.User.addGroup({
                                username: payload.username,
                                group: selectedGroup.value.name,
                              })
                  .then(function() { omegaup.UI.success(T.userEditSuccess); })
                  .fail(omegaup.UI.apiError);
            } else {
              omegaup.API.User.removeGroup({
                                username: payload.username,
                                group: selectedGroup.value.name,
                              })
                  .then(function() { omegaup.UI.success(T.userEditSuccess); })
                  .fail(omegaup.UI.apiError);
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
