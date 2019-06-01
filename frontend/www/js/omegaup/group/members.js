import group_Members from '../components/group/Members.vue';
import {OmegaUp, UI, T, API} from '../omegaup.js';
import Vue from 'vue';

OmegaUp.on('ready', function() {
  const formData = document.querySelector('#form-data');
  const groupAlias = formData.getAttribute('data-alias');
  let groupMembers = new Vue({
    el: '#group_members',
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
