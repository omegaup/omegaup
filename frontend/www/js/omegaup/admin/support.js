import admin_Support from '../components/admin/Support.vue';
import {API, UI, OmegaUp, T} from '../omegaup.js';
import Vue from 'vue';

OmegaUp.on('ready', function() {
  let adminSupport = new Vue({
    el: '#admin-support',
    render: function(createElement) {
      return createElement('omegaup-admin-support', {
        props: {
          username: this.username,
          link: this.link,
          verified: this.verified,
        },
        on: {
          'search-email': function(email) {
            adminSupport.username = null;
            adminSupport.link = null;
            adminSupport.verified = false;
            omegaup.API.User.extraInformation({email: email})
                .then(function(data) {
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
          'generate-token': function(email) {
            omegaup.API.Reset.generateToken({
                               email: email,
                             })
                .then(function(data) {
                  omegaup.UI.success(
                      T.passwordResetTokenWasGeneratedSuccessfully);
                  adminSupport.link = data.link;
                })
                .fail(omegaup.UI.apiError);
          },
          'copy-token': function() {
            omegaup.UI.success(T.passwordResetLinkCopiedToClipboard);
          },
          'reset': function() {
            adminSupport.username = null;
            adminSupport.link = null;
            adminSupport.verified = false;
          }
        },
      });
    },
    data: {username: null, link: null, verified: false},
    components: {
      'omegaup-admin-support': admin_Support,
    },
  });
});
