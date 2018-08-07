import user_ManageIdentities from '../components/user/ManageIdentities.vue';
import Vue from 'vue';
import {API, UI, OmegaUp, T} from '../omegaup.js';

OmegaUp.on('ready', function() {
  let manageIdentities = new Vue({
    el: '#manage-identities',
    render: function(createElement) {
      return createElement('omegaup-user-manage-identities', {
        props: {
          T: T,
          identities: this.identities,
        },
        on: {
          'add-identity': function(username) {
            API.User.addIdentity({
                      usernameOrEmail: username,
                    })
                .then(function(data) {
                  refreshIdentityList();
                  UI.success(T.profileIdentityAdded);
                  manageIdentities.$children[0].reset();
                })
                .fail(UI.apiError);
          },
          'mark-as-default': function(username) {
            API.User.markAsDefault({
                      usernameOrEmail: username,
                    })
                .then(function(data) {
                  UI.success(T.profileIdentityMarkedAsDefault);
                })
                .fail(UI.apiError);
          },
          remove: function(identity) {
            API.User.removeIdentity({usernameOrEmail: identity.username})
                .then(function(data) {
                  refreshIdentityList();
                  UI.success(T.profileIdentityRemoved);
                })
                .fail(UI.apiError);
          },
        },
      });
    },
    data: {
      identities: [],
    },
    components: {
      'omegaup-user-manage-identities': user_ManageIdentities,
    },
  });

  function refreshIdentityList() {
    API.User.listIdentities({})
        .then(function(data) { manageIdentities.identities = data.identities; })
        .fail(UI.apiError);
  }

  refreshIdentityList();
});
