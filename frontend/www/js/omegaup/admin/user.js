import admin_User from '../components/admin/User.vue';
import {API, UI, OmegaUp, T} from '../omegaup.js';
import Vue from 'vue';

OmegaUp.on('ready', function() {
  var payload = JSON.parse(document.getElementById('payload').innerText);

  var adminUser = new Vue({
    el: '#admin-user',
    render: function(createElement) {
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
          'change-experiment': function(experiment) {
            if (experiment.selected) {
              omegaup.API.User.addExperiment({
                                username: payload.username,
                                experiment: experiment.value.name,
                              })
                  .then(function() { omegaup.UI.success(T.userEditSuccess); })
                  .fail(omegaup.UI.apiError);
            } else {
              omegaup.API.User.removeExperiment({
                                username: payload.username,
                                experiment: experiment.value.name,
                              })
                  .then(function() { omegaup.UI.success(T.userEditSuccess); })
                  .fail(omegaup.UI.apiError);
            }
          },
          'change-role': function(role) {
            if (role.selected) {
              omegaup.API.User.addRole({
                                username: payload.username,
                                role: role.value.name,
                              })
                  .then(function() { omegaup.UI.success(T.userEditSuccess); })
                  .fail(omegaup.UI.apiError);
            } else {
              omegaup.API.User.removeRole({
                                username: payload.username,
                                role: role.value.name,
                              })
                  .then(function() { omegaup.UI.success(T.userEditSuccess); })
                  .fail(omegaup.UI.apiError);
            }
          },
          'verify-user': function() {
            omegaup.API.User.verifyEmail({usernameOrEmail: payload.username})
                .then(function() { adminUser.verified = true; })
                .fail(omegaup.UI.apiError);
          },
        },
      });
    },
    data: {
      experiments: payload.experiments,
      roles: payload.systemRoles,
      verified: payload.verified,
    },
    components: {
      'omegaup-admin-user': admin_User,
    },
  });
});
