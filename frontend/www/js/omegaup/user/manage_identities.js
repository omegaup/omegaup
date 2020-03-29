import user_ManageIdentities from '../components/user/ManageIdentities.vue';
import Vue from 'vue';
import { OmegaUp } from '../omegaup';
import API from '../api.js';
import * as UI from '../ui';
import T from '../lang';

OmegaUp.on('ready', function() {
  let manageIdentities = new Vue({
    el: '#manage-identities',
    render: function(createElement) {
      return createElement('omegaup-user-manage-identities', {
        props: {
          identities: this.identities,
        },
        on: {
          'add-identity': function(username, password) {
            API.User.associateIdentity({
              username: username,
              password: password,
            })
              .then(function(data) {
                refreshIdentityList();
                UI.success(T.profileIdentityAdded);
              })
              .catch(UI.apiError);
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
      .then(function(data) {
        manageIdentities.identities = data.identities;
      })
      .catch(UI.apiError);
  }

  refreshIdentityList();
});
