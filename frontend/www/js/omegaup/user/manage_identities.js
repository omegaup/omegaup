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
          'add-identity': function(username, password) {
            API.User.associateIdentity({
                      usernameOrEmail: username,
                      password: password,
                    })
                .then(function(data) {
                  refreshIdentityList();
                  UI.success(T.profileIdentityAdded);
                  manageIdentities.$children[0].reset();
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
    API.User.listAssociatedIdentities({})
        .then(function(data) { manageIdentities.identities = data.identities; })
        .fail(UI.apiError);
  }

  refreshIdentityList();
});
