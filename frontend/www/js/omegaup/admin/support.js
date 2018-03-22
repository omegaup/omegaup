import admin_Support from '../components/admin/Support.vue';
import {API, UI, OmegaUp, T} from '../omegaup.js';
import Vue from 'vue';

OmegaUp.on('ready', function() {
  let adminSupport = new Vue({
    el: '#admin-support',
    render: function(createElement) {
      return createElement('omegaup-admin-support', {
        props: {
          valid: this.valid,
          link: this.link,
          password_change_request: this.password_change_request,
          username: this.username
        },
        on: {
          'search-email': function(email) {
            adminSupport.valid = false;
            adminSupport.password_change_request = false;
            adminSupport.link = '';
            adminSupport.username = '';
            omegaup.API.User.passwordChangeRequest({email: email})
                .then(function(data) {
                  adminSupport.valid = true;
                  adminSupport.password_change_request =
                      data.password_change_request;
                  adminSupport.username = data.username;
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
            adminSupport.valid = false;
            adminSupport.password_change_request = false;
            adminSupport.link = '';
            adminSupport.username = '';
          }
        },
      });
    },
    data:
        {valid: false, password_change_request: false, link: '', username: ''},
    components: {
      'omegaup-admin-support': admin_Support,
    },
  });
});
