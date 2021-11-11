import user_ManageIdentities from '../components/user/ManageIdentities.vue';
import Vue from 'vue';
import { OmegaUp } from '../omegaup-legacy';
import * as api from '../api';
import * as ui from '../ui';
import T from '../lang';

OmegaUp.on('ready', function () {
  let manageIdentities = new Vue({
    el: '#manage-identities',
    render: function (createElement) {
      return createElement('omegaup-user-manage-identities', {
        props: {
          identities: this.identities,
        },
        on: {
          'add-identity': function ({ username, password }) {
            api.User.associateIdentity({
              username: username,
              password: password,
            })
              .then(function (data) {
                refreshIdentityList();
                ui.success(T.profileIdentityAdded);
              })
              .catch(ui.apiError);
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
    api.User.listAssociatedIdentities({})
      .then(function (data) {
        manageIdentities.identities = data.identities;
      })
      .catch(ui.apiError);
  }

  refreshIdentityList();
});
