import group_Members from '../components/group/Members.vue';
import {OmegaUp, UI, T, API} from '../omegaup.js';
import Vue from 'vue';

OmegaUp.on('ready', function() {
  const formData = document.querySelector('#form-data');
  const groupAlias = formData.getAttribute('data-alias');
  const payload = JSON.parse(document.getElementById('payload').innerText);
  let groupMembers = new Vue({
    el: '#group_members',
    render: function(createElement) {
      return createElement('omegaup-group-members', {
        props: {
          identities: this.identities,
          identitiesCsv: this.identitiesCsv,
          countries: this.countries,
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
          'edit-identity-member': function(groupMembersInstance, identity,
                                           username, name, selectedCountry,
                                           selectedState, school) {
            API.Identity.update({
                          username: username,
                          name: name,
                          country_id: selectedCountry,
                          state_id: selectedState,
                          school_name: school,
                          group_alias: groupAlias,
                          original_identity: identity,
                        })
                .then(function(data) {
                  refreshMemberList();
                  UI.success(T.groupEditMemberUpdated);
                  groupMembersInstance.reset();
                })
                .fail(UI.apiError);
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
      countries: payload.countries,
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
