import group_Members from '../components/group/Members.vue';
import identity_Edit from '../components/identity/Edit.vue';
import {OmegaUp, UI, T, API} from '../omegaup.js';
import Vue from 'vue';

OmegaUp.on('ready', function() {
  const formData = document.querySelector('#form-data');
  const groupAlias = formData.getAttribute('data-alias');
  const payload = JSON.parse(document.getElementById('payload').innerText);

  let groupMembers = new Vue({
    el: '#group-members div.list',
    render: function(createElement) {
      return createElement('omegaup-group-members', {
        props: {
          identities: this.identities,
          identitiesCsv: this.identitiesCsv,
          groupAlias: this.groupAlias,
        },
        on: {
          'add-member': function(groupMembersInstance, username) {
            API.Group.addUser({
                       group_alias: groupAlias,
                       usernameOrEmail: username,
                     })
                .then(function(data) {
                  refreshMemberList();
                  UI.success(T.groupEditMemberAdded);
                  groupMembersInstance.reset();
                })
                .fail(UI.apiError);
          },
          'edit-identity': function(identity) {
            groupIdentities.show = true;
            groupIdentities.identity = identity;
            groupIdentities.username = identity.username;
            groupIdentities.$el.scrollIntoView();
          },
          'change-password-identity-member': function(
              groupMembersInstance, username, newPassword, newPasswordRepeat) {
            if (newPassword !== newPasswordRepeat) {
              $('.modal').modal('hide');
              UI.error(T.userPasswordMustBeSame);
              return;
            }

            API.Identity.changePassword({
                          group_alias: groupAlias,
                          password: newPassword,
                          username: username,
                        })
                .then(function(data) {
                  refreshMemberList();
                  UI.success(T.groupEditMemberPasswordUpdated);
                  groupMembersInstance.reset();
                })
                .fail(function(response) {
                  $('.modal').modal('hide');
                  UI.apiError(response);
                });
          },
          remove: function(username) {
            API.Group.removeUser(
                         {group_alias: groupAlias, usernameOrEmail: username})
                .then(function(data) {
                  refreshMemberList();
                  UI.success(T.groupEditMemberRemoved);
                })
                .fail(UI.apiError);
          },
        },
      });
    },
    data: {
      identities: [],
      identitiesCsv: [],
      groupAlias: groupAlias,
    },
    components: {
      'omegaup-group-members': group_Members,
    },
  });

  let groupIdentities = new Vue({
    el: '#group-members div.form',
    render: function(createElement) {
      return createElement('omegaup-identity-edit', {
        props: {
          show: this.show,
          identity: this.identity,
          username: this.username,
          countries: this.countries,
        },
        on: {
          'edit-identity-member': function(identity, countryId, stateId) {
            API.Identity.update({
                          username: identity.username,
                          name: identity.name,
                          country_id: countryId,
                          state_id: stateId,
                          school_name: identity.school,
                          group_alias: groupAlias,
                          original_username: groupIdentities.username,
                        })
                .then(function(data) {
                  UI.success(T.groupEditMemberUpdated);
                  groupIdentities.show = false;
                })
                .fail(function(response) { UI.apiError(response); });
          },
          cancel: function() {
            refreshMemberList();
            groupIdentities.show = false;
          },
        },
      });
    },
    data: {
      show: false,
      identity: {},
      username: '',
      countries: payload.countries,
    },
    components: {
      'omegaup-identity-edit': identity_Edit,
    },
  });

  function refreshMemberList() {
    $('.modal').modal('hide');
    API.Group.members({group_alias: groupAlias})
        .then(function(data) {
          groupMembers.identities = [];
          groupMembers.identitiesCsv = [];
          for (let identity of data.identities) {
            if (identity.username.split(':').length == 1) {
              groupMembers.identities.push(identity);
            } else {
              groupMembers.identitiesCsv.push(identity);
            }
          }
        })
        .fail(UI.apiError);
  }

  refreshMemberList();
});
