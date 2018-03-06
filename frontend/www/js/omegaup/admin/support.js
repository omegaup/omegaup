import admin_Support from '../components/admin/Support.vue';
import {API, UI, OmegaUp, T} from '../omegaup.js';
import Vue from 'vue';

OmegaUp.on('ready', function() {
  var adminSupport = new Vue({
    el: '#admin-support',
    render: function(createElement) {
      return createElement('omegaup-admin-support', {
        props: {user: this.user},
        on: {
          'search-username': function(username) {
            adminSupport.user = null;
            omegaup.API.User.profile({username: username})
                .then(function(data) { adminSupport.user = data.userinfo; })
                .fail(omegaup.UI.apiError);
          },
          'verify-user': function(username) {
            omegaup.API.User.verifyEmail({usernameOrEmail: username})
                .then(function() {
                  adminSupport.user.verified = '1';
                  omegaup.UI.success(T.userVerified);
                })
                .fail(omegaup.UI.apiError);
          },
          'reset': function() { adminSupport.user = null; }
        },
      });
    },
    data: {user: null},
    components: {
      'omegaup-admin-support': admin_Support,
    },
  });
});
