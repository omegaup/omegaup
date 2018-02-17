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
          roleNames: payload.roleNames,
          groupNames: payload.groupNames,
          roles: this.roles,
          groups: this.groups,
        },
        on: {
          'change-role': function(role, enabled) {
            if (enabled) {
              omegaup.API.User.addRole({
                                username: payload.username,
                                role: role,
                              })
                  .then(function() { omegaup.UI.success(T.userEditSuccess); })
                  .fail(omegaup.UI.apiError);
            } else {
              omegaup.API.User.removeRole({
                                username: payload.username,
                                role: role,
                              })
                  .then(function() { omegaup.UI.success(T.userEditSuccess); })
                  .fail(omegaup.UI.apiError);
            }
          },
          'change-group': function(group, enabled) {
            if (enabled) {
              omegaup.API.User.addGroup({
                                username: payload.username,
                                group: group,
                              })
                  .then(function() { omegaup.UI.success(T.userEditSuccess); })
                  .fail(omegaup.UI.apiError);
            } else {
              omegaup.API.User.removeGroup({
                                username: payload.username,
                                group: group,
                              })
                  .then(function() { omegaup.UI.success(T.userEditSuccess); })
                  .fail(omegaup.UI.apiError);
            }
          },
        },
      });
    },
    data: {
      roles: payload.systemRoles,
      groups: payload.systemGroups,
    },
    components: {
      'omegaup-user-roles': user_Roles,
    },
  });
});
