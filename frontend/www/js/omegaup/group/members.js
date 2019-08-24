import group_Members from '../components/group/Members.vue';
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
          countries: this.countries,
          showEditForm: this.showEditForm,
          showChangePasswordForm: this.showChangePasswordForm,
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
          'edit-identity': function(groupMembersInstance, identity) {
            groupMembersInstance.showEditForm = true;
            groupMembersInstance.showChangePasswordForm = false;
            groupMembersInstance.identity = identity;
            groupMembersInstance.username = identity.username;
          },
          'edit-identity-member': function(identityEditInstance,
                                           groupMembersInstance, identity,
                                           countryId, stateId) {
            API.Identity.update({
                          username: identity.username,
                          name: identity.name,
                          country_id: countryId,
                          state_id: stateId,
                          school_name: identity.school,
                          group_alias: groupAlias,
                          original_username: identityEditInstance.username,
                        })
                .then(function(data) {
                  UI.success(T.groupEditMemberUpdated);
                  groupMembersInstance.showEditForm = false;
                  refreshMemberList();
                })
                .fail(function(response) { UI.apiError(response); });
          },
          'change-password-identity': function(groupMembersInstance, username) {
            groupMembersInstance.showEditForm = false;
            groupMembersInstance.showChangePasswordForm = true;
            groupMembersInstance.username = username;
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
                  groupMembersInstance.showChangePasswordForm = false;
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
          cancel: function(groupMembersInstance) {
            refreshMemberList();
            groupMembersInstance.showEditForm = false;
            groupMembersInstance.showChangePasswordForm = false;
            groupMembersInstance.$el.scrollIntoView();
          },
        },
      });
    },
    data: {
      identities: [],
      identitiesCsv: [],
      groupAlias: groupAlias,
      countries: payload.countries,
      showEditForm: false,
      showChangePasswordForm: false,
    },
    components: {
      'omegaup-group-members': group_Members,
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
