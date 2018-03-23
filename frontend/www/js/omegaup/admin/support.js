import admin_Support from '../components/admin/Support.vue';
import {API, UI, OmegaUp, T} from '../omegaup.js';
import Vue from 'vue';

OmegaUp.on('ready', function() {
  let adminSupport = new Vue({
    el: '#admin-support',
    render: function(createElement) {
      return createElement('omegaup-admin-support', {
        props: {link: this.link, username: this.username},
        on: {
          'search-email': function(email) {
            adminSupport.link = null;
            adminSupport.username = null;
            omegaup.API.User.passwordChangeRequest({email: email})
                .then(function(data) { adminSupport.username = data.username; })
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
            adminSupport.link = '';
            adminSupport.username = null;
          }
        },
      });
    },
    data: {link: null, username: null},
    components: {
      'omegaup-admin-support': admin_Support,
    },
  });
});
