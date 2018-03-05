import admin_Support from '../components/admin/Support.vue';
import {API, UI, OmegaUp, T} from '../omegaup.js';
import Vue from 'vue';

OmegaUp.on('ready', function() {
  var adminSupport = new Vue({
    el: '#admin-support',
    render: function(createElement) {
      return createElement('omegaup-admin-support', {
        props: {valid: this.valid, verified: this.verified},
        on: {
          'search-username': function(username) {
            adminSupport.valid = false;
            omegaup.API.User.profile({username: username})
                .then(function(data) {
                  adminSupport.valid = true;
                  adminSupport.verified = data.userinfo.verified == '1';
                })
                .fail(omegaup.UI.apiError);
          },
          'verify-user': function(username) {
            omegaup.API.User.verifyEmail({usernameOrEmail: username})
                .then(function() {
                  adminSupport.verified = true;
                  omegaup.UI.success(T.userVerified);
                })
                .fail(omegaup.UI.apiError);
          },
          'reset': function() { adminSupport.valid = false; }
        },
      });
    },
    data: {valid: false, verified: false},
    components: {
      'omegaup-admin-support': admin_Support,
    },
  });
});
