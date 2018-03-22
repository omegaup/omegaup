import admin_Support from '../components/admin/Support.vue';
import {API, UI, OmegaUp, T} from '../omegaup.js';
import Vue from 'vue';

OmegaUp.on('ready', function() {
  var adminSupport = new Vue({
    el: '#admin-support',
    render: function(createElement) {
      return createElement('omegaup-admin-support', {
        props: {
          valid: this.valid,
          username: this.username,
          verified: this.verified
        },
        on: {
          'search-email': function(email) {
            adminSupport.username = '';
            adminSupport.verified = false;
            adminSupport.valid = false;
            omegaup.API.User.statusVerified({email: email})
                .then(function(data) {
                  adminSupport.valid = data.valid;
                  adminSupport.username = data.username;
                  adminSupport.verified = data.verified;
                })
                .fail(omegaup.UI.apiError);
          },
          'verify-user': function(email) {
            omegaup.API.User.verifyEmail({usernameOrEmail: email})
                .then(function() {
                  adminSupport.verified = true;
                  omegaup.UI.success(T.userVerified);
                })
                .fail(omegaup.UI.apiError);
          },
          'reset': function() {
            adminSupport.valid = false;
            adminSupport.username = '';
            adminSupport.verified = false;
          }
        },
      });
    },
    data: {valid: false, username: '', verified: false},
    components: {
      'omegaup-admin-support': admin_Support,
    },
  });
});
